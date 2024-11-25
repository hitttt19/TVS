<?php
include('../db_connection.php');
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../landingpage.php");
    exit();
}

// Initialize variables for handling the form
$current_enforcer_name = ''; // Default empty value

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = isset($_POST['id']) ? trim($_POST['id']) : '';
    $datetime = isset($_POST['datetime']) ? trim($_POST['datetime']) : '';
    $license_id = isset($_POST['license_id']) ? trim($_POST['license_id']) : '';
    $traffic_enforcer = isset($_POST['traffic_enforcer']) ? trim($_POST['traffic_enforcer']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';
    $offense_name = isset($_POST['offense_name']) ? trim($_POST['offense_name']) : '';
    $offense_rate = isset($_POST['offense_rate']) ? trim($_POST['offense_rate']) : '';

    // Validate fields for create and update actions only
    if (($action === 'create' || $action === 'update') && 
        (empty($datetime) || empty($license_id) || empty($traffic_enforcer) || empty($status) || empty($offense_name) || empty($offense_rate))) {
        $_SESSION['message_error'] = "All fields are required.";
    } else {
        try {
            if ($action === 'create' || $action === 'update') {
                // Get the enforcer_id based on the traffic_enforcer name
                $stmt = $pdo->prepare("SELECT id FROM traffic_enforcers WHERE CONCAT(firstname, ' ', COALESCE(middlename, ''), ' ', lastname) = ?");
                $stmt->execute([$traffic_enforcer]);
                $enforcer_id = $stmt->fetchColumn();

                // Check if enforcer_id was found
                if (!$enforcer_id) {
                    $_SESSION['message_error'] = "Selected enforcer not found.";
                } else {
                    // Proceed with create or update action
                    if ($action === 'create') {
                        do {
                            // Generate a random 7-digit number for ticket_no
                            $ticket_no = str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT);
                            
                            // Check if the ticket number already exists
                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM offense_records WHERE ticket_no = ?");
                            $stmt->execute([$ticket_no]);
                            $ticket_no_exists = $stmt->fetchColumn();
                        } while ($ticket_no_exists > 0);

                        // Insert the new offense record
                        $stmt = $pdo->prepare("INSERT INTO offense_records (datetime, ticket_no, license_id, offense_name, offense_rate, status, enforcer_id) 
                                              VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$datetime, $ticket_no, $license_id, $offense_name, $offense_rate, $status, $enforcer_id]);

                        $_SESSION['message_success'] = "Offense record created successfully!";
                        header("Location: offense_records.php");
                        exit();
                    } elseif ($action === 'update') {
                        // Update existing offense record with enforcer_id
                        $stmt = $pdo->prepare("UPDATE offense_records SET datetime = ?, license_id = ?, offense_name = ?, offense_rate = ?, status = ?, enforcer_id = ? WHERE id = ?");
                        $stmt->execute([$datetime, $license_id, $offense_name, $offense_rate, $status, $enforcer_id, $id]);

                        $_SESSION['message_success'] = "Offense record updated successfully!";
                        header("Location: offense_records.php");
                        exit();
                    }
                }
            } elseif ($action === 'delete') {
                // Delete the offense record by ID
                if (empty($id)) {
                    $_SESSION['message_error'] = "Offense ID is required to delete.";
                } else {
                    $pdo->beginTransaction();
            
                    $stmt = $pdo->prepare("SELECT enforcer_id FROM offense_records WHERE id = ?");
                    $stmt->execute([$id]);
                    $offense_record = $stmt->fetch();
            
                    if (!$offense_record) {
                        $_SESSION['message_error'] = "Offense record not found.";
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM offense_records WHERE id = ?");
                        $stmt->execute([$id]);
            
                        $pdo->commit();
            
                        // Set a red delete success message
                        $_SESSION['message_delete'] = "Offense record deleted successfully!";
                        header("Location: offense_records.php");
                        exit();
                    }
                }
            }

        } catch (PDOException $e) {
            // Rollback the transaction if any error occurs
            $pdo->rollBack();
            $error = "Error: " . htmlspecialchars($e->getMessage());
        }
    }
}

