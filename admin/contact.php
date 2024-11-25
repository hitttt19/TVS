
<?php
include('../db_connection.php');
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../landingpage.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // Get form data
    $service_provider = isset($_POST['service_provider']) ? trim($_POST['service_provider']) : '';
    $mobile_no = isset($_POST['mobile_no']) ? trim($_POST['mobile_no']) : '';
    $service_type = isset($_POST['service_type']) ? trim($_POST['service_type']) : '';
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    // Only validate mobile number if the action is not delete
    if ($action !== 'delete') {
        // Check if mobile number is valid (exactly 11 digits)
        if (!preg_match('/^\d{11}$/', $mobile_no)) {
            $_SESSION['message_error'] = "Mobile number must be exactly 11 digits.";
        }
        // Check for duplicate mobile number
        else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM contacts WHERE mobile_no = ?");
            $stmt->execute([$mobile_no]);
            $existing_count = $stmt->fetchColumn();

            if ($existing_count > 0 && $action != 'update') {
                $_SESSION['message_error'] = "This mobile number is already registered.";
            } else {
                // Proceed with inserting or updating the contact
                try {
                    switch ($action) {
                        case 'create':
                            $stmt = $pdo->prepare("INSERT INTO contacts (service_provider, mobile_no, service_type) VALUES (?, ?, ?)");
                            $stmt->execute([$service_provider, $mobile_no, $service_type]);
                            $_SESSION['message_success'] = "Contact created successfully!";
                            break;

                        case 'update':
                            $stmt = $pdo->prepare("UPDATE contacts SET service_provider = ?, mobile_no = ?, service_type = ? WHERE id = ?");
                            $stmt->execute([$service_provider, $mobile_no, $service_type, $id]);
                            $_SESSION['message_success'] = "Contact updated successfully!";
                            break;
                    }
                    // Redirect to avoid form resubmission
                    header("Location: contact.php");
                    exit();
                } catch (PDOException $e) {
                    $error = "Error: " . htmlspecialchars($e->getMessage());
                }
            }
        }
    }
    
    // Delete action doesn't require mobile number check
    if ($action == 'delete') {
        try {
            $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['message_success'] = "Contact deleted successfully!";
            header("Location: contact.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['message_error'] = "Error deleting contact: " . htmlspecialchars($e->getMessage());
        }
    }
}

// Search functionality
$search = isset($_GET['search']) ? '%' . trim($_GET['search']) . '%' : '%';
$stmt = $pdo->prepare("SELECT * FROM contacts WHERE service_provider LIKE ? OR mobile_no LIKE ? OR service_type LIKE ? ORDER BY id DESC");
$stmt->execute([$search, $search, $search]);
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RegulaDrive</title>
    <link rel="icon" href="../logo/RegLogo.png" id="favicon">
    <link rel="stylesheet" href="../css/Index.css">
    <link rel="stylesheet" href="../css/contact.css">
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
                <li class="nav-item" onclick="window.location='drivers_list.php';">
                    <a href="#"><img src="../icons/Driver License.png" alt="Drivers List Icon" class="icon"><span class="text">Drivers List</span></a>
                </li>
                <li class="nav-item" onclick="window.location='reports.php';">
                    <a href="#"><img src="../icons/Business Report.png" alt="Reports Icon" class="icon"><span class="text">Reports</span></a>
                </li>
                <li class="nav-item active" onclick="window.location='contact.php';">
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
        
        <div class="main-content">
            <header class="header">
                <div class="header-left">
                    <h2>Bogo City Traffic Violations System</h2>
                </div>
                <div class="user-menu">
                    <img src="../icons/profile.png" class="user-icon">
                    <span class="user-name">Admin</span>
                </div>
            </header>

            <section id="contact" class="content-section contact active">
                <h2>Contact Management</h2>
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <div class="con-controls">
                    <!-- <label for="show-entries">Show</label>
                    <select id="show-entries">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>entries</span> -->
                    <button class="con-create-new" onclick="showContactForm()">Create New</button>
                    <div class="search-container">
                        <label for="search">Search:</label>
                        <input type="text" id="search" name="search" placeholder="Search Contacts" oninput="searchContact()" value="<?php echo htmlspecialchars(isset($_GET['search']) ? $_GET['search'] : ''); ?>">
                    </div>
                </div>
                <div class="con-table-container">
                    <table class="con-table">
                        <thead>
                            <tr>
                                <th>Service Provider</th>
                                <th>Mobile No.</th>
                                <th>Service Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="contactTable">
                            <?php if (empty($contacts)): ?>
                                <tr>
                                    <td colspan="4">No contacts found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($contacts as $contact): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($contact['service_provider']); ?></td>
                                    <td><?php echo htmlspecialchars($contact['mobile_no']); ?></td>
                                    <td><?php echo htmlspecialchars($contact['service_type']); ?></td>
                                    <td>
                                    <button class="con-edit-btn" onclick="editContact(<?php echo htmlspecialchars($contact['id']); ?>, '<?php echo htmlspecialchars($contact['service_provider']); ?>', '<?php echo htmlspecialchars($contact['mobile_no']); ?>', '<?php echo htmlspecialchars($contact['service_type']); ?>')">
                                    <img src="../icons/edit.png" alt="Edit" />
                                    </button>
                                    <form action="contact.php" method="post" style="display:inline;" onsubmit="return confirmDelete();">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($contact['id']); ?>">
                                        <button type="submit" name="action" value="delete" class="con-delete-btn">
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

    <!-- Modal for Create/Edit Contact -->
