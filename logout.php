<?php
session_start();
session_destroy();

// Prevent caching of the page to prevent back button navigation
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); // Prevent caching
header("Pragma: no-cache");
header("Expires: 0");

// Redirect to the landing page
header('Location: landingpage.php');
exit;
?>
