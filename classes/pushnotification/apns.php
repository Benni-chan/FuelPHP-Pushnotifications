<?php
/**
 * Pushnotification is a package for FuelPHP to send pushnotifications
 * to smartphones.
 *
 * @package    pushnotification
 * @version    1.0
 * @author     Benjamin Waller
 * @license    GPLv3
 * @copyright  2013 Benjamin Waller
 * @link       https://github.com/Benni-chan/FuelPHP-Pushnotifications
 */

 #  THIS IS BASED ON
 ##
 ##     Copyright (c) 2010 Benjamin Ortuzar Seconde <bortuzar@gmail.com>
 ##
 ##     This file is part of APNS.
 ##
 ##     APNS is free software: you can redistribute it and/or modify
 ##     it under the terms of the GNU Lesser General Public License as
 ##     published by the Free Software Foundation, either version 3 of
 ##     the License, or (at your option) any later version.
 ##
 ##     APNS is distributed in the hope that it will be useful,
 ##     but WITHOUT ANY WARRANTY; without even the implied warranty of
 ##     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 ##     GNU General Public License for more details.
 ##
 ##     You should have received a copy of the GNU General Public License
 ##     along with APNS.  If not, see <http://www.gnu.org/licenses/>.
 ##
 ##
 ## $Id: Apns.php 168 2010-08-28 01:24:04Z Benjamin Ortuzar Seconde $
 ##
 #######################################################################


namespace Pushnotification;

class ApnsConfigError extends \FuelException {}
class ApnsConnectionError extends \FuelException {}

class Pushnotification_Apns
{
	/*******************************
		PROTECTED : */

	
	
		protected $server;
		protected $key_cert_file_path;
		protected $passphrase;
		protected $push_stream;
		protected $feedback_stream;
		protected $timeout;
		protected $id_counter = 0;
		protected $expiry;
		protected $allow_reconnect = true;
		protected $additional_data = array();
		protected $apn_resonses = array(
			0 => 'No errors encountered',
			1 => 'Processing error',
			2 => 'Missing device token',
			3 => 'Missing topic',
			4 => 'Missing payload',
			5 => 'Invalid token size',
			6 => 'Invalid topic size',
			7 => 'Invalid payload size',
			8 => 'Invalid token',
			255 => 'None (unknown)',
		);
	
		private $connection_start;
	
		public $error;
		public $payload_method = 'simple';
	
