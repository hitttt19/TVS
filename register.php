<?php
require 'db_connection.php'; // Include your database connection file
session_start(); // Start the session to store messages

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input
    $license_id = htmlspecialchars(trim($_POST['license_id']));
    $license_type = htmlspecialchars(trim($_POST['license_type']));
    $firstname = htmlspecialchars(trim($_POST['firstname']));
    $middlename = htmlspecialchars(trim($_POST['middlename']));
    $lastname = htmlspecialchars(trim($_POST['lastname']));
    $gender = htmlspecialchars(trim($_POST['gender']));
    $date_of_birth = htmlspecialchars(trim($_POST['date_of_birth']));
    $civil_status = htmlspecialchars(trim($_POST['civil_status']));
    $present_address = htmlspecialchars(trim($_POST['present_address']));
    $permanent_address = htmlspecialchars(trim($_POST['permanent_address']));
    $nationality = htmlspecialchars(trim($_POST['nationality']));
    $contact_number = htmlspecialchars(trim($_POST['contact_number']));
    $username = htmlspecialchars(trim($_POST['username']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message_error'] = "Invalid email format. Please try again.";
        header("Location: landingpage.php");
        exit();
    }

    // Validate contact number
    if (!preg_match('/^[0-9]{11}$/', $contact_number)) {
        $_SESSION['message_error'] = "Invalid contact number. Must be 11 digits.";
        header("Location: landingpage.php");
        exit();
    }

    // Validate password
    if (empty($password)) {
        $_SESSION['message_error'] = "Password cannot be empty.";
        header("Location: landingpage.php");
        exit();
    }

    // Handle photo upload for registration
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        session_start(); // Ensure session is started
        $photo = $_FILES['photo'];
        
        // Validate file type and size
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_file_size = 10 * 1024 * 1024; // 10 MB

        if (!in_array($photo['type'], $allowed_types)) {
            $_SESSION['message_error'] = "Invalid photo format. Please upload a JPEG, PNG, or GIF.";
            header("Location: landingpage.php");
            exit();
        } elseif ($photo['size'] > $max_file_size) {
            $_SESSION['message_error'] = "File size exceeds the 10MB limit.";
            header("Location: landingpage.php");
            exit();
        }

        // Prepare upload directory
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true); // Create the directory if it doesn't exist
        }

        // Create a unique file name to prevent overwriting
        $photo_name = basename($photo['name']);
        $photo_name = preg_replace('/[^a-zA-Z0-9._-]/', '', $photo_name); // Sanitize file name
        $unique_name = uniqid('photo_', true) . '_' . $photo_name; // Add unique ID
        $photo_path = $upload_dir . $unique_name;

        // Move uploaded file to the specified directory
        if (!move_uploaded_file($photo['tmp_name'], $photo_path)) {
            $_SESSION['message_error'] = "Failed to upload the photo.";
            header("Location: landingpage.php");
            exit();
        }
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Check if username already exists
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM drivers WHERE username = ?");
        $stmt_check->execute([$username]);
        if ($stmt_check->fetchColumn() > 0) {
            $_SESSION['message_error'] = "Username already exists. Please choose another.";
            header("Location: landingpage.php");
            exit();
        }

        // Insert into drivers table
        $stmt = $pdo->prepare("INSERT INTO drivers (license_id, license_type, firstname, middlename, lastname, gender, date_of_birth, civil_status, present_address, permanent_address, nationality, contact_number, username, email, password, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$license_id, $license_type, $firstname, $middlename, $lastname, $gender, $date_of_birth, $civil_status, $present_address, $permanent_address, $nationality, $contact_number, $username, $email, $hashed_password, $photo_path]);

        $_SESSION['message_success'] = "Registration successful!";
        header("Location: landingpage.php");
        exit();
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $_SESSION['message_error'] = "A database error occurred. Please try again.";
        header("Location: landingpage.php");
        exit();
    }

    $pdo = null; // Close the PDO connection
}
?>