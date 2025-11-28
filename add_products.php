<?php
include('connection.php');

try {
    $conn = $GLOBALS['conn'];

    // Get a valid user ID
    $stmt = $conn->query("SELECT id FROM users LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        // Create a dummy user if none exists
        $conn->exec("INSERT INTO users (first_name, last_name, password, email, created_at, updated_at) VALUES ('Admin', 'User', 'password', 'admin@example.com', NOW(), NOW())");
        $user_id = $conn->lastInsertId();
    } else {
        $user_id = $user['id'];
    }

    $products = [
        [
            'product_name' => 'Apple',
            'description' => 'Fresh Red Apple',
            'img' => 'image-1.png',
            'price' => 1.50,
            'stock' => 100
        ],
        [
            'product_name' => 'Banana',
            'description' => 'Sweet Banana',
            'img' => 'image-2.png',
            'price' => 0.80,
            'stock' => 150
        ],
        [
            'product_name' => 'Orange',
            'description' => 'Juicy Orange',
            'img' => 'image-3.png',
            'price' => 1.20,
            'stock' => 120
        ],
        [
            'product_name' => 'Milk',
            'description' => 'Fresh Milk 1L',
            'img' => 'image-1.png', // Reusing image
            'price' => 3.50,
            'stock' => 50
        ],
        [
            'product_name' => 'Bread',
            'description' => 'Whole Wheat Bread',
            'img' => 'image-2.png', // Reusing image
            'price' => 2.00,
            'stock' => 80
        ]
    ];

    foreach ($products as $p) {
        $stmt = $conn->prepare("INSERT INTO products (product_name, description, img, price, stock, created_by, created_at, updated_at) VALUES (:name, :desc, :img, :price, :stock, :user, NOW(), NOW())");
        $stmt->execute([
            ':name' => $p['product_name'],
            ':desc' => $p['description'],
            ':img' => $p['img'],
            ':price' => $p['price'],
            ':stock' => $p['stock'],
            ':user' => $user_id
        ]);
        echo "Added product: " . $p['product_name'] . "<br>";
    }

    echo "All products added successfully.";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