<div id="contactFormModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeContactForm">&times;</span>
        <h2 id="formTitle">Create New Contact</h2>
        <form action="contact.php" method="post">
            <input type="hidden" id="contactId" name="id">
            <label for="service_provider">Service Provider:</label>
                <select id="service_provider" name="service_provider" required>
                    <option value="TM">TM</option>
                    <option value="Globe">Globe</option>
                    <option value="GOMO">GOMO</option>
                    <option value="Smart">Smart</option>
                    <option value="Sun">Sun</option>
                    <option value="TNT">TNT</option>
                    <option value="DITO">DITO</option>
                </select>
            
            <label for="mobile_no">Mobile No.:</label>
            <input type="text" id="mobile_no" name="mobile_no" required>

            <label for="service_type">Service Type:</label>
            <select id="service_type" name="service_type" required>
                <option value="Towing">Towing</option>
                <option value="Traffic Law Consultation">Traffic Law Consultation</option>
                <option value="Traffic School">Traffic School</option>
                <option value="Vehicle Repair Services">Vehicle Repair Services</option>
                <option value="Insurance Services">Insurance Services</option>
            </select>

            <button type="submit" id="formAction" name="action" value="create">Save</button>
        </form>
    </div>
</div>
        <script src="../js/script.js"></script>
        <script>
        function showContactForm() {
            document.getElementById('formTitle').innerText = 'Create New Contact';
            document.getElementById('contactId').value = '';
            document.getElementById('service_provider').value = '';
            document.getElementById('mobile_no').value = '';
            document.getElementById('service_type').value = 'Towing'; // Default value
            document.getElementById('formAction').value = 'create';
            document.getElementById('contactFormModal').style.display = 'block';
        }

        function editContact(id, service_provider, mobile_no, service_type) {
            document.getElementById('formTitle').innerText = 'Edit Contact';
            document.getElementById('contactId').value = id;
            document.getElementById('service_provider').value = service_provider;
            document.getElementById('mobile_no').value = mobile_no;
            document.getElementById('service_type').value = service_type; // Set selected service type
            document.getElementById('formAction').value = 'update';
            document.getElementById('contactFormModal').style.display = 'block';
        }

        document.getElementById('closeContactForm').onclick = function() {
            document.getElementById('contactFormModal').style.display = 'none';
        }

        function validateMobileNo() {
            const mobileInput = document.getElementById('mobile_no');
            const mobileValue = mobileInput.value;

            if (mobileValue.length !== 11 || !/^\d+$/.test(mobileValue)) {
                mobileInput.setCustomValidity('Please enter a valid 11-digit mobile number.');
            } else {
                mobileInput.setCustomValidity('');
            }
        }

        function confirmDelete() {
        return confirm("Are you sure you want to delete this contact?");
    }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search');
            const contactTable = document.getElementById('contactTable');

            searchInput.addEventListener('keyup', function() {
                const filter = searchInput.value.toLowerCase();
                const rows = contactTable.getElementsByTagName('tr');

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
         // Display Toastify notifications
<?php if (isset($_SESSION['message_success'])): ?>
    Toastify({
        text: "<?php echo $_SESSION['message_success']; ?>",
        duration: 3000,
        backgroundColor: "green",
        close: true
    }).showToast();
    <?php unset($_SESSION['message_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['message_error'])): ?>
    Toastify({
        text: "<?php echo $_SESSION['message_error']; ?>",
        duration: 3000,
        backgroundColor: "red",
        close: true
    }).showToast();
    <?php unset($_SESSION['message_error']); ?>
<?php endif; ?>

    </script>

</body>
</html>