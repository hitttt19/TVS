<?php
session_start();
if (!isset($_SESSION['driver_id'])) {
    header("Location: ../landingpage.php");
    exit();
}

// Database connection
include('../db_connection.php');
// Initialize variables
$aboutUs = ''; 
$systemName = 'Bogo City Traffic Violations System'; // Default system name
$shortName = 'RegulaDrive'; // Default short name
$logoPath = 'logo/RegLogo.png'; // Default logo

$driverId = $_SESSION['driver_id'];
$query = "SELECT photo FROM drivers WHERE id = :id";
$stmt = $pdo->prepare($query);
$stmt->execute(['id' => $driverId]);
$driverDetails = $stmt->fetch(PDO::FETCH_ASSOC);

// Default image if no profile photo exists
$profileImage = !empty($driverDetails['photo']) ? htmlspecialchars($driverDetails['photo']) : '../icons/profile.png';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if we are updating driver information
    if (isset($_POST['action']) && $_POST['action'] === 'update_info') {
        $license_id = $_POST['license_id'];
        $firstname = $_POST['firstname'];
        $middlename = $_POST['middlename'];
        $lastname = $_POST['lastname'];
        $date_of_birth = $_POST['date_of_birth'];
        $contact_number = $_POST['contact_number'];
        $civil_status = $_POST['civil_status'];
        $nationality = $_POST['nationality'];
        $present_address = $_POST['present_address'];
        $permanent_address = $_POST['permanent_address'];

        // Validate contact number (must be exactly 11 digits)
        if (!preg_match('/^\d{11}$/', $contact_number)) {
            $_SESSION['message_error'] = "Contact number must be exactly 11 digits.";
            header("Location: driver_profile.php"); // Redirect back to form
            exit();
        } else {
            // Continue with updating the database if validation is successful
            $stmt = $pdo->prepare("UPDATE drivers SET firstname = ?, middlename = ?, lastname = ?, date_of_birth = ?, contact_number = ?, civil_status = ?, nationality = ?, present_address = ?, permanent_address = ? WHERE license_id = ?");
            $stmt->execute([$firstname, $middlename, $lastname, $date_of_birth, $contact_number, $civil_status, $nationality, $present_address, $permanent_address, $license_id]);

            $_SESSION['message_success'] = "Driver information updated successfully.";
            header("Location: driver_profile.php"); // Redirect after success
            exit();
        }
    }

    // Check if we are changing the password
    if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
        $driver_id = $_SESSION['driver_id'];
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Fetch the current hashed password from the database
        $stmt = $pdo->prepare("SELECT password FROM drivers WHERE id = ?");
        $stmt->execute([$driver_id]);
        $driver = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($driver) {
            // Verify the current password
            if (password_verify($current_password, $driver['password'])) {
                // Check if new password and confirmation match
                if ($new_password === $confirm_password) {
                    // Hash the new password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                    // Update the password in the database
                    $update_stmt = $pdo->prepare("UPDATE drivers SET password = ? WHERE id = ?");
                    $update_stmt->execute([$hashed_password, $driver_id]);

                    // Set success message in the session
                    $_SESSION['message_success'] = "Password changed successfully.";
                    header("Location: driver_profile.php");
                    exit();
                } else {
                    $password_error = "New passwords do not match.";
                }
            } else {
                $password_error = "Current password is incorrect.";
            }
        } else {
            $password_error = "Driver not found.";
        }
    }
}

// Handle photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $license_id = $_POST['license_id'];
    $photo = $_FILES['photo'];

    // Validate file
    if ($photo['error'] === UPLOAD_ERR_OK) {
        // Check if file is valid
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_file_size = 10 * 1024 * 1024; // 10 MB
        if (!in_array($photo['type'], $allowed_types)) {
            $error_message = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        } elseif ($photo['size'] > $max_file_size) {
            $error_message = "File size exceeds the 10MB limit.";
        } else {
            $uploads_dir = '../uploads/';
            $tmp_name = $photo['tmp_name'];
            $name = basename($photo['name']);
            $unique_name = uniqid('photo_', true) . '_' . $name;
            $file_path = $uploads_dir . $unique_name;

            // Move uploaded file to the specified directory
            if (move_uploaded_file($tmp_name, $file_path)) {
                // Update the database with the new photo path
                $stmt = $pdo->prepare("UPDATE drivers SET photo = ? WHERE license_id = ?");
                $stmt->execute([$file_path, $license_id]);
                $_SESSION['message_success'] = "Profile updated successfully.";
                header("Location: driver_profile.php");
                exit();
            } else {
                $error_message = "Failed to upload photo.";
            }
        }
    } else {
        $error_message = "Error uploading file.";
    }
}