		/**
		* Connects to the server with the certificate and passphrase
		*
		* @return <void>
		*/
		protected function connect($server) {

			$ctx = stream_context_create();
			stream_context_set_option($ctx, 'ssl', 'local_cert', $this->key_cert_file_path);
			stream_context_set_option($ctx, 'ssl', 'passphrase', $this->passphrase);
			
			$stream = stream_socket_client($server, $err, $errstr, $this->timeout, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
			#log_message('debug',"APN: Maybe some errors: $err: $errstr");
			
			
			if (!$stream) {
			
				if ($err)
					throw new ApnsConnectionError("APN Failed to connect: $err $errstr");
				else
					throw new ApnsConnectionError("APN Failed to connect: Something wrong with context");
				
				return false;
			}
			else {
				stream_set_timeout($stream,20);
				#log_message('debug',"APN: Opening connection to: {$server}");
				return $stream;
			}
		}
	
	
	
		/**
		* Generates the payload
		* 
		* @param <string> $message
		* @param <int> $badge
		* @param <string> $sound
		* @return <string>
		*/
		protected function generate_payload($message, $badge = NULL, $sound = NULL) {

		   $body = array();

		   // additional data
				if (is_array($this->additional_data) && count($this->additional_data))
				{
					$body = $this->additional_data;
				}
	   
			//message
				$body['aps'] = array('alert' => $message);

			//badge
				if ($badge)
					$body['aps']['badge'] = $badge;
			
				if ($badge == 'clear')
					$body['aps']['badge'] = 0;

			 //sound
				if ($sound)
					$body['aps']['sound'] = $sound;
				

		   $payload = json_encode($body);
		   #log_message('debug',"APN: generate_payload '$payload'");
		   return $payload;
		}
	
	
	
		/**
		 * Writes the contents of payload to the file stream
		 * 
		 * @param <string> $device_token
		 * @param <string> $payload
		 */
		protected function send_payload_simple($device_token, $payload){

			$this->id_counter++;		

			#log_message('debug',"APN: send_payload_simple to '$device_token'");

			$msg = chr(0) 									// command
				. pack('n',32)									// token length
				. pack('H*', $device_token)						// device token
				. pack('n',strlen($payload))					// payload length
				. $payload;										// payload
		
			#log_message('debug',"APN: payload: '$msg'");
			#log_message('debug',"APN: payload length: '".strlen($msg)."'");
			$result = fwrite($this->push_stream, $msg, strlen($msg));
		
			if ($result)
				return true;
			else
				return false;
		}
	
	
		/**
		 * Writes the contents of payload to the file stream with enhanced api (expiry, debug)
		 * 
		 * @param <string> $device_token
		 * @param <string> $payload
		 */
		protected function send_payload_enhance($device_token, $payload, $expiry = 86400) {
		
			if (!is_resource($this->push_stream))
				$this->reconnect_push();
		
		
			$this->id_counter++;

			#log_message('debug',"APN: send_payload_enhance to '$device_token'");

			$msg = chr(1)										// command
				. pack("N",time())								// identifier
				. pack("N",time() + $expiry)					// expiry
				. pack('n',32)									// token length
				. pack('H*', $device_token)						// device token
				. pack('n',strlen($payload))					// payload length
				. $payload;
			
			#$response = @unpack('Ccommand/Nidentifier/Nexpiry/ntoken_length/H*device_token/npayload_length', $msg);// payload
		
			#log_message('debug',"APN: unpack: '".print_r($response,true)."'");
			#log_message('debug',"APN: payload: '$msg'");
			#log_message('debug',"APN: payload length: '".strlen($msg)."'");
			$result = fwrite($this->push_stream, $msg, strlen($msg));
		
			if ($result)
			{
				return $this->get_payload_statuses();
			}
	
			return false;
		}
	
	
		protected function timeout_soon($left_seconds = 5)
		{
			$t = ( (round(microtime(true) - $this->connection_start) >= ($this->timeout - $left_seconds)));
			return (bool)$t;
		}
	
	
	
	/* 	PROTECTED ^ 
	*******************************/

        
		/**
		 * Connects to the APNS server with a certificate and a passphrase
		 *
		 * @param <string> $server
		 * @param <string> $key_cert_file_path
		 * @param <string> $passphrase
		 */
		function __construct() {
			
			$this->push_server = \Config::get('pushnotification.Pushnotification_Apns.use_sandbox', 'true') ? \Config::get('pushnotification.Pushnotification_Apns.sandbox.push_gateway', '') : \Config::get('pushnotification.Pushnotification_Apns.production.push_gateway', '');
			$this->feedback_server = \Config::get('pushnotification.Pushnotification_Apns.use_sandbox', 'true') ? \Config::get('pushnotification.Pushnotification_Apns.sandbox.feedback_gateway', '') : \Config::get('pushnotification.Pushnotification_Apns.production.feedback_gateway', '');
		
			$this->key_cert_file_path = \Config::get('pushnotification.Pushnotification_Apns.use_sandbox', 'true') ? \Config::get('pushnotification.Pushnotification_Apns.sandbox.certificate', '') : \Config::get('pushnotification.Pushnotification_Apns.production.certificate', '');
			$this->passphrase = \Config::get('pushnotification.Pushnotification_Apns.use_sandbox', 'true') ? \Config::get('pushnotification.Pushnotification_Apns.sandbox.certificate_passphrase', '') : \Config::get('pushnotification.Pushnotification_Apns.production.certificate_passphrase', '');
			
			$this->timeout = \Config::get('pushnotification.Pushnotification_Apns.production.timeout', '80');
			$this->expiry = \Config::get('pushnotification.Pushnotification_Apns.production.expiry', '86400');
			
			if(!file_exists($this->key_cert_file_path))
			{
				throw new ApnsConfigError('APN Failed to connect: APN Permission file not found');
			}
		}
		
		/**
		 * Public connector to push service
		 */
		public function connect_to_push()
		{
			if (!$this->push_stream or !is_resource($this->push_stream))
			{
				#log_message('debug',"APN: connect_to_push");
		
				$this->push_stream = $this->connect($this->push_server);
			
				if ($this->push_stream)
				{
					$this->connection_start = microtime(true);
					//stream_set_blocking($this->push_stream,0);
				}
			}
		
			return $this->push_stream;
		}
	
		/**
		 * Public connector to feedback service
		 */
		public function connect_to_feedback()
		{
			#log_message('info',"APN: connect_to_feedback");
			return $this->feedback_stream = $this->connect($this->feedback_server);
		}
	
		/**
		 * Public diconnector to push service
		 */
		function disconnect_push()
		{
			#log_message('debug',"APN: disconnect_push");
			if ($this->push_stream && is_resource($this->push_stream))
			{
				$this->connection_start = 0;
				return @fclose($this->push_stream);
			}
			else
				return true;
		}
	
		/**
		 * Public disconnector to feedback service
		 */
		function disconnect_feedback()
		{
			#log_message('info',"APN: disconnect_feedback");
			if ($this->feedback_stream && is_resource($this->feedback_stream))
				return @fclose($this->feedback_stream);
			else
				return true;
		}
	
		function reconnect_push()
		{
			$this->disconnect_push();
				
			if ($this->connect_to_push())
			{
				#log_message('debug',"APN: reconnect");
				return true;
			}
			else
			{
				#log_message('debug',"APN: cannot reconnect");
				return false;
			}
		}
	
		function try_reconnect_push()
		{
			if ($this->allow_reconnect)
			{
				if($this->timeout_soon())
				{
					return $this->reconnect_push();
				}
			}
		
			return false;
		}
	
        
		/**
		 * Sends a message to device
		 * 
		 * @param <string> $device_token
		 * @param <string> $message
		 * @param <int> $badge
		 * @param <string> $sound
		 */
		public function send_message($device_token, $message, $badge = NULL, $sound = NULL, $expiry = '')
		{
			$this->error = '';
		
			if (!ctype_xdigit($device_token))
			{
				#log_message('debug',"APN: Error - '$device_token' token is invalid. Provided device token contains not hexadecimal chars");
				$this->error = 'Invalid device token. Provided device token contains not hexadecimal chars';
				return false;
			}
		
			// restart the connection
			$this->try_reconnect_push();
		
			#log_message('info',"APN: send_message '$message' to $device_token");
		
			//generate the payload
			$payload = $this->generate_payload($message, $badge, $sound);

			$device_token = str_replace(' ', '', $device_token);
		
			//send payload to the device.
			if ($this->payload_method == 'simple')
				$this->send_payload_simple($device_token, $payload);
			else
			{
				if (!$expiry)
					$expiry = $this->expiry;
			
				return $this->send_payload_enhance($device_token, $payload, $expiry);
			}
		}


		/**
		 * Writes the contents of payload to the file stream
		 * 
		 * @param <string> $device_token
		 * @param <string> $payload
		 * @return <bool> 
		 */
		function get_payload_statuses()
		{
		
			$read = array($this->push_stream);
			$null = null;
			$changed_streams = stream_select($read, $null, $null, 0, 2000000);

			if ($changed_streams === false)
			{    
				#log_message('error',"APN Error: Unabled to wait for a stream availability");
			}
			elseif ($changed_streams > 0)
			{
			
				$response_binary = fread($this->push_stream, 6);
				if ($response_binary !== false || strlen($response_binary) == 6) {
				
					if (!$response_binary)
						return true;
				
					$response = @unpack('Ccommand/Cstatus_code/Nidentifier', $response_binary);
				
					#log_message('debug','APN: debugPayload response - '.print_r($response,true));
				
					if ($response && $response['status_code'] > 0)
					{
						#log_message('error','APN: debugPayload response - status_code:'.$response['status_code'].' => '.$this->apn_resonses[$response['status_code']]);
						$this->error = $this->apn_resonses[$response['status_code']];
						return false;
					}
					else
					{
						#if (isset($response['status_code']))
							#log_message('debug','APN: debugPayload response - '.print_r($response['status_code'],true));
					}
				
				}
				else
				{
					#log_message('debug',"APN: response_binary = $response_binary");
					return false;
				}
			}
			else
			{}#log_message('debug',"APN: No streams to change, $changed_streams");
		
			return true;
		}



		/**
		* Gets an array of feedback tokens
		*
		* @return <array>
		*/
		public function get_feedback_tokens() {
	    
			#log_message('debug',"APN: get_feedback_tokens {$this->feedback_stream}");
			$this->connect_to_feedback();
		
		    $feedback_tokens = array();
		    //and read the data on the connection:
		    while(!feof($this->feedback_stream)) {
		        $data = fread($this->feedback_stream, 38);
		        if(strlen($data)) {	   
		        	//echo $data;     	
		            $feedback_tokens[] = unpack("N1timestamp/n1length/H*devtoken", $data);
		        }
		    }
		
			$this->disconnect_feedback();
		
		    return $feedback_tokens;
		}

	
		/**
		* Sets additional data which will be send with main apn message
		*
		* @param <array> $data
		* @return <array>
		*/
		public function set_data($data)
		{
			if (!is_array($data))
			{
				#log_message('error',"APN: cannot add additional data - not an array");
				return false;
			}
		
			if (isset($data['apn']))
			{
				#log_message('error',"APN: cannot add additional data - key 'apn' is reserved");
				return false;
			}
		
			return $this->additional_data = $data;
		}
	


		/**
		* Closes the stream
		*/
		function __destruct(){
			$this->disconnect_push();
			$this->disconnect_feedback();
		}
}
