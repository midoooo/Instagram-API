<?php

set_time_limit(0); 							//script runs infinitely
ini_set('default_socket_timeout', 300);		//server settings
session_start(); 							//starts new or resume existing session


/*-------- CONFIGURE THESE --- Instagram API KEYS --------*/	
define("clientID"       ,	 'b36238d8a21643498d078dca8b9b8676'); 			//associated your developer account with this program
define("clientSecret"   ,	 '8133fc8867554d799a38de6a8794e621'); 			//password
define("redirectURI"    ,	 'http://www.steppeego.com/instagram/'  ); 		//after users choose whether to let you use access account or not (must match the one you registered)

// define("imageDirectory" ,    'images/'); 		

  
//Connect with Instagram
function connectToInstagram($url){
	$ch = curl_init();						//used to transfer data with a url
	
	curl_setopt_array($ch, array(			//sets options for a curl transfer
		CURLOPT_URL => $url,				//the url
		CURLOPT_RETURNTRANSFER => true,		//return the results if successful
		CURLOPT_SSL_VERIFYPEER => false,	//we dont need to verify any certificates
		CURLOPT_SSL_VERIFYHOST => 2			//we wont verify host
	));

	$result = curl_exec($ch);				//executue the transfer
	curl_close($ch);						//close the curl session
	return $result;							//returns all the data we gathered
}


//Get Instagram userID
function getUserID($userName){
	$url = 'https://api.instagram.com/v1/users/search?q='. $userName .'&client_id='. clientID;
	$instagramInfo = connectToInstagram($url);
	$results = json_decode($instagramInfo, true); 	//takes a JSON encoded string and converts it into a PHP variables

	return $results['data'][0]['id'];				//returns the userID
}


//Print out the images
function printImages($userID){
	$url = 'https://api.instagram.com/v1/users/'. $userID .'/media/recent?client_id='. clientID .'&count=1000';
	$instagramInfo = connectToInstagram($url);
	$results = json_decode($instagramInfo, true);
	
	//parse through results
	foreach($results['data'] as $item){
		$image_url = $item['images']['low_resolution']['url'];
		echo '<img src="'.$image_url.'" /> <br><br>';
		
		//savePicture($image_url);
	}
}

/*
//Save the Picture
function savePicture($image_url){
	$filename = basename($image_url);
	$destination = imageDirectory.$filename;
	file_put_contents($destination, file_get_contents($image_url));
}
*/

//------------PROGRAM STARTS HERE-------------------------
//Get user code and save info to session variables
if( isset($_GET['code']) ){
	$code = $_GET['code'];
	$url = "https://api.instagram.com/oauth/access_token";
	$access_token_settings = array(
			'client_id'                =>     clientID,
			'client_secret'            =>     clientSecret,
			'grant_type'               =>     'authorization_code',
			'redirect_uri'             =>     redirectURI,
			'code'                     =>     $code
	);
	$curl = curl_init($url);    									//we need to transfer some data
	curl_setopt($curl,CURLOPT_POST,true);   						//using POST
	curl_setopt($curl,CURLOPT_POSTFIELDS,$access_token_settings);   //use these settings
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);   				//return results as string
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);   			//don't need to verify any certificates
	$result = curl_exec($curl);   									//go get the data!
	curl_close($curl);   											//close connection to free up your resources

	$results = json_decode($result,true);
	
	$userName = $results['user']['username']; 
	$userID = getUserID($userName);
	printImages($userID);
	
}else{ ?>

<!doctype html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
	</head>
	<body>
		<!-- When they click this, they will be prompted to Login to Instagram -->
		<a href="https://api.instagram.com/oauth/authorize/?client_id=<?php echo clientID; ?>&redirect_uri=<?php echo redirectURI; ?>&response_type=code">Login</a>
	</body>
</html>

<?php } ?>