<?php
session_start();
include('../db_connection.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../landingpage.php");
    exit();
}

// Handle POST requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle create action
    if (isset($_POST['action']) && $_POST['action'] === 'create') {
        $badge_id = trim($_POST['badge_id']);
        $firstname = trim($_POST['firstname']);
        $middlename = trim($_POST['middlename']);
        $lastname = trim($_POST['lastname']);
        $gender = trim($_POST['gender']);
        $date_of_birth = trim($_POST['date_of_birth']);
        $present_address = trim($_POST['present_address']);
        $permanent_address = trim($_POST['permanent_address']);
        $contact_number = trim($_POST['contact_number']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

        // Check for duplicate badge_id
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM traffic_enforcers WHERE badge_id = ?");
        $checkStmt->execute([$badge_id]);
        if ($checkStmt->fetchColumn() > 0) {
            $_SESSION['message_error'] = "Badge ID already exists.";
            header("Location: enforcers_list.php");
            exit();
        }

        // Proceed with insert
        try {
            $stmt = $pdo->prepare("INSERT INTO traffic_enforcers (badge_id, firstname, middlename, lastname, gender, date_of_birth, present_address, permanent_address, contact_number, username, email, password, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$badge_id, $firstname, $middlename, $lastname, $gender, $date_of_birth, $present_address, $permanent_address, $contact_number, $username, $email, $password, null]);

            $_SESSION['message_success'] = "Traffic enforcer account added successfully.";
            header("Location: enforcers_list.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['message_error'] = "Error adding account: " . htmlspecialchars($e->getMessage());
        }
    }

    // Handle edit action
    if (isset($_POST['action']) && $_POST['action'] === 'edit' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $badge_id = trim($_POST['badge_id']);
        $firstname = trim($_POST['firstname']);
        $middlename = trim($_POST['middlename']);
        $lastname = trim($_POST['lastname']);
        $gender = trim($_POST['gender']);
        $date_of_birth = trim($_POST['date_of_birth']);
        $present_address = trim($_POST['present_address']);
        $permanent_address = trim($_POST['permanent_address']);
        $contact_number = trim($_POST['contact_number']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);

        // Check for duplicate badge_id, ignoring the current enforcer
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM traffic_enforcers WHERE badge_id = ? AND id != ?");
        $checkStmt->execute([$badge_id, $id]);
        if ($checkStmt->fetchColumn() > 0) {
            $_SESSION['message_error'] = "Badge ID already exists.";
            header("Location: enforcers_list.php");
            exit();
        }

        // Proceed with update
        try {
            $updateStmt = $pdo->prepare("UPDATE traffic_enforcers SET badge_id = ?, firstname = ?, middlename = ?, lastname = ?, gender = ?, date_of_birth = ?, present_address = ?, permanent_address = ?, contact_number = ?, username = ?, email = ? WHERE id = ?");
            $updateStmt->execute([$badge_id, $firstname, $middlename, $lastname, $gender, $date_of_birth, $present_address, $permanent_address, $contact_number, $username, $email, $id]);

            $_SESSION['message_success'] = "Traffic enforcer account updated successfully.";
            header("Location: enforcers_list.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['message_error'] = "Error updating account: " . htmlspecialchars($e->getMessage());
        }
    }

    // Handle delete action
    if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];

        try {
            $stmt = $pdo->prepare("DELETE FROM traffic_enforcers WHERE id = ?");
            $stmt->execute([$id]);

            $_SESSION['message_success'] = "Traffic enforcer account deleted successfully.";
        } catch (PDOException $e) {
            $_SESSION['message_error'] = "Error deleting account: " . htmlspecialchars($e->getMessage());
        }
    }
}

// Fetch traffic enforcers from the database
try {
    $stmt = $pdo->prepare("SELECT * FROM traffic_enforcers ORDER BY created_at DESC");
    $stmt->execute();
    $enforcers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching enforcers: " . htmlspecialchars($e->getMessage());
}

// Handle view request
if (isset($_GET['view_id'])) {
    $view_id = (int)$_GET['view_id'];
    $stmt = $pdo->prepare("SELECT * FROM traffic_enforcers WHERE id = ?");
    $stmt->execute([$view_id]);
    $enforcerDetails = $stmt->fetch(PDO::FETCH_ASSOC);
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
    <link rel="stylesheet" href="../css/EnforcerL.css">
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
                <li class="nav-item active" onclick="window.location='enforcers_list.php';">
                    <a href="#"><img src="../icons/enforcer.png" alt="Enforcer Icon" class="icon"><span class="text">Traffic Enforcers</span></a>
                </li>
                <li class="nav-item" onclick="window.location='drivers_list.php';">
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
                <li class="nav-item active" onclick="window.location='enforcers_list.php';">
                    <a href="#"><img src="../icons/enforcer.png" alt="Enforcer Icon" class="icon"><span class="text">Traffic Enforcers</span></a>
                </li>
                <li class="nav-item" onclick="window.location='drivers_list.php';">
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

        <!-- Toast Notifications -->
        <?php if (isset($_SESSION['message_success'])): ?>
            <script>
                Toastify({
                    text: "<?php echo $_SESSION['message_success']; ?>",
                    backgroundColor: "green",
                    duration: 3000
                }).showToast();
            </script>
            <?php unset($_SESSION['message_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['message_error'])): ?>
            <script>
                Toastify({
                    text: "<?php echo $_SESSION['message_error']; ?>",
                    backgroundColor: "red",
                    duration: 3000
                }).showToast();
            </script>
            <?php unset($_SESSION['message_error']); ?>
        <?php endif; ?>

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

            <section id="enforcers-list" class="content-section enforcers-list active">
                <h2>List of Traffic Enforcers</h2>
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['message_success'])): ?>
                    <div class="success-message"><?php echo htmlspecialchars($_SESSION['message_success']); unset($_SESSION['message_success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['message_error'])): ?>
                    <div class="error-message"><?php echo htmlspecialchars($_SESSION['message_error']); unset($_SESSION['message_error']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['message_success'])): ?>
                    <script>
                        Toastify({
                            text: "<?php echo $_SESSION['message_success']; ?>",
                            backgroundColor: "green",
                            duration: 3000
                        }).showToast();
                    </script>
                    <?php unset($_SESSION['message_success']); ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['message_error'])): ?>
                    <script>
                        Toastify({
                            text: "<?php echo $_SESSION['message_error']; ?>",
                            backgroundColor: "red",
                            duration: 3000
                        }).showToast();
                    </script>
                    <?php unset($_SESSION['message_error']); ?>
                <?php endif; ?>
                <div class="controls">
                    <button class="create-new" onclick="showEnforcerForm()">Create New</button>
                    <div class="search-container">
                        <label for="search">Search:</label>
                        <input type="text" id="search" name="search" placeholder="Search Enforcers">
                    </div>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Badge Id</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="enforcerTable">
                            <?php if (empty($enforcers)): ?>
                                <tr>
                                    <td colspan="5">No traffic enforcers found.</td>
                                </tr>
                            <?php else: 
                                foreach ($enforcers as $enforcer): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($enforcer['badge_id']); ?></td>
                                    <td><?php echo htmlspecialchars($enforcer['firstname'] . " " . $enforcer['middlename'] . " " . $enforcer['lastname']); ?></td>
                                    <td><?php echo htmlspecialchars($enforcer['username']); ?></td>
                                    <td><?php echo htmlspecialchars($enforcer['email']); ?></td>
                                    <td>
                                        <button class="view-btn" onclick="viewEnforcer(<?php echo htmlspecialchars($enforcer['id']); ?>)">
                                            <img src="../icons/view.png" alt="View">
                                        </button>
                                        <button class="edit-btn" onclick="editEnforcer(<?php echo htmlspecialchars($enforcer['id']); ?>, '<?php echo htmlspecialchars($enforcer['badge_id']); ?>', '<?php echo htmlspecialchars($enforcer['firstname']); ?>', '<?php echo htmlspecialchars($enforcer['middlename']); ?>', '<?php echo htmlspecialchars($enforcer['lastname']); ?>', '<?php echo htmlspecialchars($enforcer['gender']); ?>', '<?php echo htmlspecialchars($enforcer['date_of_birth']); ?>', '<?php echo htmlspecialchars($enforcer['present_address']); ?>', '<?php echo htmlspecialchars($enforcer['permanent_address']); ?>', '<?php echo htmlspecialchars($enforcer['contact_number']); ?>', '<?php echo htmlspecialchars($enforcer['username']); ?>', '<?php echo htmlspecialchars($enforcer['email']); ?>')">
                                            <img src="../icons/edit.png" alt="Edit">
                                        </button>
                                        <button class="delete-btn" onclick="deleteEnforcer(<?php echo htmlspecialchars($enforcer['id']); ?>)">
                                            <img src="../icons/delete.png" alt="Delete">
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </section>

            <!-- Modal for Create/Edit Enforcer -->
            <div id="enforcerFormModal" class="modal">
                <div class="modal-content">
                    <span class="close" id="closeEnforcerForm">&times;</span>
                    <h2 id="formTitle">Add New Enforcer</h2>
                    <form action="enforcers_list.php" method="post">
                        <input type="hidden" id="entryId" name="id">
                        <div class="form-group">
                            <label for="badge_id">Badge ID:</label>
                            <input type="text" id="badge_id" name="badge_id" required>
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
                                <option value="">Select</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="date_of_birth">Date of Birth:</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" required>
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

        <!-- Modal for View Enforcer -->
<div id="viewEnforcerModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <button class="print-btn"><span class="icon">🖨️</span> <span class="text">Print</span></button>
            <h2>Enforcer's Information</h2>
            <button class="close" id="closeViewEnforcer"><span class="icon">✖</span><span class="text">Close</span></button>
        </div>

        <?php if (isset($enforcerDetails)): ?>
        <div class="container-section">
            <div class="enforcer-info">
                <div class="info-left">
                    <p><strong>Badge ID:</strong> <?php echo htmlspecialchars($enforcerDetails['badge_id']); ?></p>
                    <p><strong>First Name:</strong> <?php echo htmlspecialchars($enforcerDetails['firstname']); ?></p>
                    <p><strong>Middle Name:</strong> <?php echo htmlspecialchars($enforcerDetails['middlename']); ?></p>
                    <p><strong>Last Name:</strong> <?php echo htmlspecialchars($enforcerDetails['lastname']); ?></p>
                    <p><strong>Gender:</strong> <?php echo htmlspecialchars($enforcerDetails['gender']); ?></p>
                    <p><strong>Date of Birth:</strong> 
                        <?php 
                            // Format the date of birth
                            $dob = new DateTime($enforcerDetails['date_of_birth']);
                            echo htmlspecialchars($dob->format('F d, Y')); 
                        ?>
                    </p>
                    <p><strong>Present Address:</strong> <?php echo htmlspecialchars($enforcerDetails['present_address']); ?></p>
                    <p><strong>Permanent Address:</strong> <?php echo htmlspecialchars($enforcerDetails['permanent_address']); ?></p>
                    <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($enforcerDetails['contact_number']); ?></p>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($enforcerDetails['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($enforcerDetails['email']); ?></p>
                </div>
                <div class="info-right">
                    <!-- Display Enforcer's Profile Photo -->
                    <div class="profile-photo-container">
                        <img src="<?php echo !empty($enforcerDetails['photo']) ? (strpos($enforcerDetails['photo'], '../') === 0 ? htmlspecialchars($enforcerDetails['photo']) : '../' . htmlspecialchars($enforcerDetails['photo'])) : '../image/defimage.png'; ?>" alt="Enforcer's Photo" class="profile-photo" />
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div> 
    <script src="../js/script.js"></script>
    <script src="../js/enforcerlistmodals.js"></script>
    <script src="../js/enforcerdetailsprint.js"></script>
</body>
</html>