// Get offense records to display in the list (for reading)
$stmt = $pdo->prepare("SELECT * FROM offense_records");
$stmt->execute();
$offense_records = $stmt->fetchAll();

// Initialize search variable
$search = isset($_GET['search']) ? "%" . $_GET['search'] . "%" : "%";

// Prepare the SQL query to search offense records
$stmt = $pdo->prepare("
    SELECT offense_records.*, 
           drivers.present_address, 
           CONCAT(drivers.firstname, ' ', COALESCE(drivers.middlename, ''), ' ', drivers.lastname) AS driver_name,
           CONCAT(traffic_enforcers.firstname, ' ', COALESCE(traffic_enforcers.middlename, ''), ' ', traffic_enforcers.lastname) AS traffic_enforcer
    FROM offense_records
    LEFT JOIN drivers ON offense_records.license_id = drivers.license_id
    LEFT JOIN traffic_enforcers ON offense_records.enforcer_id = traffic_enforcers.id
    LEFT JOIN offenses ON offense_records.offense_name = offenses.name
    WHERE ticket_no LIKE ? 
       OR offense_records.license_id LIKE ? 
       OR traffic_enforcers.firstname LIKE ? 
       OR offense_records.status LIKE ? 
       OR offenses.name LIKE ?
    ORDER BY datetime DESC
");
$stmt->execute([$search, $search, $search, $search, $search]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Get offense names and rates for the form dropdowns
$offenses_stmt = $pdo->prepare("SELECT id, name, rate FROM offenses");
$offenses_stmt->execute();
$offenses = $offenses_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get traffic enforcers for the form dropdown
$enforcers_stmt = $pdo->prepare("SELECT CONCAT(firstname, ' ', COALESCE(middlename, ''), ' ', lastname) AS name FROM traffic_enforcers");
$enforcers_stmt->execute();
$enforcers = $enforcers_stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RegulaDrive</title>
    <link rel="icon" href="../logo/RegLogo.png" id="favicon">
    <link rel="stylesheet" href="../css/Index.css">
    <link rel="stylesheet" href="../css/OffenseR.css">
    <link rel="stylesheet" href="../css/summonL.css">
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
                <li class="nav-item active" onclick="window.location='offense_records.php';">
                    <a href="#"><img src="../icons/Important Note.png" alt="Offense Records Icon" class="icon"><span class="text">Offense Records</span></a>
                </li>
                <li class="nav-item" onclick="window.location='enforcers_list.php';">
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
        <li class="nav-item active" onclick="window.location='offense_records.php';">
                    <a href="#"><img src="../icons/Important Note.png" alt="Offense Records Icon" class="icon"><span class="text">Offense Records</span></a>
                </li>
                <li class="nav-item" onclick="window.location='enforcers_list.php';">
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

            <section id="offense-records" class="content-section offense-records active">
                <h2>List of Offense Records</h2>
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <div class="offenseR-controls">
                    <!-- <label for="show-entries">Show</label>
                    <select id="show-entries">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>entries</span> -->
                    <button class="create-new" onclick="showRecordForm()">Create New</button>
                    <div class="search-container">
                        <label for="search">Search:</label>
                        <input type="text" id="search" name="search" placeholder="Search Records" oninput="searchRecords()" value="<?php echo htmlspecialchars(isset($_GET['search']) ? $_GET['search'] : ''); ?>">
                    </div>
                </div>
                <div class="offenseR-table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <!-- <th>#</th> -->
                                <th>Datetime</th>
                                <th>Ticket No.</th>
                                <th>License ID</th>
                                <th>Driver Name</th>
                                <th>Offense</th>
                                <th>Penalty</th>
                                <th>Enforcer</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($records)): ?>
                                <tr>
                                    <td colspan="9" style="text-align: left;">No offense records found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($records as $record): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($record['datetime']))); ?></td>
                                        <td><?php echo htmlspecialchars($record['ticket_no']); ?></td>
                                        <td><?php echo htmlspecialchars($record['license_id']); ?></td>
                                        <td><?php echo htmlspecialchars($record['driver_name']); ?></td>
                                        <td><?php echo htmlspecialchars($record['offense_name']); ?></td>
                                        <td><?php echo htmlspecialchars('₱' . number_format($record['offense_rate'], 2)); ?></td> <!-- Updated Line -->
                                        <td><?php echo htmlspecialchars($record['traffic_enforcer']); ?></td>
                                        <td><span class="status <?php echo htmlspecialchars($record['status']); ?>"><?php echo htmlspecialchars($record['status']); ?></span></td>
                                        <td>
                                            <button class="edit-btn" onclick="editRecord('<?php echo htmlspecialchars($record['id']); ?>', '<?php echo htmlspecialchars($record['datetime']); ?>', '<?php echo htmlspecialchars($record['ticket_no']); ?>', '<?php echo htmlspecialchars($record['license_id']); ?>', '<?php echo htmlspecialchars($record['traffic_enforcer']); ?>', '<?php echo htmlspecialchars($record['status']); ?>', '<?php echo htmlspecialchars($record['offense_name']); ?>', '<?php echo htmlspecialchars($record['offense_rate']); ?>')">
                                                <img src="../icons/Edit.png" alt="Edit Icon" class="icon">
                                            </button>
                                            <form action="offense_records.php" method="post" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($record['id']); ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this record?');">
                                                    <img src="../icons/Delete.png" alt="Delete Icon" class="icon">
                                                </button>
                                                <?php if ($record['status'] !== 'Resolved'): ?>
                                                    <button type="button" class="send-btn" 
                                                        data-datetime="<?php echo htmlspecialchars($record['datetime']); ?>" 
                                                        style="display: none;" 
                                                        onclick="openLetterModal('<?php echo htmlspecialchars($record['ticket_no']); ?>', '<?php echo htmlspecialchars($record['driver_name']); ?>', '<?php echo htmlspecialchars($record['offense_name']); ?>', '<?php echo htmlspecialchars($record['datetime']); ?>', '<?php echo htmlspecialchars($record['offense_rate']); ?>', '<?php echo htmlspecialchars($record['present_address']); ?>')">
                                                        <img src="../icons/send.png" alt="Send Icon" class="icon">
                                                    </button>
                                                <?php endif; ?>
                                            </form>
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

