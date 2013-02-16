# Fuel Push Notification Package.

A full fledged push notification class for Fuel. Send notifications using Google Cloud Messaging or Apple Push Notification Service.

# Summary

* send push notifications to gcm (android) or apns (ios)
* easily extendible for other services like windows phone or blackberry

# Usage

	gcm = Pushnotification::forge('gcm');
	$gcm->setMessage('Test message '.date('d.m.Y H:i:s'));
	
	$gcm->addRecepient(''); // you can also use an array (up to 1000 devices per send)

	// set additional data
	$gcm->setData(array(
		'some_key' => 'some_val'
	));

	// also you can add time to live
	//    $gcm->setTtl(500);
	// and unset in further
	$gcm->setTtl(false);

	// set group for messages if needed
	//    $this->gcm->setGroup('Test');
	// or set to default
	$gcm->setGroup(false);

	// then send
	if ($gcm->send())
		echo 'Success for all messages ';
	else
		echo 'Some messages have errors ';

	// and see responses for more info
	print_r($gcm->status);
	print_r($gcm->messages_statuses);
	
	
	
	$apns = Pushnotification::forge('apns');
	//$apns->payloadMethod = 'enhance'; // you can turn on this method for debuggin purpose
	$apns->payloadMethod = 'simple';
	$apns->connectToPush();
		
	// adding custom variables to the notification
	$apns->setData(array( 'someKey' => true ));
		
	$device_token = "";
		
	$send_result = $apns->sendMessage($device_token, 'Test notif #1 (TIME:'.date('H:i:s').')', 0, 'default'  );
			
	if($send_result)
		echo "apns send successful";
	else
		echo $apns->error;
		
	$apns->disconnectPush();


# Exceptions

	+ \GcmConfigError, thrown when the configuration for GCM service is invalid
	+ \ApnsConfigError, thrown when the configuration for APNS service is invalid

	
# That's it. Questions? 