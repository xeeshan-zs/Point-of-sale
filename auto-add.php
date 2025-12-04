<?php
include 'connection.php';

$message = "";

// Auto-Add Logic (Generates dummy products)
if (isset($_POST['auto_add'])) {
    try {
        $products = [
            ['name' => 'Apple', 'desc' => 'Fresh Red Apple', 'price' => 50, 'stock' => 100],
            ['name' => 'Banana', 'desc' => 'Yellow Banana', 'price' => 30, 'stock' => 150],
            ['name' => 'Orange', 'desc' => 'Juicy Orange', 'price' => 40, 'stock' => 120],
            ['name' => 'Milk', 'desc' => 'Fresh Milk 1L', 'price' => 120, 'stock' => 50],
            ['name' => 'Bread', 'desc' => 'Whole Wheat Bread', 'price' => 80, 'stock' => 30]
        ];

        $stmt = $conn->prepare("INSERT INTO products (product_name, description, img, price, stock, created_by, created_at, updated_at) VALUES (:name, :desc, :img, :price, :stock, :created_by, NOW(), NOW())");

        foreach ($products as $prod) {
            $stmt->execute([
                ':name' => $prod['name'] . ' ' . rand(1, 100), // Append random number to avoid duplicates if run multiple times
                ':desc' => $prod['desc'],
                ':img' => 'default.png',
                ':price' => $prod['price'],
                ':stock' => $prod['stock'],
                ':created_by' => 41 // Assuming user ID 41 exists based on schema
            ]);
        }
        $message = "Successfully auto-added 5 products!";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Manual Add Logic
if (isset($_POST['add_product'])) {
    try {
        $name = $_POST['product_name'];
        $desc = $_POST['description'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $img = 'default.png'; // Placeholder
        $created_by = 41; // Default user

        $stmt = $conn->prepare("INSERT INTO products (product_name, description, img, price, stock, created_by, created_at, updated_at) VALUES (:name, :desc, :img, :price, :stock, :created_by, NOW(), NOW())");
        $stmt->execute([
            ':name' => $name,
            ':desc' => $desc,
            ':img' => $img,
            ':price' => $price,
            ':stock' => $stock,
            ':created_by' => $created_by
        ]);
        $message = "Product '$name' added successfully!";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Fetch existing products
$current_products = [];
try {
    $stmt = $conn->query("SELECT * FROM products ORDER BY id DESC");
    $current_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Error fetching products: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto Add Products</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { padding: 20px; font-family: sans-serif; }
        .container { max-width: 800px; margin: 0 auto; }
        .btn { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
        .btn-green { background: #28a745; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .message { padding: 10px; background: #e2e3e5; margin-bottom: 20px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Auto Add Products</h1>
        
        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div style="display: flex; gap: 20px; margin-bottom: 30px;">
            <!-- Auto Add Section -->
            <div style="flex: 1; border: 1px solid #ccc; padding: 20px; border-radius: 8px;">
                <h2>Auto Generate</h2>
                <p>Click to automatically add 5 dummy products.</p>
                <form method="POST">
                    <button type="submit" name="auto_add" class="btn btn-green">Auto Add 5 Products</button>
                </form>
            </div>

            <!-- Manual Add Section -->
            <div style="flex: 1; border: 1px solid #ccc; padding: 20px; border-radius: 8px;">
                <h2>Manual Add</h2>
                <form method="POST">
                    <div style="margin-bottom: 10px;">
                        <label>Name:</label><br>
                        <input type="text" name="product_name" required style="width: 100%">
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label>Description:</label><br>
                        <input type="text" name="description" required style="width: 100%">
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label>Price:</label><br>
                        <input type="number" name="price" step="0.01" required style="width: 100%">
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label>Stock:</label><br>
                        <input type="number" name="stock" required style="width: 100%">
                    </div>
                    <button type="submit" name="add_product" class="btn">Add Product</button>
                </form>
            </div>
        </div>

        <h2>Current Database Products</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Stock</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($current_products as $product): ?>
                    <tr>
                        <td><?= htmlspecialchars($product['id']) ?></td>
                        <td><?= htmlspecialchars($product['product_name']) ?></td>
                        <td><?= htmlspecialchars($product['description']) ?></td>
                        <td><?= htmlspecialchars($product['price']) ?></td>
                        <td><?= htmlspecialchars($product['stock']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
