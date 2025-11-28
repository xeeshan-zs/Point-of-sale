<?php
	// Add connection file.
	include('connection.php');

	// Get the search term using $_GET
	$search_term = isset($_GET['search_term']) ? $_GET['search_term'] : '';
	// Transform to lowercase and remove spaces
	$search_term = trim(strtolower($search_term));

	// Search database.
	$conn = $GLOBALS['conn'];
	$stmt = $conn->prepare("
				SELECT * FROM products 
					WHERE product_name LIKE '%$search_term%'
					ORDER BY created_at DESC"
			);

	$stmt->execute();
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$rows = $stmt->fetchAll();

	echo json_encode([
		'length' => count($rows),
		'data' => $rows
	]);

