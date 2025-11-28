<?php
$servername = "localhost";
$username = "root";
$password = "";

try {
    // Connect to MySQL server (without selecting a database)
    $conn = new PDO("mysql:host=$servername", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS point_of_sale";
    $conn->exec($sql);
    echo "Database 'point_of_sale' created successfully.<br>";

    // Select the database
    $conn->exec("USE point_of_sale");

    // Read SQL files
    $sqlFiles = ['point_of_sale.sql', 'inventory.sql'];

    foreach ($sqlFiles as $file) {
        if (file_exists($file)) {
            $sqlContent = file_get_contents($file);
            
            // Split SQL file into individual queries (basic split by ;)
            // Note: This is a simple splitter and might not handle complex SQL with triggers/procedures correctly if they contain ;
            // But for standard dumps it usually works or we can execute the whole block if PDO allows.
            // PDO::exec can execute multiple statements in one go if the driver supports it.
            // Let's try executing the whole content first.
            
            try {
                $conn->exec($sqlContent);
                echo "Imported $file successfully.<br>";
            } catch (PDOException $e) {
                echo "Error importing $file: " . $e->getMessage() . "<br>";
                // Fallback: try splitting
                $queries = explode(';', $sqlContent);
                foreach ($queries as $query) {
                    $query = trim($query);
                    if (!empty($query)) {
                        try {
                            $conn->exec($query);
                        } catch (PDOException $e2) {
                             // Ignore "table already exists" errors or similar
                             echo "Warning executing query: " . substr($query, 0, 50) . "... " . $e2->getMessage() . "<br>";
                        }
                    }
                }
            }
        } else {
            echo "File $file not found.<br>";
        }
    }

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
