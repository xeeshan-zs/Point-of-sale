<?php
    include('connection.php');

    try {
        $conn = $GLOBALS['conn'];
        
        // SQL to create table
        $sql = "CREATE TABLE IF NOT EXISTS receipts (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            trans_id VARCHAR(50) NOT NULL,
            receipt_details TEXT,
            total_amount DECIMAL(10,2),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";

        // Execute query
        $conn->exec($sql);
        echo "Table 'receipts' created successfully and is ready to receive data.<br>";
        
        // Optional: Show table structure or status
        echo "Table structure:<br>";
        echo "<pre>
            id INT AUTO_INCREMENT
            trans_id VARCHAR(50)
            receipt_details TEXT
            total_amount DECIMAL(10,2)
            created_at DATETIME
        </pre>";

    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
?>