// Get the driver's license ID
$driver_id = $_SESSION['driver_id'];
$stmt = $pdo->prepare("SELECT license_id FROM drivers WHERE id = ?");
$stmt->execute([$driver_id]);
$driver = $stmt->fetch(PDO::FETCH_ASSOC);

if ($driver === false) {
    die('Driver not found or no data returned.');
}

$license_id = $driver['license_id'];

// Fetch driver's full details
$stmt = $pdo->prepare("SELECT * FROM drivers WHERE license_id = ?");
$stmt->execute([$license_id]);
$driver = $stmt->fetch(PDO::FETCH_ASSOC);

// Photo verification status
$id_photo_status = $driver['id_photo_status'] ?? 'pending'; // default to pending if no status
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RegulaDrive</title>
    <link rel="icon" href="../logo/RegLogo.png" id="favicon">
    <link rel="stylesheet" href="../Drivercss/DriverIndex.css">
    <link rel="stylesheet" href="../Drivercss/ProfSec.css">
    <link rel="stylesheet" href="../css/toast.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.11.2/toastify.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.11.2/toastify.min.js"></script>
</head>
<body>
    <div class="container">
    <nav class="sidebar">
            <div class="sidebar-header">
                <img src="../logo/RegLogo.png" alt="Reguladrive Logo" class="logo">
                <h2 class="title">REGULADRIVE</h2>
            </div>
            <ul class="nav-list">
                <li class="nav-item" onclick="window.location='driver_dashboard.php';">
                    <a href="#">
                        <img src="../icons/Dashboard.png" alt="Dashboard Icon" class="icon">
                        <span class="text">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item" onclick="window.location='driver_offenseRecords.php';">
                    <a href="#">
                        <img src="../icons/Note.png" alt="Offense Records Icon" class="icon">
                        <span class="text">Offense Records</span>
                    </a>
                </li>
                <li class="nav-item" onclick="window.location='driver_offenseList.php';">
                    <a href="#">
                        <img src="../icons/Important Note.png" alt="Offense List Icon" class="icon">
                        <span class="text">Offense List</span>
                    </a>
                </li>
                <li class="nav-item" onclick="window.location='driver_notifications.php';">
                    <a href="#">
                        <img src="../icons/Commercial.png" alt="Notification Icon" class="icon">
                        <span class="text">Notification</span>
                    </a>
                </li>
                <li class="nav-section" onclick="window.location='driver_helpDesk.php';">
                    <span class="text">Help Desk</span>
                    <div class="help-desk-dropdown">
                        <ul>
                            <?php foreach ($contacts as $contact): ?>
                                <li>
                                    <strong><?php echo htmlspecialchars($contact['service_provider']); ?></strong> - 
                                    <?php echo htmlspecialchars($contact['mobile_no']); ?> 
                                    <em>(<?php echo htmlspecialchars($contact['service_type']); ?>)</em>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </li>
            </ul>
            <footer style="background-color: #004d40; color: #fff; text-align: center; padding: 20px 10px;">
    <div>
        <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($systemName); ?>. All Rights Reserved.</p>
    </div>
        </nav>
        <div class="main-content">
            <header class="header">
                <div class="header-left">
                    <h2>Bogo City Traffic Violations System</h2>
                </div>
                <div class="user-menu">
                    <!-- Display the driver's profile photo -->
                    <img src="<?php echo !empty($driver['photo']) ? (strpos($driver['photo'], '../') === 0 ? htmlspecialchars($driver['photo']) : '../' . htmlspecialchars($driver['photo'])) : '../icons/default.jpg'; ?>" alt="Driver's Profile Photo" class="user-icon">
                    <span class="user-name">
                        <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Unknown'; ?>
                    </span>
                    <div class="user-dropdown">
                        <a href="driver_profile.php">
                            <span class="text">My Profile</span>
                        </a>
                        <a href="../logout.php">Logout</a>
                    </div>
                </div>
            </header>

            <section id="profile" class="content-section profile active">
                <h2>Driver's Information</h2>
                <div class="profile-container">
                    <div class="profile-details">
                        <div class="profile-item">
                            <label>License ID:</label>
                            <span><?php echo htmlspecialchars($driver['license_id'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="profile-item">
                            <label>License Type:</label>
                            <span><?php echo htmlspecialchars($driver['license_type'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="profile-item">
                            <label>Name:</label>
                            <span><?php echo htmlspecialchars(($driver['firstname'] ?? 'N/A') . ' ' . ($driver['lastname'] ?? 'N/A')); ?></span>
                        </div>
                        <div class="profile-item">
                            <label>DOB:</label>
                            <?php
                                $dateOfBirth = isset($driver['date_of_birth']) ? new DateTime($driver['date_of_birth']) : null;
                                $formattedDOB = $dateOfBirth ? $dateOfBirth->format('F d, Y') : 'N/A';
                            ?>
                            <span><?php echo htmlspecialchars($formattedDOB); ?></span>
                        </div>
                        <div class="profile-item">
                            <label>Contact No.:</label>
                            <span><?php echo htmlspecialchars($driver['contact_number'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="profile-item">
                            <label>Civil Status:</label>
                            <span><?php echo htmlspecialchars($driver['civil_status'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="profile-item">
                            <label>Nationality:</label>
                            <span><?php echo htmlspecialchars($driver['nationality'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="profile-item">
                            <label>Present Address:</label>
                            <span><?php echo htmlspecialchars($driver['present_address'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="profile-item">
                            <label>Permanent Address:</label>
                            <span><?php echo htmlspecialchars($driver['permanent_address'] ?? 'N/A'); ?></span>
                        </div>
                    </div>
                    
                    <div class="profile-section">
                        <div class="profile-image">
                            <img src="<?php echo !empty($driver['photo']) ? (strpos($driver['photo'], '../') === 0 ? htmlspecialchars($driver['photo']) : '../' . htmlspecialchars($driver['photo'])) : '../icons/default.jpg'; ?>" alt="Driver's Photo" class="profile-photo" />
                        </div>

                        <!-- Display ID Photo Status -->
                        <div class="id-photo-status">
                            <?php if ($id_photo_status === 'approved'): ?>
                                <span class="verified-label">Verified</span>
                            <?php elseif ($id_photo_status === 'rejected'): ?>
                                <span class="not-verified-label">Not Verified</span>
                            <?php else: ?>
                                <span class="pending-label">Pending Verification</span>
                            <?php endif; ?>
                        </div>

                        <div class="upload-section">
                        <form id="uploadForm" action="" method="POST" enctype="multipart/form-data">
                            <label class="file-upload">
                                <input type="file" name="photo" accept="image/*" required onchange="uploadPhoto()">
                                <i class="fas fa-upload"></i> Upload Photo
                            </label>
                            <input type="hidden" name="license_id" value="<?php echo htmlspecialchars($driver['license_id']); ?>">
                        </form>
                            <div id="uploadStatus"></div>
                            <?php if (isset($error_message)): ?>
                                <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="action-buttons">
                            <div class="tooltip">
                                <button onclick="openModal()" class="edit-btn"><i class="fas fa-user-edit"></i></button>
                                <span class="tooltiptext">Edit Profile</span>
                            </div>
                            <div class="tooltip">
                                <button onclick="openChangePasswordModal()" class="change-password-btn"><i class="fas fa-lock"></i></button>
                                <span class="tooltiptext">Change Password</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Edit Modal -->
            <div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Edit Profile</h2>
        <form method="POST">
            <input type="hidden" name="action" value="update_info">
            <input type="hidden" name="license_id" value="<?php echo htmlspecialchars($driver['license_id']); ?>">

            <div class="form-group">
                <label for="firstname">First Name:</label>
                <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($driver['firstname']); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="middlename">Middle Name:</label>
                <input type="text" id="middlename" name="middlename" value="<?php echo htmlspecialchars($driver['middlename']); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="lastname">Last Name:</label>
                <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($driver['lastname']); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="dob">Date of Birth:</label>
                <input type="date" id="dob" name="date_of_birth" value="<?php echo htmlspecialchars($driver['date_of_birth']); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="contact_number">Contact No.:</label>
                <input type="text" id="contact_number" name="contact_number" 
                    value="<?php echo htmlspecialchars($driver['contact_number']); ?>" required>
                <?php if (isset($contact_number_error)): ?>
                    <div class="error" style="color: red;">
                        <?php echo $contact_number_error; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="civil_status">Civil Status:</label>
                <input type="text" id="civil_status" name="civil_status" value="<?php echo htmlspecialchars($driver['civil_status']); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="nationality">Nationality:</label>
                <input type="text" id="nationality" name="nationality" value="<?php echo htmlspecialchars($driver['nationality']); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="present_address">Present Address:</label>
                <input type="text" id="present_address" name="present_address" value="<?php echo htmlspecialchars($driver['present_address']); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="permanent_address">Permanent Address:</label>
                <input type="text" id="permanent_address" name="permanent_address" value="<?php echo htmlspecialchars($driver['permanent_address']); ?>" readonly>
            </div>

            <button type="submit" class="submit-btn">Update Information</button>
        </form>
    </div>
</div>


            <!-- Change Password Modal -->
            <div id="changePasswordModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeChangePasswordModal()">&times;</span>
                    <h2>Change Password</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">

                        <div class="form-group">
                            <label for="current_password">Current Password:</label>
                            <div class="password-field">
                                <input type="password" id="current_password" name="current_password" required>
                                <i class="fas fa-eye-slash toggle-password" onclick="togglePasswordVisibility('current_password', this)"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password:</label>
                            <div class="password-field">
                                <input type="password" id="new_password" name="new_password" required>
                                <i class="fas fa-eye-slash toggle-password" onclick="togglePasswordVisibility('new_password', this)"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password:</label>
                            <div class="password-field">
                                <input type="password" id="confirm_password" name="confirm_password" required>
                                <i class="fas fa-eye-slash toggle-password" onclick="togglePasswordVisibility('confirm_password', this)"></i>
                            </div>
                        </div>

                        <button type="submit" class="submit-btn">Change Password</button>
                    </form>

                    <?php if (isset($password_error)): ?>
                        <div class="error"><?php echo htmlspecialchars($password_error); ?></div>
                    <?php endif; ?>
                    <?php if (isset($password_success)): ?>
                        <div class="success"><?php echo htmlspecialchars($password_success); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['message_success'])): ?>
    <script>
        Toastify({
            text: "<?php echo addslashes(htmlspecialchars($_SESSION['message_success'])); ?>",
            duration: 2500,  
            close: true,
            gravity: "top",  
            position: 'right',  
            backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)", 
            stopOnFocus: true, 
            className: "toastify-enter toastify-glass",  
            onClick: function() {}  
        }).showToast();
    </script>
    <?php unset($_SESSION['message_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['message_error'])): ?>
    <script>
        Toastify({
            text: "<?php echo addslashes(htmlspecialchars($_SESSION['message_error'])); ?>",
            duration: 2500, 
            close: true,
            gravity: "top",  
            position: 'right',  
            backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)",  
            stopOnFocus: true,  
            className: "toastify-enter toastify-glass",  
            onClick: function() {}  
        }).showToast();
    </script>
    <?php unset($_SESSION['message_error']); ?>
<?php endif; ?>


    <script>
        function openModal() {
            document.getElementById('editModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Close the modal when the user clicks outside of it
        window.onclick = function(event) {
            var modal = document.getElementById('editModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        function uploadPhoto() {
        const statusDiv = document.getElementById('uploadStatus');
        statusDiv.innerHTML = 'Uploading...'; // Show uploading message
        document.getElementById('uploadForm').submit();
    }
    </script>

    <script>
        // Function to toggle password visibility
        function togglePasswordVisibility(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text'; // Show password
                icon.classList.remove('fa-eye-slash'); // Change icon
                icon.classList.add('fa-eye'); // Change icon
            } else {
                input.type = 'password'; // Hide password
                icon.classList.remove('fa-eye'); // Change icon
                icon.classList.add('fa-eye-slash'); // Change icon
            }
        }

        // Function to open the Change Password modal
        function openChangePasswordModal() {
            document.getElementById('changePasswordModal').style.display = 'block';
        }

        // Function to close the Change Password modal
        function closeChangePasswordModal() {
            document.getElementById('changePasswordModal').style.display = 'none';
        }

        // Close the modal when the user clicks outside of it
        window.onclick = function(event) {
            var modal = document.getElementById('changePasswordModal');
            if (event.target === modal) {
                closeChangePasswordModal(); // Call close function
            }
        }
    </script>

    <script src="js/script.js"></script>
</body>
</html>