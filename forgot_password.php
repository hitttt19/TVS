<?php
session_start();
include('db_connection.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$aboutUs = ''; 
$systemName = 'Bogo City Traffic Violations System'; // Default system name
$shortName = 'RegulaDrive'; // Default short name
$logoPath = 'logo/RegLogo.png'; // Default logo

require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message_error'] = "Invalid email format. Please try again.";
        header("Location: forgot_password.php");
        exit();
    }

    // Check if the email exists in the database
    $stmt = $pdo->prepare("SELECT * FROM drivers WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Generate a random 6-digit OTP
        $otp = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime('+10 minutes')); // OTP expires in 10 minutes

        // Save the OTP and expiry in the database
        $updateStmt = $pdo->prepare("UPDATE drivers SET otp = ?, otp_expiry = ? WHERE email = ?");
        $updateStmt->execute([$otp, $expiry, $email]);

        // Set up PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();                                      // Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                  // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                              // Enable SMTP authentication
            $mail->Username   = 'reguladrive@gmail.com';             // Your Gmail address
            $mail->Password   = 'befx wqgc wdgi oenj';            // Your Gmail app password (NOT your Gmail account password)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;    // Enable TLS encryption
            $mail->Port       = 587;                               // TCP port to connect to

            // Recipients
            $mail->setFrom('your-email@gmail.com', 'RegulaDrive Team');   // Sender's email and name
            $mail->addAddress($email);                                // Recipient's email

            // Content
            $mail->isHTML(false);                                    // Set email format to plain text
            $mail->Subject = 'Your OTP for Password Reset';
            $mail->Body    = "Hello,\n\nYour OTP is: $otp. It will expire in 10 minutes.\n\nPlease use this code to reset your password.";

            // Send the email
            if ($mail->send()) {
                // Set the success message for Toastify notification
                $_SESSION['message_success'] = "OTP has been sent to your email.";

                // Redirect to OTP verification page after sending the email
                header("Location: verify_otp.php?email=" . urlencode($email)); // Redirect to OTP verification page
                exit();
            } else {
                $_SESSION['message_error'] = "Failed to send OTP. Please try again.";
                header("Location: forgot_password.php");
                exit();
            }
        } catch (Exception $e) {
            $_SESSION['message_error'] = "Mailer Error: " . $mail->ErrorInfo;
            header("Location: forgot_password.php");
            exit();
        }
    } else {
        $_SESSION['message_error'] = "Email not found in our system.";
        header("Location: forgot_password.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.11.2/toastify.min.css">
    <link rel="icon" href="<?php echo !empty($logoPath) ? htmlspecialchars($logoPath) : 'logo/RegLogo.png'; ?>" id="favicon">
    <link rel="stylesheet" href="forgotpass.css">
    <title>Forgot Password</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.11.2/toastify.min.js"></script>
    <script>
        // JavaScript to validate email format before form submission
        function validateEmail() {
            const email = document.getElementById("email").value;
            const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

            if (!emailPattern.test(email)) {
                alert("Invalid email format.");
                return false;
            }
            return true;
        }

        // JavaScript to handle form submit gracefully
        function submitForm(event) {
            event.preventDefault(); // Prevent form submission initially

            if (validateEmail()) {
                document.getElementById('forgot_password_form').submit(); // Only submit if validation passes
            }
        }

        // Display Toastify message on page load if success/error messages are set
        window.onload = function() {
            <?php
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

    <!-- Login section aligned to the right -->
    <div class="login-section">
        <form action="login.php" method="post">
            <input type="text" name="login" id="login" placeholder="Username or Email" required>
            <input type="password" name="password" id="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</header>

    <div class="container">
        <h2>Forgot Password</h2>
        <form id="forgot_password_form" method="POST" onsubmit="submitForm(event);">
            <label for="email">Enter your email:</label>
            <input type="email" name="email" id="email" required>
            <button type="submit">Send OTP</button>
        </form>

        <div class="message">
            <?php
            if (isset($_SESSION['message_error'])) {
                echo "<p class='error'>{$_SESSION['message_error']}</p>";
                unset($_SESSION['message_error']);
            }
            if (isset($_SESSION['message_success'])) {
                echo "<p class='success'>{$_SESSION['message_success']}</p>";
                unset($_SESSION['message_success']);
            }
            ?>
        </div>
    </div>
</body>
</html>
