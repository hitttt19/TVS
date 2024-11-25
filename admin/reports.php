<?php
include('../db_connection.php');
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../landingpage.php");
    exit();
}

// Handle filtering based on POST
$filter_start = isset($_POST['date-start']) ? $_POST['date-start'] : '';
$filter_end = isset($_POST['date-end']) ? $_POST['date-end'] : '';
$ticket_no = isset($_POST['ticket-no']) && $_POST['ticket-no'] ? $_POST['ticket-no'] : '';
$license_id = isset($_POST['license-id']) && $_POST['license-id'] ? $_POST['license-id'] : '';
$offense = isset($_POST['offense']) && $_POST['offense'] ? $_POST['offense'] : '';
$status = isset($_POST['status']) && $_POST['status'] ? $_POST['status'] : '';

// Start building the SQL query
$query = "SELECT orc.*, o.name AS offense_name, o.rate AS offense_rate, 
                  te.firstname AS enforcer_firstname, te.middlename AS enforcer_middlename, te.lastname AS enforcer_lastname
           FROM offense_records orc
           LEFT JOIN offenses o ON orc.offense_name = o.name
           LEFT JOIN traffic_enforcers te ON orc.enforcer_id = te.id
           WHERE 1=1"; // Filter conditions would go here

$params = [];

// Add date filters if provided
if ($filter_start) {
    $query .= " AND orc.datetime >= :start_date";
    $params[':start_date'] = $filter_start . ' 00:00:00';
}

if ($filter_end) {
    $query .= " AND orc.datetime <= :end_date";
    $params[':end_date'] = $filter_end . ' 23:59:59';
}

// Add ticket number filter if provided
if ($ticket_no) {
    $query .= " AND orc.ticket_no LIKE :ticket_no";
    $params[':ticket_no'] = '%' . $ticket_no . '%';
}

// Add license ID filter if provided
if ($license_id) {
    $query .= " AND orc.license_id LIKE :license_id";
    $params[':license_id'] = '%' . $license_id . '%';
}

// Add offense filter if provided
if ($offense) {
    $query .= " AND o.name LIKE :offense";
    $params[':offense'] = '%' . $offense . '%';
}

// Add status filter if provided
if ($status) {
    $query .= " AND orc.status = :status";
    $params[':status'] = $status;
}

