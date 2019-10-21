<?php 
	//if the post request has a message
  if(isset($_POST['message'])){
	//build the connection to our MySQL database, conn will represent the connection
	$conn = mysqli_connect("localhost","blabal","balbla","blabla");
	if(!$conn){//if the connection
    	die('MySQL connection failed');
   	}
	
	//send a notification function
	//it builds a connection with the firebase server
	//and sends notifications to the given tokens
	//it will be called later in our code
	function send_notification ($tokens, $message)
	{
		// Set POST variables for firebase
		$url = 'https://fcm.googleapis.com/fcm/send';
		$fields = array(
			 'registration_ids' => $tokens,
			 'data' => $message
			);
			//Authorization:key = legacy server key in firebase
		$headers = array(
			'Authorization:key = A********************************',
			'Content-Type: application/json'
			);
				   	
	// "I am going to sendd a notification";
	// Open connection
	$ch = curl_init();
       curl_setopt($ch, CURLOPT_URL, $url);
       curl_setopt($ch, CURLOPT_POST, true);
       curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);  
       curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
       curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
       $result = curl_exec($ch);           
       if ($result === FALSE) {
           die('Curl failed: ' . curl_error($ch));
       }
       curl_close($ch);
       echo $result;
       return $result;
	}//
	
	
	//prepare the quire to get the users
	$sql = "Select token From fcm_users_table;";
	//execute the query
	$result = mysqli_query($conn,$sql);
	
	
	//since firebase can send 1000 notifications at once with the method we use
	//we will use multidimensional array	
	$tokens = array();
	
	if(mysqli_num_rows($result) > 0 ){
	        $i = 0;
			//for every token
		while ($row = mysqli_fetch_assoc($result)) {
			//for debugging
			echo "token: ".$row['token'];
			$i++;
			//add the token to our array of tokens
			$tokens[floor($i/1000)][] = $row['token'];
		}
	}
	//close the connection with our database
	mysqli_close($conn);
		
	
	//build the mssage array with post parameters from the request, it will be used later in the method
	$message = array(
	"title" => $_POST['title'],
	"message" => $_POST['message'],
	"img_url" => $_POST['img_url'],
	"img_text" => $_POST['img_text']);
	//for debugging
	echo "message: ".$message;
	
	//call the send notification function for each array of tokens
	foreach($tokens as $val) $pushStatus[] = send_notification($val, $message);

	//for debugging
	echo $pushStatus;
	}
?>
