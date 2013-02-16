<?php
/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.5
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
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