// Order by datetime
$query .= " ORDER BY orc.datetime DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Fetch distinct ticket numbers
$stmt = $pdo->query("SELECT DISTINCT ticket_no FROM offense_records");
$ticket_numbers = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch distinct license IDs
$stmt = $pdo->query("SELECT DISTINCT license_id FROM offense_records");
$license_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch distinct offenses
$stmt = $pdo->query("SELECT DISTINCT name FROM offenses");
$offenses = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RegulaDrive</title>
    <link rel="icon" href="../logo/RegLogo.png" id="favicon">
    <link rel="stylesheet" href="../css/reports.css">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                <li class="nav-item" onclick="window.location='drivers_list.php';">
                    <a href="#"><img src="../icons/Driver License.png" alt="Drivers List Icon" class="icon"><span class="text">Drivers List</span></a>
                </li>
                <li class="nav-item active" onclick="window.location='reports.php';">
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
                <li class="nav-item active" onclick="window.location='offenses_list.php';">
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
    <section id="reports" class="content-section reports active">
    <h2>Reports</h2>
    <div class="report-controls">
        <div class="date-range">
            <form id="filterForm" method="post" action="reports.php">
                <div class="date-inputs">
                    <label for="date-start">From:</label>
                    <input type="date" id="date-start" name="date-start" value="<?php echo htmlspecialchars($filter_start); ?>">
                    <label for="date-end">To:</label>
                    <input type="date" id="date-end" name="date-end" value="<?php echo htmlspecialchars($filter_end); ?>">
                </div>
                <div class="dropdown-inputs">
                    <label for="ticket-no">Ticket No:</label>
                    <input type="text" id="ticket-no" name="ticket-no" value="<?php echo htmlspecialchars($ticket_no); ?>">

                    <label for="license-id">License ID:</label>
                    <input type="text" id="license-id" name="license-id" value="<?php echo htmlspecialchars($license_id); ?>">

                    <label for="offense">Offense:</label>
                    <select id="offense" name="offense">
                        <option value="">Select Offense</option>
                        <?php foreach ($offenses as $offense_name): ?>
                            <option value="<?php echo htmlspecialchars($offense_name); ?>" <?php echo (isset($_POST['offense']) && $_POST['offense'] == $offense_name) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($offense_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="status">Status:</label>
                    <select id="status" name="status">
                        <option value="">Select Status</option>
                        <option value="Pending" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="Resolved" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Resolved') ? 'selected' : ''; ?>>Resolved</option>
                        <option value="Unsettled" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Unsettled') ? 'selected' : ''; ?>>Unsettled</option>
                    </select>
                    <button class="filter-btn" type="submit">Filter</button>
                    <button class="print-btn" type="button" onclick="openModal()">Print</button>
                </div>
            </form>
        </div>
    </div>

                <div class="report-table-container">
                    <div class="table-scroll">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Datetime</th>
                                    <th>Ticket No.</th>
                                    <th>License ID</th>
                                    <th>Enforcer</th>
                                    <th>Offense</th>
                                    <th>Penalty</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="reportTableBody">
                                <?php if (count($records) > 0): ?>
                                    <?php foreach ($records as $record): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($record['datetime']))); ?></td>
                                            <td><?php echo htmlspecialchars($record['ticket_no']); ?></td>
                                            <td><?php echo htmlspecialchars($record['license_id']); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($record['enforcer_firstname']) . ' ' . 
                                                        htmlspecialchars($record['enforcer_middlename']) . ' ' . 
                                                        htmlspecialchars($record['enforcer_lastname']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($record['offense_name']); ?></td>
                                            <td><?php echo htmlspecialchars('₱' . number_format($record['offense_rate'], 2)); ?></td> <!-- Updated Line -->
                                            <td><span class="status <?php echo htmlspecialchars($record['status']); ?>"><?php echo htmlspecialchars($record['status']); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7">No records found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
    
    <!-- Print Modal -->
    <div id="printModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Print Report</h2>
            <div id="printableArea">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Datetime</th>
                            <th>Ticket No.</th>
                            <th>License ID</th>
                            <th>Enforcer</th>
                            <th>Offense</th>
                            <th>Penalty</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="printTableBody">
                        <!-- Rows will be inserted here by JavaScript -->
                    </tbody>
                </table>
            </div>
            <button class="print-btn" onclick="printContent()">Print</button>
        </div>
    </div>

    <script src="../js/script.js"></script>
    <script>
        function openModal() {
            // Show the modal
            document.getElementById('printModal').style.display = 'block';

            // Get the table body from the main report and clone it
            var tableBody = document.querySelector('.report-table-container tbody').innerHTML;

            // Insert cloned rows into the modal's table
            document.getElementById('printTableBody').innerHTML = tableBody;
        }

        function closeModal() {
            // Hide the modal
            document.getElementById('printModal').style.display = 'none';
        }

        function printContent() {
        closeModal();
        
        // Create a new window and write the content to it
        var printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Print Report</title>');
        printWindow.document.write('<style>');
        printWindow.document.write('body { font-family: Arial, sans-serif; margin: 20px; }');
        printWindow.document.write('.header { display: flex; align-items: center; margin-top: 20px; }');
        printWindow.document.write('.logo { width: 70px; height: 70px; margin-right: 10px; }'); // Fixed 'heigth' to 'height'
        printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 20px; }');
        printWindow.document.write('th, td { border: 1px solid #000; padding: 8px; text-align: left; }');
        printWindow.document.write('th { background-color: #f2f2f2; }');
        printWindow.document.write('</style>');
        printWindow.document.write('</head><body>');

        // Include date filters in the printout
        var startDate = document.getElementById('date-start').value;
        var endDate = document.getElementById('date-end').value;

        printWindow.document.write('<div class="header">');
        printWindow.document.write('<img src="../logo/RegLogo.png" alt="Reguladrive Logo" class="logo">');
        printWindow.document.write('<h2 style="margin: 0;">RegulaDrive Report</h2>'); // Add inline style to remove margin
        printWindow.document.write('</div>');
        printWindow.document.write(document.getElementById('printableArea').innerHTML);

        printWindow.document.write('</body></html>');
        printWindow.document.close(); // Necessary for IE >= 10
        printWindow.focus(); // Necessary for IE >= 10
        printWindow.print();
            }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search');
            const reportTableBody = document.getElementById('reportTableBody');

            searchInput.addEventListener('keyup', function() {
                const filter = searchInput.value.toLowerCase();
                const rows = reportTableBody.getElementsByTagName('tr');

                for (let i = 0; i < rows.length; i++) {
                    const cells = rows[i].getElementsByTagName('td');
                    let match = false;

                    for (let j = 0; j < cells.length; j++) {
                        if (cells[j]) {
                            if (cells[j].textContent.toLowerCase().includes(filter)) {
                                match = true;
                                break;
                            }
                        }
                    }
                    rows[i].style.display = match ? '' : 'none';
                }
            });
        });
    </script>
    <script>
        function validateForm() {
            const form = document.getElementById('filterForm');
            const formData = new FormData(form);
            const params = new URLSearchParams();

            for (const [key, value] of formData) {
                if (value) {
                    params.append(key, value);
                }
            }
            // Redirect to the URL with only non-empty parameters
            window.location.href = form.action + '?' + params.toString();
            return false; // Prevent default form submission
        }
    </script>
</body>
</html>