<!-- Modal for Create/Edit Record -->
<div id="recordFormModal" class="modal">
    <div class="modal-content">
        <span class="close-button" onclick="closeRecordModal()">&times;</span>
        <h2 id="formTitle">Create New Record</h2>
        <form action="offense_records.php" method="post">
            <input type="hidden" id="record_id" name="id">
            
            <div class="form-group inline-group">
                <label for="datetime">Datetime:</label>
                <input type="datetime-local" id="datetime" name="datetime" required>
            </div>
            
            <div class="form-group inline-group">
                <label for="ticket_no">Ticket No.:</label>
                <input type="text" id="ticket_no" name="ticket_no" required 
                    value="<?php echo isset($record) ? htmlspecialchars($record['ticket_no']) : ''; ?>" 
                    readonly>
            </div>

            <div class="form-group inline-group">
                <label for="license_id">License ID:</label>
                <input type="text" id="license_id" name="license_id" required>
            </div>

            <div class="form-group inline-group">
                <label for="offense_name">Offense:</label>
                <select id="offense_name" name="offense_name" required onchange="updateOffenseRate()">
                    <?php foreach ($offenses as $offense): ?>
                        <option value="<?php echo htmlspecialchars($offense['name']); ?>" data-rate="<?php echo htmlspecialchars($offense['rate']); ?>">
                            <?php echo htmlspecialchars($offense['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group inline-group">
                <label for="offense_rate">Penalty:</label>
                <input type="text" id="offense_rate" name="offense_rate" required readonly>
            </div>

            <div class="form-group inline-group">
                <label for="traffic_enforcer">Enforcer:</label>
                <select id="traffic_enforcer" name="traffic_enforcer" required>
                    <option value="">Select Enforcer</option> <!-- Optional default option -->
                    <?php foreach ($enforcers as $enforcer): ?>
                        <option value="<?php echo htmlspecialchars($enforcer['name']); ?>" 
                                <?php echo ($enforcer['name'] === $current_enforcer_name) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($enforcer['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group inline-group">
                <label for="status">Status:</label>
                <select id="status" name="status" required>
                    <option value="Pending">Pending</option>
                    <option value="Resolved">Resolved</option>
                    <option value="Unsettled">Unsettled</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" id="formAction" name="action" value="create" class="button">Save</button>
            </div>
        </form>
    </div>
</div>
        <!-- Modal for Sending Letter -->
            <div id="myModal" class="modal">
                <div class="modal-content">
                    <span class="close-button" onclick="closeLetterModal()">&times;</span>
                    <div class="letter-content">
                        <header>
                            <div class="header-left">
                                <img src="../logo/RegLogo.png" alt="City Logo" class="city-logo">
                            </div>
                            <div class="header-right">
                                <h2>Office of the City Mayor</h2>
                                <h3>Bogo Traffic Management Office</h3>
                                <p>Bogo City</p>
                            </div>
                        </header>
                        <hr>
                        <section class="letter-body">
                            <p class="date"></p>
                            <p><strong><span class="driver-address"></span></strong></p><br>
                            <p>Dear Mr./Mrs. <strong class="driver-name"></strong>,</p>
                            <p>This shall serve as a formal written demand for the immediate payment in full of a fine for:</p>
                            <table class="citation-details">
                                <tr>
                                    <td>Traffic Citation Ticket No.:</td>
                                    <td><strong class="ticket-no"></strong></td>
                                </tr>
                                <tr>
                                    <td>For violation of:</td>
                                    <td><strong class="offense-name"></strong></td>
                                </tr>
                                <tr>
                                    <td>Date of Violation:</td>
                                    <td><strong class="violation-date"></strong></td>
                                </tr>
                                <tr>
                                    <td>Amount of Fine:</td>
                                    <td><strong class="offense-rate"></strong></td>
                                </tr>
                            </table>
                            <p>As stated in the citation ticket, you were given seven (7) days from the date of the violation to settle the fine, but our records show that, to date, no payment has been received.</p>
                            <p><strong>Failure to pay the fine</strong> will result in the matter being referred to the City Prosecutor's Office for the filing of an appropriate case against you in a court of law.</p>
                        </section>
                        <section class="signature">
                            <p>Sincerely,</p>
                            <p><strong>ROGEL L. LAYSON</strong><br>Lieutenant PNP (Ret)<br>OIC - Bogo Traffic Management Office</p>
                        </section>
                    </div>
                    <div class="button-container">
                        <button class="modal-button" onclick="sendLetter()">Send</button>
                    </div>
                </div>
            </div>
            <?php if (isset($_SESSION['message_success'])): ?>
    <script>
        Toastify({
            text: "<?php echo $_SESSION['message_success']; ?>",
            duration: 3000,
            backgroundColor: "green",
            close: true
        }).showToast();
    </script>
    <?php unset($_SESSION['message_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['message_delete'])): ?>
    <script>
        Toastify({
            text: "<?php echo $_SESSION['message_delete']; ?>",
            duration: 3000,
            backgroundColor: "green",
            close: true
        }).showToast();
    </script>
    <?php unset($_SESSION['message_delete']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['message_error'])): ?>
    <script>
        Toastify({
            text: "<?php echo $_SESSION['message_error']; ?>",
            duration: 3000,
            backgroundColor: "red",
            close: true
        }).showToast();
    </script>
    <?php unset($_SESSION['message_error']); ?>
<?php endif; ?>


    <script src="../js/script.js"></script>
    <script src="../js/offenseRecords.js"></script>

</body>
</html>
