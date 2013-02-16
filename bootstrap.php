<?php
/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Pushnotification
 * @version    1.0
 * @author     Benjamin Waller
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */


Autoloader::add_core_namespace('Pushnotification');

Autoloader::add_classes(array(
	/**
	 * Pushnotification classes.
	 */
	'Pushnotification\\Pushnotification'                   => __DIR__.'/classes/pushnotification.php',
	'Pushnotification\\Pushnotification_Gcm'               => __DIR__.'/classes/pushnotification/gcm.php',
	'Pushnotification\\Pushnotification_Apns'              => __DIR__.'/classes/pushnotification/apns.php',

	/**
	 * GCM exceptions
	 */
	'Pushnotification\\GcmConfigError'                     => __DIR__.'/classes/pushnotification/gcm.php',

	/**
	 * APNS exceptions
	 */
	'Pushnotification\\ApnsConfigError'                    => __DIR__.'/classes/pushnotification/apns.php',

));
