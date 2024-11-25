<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RegulaDrive</title>
    <link rel="stylesheet" href="privacy.css"> <!-- Link to your custom stylesheet -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="<?php echo !empty($logoPath) ? htmlspecialchars($logoPath) : 'logo/RegLogo.png'; ?>" id="favicon">
</head>
<body>
<header>
    <div class="logo">
        <a href="landingpage.php">
            <img src="logo/RegLogo.png" alt="RD Logo">
            <span class="RDtxt">RegulaDrive</span>
        </a>
    </div>
</header>

<main style="padding: 20px; max-width: 800px; margin: 0 auto; font-family: Arial, sans-serif; line-height: 1.6;">
    <h1>Privacy Policy</h1>
    <p>Your privacy is important to us. This privacy policy explains how we collect, use, and protect your information when you use RegulaDrive.</p>

    <h2>1. Information We Collect</h2>
    <p>We collect the following types of information:</p>
    <ul>
        <li>Personal Information: such as name, email address, phone number, and other details provided during registration.</li>
        <li>Usage Data: including IP address, browser type, and activity logs on our system.</li>
    </ul>

    <h2>2. How We Use Your Information</h2>
    <p>We use your information to:</p>
    <ul>
        <li>Provide and manage our services.</li>
        <li>Communicate with you regarding system updates, notifications, and announcements.</li>
        <li>Improve our system by analyzing user behavior.</li>
    </ul>

    <h2>3. Sharing Your Information</h2>
    <p>We do not sell, trade, or rent your personal information to others. However, we may share information with:</p>
    <ul>
        <li>Law enforcement agencies if required by law.</li>
        <li>Trusted third-party service providers assisting us with system functionality.</li>
    </ul>

    <h2>4. Data Security</h2>
    <p>We implement security measures to protect your information. However, no method of transmission or storage is completely secure, and we cannot guarantee absolute security.</p>

    <h2>5. Your Rights</h2>
    <p>You have the right to access, update, or delete your personal information. To exercise these rights, please <a href="contact-us.php" style="color: blue;">contact us</a>.</p>

    <h2>6. Changes to This Policy</h2>
    <p>We reserve the right to update this privacy policy at any time. We will notify you of significant changes through our system or via email.</p>

    <p>If you have any questions or concerns about our privacy policy, please <a href="contact-us.php" style="color: blue;">contact us</a>.</p>
</main>

<footer style="background-color: #0A3B31; color: #fff; text-align: center; padding: 20px;">
    <p>&copy; <?php echo date('Y'); ?> RegulaDrive. All Rights Reserved.</p>
    <p>
        <a href="privacy-policy.php" style="color: #fff; text-decoration: underline;">Privacy Policy</a> |
        <a href="terms-of-service.php" style="color: #fff; text-decoration: underline;">Terms of Service</a>
    </p>
</footer>
<script src="termspolicyanimation.js"></script>
</body>
</html>
