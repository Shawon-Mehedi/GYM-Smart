<?php 
    // connect to the database
	$conn = mysqli_connect('localhost', 'Shawon', 'shawon', 'gym_smart');

	// check connection
	if(!$conn){
		echo 'Connection error: '.mysqli_connect_error();
	}
?>
