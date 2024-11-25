<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RegulaDrive</title>
    <link rel="stylesheet" href="terms.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="logo/RegLogo.png" id="favicon">
</head>
<body>
<header>
    <div class="logo">
        <a href="landingpage.php" class="link">
            <img src="logo/RegLogo.png" alt="RD Logo">
            <span class="RDtxt">RegulaDrive</span>
        </a>
    </div>
</header>

<main>
    <h1>Terms of Service</h1>
    <p>Welcome to RegulaDrive. These terms of service outline the rules and regulations for the use of our system.</p>
    <p>By accessing this website or using our services, you accept these terms in full. Do not continue to use RegulaDrive if you do not agree to all of the terms and conditions stated on this page.</p>
    
    <h2>1. License</h2>
    <p>Unless otherwise stated, RegulaDrive and/or its licensors own the intellectual property rights for all material on RegulaDrive. All intellectual property rights are reserved. You may access this from RegulaDrive for your own personal use subjected to restrictions set in these terms and conditions.</p>
    <p>You must not:</p>
    <ul>
        <li>Republish material from RegulaDrive</li>
        <li>Sell, rent, or sub-license material from RegulaDrive</li>
        <li>Reproduce, duplicate, or copy material from RegulaDrive</li>
        <li>Redistribute content from RegulaDrive</li>
    </ul>

    <h2>2. User Responsibilities</h2>
    <p>You are responsible for ensuring the information you provide is accurate and up-to-date. Any misuse of the system, including attempts to compromise its security, is strictly prohibited.</p>

    <h2>3. Limitation of Liability</h2>
    <p>RegulaDrive will not be held accountable for any damages arising from the use or inability to use the service, or from any unauthorized access to or alteration of your transmissions or data.</p>

    <h2>4. Modifications</h2>
    <p>We reserve the right to modify these terms at any time. Any changes will be effective immediately upon posting. Your continued use of the service constitutes acceptance of the modified terms.</p>

    <h2>5. Governing Law</h2>
    <p>These terms will be governed by and constructed in accordance with the laws of the applicable jurisdiction.</p>

    <p>If you have any questions or concerns regarding these terms, please <a href="contact-us.php" class="link">contact us</a>.</p>
</main>

<footer>
    <p>&copy; <?php echo date('Y'); ?> RegulaDrive. All Rights Reserved.</p>
    <p>
        <a href="privacy-policy.php" class="link">Privacy Policy</a> |
        <a href="terms-of-service.php" class="link">Terms of Service</a>
    </p>
</footer>
<script src="termspolicyanimation.js"></script>
</body>
</html>
