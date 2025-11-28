<?php
include('connection.php');
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Route actions
if($action === 'checkout') saveProducts();
if($action === 'add_product') addProduct();
if($action === 'update_product') updateProduct();
if($action === 'delete_product') deleteProduct();
if($action === 'get_product') getProductJson();

function getProducts(){
	// Get connection variable
	$conn = $GLOBALS['conn'];

	// Query all products
	$stmt = $conn->prepare("SELECT * FROM products ORDER BY product_name ASC");
	$stmt->execute();
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

	// Return rows
	return $rows;
}

function getProductJson(){
    $id = $_GET['id'];
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($product);
    exit;
}

function addProduct(){
    try {
        $conn = $GLOBALS['conn'];
        
        $product_name = $_POST['product_name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        
        // Get a valid user ID for created_by
        $stmt = $conn->query("SELECT id FROM users LIMIT 1");
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            // Create a dummy user if none exists
            $conn->exec("INSERT INTO users (first_name, last_name, password, email, created_at, updated_at) VALUES ('Admin', 'User', 'password', 'admin@example.com', NOW(), NOW())");
            $user_id = $conn->lastInsertId();
        } else {
            $user_id = $user['id'];
        }

        // Handle Image Upload
        $img = 'default.png'; // Default image
        if(isset($_FILES['img']) && $_FILES['img']['error'] == 0){
            $target_dir = "images/";
            $file_extension = pathinfo($_FILES["img"]["name"], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if(move_uploaded_file($_FILES["img"]["tmp_name"], $target_file)){
                $img = $new_filename;
            }
        }

        $stmt = $conn->prepare("INSERT INTO products (product_name, description, price, stock, img, created_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$product_name, $description, $price, $stock, $img, $user_id]);

        echo json_encode(['success' => true, 'message' => 'Product added successfully!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

function updateProduct(){
    try {
        $conn = $GLOBALS['conn'];
        
        $id = $_POST['id'];
        $product_name = $_POST['product_name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        
        $sql = "UPDATE products SET product_name=?, description=?, price=?, stock=?, updated_at=NOW()";
        $params = [$product_name, $description, $price, $stock];

        // Handle Image Upload if provided
        if(isset($_FILES['img']) && $_FILES['img']['error'] == 0){
            $target_dir = "images/";
            $file_extension = pathinfo($_FILES["img"]["name"], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if(move_uploaded_file($_FILES["img"]["tmp_name"], $target_file)){
                $sql .= ", img=?";
                $params[] = $new_filename;
            }
        }
        
        $sql .= " WHERE id=?";
        $params[] = $id;

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        echo json_encode(['success' => true, 'message' => 'Product updated successfully!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

function deleteProduct(){
    try {
        $conn = $GLOBALS['conn'];
        $id = $_POST['id'];
        
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Product deleted successfully!']);
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            echo json_encode(['success' => false, 'message' => 'Cannot delete product because it is associated with existing sales or inventory records.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

function saveProducts(){
	try {	

		// Get connection variable
		$conn = $GLOBALS['conn_pos'];

		$data = $_POST['data'];
		$customer = $_POST['customer'];

		// Insert to customer
		$sql = "INSERT INTO 
					customers(first_name, last_name, address, contact, date_created, date_updated) 
				VALUES 
					(:first_name, :last_name, :address, :contact, :date_created, :date_updated)";
		$db_arr = [
			'first_name' => $customer['firstName'],
			'last_name' => $customer['lastName'],
			'contact' => $customer['contact'],
			'address' => $customer['address'],
			'date_created' => date('Y-m-d H:i:s'),
			'date_updated' => date('Y-m-d H:i:s')
		];
		$stmt = $conn->prepare($sql);
		$stmt->execute($db_arr);

		$customer_id = $conn->lastInsertId();

		// Insert to sales	
		$sql = "INSERT INTO 
					sales(customer_id, user_id, total_amount, amount_tendered, change_amt, date_created, date_updated) 
				VALUES 
					(:customer_id, :user_id, :total_amount, :amount_tendered, :change_amt, :date_created, :date_updated)";

		$total_amount = $_POST['totalAmt'];
		$change_amt = $_POST['change'];
		$tenderedAmt = $_POST['tenderedAmt'];
		$user_id = 31;

		$db_arr = [
			'customer_id' => $customer_id, 
			'user_id' => $user_id, // hard code for now
			'total_amount' => $total_amount, 
			'amount_tendered' => $tenderedAmt, 
			'change_amt' => $change_amt,
			'date_created' => date('Y-m-d H:i:s'),
			'date_updated' => 	date('Y-m-d H:i:s')
		];
		$stmt = $conn->prepare($sql);
		$stmt->execute($db_arr);
		$sales_id = $conn->lastInsertId();

		// Insert order items
		foreach($data as $product_id => $order_item){	
			// Insert to sales	
			$sql = "INSERT INTO 
						sales_item(sales_id , product_id, quantity, unit_price, sub_total, date_created, date_updated) 
					VALUES 
						(:sales_id , :product_id, :quantity, :unit_price, :sub_total, :date_created, :date_updated)";

			$db_arr = [
				'sales_id' =>  $sales_id,
				'product_id' => $product_id, 
				'quantity' => $order_item['orderQty'], 
				'unit_price' => $order_item['price'], 
				'sub_total' => $order_item['amount'], 
				'date_created' => date('Y-m-d H:i:s'),
				'date_updated' => date('Y-m-d H:i:s')	
			];

			$stmt = $conn->prepare($sql);
			$stmt->execute($db_arr);

			// Get cur stock 
			$inv_conn = $GLOBALS['conn'];
			$stmt = $inv_conn->prepare("
						SELECT products.stock FROM products
							where id = $product_id
						");
			$stmt->execute();
			$product = $stmt->fetch(PDO::FETCH_ASSOC);
			$cur_stock = (int) $product['stock'];

			// Update inventory qty of products.
			$new_stock = $cur_stock - (int) $order_item['orderQty'];

			$sql = "UPDATE products 
							SET 
							stock=?
							WHERE id=?";

			$stmt = $inv_conn->prepare($sql);
			$stmt->execute([$new_stock, $product_id]);
		}


		echo json_encode([
			'success' => true,
			'id' => $sales_id,
			'message' => 'Order successfully checkout!',
			'products' => getProducts()
		]);
		
	} catch (Exception $e) {		
		echo json_encode([
			'success' => false,
			'message' => $e->getMessage()
		]);
	}
}



