<?php 

	$password = "PASSWORD_TO_OPEN_CERTIFICATE";
	$message = "Hello World"; // The msg that would like to send notification.
	
	$tokenlist = $this->Model_user->get_token($app_package);

	
	$ctx = stream_context_create();
	stream_context_set_option($ctx, "ssl", "local_cert", "path/to/CERTIFICATE_FILE"); // Example => Certi/APSCertificates.pem
	stream_context_set_option($ctx, "ssl", "passphrase", $password);

	$connect = stream_socket_client( 	"ssl://gateway.push.apple.com:2195", 
										$err, $errstr, 60, 
										STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, 
										$ctx);

	if (!$connect) { exit("Failed to connect: $err $errstr"); }


	$body['aps'] = array(	"alert" => $message,
	  						"sound" => "default",
	  						"link_url" => $url );

	$payload = json_encode($body);


	/* Single Token */
	/*
	$device_token = "YOUR_DEVICE_TOKEN_GENERATED_IN_APP";
	$msg = chr(0) . pack("n", 32) . pack("H*", $device_token) . pack("n", strlen($payload)) . $payload;
	$result = fwrite($fp, $msg, strlen($msg)); 
	*/

	/* Multiple Token */

	$tokenlist = $from_database;

	foreach ($tokenlist->result() as $ajc) {
		$device_token = $ajc->token;
		$msg = chr(0) . pack("n", 32) . pack("H*", $device_token) . pack("n", strlen($payload)) . $payload;
		$result = fwrite($fp, $msg, strlen($msg)); 
	}

	fclose($fp);

	if (!$result) {	 echo "Fail. Message not delivered.";  		} 
	else { 	echo "Success. Message successfully delivered."; 	}

?>