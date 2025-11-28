<?php
	$servername = 'localhost';
	$username = 'root';
	$password = '';


	// Connecting to database.
	try {
		$conn = new PDO("mysql:host=$servername;dbname=point_of_sale", $username, $password);
		// set the PDO error mode to exception.
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch (\Exception $e) {
		$error_message = $e->getMessage();
        die("Connection failed: " . $error_message . " <br>Please run <a href='setup_database.php'>setup_database.php</a> first.");
	}

	// Make the connection variable global.
	$GLOBALS['conn'] = $conn;
    $GLOBALS['conn_pos'] = $conn;
?>
