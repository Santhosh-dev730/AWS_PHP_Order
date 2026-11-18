<?php
/**
 * db.php - Database Connection File
 * 
 * This file establishes a connection between PHP and MySQL using MySQLi.
 * Default credentials for XAMPP are used:
 * - Host: localhost
 * - Username: root
 * - Password: "" (blank by default)
 * - Database: ecommerce_db
 */

// Database configuration settings
$db_host = "localhost";
$db_user = "root";
$db_pass = "santhosh";     // MySQL root password
$db_name = "ecommerce_db";

// Create MySQLi connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check if connection was successful
if ($conn->connect_error) {
    die("
        <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 6px; margin: 30px auto; max-width: 600px;'>
            <h3 style='margin-top:0;'>⚠️ Database Connection Failed!</h3>
            <p><strong>Error:</strong> " . htmlspecialchars($conn->connect_error) . "</p>
            <p>Please check the following:</p>
            <ol>
                <li>Is MySQL running in your <strong>XAMPP Control Panel</strong>?</li>
                <li>Have you imported the <code>database.sql</code> file into MySQL Workbench / phpMyAdmin?</li>
                <li>If your MySQL has a password, update <code>$db_pass</code> in <code>db.php</code>.</li>
            </ol>
        </div>
    ");
}

// Set character set to UTF-8
$conn->set_charset("utf8mb4");
?>
