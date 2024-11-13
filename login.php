<?php
require 'db_connection.php'; // Include your database connection file
session_start(); // Start session at the beginning
// Redirect to landing page if session is not set (not logged in)

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if both 'login' and 'password' fields are set
    if (isset($_POST['login']) && isset($_POST['password'])) {
        // Sanitize and validate input
        $login = htmlspecialchars(trim($_POST['login'])); // This can be either username or email
        $password = htmlspecialchars(trim($_POST['password']));

        try {
            // Determine if the login is an email or username
            if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
                // Login is an email
                $stmt = $pdo->prepare("
                    SELECT id, username, password, 'admin' AS user_type FROM admins WHERE email = ?
                    UNION
                    SELECT id, username, password, 'driver' AS user_type FROM drivers WHERE email = ?
                    UNION
                    SELECT id, username, password, 'enforcer' AS user_type FROM traffic_enforcers WHERE email = ?
                ");
                $stmt->execute([$login, $login, $login]);
            } else {
                // Login is a username
                $stmt = $pdo->prepare("
                    SELECT id, username, password, 'admin' AS user_type FROM admins WHERE username = ?
                    UNION
                    SELECT id, username, password, 'driver' AS user_type FROM drivers WHERE username = ?
                    UNION
                    SELECT id, username, password, 'enforcer' AS user_type FROM traffic_enforcers WHERE username = ?
                ");
                $stmt->execute([$login, $login, $login]);
            }

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Set session variables based on user type
                    if ($user['user_type'] == 'admin') {
                        $_SESSION['admin_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['message_success'] = "Login successful!"; // Set success message
                        header("Location: admin/admin_dashboard.php"); // Redirect to admin dashboard
                    } elseif ($user['user_type'] == 'driver') {
                        $_SESSION['driver_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['message_success'] = "Login successful!"; // Set success message
                        header("Location: driver/driver_dashboard.php"); // Redirect to driver dashboard
                    } elseif ($user['user_type'] == 'enforcer') {
                        $_SESSION['enforcer_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['message_success'] = "Login successful!"; // Set success message
                        header("Location: enforcer/enforcer_dashboard.php"); // Redirect to enforcer dashboard
                    }
                    exit();
                } else {
                    $_SESSION['message_error'] = "Invalid password. Please try again."; // Set error message for invalid password
                    header("Location: landingpage.php"); // Redirect to landing page
                    exit();
                }
            } else {
                $_SESSION['message_error'] = "Username or email not found."; // Set error message for not found user
                header("Location: landingpage.php"); // Redirect to landing page
                exit();
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $_SESSION['message_error'] = "A database error occurred. Please try again later.";
            header("Location: landingpage.php"); // Redirect to landing page
            exit();
        }
    } else {
        $_SESSION['message_error'] = "Please enter both username/email and password."; // Set error message for missing fields
        header("Location: landingpage.php"); // Redirect to landing page
        exit();
    }
}
?>
