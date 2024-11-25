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
    $title = trim($_POST['announcement-title']);
    $content = trim($_POST['announcement-content']);
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    try {
        switch ($action) {
            case 'create':
                if (empty($title) || empty($content)) {
                    throw new Exception('Title and content are required.');
                }
                $stmt = $pdo->prepare("INSERT INTO announcements (title, content) VALUES (?, ?)");
                $stmt->execute([$title, $content]);
                $_SESSION['message_success'] = "Announcement posted successfully!";
                break;

            case 'update':
                if (empty($title) || empty($content) || $id <= 0) {
                    throw new Exception('Title, content, and a valid ID are required.');
                }
                $stmt = $pdo->prepare("UPDATE announcements SET title = ?, content = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$title, $content, $id]);
                $_SESSION['message_success'] = "Announcement updated successfully!";
                break;

            case 'delete':
                if ($id <= 0) {
                    throw new Exception('Invalid ID.');
                }
                $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['message_success'] = "Announcement deleted successfully!";
                break;
        }
        // Redirect to avoid form resubmission
        header("Location: announcement.php");
        exit();
    } catch (Exception $e) {
        $error = "Error: " . htmlspecialchars($e->getMessage());
    } catch (PDOException $e) {
        $error = "Database Error: " . htmlspecialchars($e->getMessage());
    }
}

// Fetch existing announcements
$stmt = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC");
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RegulaDrive</title>
    <link rel="icon" href="../logo/RegLogo.png" id="favicon">
    <link rel="stylesheet" href="../css/Index.css">
    <link rel="stylesheet" href="../css/announcement.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.11.2/toastify.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                <li class="nav-item active" onclick="window.location='announcement.php';">
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
        <li class="nav-item " onclick="window.location='admin_dashboard.php';">
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
                <li class="nav-item active" onclick="window.location='announcement.php';">
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
            <section id="announcement" class="content-section announcement active">
                <h2>Announcement</h2>
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form class="announcement-form" action="announcement.php" method="post">
                    <input type="hidden" id="announcementId" name="id">
                    <div class="form-group">
                        <label for="announcement-title">Title</label>
                        <input type="text" id="announcement-title" name="announcement-title" placeholder="Enter announcement title" required>
                    </div>
                    <div class="form-group">
                        <label for="announcement-content">Content</label>
                        <textarea id="announcement-content" name="announcement-content" rows="5" placeholder="Enter announcement content" required></textarea>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="announcement-submit-btn" id="formAction" name="action" value="create">Submit</button>
                    </div>
                </form>
                <h3>Previous Announcements</h3>
                <div class="announcement-list">
                    <ul>
                        <?php foreach ($announcements as $announcement): ?>
                        <li>
                            <strong>Title:</strong> <?php echo htmlspecialchars($announcement['title']); ?>
                            <div class="button-group">
                                <button type="button" class="edit-btn" onclick="editAnnouncement(<?php echo htmlspecialchars($announcement['id']); ?>, '<?php echo htmlspecialchars(addslashes($announcement['title'])); ?>', '<?php echo htmlspecialchars(addslashes($announcement['content'])); ?>')">
                                    <img src="../icons/edit.png" alt="Edit">
                                </button>
                                <form action="announcement.php" method="post" style="display:inline;"onsubmit="return confirmDelete()">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($announcement['id']); ?>">
                                    <input type="hidden" name="announcement-title" value="<?php echo htmlspecialchars($announcement['title']); ?>">
                                    <input type="hidden" name="announcement-content" value="<?php echo htmlspecialchars($announcement['content']); ?>">
                                    <button type="submit" name="action" value="delete" class="delete-btn">
                                        <img src="../icons/delete.png" alt="Delete">
                                    </button>
                                </form>
                                <script>
                                    function confirmDelete() {
                                    return confirm("Are you sure you want to delete this announcement?");
                                    }
                            </script>
                            </div>
                            <p><?php echo htmlspecialchars($announcement['content']); ?></p>
                            <small><?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($announcement['created_at']))); ?></small>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section> 
        </div>
    </div>
    <script src="../js/script.js"></script>
    <script>
        function editAnnouncement(id, title, content) {
            document.getElementById('announcementId').value = id;
            document.getElementById('announcement-title').value = title;
            document.getElementById('announcement-content').value = content;
            document.getElementById('formAction').value = 'update';
        }
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
