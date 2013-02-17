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

 # THIS IS BASED ON :
 # CM (Google Cloud Messaging)
 # @copyright (c) 2012 AntonGorodezkiy
 # info: https://github.com/antongorodezkiy/codeigniter-gcm/
 # Description: PHP Codeigniter Google Cloud Messaging Library
 # License: GNU/GPL 2

namespace Pushnotification;

class GcmConfigError extends \FuelException {}

class Pushnotification_Gcm
{
	protected $api_key = '';
	protected $api_send_address = '';
	protected $payload = array();
	protected $additional_data = array();
	protected $recepients = array();
	protected $message = '';
	
	public $status = array();
	public $messages_statuses = array();
	public $response_data = null;
	public $response_info = null;
	
	
	protected $error_statuses = array(
		'Unavailable' => 'Maybe missed API key',
		'MismatchSenderId' => 'Make sure you\'re using one of those when trying to send messages to the device. If you switch to a different sender, the existing registration IDs won\'t work.',
		'MissingRegistration' => 'Check that the request contains a registration ID',
		'InvalidRegistration' => 'Check the formatting of the registration ID that you pass to the server. Make sure it matches the registration ID the phone receives in the google',
		'NotRegistered' => 'Not registered',
		'MessageTooBig' => 'The total size of the payload data that is included in a message can\'t exceed 4096 bytes'
	);
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->api_key = \Config::get('pushnotification.Pushnotification_Gcm.api_key', '');
		$this->api_send_address = \Config::get('pushnotification.Pushnotification_Gcm.send_address', '');
		
		if (!$this->api_key) {
			throw new GcmConfigError('GCM: Needed API Key');
		}
		
		if (!$this->api_send_address) {
			throw new GcmConfigError('GCM: Needed API Send Address');
		}
	}
	
	
	/**
	* Sets additional data which will be send with main apn message
	*
	* @param <array> $data
	* @return <array>
	*/
	public function set_ttl($ttl = '')
	{
		if (!$ttl)
			unset($this->payload['time_to_live']);
		else
			$this->payload['time_to_live'] = $ttl;
	}
	
	
	/**
	 * Setting GCM message
	 *
	 * @param <string> $message
	 */
	public function set_message($message = '') {
		
		$this->message = $message;
		$this->payload['data']['message'] = $message;

	}
	
	
	/**
	 * Setting data to message
	 *
	 * @param <string> $data
	 */
	public function set_data($data = array()) {

		$this->payload['data'] = $data;
		
		if ($this->message)
			$this->payload['data']['message'] = $this->message;
		
	}
	
	
	/**
	 * Setting group of messages
	 *
	 * @param <string> $group
	 */
	public function set_group($group = '') {
		
		if (!$group)
			unset($this->payload['collapse_key']);
		else
			$this->payload['collapse_key'] = $group;
	}
	
	
	/**
	 * Adding one recepient
	 *
	 * @param <string> $group
	 */
	public function add_recepient($registrationId) {
		
		$this->payload['registration_ids'][] = $registrationId;
	}
	
	
	/**
	 * Setting all recepients
	 *
	 * @param <string> $group
	 */
	public function set_recepients($registrationIds) {
		
		$this->payload['registration_ids'] = $registrationIds;
	}
	
	
	/**
	 * Clearing group of messages
	 */
	public function clear_recepients() {
		
		$this->payload['registration_ids'] = array();
	}

	
	/**
	 * Senging messages to Google Cloud Messaging
	 *
	 * @param <string> $group
	 */
	public function send()
	{
		if (!array_key_exists('registration_ids',$this->payload) || sizeof($this->payload['registration_ids'])<1)
		{
			$this->status = array(
				'error' => 1,
				'message' => 'No device id',
				'code' => 3,
			);
			return false;
		}
		
		$this->payload['registration_ids'] = array_unique($this->payload['registration_ids']);
		
		if (isset($this->payload['time_to_live']) && !isset($this->payload['collapse_key']))
			$this->payload['collapse_key'] = 'Punchmo Notifications';
		
		$data = json_encode($this->payload);
		return $this->request($data);
	}
	
	
	protected function request($data)
	{

		$headers[] = 'Content-Type:application/json';
		$headers[] = 'Authorization:key='.$this->api_key;
		
		$curl = curl_init();
		  
		curl_setopt($curl, CURLOPT_URL, $this->api_send_address);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

		$this->response_data = curl_exec($curl);

		$this->response_info = curl_getinfo($curl);
		
		curl_close($curl);

		

		return $this->parse_response();
	}
	
	
	protected function parse_response()
	{
		if ($this->response_info['http_code'] == 200)
		{			
			$response = explode("\n",$this->response_data);
			$response_body = json_decode($response[count($response)-1]);
			
			if ($response_body->success && !$response_body->failure)
			{
				$message = 'All messages were sent successfully';
				$error = 0;
				$code = 0;
			}
			elseif ($response_body->success && $response_body->failure)
			{
				$message = $response_body->success.' of '.($response_body->success+$response_body->failure).' messages were sent successfully';
				$error = 1;
				$code = 1;
			}
			elseif (!$response_body->success && $response_body->failure)
			{
				$message = 'No messages cannot be sent. '.$response_body->results[0]->error;
				$error = 1;
				$code = 2;
			}

			$this->status = array(
				'error' => $error,
				'message' => $message,
				'code' => $code
			);
			
			$this->messages_statuses = array();
			foreach($response_body->results as $key => $result)
			{
				if (isset($result->error) && $result->error)
				{
					$this->messages_statuses[$key] = array(
						'error' => 1,
						'regid' => $this->payload['registration_ids'][$key],
						'message' => $this->error_statuses[$result->error],
						'message_id' => ''
					);
				}
				else
				{
					$this->messages_statuses[$key] = array(
						'error' => 0,
						'regid' => $this->payload['registration_ids'][$key],
						'message' => 'Message was sent successfully',
						'message_id' => $result->message_id
					);
				}
			}
			
			return !$error;
		}
		elseif ($this->response_info['http_code'] == 400)
		{
			$this->status = array(
				'error' => 1,
				'message' => 'Request could not be parsed as JSON',
				'code' => 400,
			);
			return false;
		}
		elseif ($this->response_info['http_code'] == 401)
		{
			$this->status = array(
				'error' => 1,
				'message' => 'There was an error authenticating the sender account',
				'code' => 401,
			);
			return false;
		}
		elseif ($this->response_info['http_code'] == 500)
		{
			$this->status = array(
				'error' => 1,
				'message' => 'There was an internal error in the GCM server while trying to process the request',
				'code' => 500,
			);
			return false;
		}
		elseif ($this->response_info['http_code'] == 503)
		{
			$this->status = array(
				'error' => 1,
				'message' => 'Server is temporarily unavailable',
				'code' => 503,
			);
			return false;
		}
		else
		{
			$this->status = array(
				'error' => 1,
				'message' => 'Status undefined',
				'code' => -1,
			);
			return false;
		}
	}
}
