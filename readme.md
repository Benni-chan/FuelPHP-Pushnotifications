# FuelPHP Push Notification Package.

A full fledged push notification class for FuelPHP. Send notifications using Google Cloud Messaging or Apple Push Notification Service.

# Summary

* send push notifications to gcm (android) or apns (ios)
* easily extendible for other services like windows phone or blackberry

# Usage

	gcm = Pushnotification::forge('gcm');
	$gcm->set_message('Test message '.date('d.m.Y H:i:s'));
	
	$gcm->add_recepient('ID');
	// $gcm->set_recepients($array); // you can also use an array (up to 1000 devices per send)
	
	// set additional data
	$gcm->set_data(array(
		'some_key' => 'some_val'
	));

	// also you can add time to live
	//    $gcm->set_ttl(500);
	// and unset in further
	$gcm->set_ttl(false);

	// set group for messages if needed
	//    $gcm->set_group('Test');
	// or set to default
	$gcm->set_group(false);

	// then send
	if ($gcm->send())
		echo 'Success for all messages ';
	else
		echo 'Some messages have errors ';

	// and see responses for more info
	print_r($gcm->status);
	print_r($gcm->messages_statuses);
	
	
	
	$apns = Pushnotification::forge('apns');
	//$apns->payload_method = 'enhance'; // you can turn on this method for debuggin purpose
	$apns->payload_method = 'simple';
	$apns->connect_to_push();
		
	// adding custom variables to the notification
	$apns->set_data(array( 'someKey' => true ));
		
	$device_token = "";
		
	$send_result = $apns->send_message($device_token, 'Test notif #1 (TIME:'.date('H:i:s').')', 0, 'default'  );
			
	if($send_result)
		echo "apns send successful";
	else
		echo $apns->error;
		
	$apns->disconnect_push();


# Exceptions

	+ \GcmConfigError, thrown when the configuration for GCM service is invalid
	+ \ApnsConfigError, thrown when the configuration for APNS service is invalid

	
# That's it. Questions? 
