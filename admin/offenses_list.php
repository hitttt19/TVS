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
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $rate = trim($_POST['rate']);
    $datetime = date('Y-m-d H:i:s');

    try {
        switch ($action) {
            case 'create':
                $stmt = $pdo->prepare("INSERT INTO offenses (date_created, name, description, rate) VALUES (?, ?, ?, ?)");
                $stmt->execute([$datetime, $name, $description, $rate]);
                $_SESSION['message_success'] = "Offense created successfully!";
                header("Location: offenses_list.php");
                exit();
                break;

            case 'update':
                $stmt = $pdo->prepare("UPDATE offenses SET name = ?, description = ?, rate = ? WHERE id = ?");
                $stmt->execute([$name, $description, $rate, $id]);
                $_SESSION['message_success'] = "Offense updated successfully!";
                header("Location: offenses_list.php");
                exit();
                break;

            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM offenses WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['message_success'] = "Offense deleted successfully!";
                header("Location: offenses_list.php");
                exit();
                break;
                

        }
    } catch (PDOException $e) {
        $error = "Error: " . htmlspecialchars($e->getMessage());
        $_SESSION['message_error'] = $error;
        header("Location: offenses_list.php");
        exit();
    }
}

// Search functionality
$search = isset($_GET['search']) ? '%' . trim($_GET['search']) . '%' : '%';
$stmt = $pdo->prepare("SELECT * FROM offenses WHERE name LIKE ? OR description LIKE ? ORDER BY date_created DESC");
$stmt->execute([$search, $search]);
$offenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RegulaDrive</title>
    <link rel="icon" href="../logo/RegLogo.png" id="favicon">
    <link rel="stylesheet" href="../css/Index.css">
    <link rel="stylesheet" href="../css/OffensesL.css">
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

            <section id="offenses-list" class="content-section offenses-list active">
                <h2>List of Offenses</h2>
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <div class="offenses-controls">
                    <button class="offenses-create-new" onclick="showOffenseForm()">Create New</button>
                    <div class="search-container">
                        <form action="offenses_list.php" method="get">
                            <label for="search">Search:</label>
                            <input type="text" id="search" name="search" placeholder="Search Offenses" value="<?php echo htmlspecialchars(isset($_GET['search']) ? $_GET['search'] : ''); ?>">
                            <!-- <button class="search-btn">
                                <img src="../icons/search.png" alt="search Icon" class="icon">
                            </button> -->
                        </form>
                    </div>
                </div>
                <div class="offenses-table-container">
                    <div class="offenses-table-wrapper">
                        <table class="offenses-table">
                            <thead>
                                <tr>
                                    <!-- <th>Date Created</th> -->
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Rate</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="offenseTable">
                                <?php 
                                if (empty($offenses)): ?>
                                    <tr>
                                        <td colspan="5">No offenses found.</td>
                                    </tr>
                                <?php else:
                                    foreach ($offenses as $offense): ?>
                                    <tr>
                                        <!-- <td><?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($offense['date_created']))); ?></td> -->
                                        <td><?php echo htmlspecialchars($offense['name']); ?></td>
                                        <td><?php echo htmlspecialchars($offense['description']); ?></td>
                                        <td>₱<?php echo number_format(htmlspecialchars($offense['rate']), 2); ?></td> <!-- Adding peso sign and formatting -->
                                        <td>
                                            <button class="offenses-edit-btn" onclick="editOffense(<?php echo htmlspecialchars($offense['id']); ?>, '<?php echo htmlspecialchars($offense['name']); ?>', '<?php echo htmlspecialchars($offense['description']); ?>', '<?php echo htmlspecialchars($offense['rate']); ?>')">
                                                <img src="../icons/edit.png" alt="Edit" class="icon-edit"/>
                                            </button>
                                            <form action="offenses_list.php" method="post" style="display:inline;" id="deleteForm_<?php echo $offense['id']; ?>">
                                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($offense['id']); ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="button" class="offenses-delete-btn" onclick="confirmDelete(<?php echo $offense['id']; ?>)">
                                                    <img src="../icons/delete.png" alt="Delete" class="icon-delete"/>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>


            </section>
        </div>
    </div>

    <!-- Modal for Create/Edit Offense -->
<div id="offenseFormModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeOffenseForm">&times;</span>
        <h2 id="formTitle">Create New Offense</h2>
        <form action="offenses_list.php" method="post">
            <input type="hidden" id="offenseId" name="id">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>
            <label for="description">Description:</label>
            <input type="text" id="description" name="description" required>
            <label for="rate">Rate:</label>
            <input type="text" id="rate" name="rate" required>
            <button type="submit" id="formAction" name="action" value="create">Save</button>
        </form>
    </div>
</div>

    <script src="../js/script.js"></script>
    <script>
        function showOffenseForm() {
            document.getElementById('formTitle').innerText = 'Create New Offense';
            document.getElementById('offenseId').value = '';
            document.getElementById('name').value = '';
            document.getElementById('description').value = '';
            document.getElementById('rate').value = '';
            document.getElementById('formAction').value = 'create';
            document.getElementById('offenseFormModal').style.display = 'block';
        }

        function editOffense(id, name, description, rate) {
            document.getElementById('formTitle').innerText = 'Edit Offense';
            document.getElementById('offenseId').value = id;
            document.getElementById('name').value = name;
            document.getElementById('description').value = description;
            document.getElementById('rate').value = rate;
            document.getElementById('formAction').value = 'update';
            document.getElementById('offenseFormModal').style.display = 'block';
        }

        document.getElementById('closeOffenseForm').addEventListener('click', function() {
            document.getElementById('offenseFormModal').style.display = 'none';
        });

        function confirmDelete(offenseId) {
            console.log("Attempting to delete offense with ID:", offenseId);
            const confirmation = confirm("Are you sure you want to delete this offense?");
            if (confirmation) {
                document.getElementById('deleteForm_' + offenseId).submit();
            }
        }

    </script>

    <script>
        // Live search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search');
            const offenseTable = document.getElementById('offenseTable');

            searchInput.addEventListener('keyup', function() {
                const filter = searchInput.value.toLowerCase();
                const rows = offenseTable.getElementsByTagName('tr');

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
