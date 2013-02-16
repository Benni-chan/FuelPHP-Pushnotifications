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

namespace Pushnotification;

class Pushnotification
{
	/**
	 * @var  array  Holds the list of loaded files.
	 */
	public static function _init()
	{
		\Config::load('pushnotification', true);
	}
	/**
	 * Forges a new Pushnotification object based on the defined service
	 *
	 * @param   string  $file         view filename
	 * @param   array   $data         view data
	 * @param   bool    $auto_encode  auto encode boolean, null for default
	 * @return  object  a new pushnotification instance
	 */
	public static function forge($service = null)
	{
		$class = null;

		if ($service !== null)
		{
			$class = \Config::get('pushnotification.services.'.$service, null);
		}

		if ($class === null)
		{
			$class = get_called_class();
		}

		// Class can be an array config
		if (is_array($class))
		{
			$class['service'] and $service = $class['service'];
			$class = $class['class'];
		}

		$pushnotification = new $class(null);

		#if ($service !== null)
		#{
		#	// Set service when given
		#	$service and $pushnotification->service = $service;
		#}

		return $pushnotification;
	}
}
