<?php
session_start();

// Check if already installed
$servername = "localhost";
$username = "root";
$password = "";

$installation_complete = false;
$error_message = "";
$success_message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Connect to MySQL server
        $conn = new PDO("mysql:host=$servername", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create database if it doesn't exist
        $conn->exec("CREATE DATABASE IF NOT EXISTS point_of_sale");
        $conn->exec("USE point_of_sale");

        // Disable foreign key checks to allow dropping users table
        $conn->exec("SET FOREIGN_KEY_CHECKS = 0");

        // Drop old users table if it exists (to ensure clean schema)
        $conn->exec("DROP TABLE IF EXISTS `users`");

        // Create users table with role support and username field
        $conn->exec("
            CREATE TABLE `users` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `first_name` varchar(50) NOT NULL,
                `last_name` varchar(50) NOT NULL,
                `username` varchar(50) NOT NULL UNIQUE,
                `password` varchar(255) NOT NULL,
                `email` varchar(100) NOT NULL UNIQUE,
                `role` enum('admin','salesman') NOT NULL DEFAULT 'salesman',
                `status` enum('active','inactive') NOT NULL DEFAULT 'active',
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create products table
        $conn->exec("
            CREATE TABLE IF NOT EXISTS `products` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `product_name` varchar(191) NOT NULL,
                `description` varchar(200) DEFAULT NULL,
                `img` varchar(100) DEFAULT NULL,
                `price` decimal(10,2) NOT NULL,
                `stock` int(11) NOT NULL DEFAULT 0,
                `created_by` int(11) NOT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `fk_user` (`created_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create customers table
        $conn->exec("
            CREATE TABLE IF NOT EXISTS `customers` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `first_name` varchar(100) NOT NULL,
                `last_name` varchar(100) NOT NULL,
                `address` varchar(150) NOT NULL,
                `contact` varchar(150) NOT NULL,
                `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `date_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create sales table
        $conn->exec("
            CREATE TABLE IF NOT EXISTS `sales` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `customer_id` int(11) NOT NULL,
                `user_id` int(11) NOT NULL,
                `total_amount` double(10,2) NOT NULL,
                `amount_tendered` double(10,2) NOT NULL,
                `change_amt` double(10,2) NOT NULL,
                `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `date_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `customer_id` (`customer_id`),
                KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create sales_item table
        $conn->exec("
            CREATE TABLE IF NOT EXISTS `sales_item` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `sales_id` int(11) NOT NULL,
                `product_id` int(11) NOT NULL,
                `quantity` int(11) NOT NULL,
                `unit_price` double(10,2) NOT NULL,
                `sub_total` double(10,2) NOT NULL,
                `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `date_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `product_id` (`product_id`),
                KEY `sales_id` (`sales_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create suppliers table
        $conn->exec("
            CREATE TABLE IF NOT EXISTS `suppliers` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `supplier_name` varchar(191) NOT NULL,
                `supplier_location` varchar(191) NOT NULL,
                `email` varchar(100) NOT NULL,
                `created_by` int(11) NOT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `fk_created_by` (`created_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create default admin account
        $admin_username = 'admin';
        $admin_password = password_hash('adminpassword', PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("
            INSERT INTO users (first_name, last_name, username, password, email, role, status)
            VALUES ('Admin', 'User', :username, :password, 'admin@pos.com', 'admin', 'active')
            ON DUPLICATE KEY UPDATE username = username
        ");
        $stmt->execute([
            ':username' => $admin_username,
            ':password' => $admin_password
        ]);

        // Re-enable foreign key checks
        $conn->exec("SET FOREIGN_KEY_CHECKS = 1");

        $success_message = "Installation completed successfully! Default admin account created.<br><br>
                           <strong>Username:</strong> admin<br>
                           <strong>Password:</strong> adminpassword<br><br>
                           <a href='login.php' class='btn-success'>Go to Login Page</a>";
        $installation_complete = true;

    } catch (PDOException $e) {
        $error_message = "Installation failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Installation</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .install-container {
            background: white;
            border-radius: 20px;
            padding: 50px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .install-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .install-header h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 10px;
        }

        .install-header p {
            color: #666;
            font-size: 1.1rem;
        }

        .icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }

        .install-info {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .install-info h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }

        .install-info ul {
            list-style: none;
            padding: 0;
        }

        .install-info li {
            padding: 8px 0;
            color: #555;
            display: flex;
            align-items: center;
        }

        .install-info li:before {
            content: "✓";
            color: #667eea;
            font-weight: bold;
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .btn-install {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn-install:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-success {
            display: inline-block;
            padding: 12px 30px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 15px;
            transition: background 0.3s;
        }

        .btn-success:hover {
            background: #218838;
        }

        .alert {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 1rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .warning-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .warning-box h4 {
            color: #856404;
            margin-bottom: 10px;
        }

        .warning-box p {
            color: #856404;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <div class="icon">🚀</div>
            <h1>POS System Installation</h1>
            <p>One-time setup for your Point of Sale system</p>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <strong>Error!</strong><br><?= $error_message ?>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <?= $success_message ?>
            </div>
        <?php else: ?>
            <div class="warning-box">
                <h4>⚠️ Important Information</h4>
                <p><strong>This page should only be accessed once for initial setup!</strong></p>
                <p>After installation, this link should be kept private for security reasons.</p>
            </div>

            <div class="install-info">
                <h3>What will be installed:</h3>
                <ul>
                    <li>Create point_of_sale database</li>
                    <li>Setup all required tables</li>
                    <li>Create default admin account</li>
                    <li>Initialize system configuration</li>
                </ul>
            </div>

            <div class="install-info">
                <h3>Default Admin Credentials:</h3>
                <ul>
                    <li><strong>Username:</strong> admin</li>
                    <li><strong>Password:</strong> adminpassword</li>
                </ul>
                <p style="color: #dc3545; margin-top: 15px; font-size: 0.9rem;">
                    <strong>⚠️ Please change the password after first login!</strong>
                </p>
            </div>

            <form method="POST">
                <button type="submit" class="btn-install">Install Now</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
