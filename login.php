<?php
require 'db_connection.php'; 
session_start(); // Start session at the beginning
$secretKey = '6Lfyb4oqAAAAAJukJE8nXQOLugz9m9xPrbxX3buD'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login']) && isset($_POST['password']) && isset($_POST['g-recaptcha-response'])) {
        $login = htmlspecialchars(trim($_POST['login']));
        $password = htmlspecialchars(trim($_POST['password']));
        $recaptchaResponse = $_POST['g-recaptcha-response']; 
        $ip = $_SERVER['REMOTE_ADDR']; 

        $recaptchaVerifyUrl = "https://www.google.com/recaptcha/api/siteverify";
        $response = file_get_contents($recaptchaVerifyUrl . "?secret=" . $secretKey . "&response=" . $recaptchaResponse . "&remoteip=" . $ip);
        $responseKeys = json_decode($response, true);

        if (intval($responseKeys['success']) !== 1) {
            $_SESSION['message_error'] = "CAPTCHA verification failed. Please try again.";
            header("Location: landingpage.php");
            exit();
        }

        define('MAX_ATTEMPTS', 1); // Maximum allowed attempts
        define('LOCKOUT_TIME', 120); // Lockout time in seconds (2 minutes)

        // Function to check if user is locked out
        function is_locked_out($pdo, $login, $ip) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) AS attempt_count, MAX(attempt_time) AS last_attempt
                FROM login_attempts
                WHERE (login = :login OR ip_address = :ip)
                AND attempt_time > DATE_SUB(NOW(), INTERVAL :lockout_time SECOND)
            ");
            $stmt->execute([
                'login' => $login,
                'ip' => $ip,
                'lockout_time' => LOCKOUT_TIME
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['attempt_count'] >= MAX_ATTEMPTS) {
                $last_attempt_time = strtotime($result['last_attempt']);
                $remaining_lockout = ($last_attempt_time + LOCKOUT_TIME) - time();
                if ($remaining_lockout > 0) {
                    return $remaining_lockout;
                }
            }
            return false;
        }

        // Record a failed login attempt
        function record_failed_attempt($pdo, $login, $ip) {
            $stmt = $pdo->prepare("
                INSERT INTO login_attempts (login, attempt_time, ip_address)
                VALUES (:login, NOW(), :ip)
            ");
            $stmt->execute([
                'login' => $login,
                'ip' => $ip
            ]);
        }

        // Main login logic
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['login']) && isset($_POST['password'])) {
                $login = htmlspecialchars(trim($_POST['login'])); // This can be either username or email
                $password = htmlspecialchars(trim($_POST['password']));
                $ip = $_SERVER['REMOTE_ADDR']; // Get user's IP address

                // Check if user is locked out
                $lockout = is_locked_out($pdo, $login, $ip);
                if ($lockout) {
                    $lockout_minutes = floor($lockout / 60); // Calculate full minutes
                    $lockout_seconds = $lockout % 60; // Calculate remaining seconds

                    if ($lockout_minutes > 0) {
                        $_SESSION['message_error'] = "Too many failed login attempts. Try again in " . $lockout_minutes . " minutes and " . $lockout_seconds . " seconds.";
                    } else {
                        $_SESSION['message_error'] = "Too many failed login attempts. Try again in " . $lockout_seconds . " seconds.";
                    }

                    header("Location: landingpage.php");
                    exit();
                }

                try {
                    // Determine if the login is an email or username
                    if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
                        $stmt = $pdo->prepare("
                            SELECT id, username, password, 'admin' AS user_type FROM admins WHERE email = ?
                            UNION
                            SELECT id, username, password, 'driver' AS user_type FROM drivers WHERE email = ?
                            UNION
                            SELECT id, username, password, 'enforcer' AS user_type FROM traffic_enforcers WHERE email = ?
                        ");
                        $stmt->execute([$login, $login, $login]);
                    } else {
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
                        if (password_verify($password, $user['password'])) {
                            // Login success - Clear any previous failed attempts
                            $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE login = :login OR ip_address = :ip");
                            $stmt->execute(['login' => $login, 'ip' => $ip]);

                            // Set session variables based on user type
                            if ($user['user_type'] == 'admin') {
                                $_SESSION['admin_id'] = $user['id'];
                                $_SESSION['username'] = $user['username'];
                                $_SESSION['message_success'] = "Login successful!";
                                header("Location: admin/admin_dashboard.php");
                            } elseif ($user['user_type'] == 'driver') {
                                $_SESSION['driver_id'] = $user['id'];
                                $_SESSION['username'] = $user['username'];
                                $_SESSION['message_success'] = "Login successful!";
                                header("Location: driver/driver_dashboard.php");
                            } elseif ($user['user_type'] == 'enforcer') {
                                $_SESSION['enforcer_id'] = $user['id'];
                                $_SESSION['username'] = $user['username'];
                                $_SESSION['message_success'] = "Login successful!";
                                header("Location: enforcer/enforcer_dashboard.php");
                            }
                            exit();
                        } else {
                            // Record failed attempt
                            record_failed_attempt($pdo, $login, $ip);
                            $_SESSION['message_error'] = "Invalid password. Please try again.";
                            header("Location: landingpage.php");
                            exit();
                        }
                    } else {
                        // Record failed attempt
                        record_failed_attempt($pdo, $login, $ip);
                        $_SESSION['message_error'] = "Username or email not found.";
                        header("Location: landingpage.php");
                        exit();
                    }
                } catch (PDOException $e) {
                    error_log("Database error: " . $e->getMessage());
                    $_SESSION['message_error'] = "A database error occurred. Please try again later.";
                    header("Location: landingpage.php");
                    exit();
                }
            } else {
                $_SESSION['message_error'] = "Please enter both username/email and password.";
                header("Location: landingpage.php");
                exit();
            }
        }
    }
}
?>
