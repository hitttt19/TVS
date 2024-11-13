<?php
include('../db_connection.php');
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../landingpage.php");
    exit();
}

// Handle CRUD operations
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
                $stmt->execute([$license_id, $license_type, $firstname, $middlename, $lastname, $gender, $date_of_birth, $civil_status, $present_address, $permanent_address, $nationality, $contact_number, $username, $email, $hashedPassword, null]); // Assuming photo is null for new records
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
                break;

            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM drivers WHERE id = ?");
                $stmt->execute([$id]);
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


// Handle view request
if (isset($_GET['view_id'])) {
    $view_id = (int)$_GET['view_id'];
    $stmt = $pdo->prepare("SELECT * FROM drivers WHERE id = ?");
    $stmt->execute([$view_id]);
    $driverDetails = $stmt->fetch(PDO::FETCH_ASSOC);
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

        <!-- Overlay for mobile sidebar -->
        <div class="overlay" onclick="toggleSidebar()"></div>

        <!-- Main Content Area -->
        <div class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <div class="hamburger-menu" onclick="toggleSidebar()">&#9776;</div>
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
                    <!-- <label for="show-entries">Show</label>
                    <select id="show-entries">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>entries</span> -->
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
                                        <form action="drivers_list.php" method="post" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($driver['id']); ?>">
                                            <button type="submit" name="action" value="delete" class="delete-btn">
                                                <img src="../icons/delete.png" alt="Delete" />
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- <div class="pagination">
                    <button class="previous">Previous</button>
                    <button class="page-number active">1</button>
                    <button class="next">Next</button>
                </div> -->
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
                <label for="license_id">License ID:</label>
                <input type="text" id="license_id" name="license_id" required>
            </div>

            <div class="form-group">
                <label for="license_type">License Type:</label>
                <select id="license_type" name="license_type" required>
                    <option value="">Select License Type</option>
                    <option value="Student Permit">Student Permit</option>
                    <option value="Non-Professional">Non-Professional</option>
                    <option value="Professional">Professional</option>
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
        <span class="close" id="closeViewDriver">&times;</span>
        <h2>Driver Details</h2>
        <?php if (isset($driverDetails)): ?>
        <div class="driver-details">
            <p><strong>License ID:</strong> <?php echo htmlspecialchars($driverDetails['license_id']); ?></p>
            <p><strong>License Type:</strong> <?php echo htmlspecialchars($driverDetails['license_type']); ?></p>
            <p><strong>First Name:</strong> <?php echo htmlspecialchars($driverDetails['firstname']); ?></p>
            <p><strong>Middle Name:</strong> <?php echo htmlspecialchars($driverDetails['middlename']); ?></p>
            <p><strong>Last Name:</strong> <?php echo htmlspecialchars($driverDetails['lastname']); ?></p>
            <p><strong>Gender:</strong> <?php echo htmlspecialchars($driverDetails['gender']); ?></p>
            <p class="full-width"><strong>Date of Birth:</strong> 
                <?php 
                    $dob = new DateTime($driverDetails['date_of_birth']);
                    echo htmlspecialchars($dob->format('F d, Y')); 
                ?>
            </p>
            <p class="full-width"><strong>Civil Status:</strong> <?php echo htmlspecialchars($driverDetails['civil_status']); ?></p>
            <p class="full-width"><strong>Present Address:</strong> <?php echo htmlspecialchars($driverDetails['present_address']); ?></p>
            <p class="full-width"><strong>Permanent Address:</strong> <?php echo htmlspecialchars($driverDetails['permanent_address']); ?></p>
            <p><strong>Nationality:</strong> <?php echo htmlspecialchars($driverDetails['nationality']); ?></p>
            <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($driverDetails['contact_number']); ?></p>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($driverDetails['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($driverDetails['email']); ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>



    <script src="../js/script.js"></script>
    <script>
        function showDriverForm() {
            document.getElementById('formTitle').innerText = 'Create New Driver';
            document.getElementById('driverId').value = '';
            document.getElementById('license_id').value = '';
            document.getElementById('license_type').value = '';
            document.getElementById('firstname').value = '';
            document.getElementById('middlename').value = '';
            document.getElementById('lastname').value = '';
            document.getElementById('gender').value = '';
            document.getElementById('date_of_birth').value = '';
            document.getElementById('present_address').value = '';
            document.getElementById('permanent_address').value = '';
            document.getElementById('nationality').value = '';
            document.getElementById('contact_number').value = '';
            document.getElementById('username').value = '';
            document.getElementById('email').value = '';
            document.getElementById('formAction').value = 'create';
            document.getElementById('driverFormModal').style.display = 'block';
        }

        function editDriver(id, license_id, license_type, firstname, middlename, lastname, gender, date_of_birth, present_address, permanent_address, nationality, contact_number, username, email) {
            document.getElementById('formTitle').innerText = 'Edit Driver';
            document.getElementById('driverId').value = id;
            document.getElementById('license_id').value = license_id;
            document.getElementById('license_type').value = license_type;
            document.getElementById('firstname').value = firstname;
            document.getElementById('middlename').value = middlename;
            document.getElementById('lastname').value = lastname;
            document.getElementById('gender').value = gender;
            document.getElementById('date_of_birth').value = date_of_birth;
            document.getElementById('present_address').value = present_address;
            document.getElementById('permanent_address').value = permanent_address;
            document.getElementById('nationality').value = nationality;
            document.getElementById('contact_number').value = contact_number;
            document.getElementById('username').value = username;
            document.getElementById('email').value = email;
            document.getElementById('formAction').value = 'update';
            document.getElementById('driverFormModal').style.display = 'block';
        }

        document.getElementById('closeDriverForm').onclick = function() {
            document.getElementById('driverFormModal').style.display = 'none';
        }

        function viewDriver(id) {
        // Redirect to the current page with view_id parameter
        window.location.href = 'drivers_list.php?view_id=' + id;
        }

        document.getElementById('closeViewDriver').onclick = function() {
            document.getElementById('viewDriverModal').style.display = 'none';
        }

        // Check if the view_id parameter is present and show the modal
        window.onload = function() {
            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('view_id')) {
                document.getElementById('viewDriverModal').style.display = 'block';
            }
        }
    </script>

    <script>
        function updateTable(drivers) {
            const tbody = document.querySelector('.table tbody');
            tbody.innerHTML = ''; // Clear existing rows

            drivers.forEach(driver => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${htmlspecialchars(driver.license_id)}</td>
                    <td>${htmlspecialchars(driver.firstname + " " + driver.middlename + " " + driver.lastname)}</td>
                    <td>${htmlspecialchars(driver.license_type)}</td>
                    <td>
                        <button class="view-btn" onclick="viewDriver(${driver.id})"><img src="../icons/view.png" alt="View" /></button>
                        <button class="edit-btn" onclick="editDriver(${driver.id}, '${driver.license_id}', '${driver.license_type}', '${driver.firstname}', '${driver.middlename}', '${driver.lastname}', '${driver.gender}', '${driver.date_of_birth}', '${driver.present_address}', '${driver.permanent_address}', '${driver.nationality}', '${driver.contact_number}', '${driver.username}', '${driver.email}')"><img src="../icons/edit.png" alt="Edit" /></button>
                        <form action="drivers_list.php" method="post" style="display:inline;">
                            <input type="hidden" name="id" value="${driver.id}">
                            <button type="submit" name="action" value="delete" class="delete-btn"><img src="../icons/delete.png" alt="Delete" /></button>
                        </form>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
    </script>
    <script>
        // Live search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search');
            const driverTable = document.getElementById('driverTable');

            searchInput.addEventListener('keyup', function() {
                const filter = searchInput.value.toLowerCase();
                const rows = driverTable.getElementsByTagName('tr');

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

</body>
</html>
