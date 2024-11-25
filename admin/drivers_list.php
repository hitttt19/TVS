<?php
include('../db_connection.php');
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../landingpage.php");
    exit();
}

// Handle form submissions for approving or rejecting the driver
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle approval or rejection
    if (isset($_POST['approveDriver']) || isset($_POST['rejectDriver'])) {
        // Ensure necessary parameters are available
        if (isset($_POST['license_id'], $_POST['view_id'])) {
            $license_id = $_POST['license_id'];
            $driver_id = $_POST['view_id'];

            // Check which button was clicked (approve or reject)
            $status = isset($_POST['approveDriver']) ? 'approved' : 'rejected';

            // Update the database with the new status
            $stmt = $pdo->prepare("UPDATE drivers SET id_photo_status = :status WHERE id = :driver_id");
            $stmt->execute([
                ':status' => $status,
                ':driver_id' => $driver_id
            ]);

            // Set session variables for Toastify message
            $_SESSION['toast_message'] = ucfirst($status) . ' successfully.';
            $_SESSION['toast_type'] = 'success';  // Can be 'success' or 'error'

            // Redirect back to the page to display the message in the modal
            header('Location: drivers_list.php');
            exit();
        }
    }
}

// Handle CRUD operations (create, update, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $license_id = trim($_POST['license_id']);
    $license_type = trim($_POST['license_type']);
    $firstname = trim($_POST['firstname']);
    $middlename = trim($_POST['middlename']);
    $lastname = trim($_POST['lastname']);
    $gender = trim($_POST['gender']);
    $date_of_birth = trim($_POST['date_of_birth']);
    $civil_status = trim($_POST['civil_status']);
    $present_address = trim($_POST['present_address']);
    $permanent_address = trim($_POST['permanent_address']);
    $nationality = trim($_POST['nationality']);
    $contact_number = trim($_POST['contact_number']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']); 
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    try {
        switch ($action) {
            case 'create':
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO drivers (license_id, license_type, firstname, middlename, lastname, gender, date_of_birth, civil_status, present_address, permanent_address, nationality, contact_number, username, email, password, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt->execute([$license_id, $license_type, $firstname, $middlename, $lastname, $gender, $date_of_birth, $civil_status, $present_address, $permanent_address, $nationality, $contact_number, $username, $email, $hashedPassword, null]);
                $_SESSION['toast_message'] = "Driver created successfully!";
                $_SESSION['toast_type'] = 'success'; // success or error
                break;

            case 'update':
                // Construct SQL query dynamically
                $query = "UPDATE drivers SET license_id = ?, license_type = ?, firstname = ?, middlename = ?, lastname = ?, gender = ?, date_of_birth = ?, civil_status = ?, present_address = ?, permanent_address = ?, nationality = ?, contact_number = ?, username = ?, email = ?";

                $params = [$license_id, $license_type, $firstname, $middlename, $lastname, $gender, $date_of_birth, $civil_status, $present_address, $permanent_address, $nationality, $contact_number, $username, $email];
                
                if ($password) {
                    // Hash the password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $query .= ", password = ?";
                    $params[] = $hashedPassword;
                }
                
                $query .= " WHERE id = ?";
                $params[] = $id;
                
                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                $_SESSION['toast_message'] = "Driver details updated successfully!";
                $_SESSION['toast_type'] = 'success';
                break;

            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM drivers WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['toast_message'] = "Driver deleted successfully!";
                $_SESSION['toast_type'] = 'success'; 
                break;
        }
        // Redirect to avoid form resubmission
        header("Location: drivers_list.php");
        exit();
    } catch (PDOException $e) {
        $error = "Error: " . htmlspecialchars($e->getMessage());
    }
}

// Search functionality
$search = isset($_GET['search']) ? '%' . trim($_GET['search']) . '%' : '%';
$stmt = $pdo->prepare("SELECT * FROM drivers WHERE license_id LIKE ? OR firstname LIKE ? OR lastname LIKE ? ORDER BY id DESC");
$stmt->execute([$search, $search, $search]);
$drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle view request (for ID photos)
if (isset($_GET['view_id'])) {
    $view_id = (int)$_GET['view_id'];
    $stmt = $pdo->prepare("SELECT * FROM drivers WHERE id = ?");
    $stmt->execute([$view_id]);
    $driverDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch offense records for the selected driver
    $license_id = $driverDetails['license_id'];
    $stmt = $pdo->prepare("SELECT datetime, offense_name, offense_rate, status FROM offense_records WHERE license_id = :license_id ORDER BY datetime DESC");
    $stmt->execute(['license_id' => $license_id]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch ID photos (Front and Back) for the selected driver
    $id_front_photo = $driverDetails['id_front_photo'];
    $id_back_photo = $driverDetails['id_back_photo'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RegulaDrive</title>
    <link rel="icon" href="../logo/RegLogo.png" id="favicon">
    <link rel="stylesheet" href="../css/Index.css">
    <link rel="stylesheet" href="../css/DriverL.css">
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
                <li class="nav-item" onclick="window.location='admin_dashboard.php';">
                    <a href="#"><img src="../icons/Dashboard.png" alt="Dashboard Icon" class="icon"><span class="text">Dashboard</span></a>
                </li>
                <li class="nav-item" onclick="window.location='offense_records.php';">
                    <a href="#"><img src="../icons/Important Note.png" alt="Offense Records Icon" class="icon"><span class="text">Offense Records</span></a>
                </li>
                <li class="nav-item" onclick="window.location='enforcers_list.php';">
                    <a href="#"><img src="../icons/enforcer.png" alt="Enforcer Icon" class="icon"><span class="text">Traffic Enforcers</span></a>
                </li>
                <li class="nav-item active" onclick="window.location='drivers_list.php';">
                    <a href="#"><img src="../icons/Driver License.png" alt="Drivers List Icon" class="icon"><span class="text">Drivers List</span></a>
                </li>
                <li class="nav-item" onclick="window.location='reports.php';">
                    <a href="#"><img src="../icons/Business Report.png" alt="Reports Icon" class="icon"><span class="text">Reports</span></a>
                </li>
                <li class="nav-item" onclick="window.location='contact.php';">
                    <a href="#"><img src="../icons/Call.png" alt="Contact Icon" class="icon"><span class="text">Contact</span></a>
                </li>
                <li class="nav-item" onclick="window.location='announcement.php';">
                    <a href="#"><img src="../icons/Commercial.png" alt="Announcement Icon" class="icon"><span class="text">Announcement</span></a>
                </li>
                <li class="nav-section"><span class="text">Maintenance</span></li>
                <li class="nav-item" onclick="window.location='offenses_list.php';">
                    <a href="#"><img src="../icons/Important Note.png" alt="Offenses List Icon" class="icon"><span class="text">Offenses List</span></a>
                </li>
                <li class="nav-item" onclick="window.location='settings.php';">
                    <a href="#"><img src="../icons/Settings.png" alt="Settings Icon" class="icon"><span class="text">Settings</span></a>
                </li>
                <li class="nav-item" onclick="window.location='../logout.php';">
                    <a href="#"><img src="../icons/logout.png" alt="Logout Icon" class="icon"><span class="text">Logout</span></a>
                </li>
            </ul>
        </nav>

               <!-- Mobile Sidebar -->
            <nav class="mobile-sidebar">
                <div class="sidebar-header">
                    <img src="../logo/RegLogo.png" alt="Reguladrive Logo" class="logo">
                    <span class="logo-text">Reguladrive</span>
                </div>
                <div class="hamburger" id="hamburger">
                    <i class="fas fa-bars"></i>
                </div>
                <ul class="nav-list">
                    <li class="nav-item" onclick="window.location='admin_dashboard.php';">
                        <a href="#"><img src="../icons/Dashboard.png" alt="Dashboard Icon" class="icon"><span class="text">Dashboard</span></a>
                    </li>
                    <li class="nav-item" onclick="window.location='offense_records.php';">
                                <a href="#"><img src="../icons/Important Note.png" alt="Offense Records Icon" class="icon"><span class="text">Offense Records</span></a>
                            </li>
                            <li class="nav-item" onclick="window.location='enforcers_list.php';">
                                <a href="#"><img src="../icons/enforcer.png" alt="Enforcer Icon" class="icon"><span class="text">Traffic Enforcers</span></a>
                            </li>
                            <li class="nav-item active" onclick="window.location='drivers_list.php';">
                                <a href="#"><img src="../icons/Driver License.png" alt="Drivers List Icon" class="icon"><span class="text">Drivers List</span></a>
                            </li>
                            <li class="nav-item" onclick="window.location='reports.php';">
                                <a href="#"><img src="../icons/Business Report.png" alt="Reports Icon" class="icon"><span class="text">Reports</span></a>
                            </li>
                            <li class="nav-item" onclick="window.location='contact.php';">
                                <a href="#"><img src="../icons/Call.png" alt="Contact Icon" class="icon"><span class="text">Contact</span></a>
                            </li>
                            <li class="nav-item" onclick="window.location='announcement.php';">
                                <a href="#"><img src="../icons/Commercial.png" alt="Announcement Icon" class="icon"><span class="text">Announcement</span></a>
                            </li>
                            <li class="nav-section"><span class="text">Maintenance</span></li>
                            <li class="nav-item" onclick="window.location='offenses_list.php';">
                                <a href="#"><img src="../icons/Important Note.png" alt="Offenses List Icon" class="icon"><span class="text">Offenses List</span></a>
                            </li>
                            <li class="nav-item" onclick="window.location='settings.php';">
                                <a href="#"><img src="../icons/Settings.png" alt="Settings Icon" class="icon"><span class="text">Settings</span></a>
                            </li>
                            <li class="nav-item" onclick="window.location='../logout.php';">
                                <a href="#"><img src="../icons/logout.png" alt="Logout Icon" class="icon"><span class="text">Logout</span></a>
                            </li>
                </ul>
            </nav>
        <!-- Overlay for mobile sidebar -->
        <div class="overlay" onclick="toggleSidebar()"></div>

        <!-- Main Content Area -->
        <div class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <h2>Bogo City Traffic Violations System</h2>
                </div>
                <div class="user-menu">
                    <img src="../icons/profile.png" class="user-icon">
                    <span class="user-name">Admin</span>
                </div>
            </header>

            <section id="drivers-list" class="content-section drivers-list active">
                <h2>List of Drivers</h2>
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <div class="controls">
                    <button class="create-new" onclick="showDriverForm()">Create New</button>
                    <div class="search-container">
                        <label for="search">Search:</label>
                        <input type="text" id="search" name="search" placeholder="Search Drivers" oninput="searchRecords()" value="<?php echo htmlspecialchars(isset($_GET['search']) ? $_GET['search'] : ''); ?>">
                    </div>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>License ID</th>
                                <th>Name</th>
                                <th>License Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="driverTable">
                            <?php if (empty($drivers)): ?>
                                <tr>
                                    <td colspan="4">No drivers found.</td>
                                </tr>
                            <?php else: 
                                foreach ($drivers as $driver): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($driver['license_id']); ?></td>
                                    <td><?php echo htmlspecialchars($driver['firstname'] . " " . $driver['middlename'] . " " . $driver['lastname']); ?></td>
                                    <td><?php echo htmlspecialchars($driver['license_type']); ?></td>
                                    <td>
                                        <button class="view-btn" onclick="viewDriver(<?php echo htmlspecialchars($driver['id']); ?>)">
                                            <img src="../icons/view.png" alt="View" />
                                        </button>
                                        <button class="edit-btn" onclick="editDriver(<?php echo htmlspecialchars($driver['id']); ?>, '<?php echo htmlspecialchars($driver['license_id']); ?>', '<?php echo htmlspecialchars($driver['license_type']); ?>', '<?php echo htmlspecialchars($driver['firstname']); ?>', '<?php echo htmlspecialchars($driver['middlename']); ?>', '<?php echo htmlspecialchars($driver['lastname']); ?>', '<?php echo htmlspecialchars($driver['gender']); ?>', '<?php echo htmlspecialchars($driver['date_of_birth']); ?>', '<?php echo htmlspecialchars($driver['present_address']); ?>', '<?php echo htmlspecialchars($driver['permanent_address']); ?>', '<?php echo htmlspecialchars($driver['nationality']); ?>', '<?php echo htmlspecialchars($driver['contact_number']); ?>', '<?php echo htmlspecialchars($driver['username']); ?>', '<?php echo htmlspecialchars($driver['email']); ?>')">
                                            <img src="../icons/edit.png" alt="Edit" />
                                        </button>
                                        <form action="drivers_list.php" method="post" style="display:inline;" onsubmit="return confirmDelete()">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($driver['id']); ?>">
                                       <button type="submit" name="action" value="delete" class="delete-btn">
                                       <img src="../icons/delete.png" alt="Delete" />
                                       </button>
                                    </form>

                                   <script>
                                   function confirmDelete() {
                                   return confirm("Are you sure you want to delete this driver?");
                                   }
                                  </script>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
        <!-- Modal for Create/Edit Driver -->
        <div id="driverFormModal" class="modal">
            <div class="modal-content">
                <span class="close" id="closeDriverForm">&times;</span>
                <h2 id="formTitle">Create New Driver</h2>
                <form action="drivers_list.php" method="post">
                    <input type="hidden" id="driverId" name="id">

                    <div class="form-group">
                        <label for="license_id">License ID or Other ID:</label>
                        <input type="text" id="license_id" name="license_id" required>
                    </div>

                    <div class="form-group">
                        <label for="license_type">License Type:</label>
                        <select id="license_type" name="license_type" required>
                            <option value="">Select License Type</option>
                            <option value="Student Permit">Student Permit</option>
                            <option value="Non-Professional">Non-Professional</option>
                            <option value="Professional">Professional</option>
                            <option value="Other ID">Other ID</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="firstname">First Name:</label>
                        <input type="text" id="firstname" name="firstname" required>
                    </div>

                    <div class="form-group">
                        <label for="middlename">Middle Name:</label>
                        <input type="text" id="middlename" name="middlename">
                    </div>

                    <div class="form-group">
                        <label for="lastname">Last Name:</label>
                        <input type="text" id="lastname" name="lastname" required>
                    </div>

                    <div class="form-group">
                        <label for="gender">Gender:</label>
                        <select id="gender" name="gender" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="date_of_birth">Date of Birth:</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" required>
                    </div>

                    <div class="form-group">
                        <label for="civil_status">Civil Status:</label>
                        <select name="civil_status" id="civil_status" required>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Divorced">Divorced</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Separated">Separated</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="present_address">Present Address:</label>
                        <textarea id="present_address" name="present_address" rows="3" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="permanent_address">Permanent Address:</label>
                        <textarea id="permanent_address" name="permanent_address" rows="3" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="nationality">Nationality:</label>
                        <input type="text" id="nationality" name="nationality" required>
                    </div>

                    <div class="form-group">
                        <label for="contact_number">Contact Number:</label>
                        <input type="text" id="contact_number" name="contact_number" required>
                    </div>

                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password">
                    </div>

                    <div class="form-actions">
                        <button type="submit" id="formAction" name="action" value="create">Save</button>
                    </div>
                </form>
            </div>
        </div>
    <!-- Modal for View Driver -->
<div id="viewDriverModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <button class="print-btn"><span class="icon">🖨️</span> <span class="text">Print</span></button>
            <h2>Driver's Information</h2>
            <button class="close" id="closeViewDriver"><span class="icon">✖</span><span class="text">Close</span></button>
        </div>
        
        <?php if (isset($driverDetails)): ?>
        <div class="container-section">
            <div class="driver-info">
                <div class="info-left">
                    <p><strong>License ID:</strong> <?php echo htmlspecialchars($driverDetails['license_id']); ?></p>
                    <p><strong>License Type:</strong> <?php echo htmlspecialchars($driverDetails['license_type']); ?></p>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($driverDetails['firstname']) . " " . htmlspecialchars($driverDetails['middlename'][0]) . ". " . htmlspecialchars($driverDetails['lastname']); ?></p>
                    <p><strong>DOB:</strong> <?php echo htmlspecialchars((new DateTime($driverDetails['date_of_birth']))->format('M d, Y')); ?></p>
                    <p><strong>Civil Status:</strong> <?php echo htmlspecialchars($driverDetails['civil_status']); ?></p>
                    <p><strong>Present Address:</strong> <?php echo htmlspecialchars($driverDetails['present_address']); ?></p>
                    <p><strong>Permanent Address:</strong> <?php echo htmlspecialchars($driverDetails['permanent_address']); ?></p>
                    <p><strong>Contact No.:</strong> <?php echo htmlspecialchars($driverDetails['contact_number']); ?></p>
                    <p><strong>Nationality:</strong> <?php echo htmlspecialchars($driverDetails['nationality']); ?></p>
                </div>
                <div class="info-right">
                    <!-- Display Driver's Profile Photo -->
                    <div class="profile-photo-container">
                        <img src="<?php echo !empty($driverDetails['photo']) ? (strpos($driverDetails['photo'], '../') === 0 ? htmlspecialchars($driverDetails['photo']) : '../' . htmlspecialchars($driverDetails['photo'])) : '../image/defimage.png'; ?>" alt="Driver's Photo" class="profile-photo" />
                        <?php if ($driverDetails['id_photo_status'] === 'approved'): ?>
                            <span class="verified-label">Verified</span>
                        <?php elseif ($driverDetails['id_photo_status'] === 'rejected'): ?>
                            <span class="not-verified-label">Not Verified</span>
                        <?php else: ?>
                            <span class="pending-label">Not Verified</span>
                        <?php endif; ?>

                        <!-- Button to show ID Photos -->
                        <button id="viewIDPhotosBtn" class="view-photos-btn">View ID Photos</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section for ID Front and ID Back Photos (Initially Hidden) -->
        <div class="container-section" id="idPhotosSection" style="display:none;">
            <h3>Driver's ID Photos</h3>
            <div class="id-photos">
                <div class="photo-left">
                    <h4>ID Front</h4>
                    <!-- Display ID Front Photo -->
                    <img src="<?php echo !empty($driverDetails['id_front_photo']) 
                            ? '../' . htmlspecialchars($driverDetails['id_front_photo']) 
                            : '../image/defimage.png'; ?>" alt="ID Front Photo" class="id-photo" />
                </div>
                <div class="photo-right">
                    <h4>ID Back</h4>
                    <!-- Display ID Back Photo -->
                    <img src="<?php echo !empty($driverDetails['id_back_photo']) 
                            ? '../' . htmlspecialchars($driverDetails['id_back_photo']) 
                            : '../image/defimage.png'; ?>" alt="ID Back Photo" class="id-photo" />
                </div>
            </div>

            <!-- Approve and Reject Buttons Form -->
            <form method="POST" action="">
                <input type="hidden" name="license_id" value="<?php echo htmlspecialchars($driverDetails['license_id']); ?>">
                <input type="hidden" name="view_id" value="<?php echo htmlspecialchars($driverDetails['id']); ?>">
                <button type="submit" name="approveDriver" class="approve-btn">Approve</button>
                <button type="submit" name="rejectDriver" class="reject-btn">Reject</button>
            </form>
        </div>

        <!-- Offense Records Section -->
        <div class="container-section">
            <h3>Offense Records</h3>
            <table class="offense-records">
                <thead>
                    <tr>
                        <th>DateTime</th>
                        <th>Offense</th>
                        <th>Fine</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="4">No offense records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($records as $record): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($record['datetime']))); ?></td>
                                <td><?php echo htmlspecialchars($record['offense_name']); ?></td>
                                <td>₱<?php echo htmlspecialchars(number_format($record['offense_rate'], 2)); ?></td>
                                <td>
                                    <span class="status <?php echo strtolower($record['status']); ?>">
                                        <?php echo htmlspecialchars($record['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>



<script>
   document.addEventListener('DOMContentLoaded', function () {
    <?php if (isset($_SESSION['toast_message'])): ?>
        // Check for toast message and type from session
        Toastify({
            text: "<?php echo $_SESSION['toast_message']; ?>",
            duration: 3000,
            backgroundColor: "<?php echo ($_SESSION['toast_type'] == 'success') ? 'green' : 'red'; ?>", // Green for success, red for error
            close: true,
            gravity: "top", // Top notification
            position: "right", // Right side of the page
        }).showToast();

        // Unset the session message after displaying it
        <?php unset($_SESSION['toast_message']); ?>
        <?php unset($_SESSION['toast_type']); ?>
    <?php endif; ?>
});
</script>

<script>
// Toggle the visibility of the ID photos section when the "View ID Photos" button is clicked
document.getElementById('viewIDPhotosBtn').addEventListener('click', function() {
    var idPhotosSection = document.getElementById('idPhotosSection');
    if (idPhotosSection.style.display === 'none') {
        idPhotosSection.style.display = 'block';  // Show ID photos section
    } else {
        idPhotosSection.style.display = 'none';   // Hide ID photos section
    }
});

// Close the modal when the close button is clicked
document.getElementById('closeViewDriver').addEventListener('click', function() {
    document.getElementById('viewDriverModal').style.display = 'none';
});

document.getElementById('approveRejectForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent default form submission

    var formData = new FormData(this); // Create FormData from the form

    // Send AJAX request to the server
    fetch('drivers_list.php', {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json()) // Expect JSON response
    .then(data => {
        // Check the response from the server
        if (data.success) {
            // Display success message or update the UI
            alert(data.message); // Show a success message (this can be a custom modal or notification)
            // Optionally, close or reset the modal here
            document.getElementById('viewDriverModal').style.display = 'none'; // Close modal if needed
        } else {
            // Handle errors (e.g., invalid input or system error)
            alert(data.message || 'An error occurred. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
});
</script>

    <script src="../js/script.js"></script>
    <script src="../js/driverlistmodals.js"></script>
    <script src="../js/driverdetailsprint.js"></script>
    
</body>
</html>
