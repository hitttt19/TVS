<?php
session_start();
include('db_connection.php');
$aboutUs = ''; 
$systemName = 'Bogo City Traffic Violations System'; // Default system name
$shortName = 'RegulaDrive'; // Default short name
$logoPath = 'logo/RegLogo.png'; // Default logo

// Handle POST request to verify OTP
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $otp = $_POST['otp'];

    // Validate OTP (must be exactly 6 digits)
    if (!preg_match("/^[0-9]{6}$/", $otp)) {
        $_SESSION['message_error'] = "Invalid OTP format. It must be a 6-digit number.";
        header("Location: verify_otp.php?email=" . urlencode($email));
        exit();
    }

    // Check if the OTP exists and is not expired
    $stmt = $pdo->prepare("SELECT otp, otp_expiry FROM drivers WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if ($user['otp'] == $otp) {
            // Check if OTP is expired
            if (new DateTime() > new DateTime($user['otp_expiry'])) {
                $_SESSION['message_error'] = "OTP has expired. Please request a new one.";
                header("Location: forgot_password.php");
                exit();
            } else {
                // OTP is valid, allow the user to reset their password
                $_SESSION['email_for_reset'] = $email;
                $_SESSION['message_success'] = "OTP is valid. You can now reset your password.";
                header("Location: reset_password.php");
                exit();
            }
        } else {
            $_SESSION['message_error'] = "Incorrect OTP.";
            header("Location: verify_otp.php?email=" . urlencode($email));
            exit();
        }
    } else {
        $_SESSION['message_error'] = "No user found with this email.";
        header("Location: forgot_password.php");
        exit();
    }
}
?>

<!-- verify_otp.php HTML -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.11.2/toastify.min.css">
    <link rel="icon" href="<?php echo !empty($logoPath) ? htmlspecialchars($logoPath) : 'logo/RegLogo.png'; ?>" id="favicon">
    <link rel="stylesheet" href="verifyotp.css">
    <title>Verify OTP</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.11.2/toastify.min.js"></script>
<script>
    // JavaScript to validate OTP format before form submission
    function validateOTP() {
        const otp = document.getElementById("otp").value;
        if (!/^\d{6}$/.test(otp)) {
            // Display a Toastify error message for invalid OTP format
            Toastify({
                text: "OTP must be a 6-digit number.",
                duration: 3000, // Duration in milliseconds
                backgroundColor: "#f44336", // Red background for error
                close: true, // Allow closing the toast
                gravity: "top", // Position the toast at the top
                position: "center" // Center the toast horizontally
            }).showToast();
            return false; // Return false to prevent form submission
        }
        return true; // If OTP format is valid, return true
    }

    // JS to handle OTP form submission
    function submitOTPForm(event) {
        event.preventDefault(); // Prevent default form submission

        if (validateOTP()) {
            document.getElementById('otp_form').submit(); // Submit the form if OTP is valid
        }
    }

        // Display Toastify message on page load if success/error messages are set
        window.onload = function() {
            <?php
                // Check if there is a success message and display a green toast
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
                
                // Check if there is an error message (including the OTP error) and display a red toast
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
</header>
    <div class="container">
        <h2>Verify OTP</h2>
        <form id="otp_form" method="POST" onsubmit="submitOTPForm(event);">
            <input type="text" name="otp" id="otp" required maxlength="6" placeholder="Enter 6-digit OTP">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email']); ?>">
            <button type="submit">Verify OTP</button>
        </form>

        <div class="message">
            <?php
            // Display error messages if set
            if (isset($_SESSION['message_error'])) {
                echo "<p class='error'>{$_SESSION['message_error']}</p>";
                unset($_SESSION['message_error']);
            }
            ?>
        </div>
    </div>
    
</body>
</html>
