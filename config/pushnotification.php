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


/**
 * NOTICE:
 *
 * If you need to make modifications to the default configuration, copy
 * this file to your app/config folder, and make them in there.
 *
 * This will allow you to upgrade fuel without losing your custom config.
 */
 
 return array(
	
	// ------------------------------------------------------------------------
	// Register push notification services
	// ------------------------------------------------------------------------
	
	'services' => array(
		'gcm'       => 'Pushnotification_Gcm',
		'apns'      => 'Pushnotification_Apns',
	),
	
	// ------------------------------------------------------------------------
	// Individual class config by classname
	// ------------------------------------------------------------------------
	
	// GCM (  )
	// ------------------------------------------------------------------------
	'Pushnotification_Gcm' => array(
		'api_key'       => '',
		'send_address'  => 'https://android.googleapis.com/gcm/send',
	),
	
	// APNS (  )
	// ------------------------------------------------------------------------
	'Pushnotification_Apns' => array(
		'use_sandbox'   => true,
		'sandbox'       => array(
			'certificate'   => '',
			'certificate_passphrase'   => '',
			'push_gateway' => 'ssl://gateway.sandbox.push.apple.com:2195',
			'feedback_gateway' => 'ssl://feedback.sandbox.push.apple.com:2196',
		),
		'production'       => array(
			'certificate'   => '',
			'certificate_passphrase'   => '',
			'push_gateway' => 'ssl://gateway.push.apple.com:2195',
			'feedback_gateway' => 'ssl://feedback.push.apple.com:2196',
		),
		'timeout' => 60,
		'expiry' => 86400,
	),
);
