<?php
include('connection.php');
include('sale.php');


if(isset($_GET['action'])){
	if($_GET['action'] == 'getGraphData') echo getChartData($_GET['start'], $_GET['end']);
}


function getChartData($start, $end){
	// Loop dates
	$date_amt = [];
	while ($start <= $end) {
		$sales = getSales($start, $start);
		$date_amt[$start] = array_sum(array_column($sales, 'total_amount'));
		$start = date('Y-m-d', strtotime($start . '+1 day'));
	}

	return json_encode([
		'categories' => array_keys($date_amt),
		'series' => array_values($date_amt)
	]);
}

function getRecentOrders($limit = 5){	
	$conn = $GLOBALS['conn_pos'];
	$stmt = $conn->prepare("
			SELECT * FROM sales WHERE sales.date_created ORDER BY sales.date_created DESC LIMIT $limit
		");
	$stmt->execute();
	$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

	return $sales;
}

function getSaleWidgetData(){
	$today = date('Y-m-d');
	$sales = getSales($today, $today);
	
	$qty = 0;
	$sale_amt = 0.00;
	$orders = count($sales);

	foreach($sales as $sale){
		$sale_amt += $sale['total_amount'];

		// Get order items qty
		$order_items = getOrderItems($sale['id']);
		$qty += array_sum(array_column($order_items, 'quantity'));
	}

	return [
		'qty' => $qty,
		'sale_amt' => $sale_amt,
		'orders' => $orders
	];
}


function getSales($start, $end){
	// Fetch Sales
	$conn = $GLOBALS['conn_pos'];
	$stmt = $conn->prepare("
			SELECT * FROM sales WHERE sales.date_created >= '$start 00:00:00'  and sales.date_created <= '$end 23:59:59'
		");
	$stmt->execute();
	$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

	return $sales;

	// // Get customers data.
	// $customers_data = getSaleCustomer($sale['customer_id']);

	// // Get order items data.
	// $items = getOrderItems($sale['id']);
	// $items_data = [];

	// $inv_conn = $GLOBALS['conn'];
	// foreach($items as $item){
	// 	$pid = $item['product_id'];

	// 	$stmt = $inv_conn->prepare("
	// 				SELECT products.product_name FROM products
	// 					where id = $pid
	// 				");
	// 	$stmt->execute();
	// 	$product = $stmt->fetch(PDO::FETCH_ASSOC);

	// 	$items_data[$item['id']] = $item;
	// 	$items_data[$item['id']]['product'] = $product['product_name'];
	// }

	// return [
	// 	'sales' => $sale,
	// 	'items' => $items_data,
	// 	'customer' => $customers_data
	// ];
}
