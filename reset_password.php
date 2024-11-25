<?php
session_start();
include('db_connection.php');

$aboutUs = ''; 
$systemName = 'Bogo City Traffic Violations System'; // Default system name
$shortName = 'RegulaDrive'; // Default short name
$logoPath = 'logo/RegLogo.png'; // Default logo

// Check if the user is authorized to reset password (i.e., they passed OTP verification)
if (!isset($_SESSION['email_for_reset'])) {
    header("Location: forgot_password.php");
    exit();
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_SESSION['email_for_reset'];
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate passwords
    if ($new_password !== $confirm_password) {
        $_SESSION['message_error'] = "Passwords do not match.";
        header("Location: reset_password.php");
        exit();
    }

    // Hash the new password
    $new_password_hashed = password_hash($new_password, PASSWORD_BCRYPT);

    // Update the password in the database
    $stmt = $pdo->prepare("UPDATE drivers SET password = ?, otp = NULL, otp_expiry = NULL WHERE email = ?");
    if ($stmt->execute([$new_password_hashed, $email])) {
        $_SESSION['message_success'] = "Password successfully reset. You can now login.";
        unset($_SESSION['email_for_reset']);
        header("Location: loginpage.php"); // Redirect to login page
        exit();
    } else {
        $_SESSION['message_error'] = "Failed to reset the password. Please try again.";
        header("Location: reset_password.php");
        exit();
    }
}
?>

<!-- reset_password.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.11.2/toastify.min.css">
    <link rel="icon" href="<?php echo !empty($logoPath) ? htmlspecialchars($logoPath) : 'logo/RegLogo.png'; ?>" id="favicon">
    <link rel="stylesheet" href="resetpass.css">
    <title>Reset Password</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.11.2/toastify.min.js"></script>
    <script>
        window.onload = function() {
            <?php
                if (isset($_SESSION['message_error'])) {
                    echo "Toastify({
                        text: '" . $_SESSION['message_error'] . "',
                        duration: 3000,
                        backgroundColor: '#f44336',
                        close: true,
                        gravity: 'top',
                        position: 'center'
                    }).showToast();";
                    unset($_SESSION['message_error']);
                }

                if (isset($_SESSION['message_success'])) {
                    echo "Toastify({
                        text: '" . $_SESSION['message_success'] . "',
                        duration: 3000,
                        backgroundColor: '#4CAF50',
                        close: true,
                        gravity: 'top',
                        position: 'center'
                    }).showToast();";
                    unset($_SESSION['message_success']);
                }
            ?>
        }
    </script>
</head>
<body>
<header>
    <div class="logo">
        <!-- Make logo clickable and redirect to landing page -->
        <a href="landingpage.php">
            <!-- Ensure the logo path is correctly injected into the HTML -->
            <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="RD Logo">
            <span class="RDtxt"><?php echo htmlspecialchars($shortName); ?></span>
        </a>
    </div>
</header>
<div class="container">
    <form method="POST">
        <h2>Reset Password</h2>
        <label for="new_password">New Password:</label>
        <input type="password" name="new_password" id="new_password" required>
        <label for="confirm_password">Confirm Password:</label>
        <input type="password" name="confirm_password" id="confirm_password" required>
        <button type="submit">Reset Password</button>
    </form>
    </div>
</body>
</html>
