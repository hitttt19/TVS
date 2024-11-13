<?php
require 'db_connection.php'; // Include your database connection file

// Define the plain-text password
$plainPassword = 'admin';

// Hash the password
$hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

// Update the database
$stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = ?");
$stmt->execute([$hashedPassword, 'admin']);

echo "Admin password updated to hashed version.";
?>
