<?php
header('Access-Control-Allow-Origin: *');

// var_dump($_POST);
// var_dump($_FILES);
// die;

$location = '../uploads/';

if ($_POST['type']) {
	$location .= $_POST['type'];
	// var_dump("location is ", $location);die;
} else {
	$location .= 'general';
}

$uploadfilename = $_FILES['file']['name'];

if(move_uploaded_file($_FILES['file']['tmp_name'], $location.'/'. $uploadfilename)){
	// echo 'OK';
	http_response_code(200);
	echo json_encode(
        [
			"success" => true,
			"message" => "File Uploaded Successfully"
		]
    );
} else {
	http_response_code(201);
	echo json_encode(
        [
			"success" => false,
			"message" => "File Uploaded Failed. ERROR Details: " . $_FILES['file']['error']
		]
    );
}
