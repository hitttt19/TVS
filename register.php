<?php
session_start();
include('db_connection.php'); // Ensure the correct path to your db connection file

// Initialize variables
$systemName = 'Bogo City Traffic Violations System'; // Default system name
$shortName = 'RegulaDrive'; // Default short name
$logoPath = 'logo/RegLogo.png'; // Default logo

// Fetch current settings
$query = "SELECT * FROM Settings LIMIT 1";
$stmt = $pdo->prepare($query);
$stmt->execute();
$currentSettings = $stmt->fetch(PDO::FETCH_ASSOC);

if ($currentSettings) {
    $systemName = $currentSettings['system_name'] ?: $systemName;
    $shortName = $currentSettings['system_short_name'] ?: $shortName;
    $logoPath = !empty($currentSettings['logo']) ? $currentSettings['logo'] : $logoPath;
}

// Handle POST request (form submission)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input fields
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
        header("Location: registerpage.php");
        exit();
    }

    // Validate contact number (must be exactly 11 digits)
    if (!preg_match('/^[0-9]{11}$/', $contact_number)) {
        $_SESSION['message_error'] = "Invalid contact number. It must be exactly 11 digits.";
        header("Location: registerpage.php");
        exit();
    }

    // Handle ID front photo upload
    $id_front_path = $id_back_path = $photo_path = null;
    $upload_dir = 'uploads/';

    // Create upload directory if not exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Upload ID front photo
    if (isset($_FILES['id_front']) && $_FILES['id_front']['error'] == UPLOAD_ERR_OK) {
        $id_front = $_FILES['id_front'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_file_size = 10 * 1024 * 1024; // 10 MB

        if (!in_array($id_front['type'], $allowed_types)) {
            $_SESSION['message_error'] = "Invalid ID front photo format. Please upload a JPEG, PNG, or GIF.";
            header("Location: registerpage.php");
            exit();
        } elseif ($id_front['size'] > $max_file_size) {
            $_SESSION['message_error'] = "ID front photo exceeds the 10MB limit.";
            header("Location: registerpage.php");
            exit();
        }

        $id_front_name = uniqid('id_front_', true) . '.' . pathinfo($id_front['name'], PATHINFO_EXTENSION);
        $id_front_path = $upload_dir . basename($id_front_name);

        if (!move_uploaded_file($id_front['tmp_name'], $id_front_path)) {
            $_SESSION['message_error'] = "Failed to upload the front photo of your ID.";
            header("Location: registerpage.php");
            exit();
        }
    }

    // Upload ID back photo
    if (isset($_FILES['id_back']) && $_FILES['id_back']['error'] == UPLOAD_ERR_OK) {
        $id_back = $_FILES['id_back'];

        if (!in_array($id_back['type'], $allowed_types)) {
            $_SESSION['message_error'] = "Invalid ID back photo format. Please upload a JPEG, PNG, or GIF.";
            header("Location: registerpage.php");
            exit();
        } elseif ($id_back['size'] > $max_file_size) {
            $_SESSION['message_error'] = "ID back photo exceeds the 10MB limit.";
            header("Location: registerpage.php");
            exit();
        }

        $id_back_name = uniqid('id_back_', true) . '.' . pathinfo($id_back['name'], PATHINFO_EXTENSION);
        $id_back_path = $upload_dir . basename($id_back_name);

        if (!move_uploaded_file($id_back['tmp_name'], $id_back_path)) {
            $_SESSION['message_error'] = "Failed to upload the back photo of your ID.";
            header("Location: registerpage.php");
            exit();
        }
    }

    // Upload Profile photo
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $photo = $_FILES['photo'];

        if (!in_array($photo['type'], $allowed_types)) {
            $_SESSION['message_error'] = "Invalid profile photo format. Please upload a JPEG, PNG, or GIF.";
            header("Location: registerpage.php");
            exit();
        } elseif ($photo['size'] > $max_file_size) {
            $_SESSION['message_error'] = "Profile photo exceeds the 10MB limit.";
            header("Location: registerpage.php");
            exit();
        }

        $photo_name = uniqid('photo_', true) . '.' . pathinfo($photo['name'], PATHINFO_EXTENSION);
        $photo_path = $upload_dir . basename($photo_name);

        if (!move_uploaded_file($photo['tmp_name'], $photo_path)) {
            $_SESSION['message_error'] = "Failed to upload your profile photo.";
            header("Location: registerpage.php");
            exit();
        }
    }

    // Hash the password
    $password_hashed = password_hash($password, PASSWORD_BCRYPT);

    // Prepare SQL query to insert data into the database (including photo)
    $stmt = $pdo->prepare("INSERT INTO drivers 
    (license_id, license_type, firstname, middlename, lastname, gender, date_of_birth, civil_status, 
    present_address, permanent_address, nationality, contact_number, username, email, password, 
    id_front_photo, id_back_photo, photo) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Execute the query with the values
    if ($stmt->execute([
    $license_id, 
    $license_type, 
    $firstname, 
    $middlename ?: NULL,  // If middlename is empty, pass NULL
    $lastname, 
    $gender, 
    $date_of_birth, 
    $civil_status, 
    $present_address, 
    $permanent_address, 
    $nationality, 
    $contact_number, 
    $username, 
    $email, 
    $password_hashed, 
    $id_front_path, 
    $id_back_path, 
    $photo_path
    ])) {
    $_SESSION['message_success'] = "Registration successful!";
    header("Location: landingpage.php"); // Redirect to success page
    exit();
    } else {
    $_SESSION['message_error'] = "Registration failed. Please try again.";
    header("Location: registerpage.php"); // Redirect back to register page with error message
    exit();
    }

}
?>
