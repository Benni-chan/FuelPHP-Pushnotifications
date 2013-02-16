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
