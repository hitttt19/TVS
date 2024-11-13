<?php
session_start();
include('db_connection.php'); // Ensure the correct path to your db connection file

// Initialize variables
$systemName = 'Bogo City Traffic Violations System'; // Default system name
$shortName = 'RegulaDrive'; // Default short name
$logoPath = 'logo/RegLogo.png'; // Default logo

// Fetch current settings
$query = "SELECT * FROM Settings LIMIT 1";
$stmt = $pdo->prepare($query);
$stmt->execute();
$currentSettings = $stmt->fetch(PDO::FETCH_ASSOC);

if ($currentSettings) {
    $systemName = $currentSettings['system_name'] ?: $systemName;
    $shortName = $currentSettings['system_short_name'] ?: $shortName;
    $logoPath = !empty($currentSettings['logo']) ? $currentSettings['logo'] : $logoPath;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($shortName); ?></title>
    <link rel="icon" href="logo/RegLogo.png">
    <link rel="stylesheet" href="registerpage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<header>
    <div class="logo">
        <!-- Make logo clickable and redirect to landing page -->
        <a href="landingpage.php">
          <img src="logo/RegLogo.png" alt="Reguladrive Logo" class="logo">
            <span class="RDtxt"><?php echo htmlspecialchars($shortName); ?></span>
        </a>
    </div>
    <div class="hamburger" id="hamburger">
        <i class="fas fa-bars"></i>
    </div>
    <nav>
        <ul>
            <li class="announcement">
                <a href="announcementpage.php"><i class="fas fa-bullhorn"></i> Announcement</a>
            </li>
            <li class="aboutUs">
                <a href="about-uspage.php"><i class="fas fa-info-circle"></i> About Us</a>
            </li>
        </ul>
    </nav>
</header>
<main style="background: url(image/trf.jpg) no-repeat center center fixed; background-size: cover; height: calc(100vh - 70px); display: flex; justify-content: center; align-items: center; position: relative;">
<!-- Full-page Login Form inside the overlay -->
<div class="overlay">
  <div class="container">
    <!-- Left Section -->
    <div class="left-section">
      <img src="logo/RegLogo.png" alt="RegLogo" class="icon">
      <h2>Welcome to RegulaDrive</h2>
      <p>Bogo City Traffic Violation Sysrem!</p>
      <a href="loginpage.php" class="login-btn" id="loginBtn">Login</a>
    </div>
    
    <form id="registerForm" action="register.php" method="post" enctype="multipart/form-data" class="right-section">
      <!-- Step 1 -->
      <div class="form-step active">
        <h2>Personal Info</h2>
        <div class="form-group">
          <input type="text" id="license_id" name="license_id" placeholder="License ID *" required>
        </div>
        <div class="form-group">
          <select id="license_type" name="license_type" required>
            <option value="" disabled selected>Select License Type *</option>
            <option value="Student Permit">Student Permit</option>
            <option value="Non-Professional">Non-Professional</option>
            <option value="Professional">Professional</option>
          </select>
        </div>
        <div class="form-group">
          <input type="text" id="firstname" name="firstname" placeholder="First Name *" required>
        </div>
        <div class="form-group">
          <input type="text" id="middlename" name="middlename" placeholder="Middle Name">
        </div>
        <div class="form-group">
          <input type="text" id="lastname" name="lastname" placeholder="Last Name *" required>
        </div>
        <div class="form-group">
          <select id="gender" name="gender" required>
            <option value="" disabled selected>Select Gender *</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
          </select>
        </div>
        <button type="button" class="next-btn">Next</button>
      </div>

      <!-- Step 2.1 -->
      <div class="form-step">
        <h2>Personal Info</h2>
        <div class="form-group">
          <input type="date" id="date_of_birth" name="date_of_birth" placeholder="Date of Birth *" required>
        </div>
        <div class="form-group">
          <select id="civil_status" name="civil_status" required>
            <option value="" disabled selected>Civil Status *</option>
            <option value="Single">Single</option>
            <option value="Married">Married</option>
            <option value="Divorced">Divorced</option>
            <option value="Widowed">Widowed</option>
            <option value="Separated">Separated</option>
          </select>
        </div>
        <div class="form-group">
          <input type="text" id="nationality" name="nationality" placeholder="Nationality *" required>
        </div>
        <div class="form-group">
          <input type="text" id="contact_number" name="contact_number" placeholder="Contact Number *" required>
        </div>
        <div class="form-group">
          <textarea id="present_address" name="present_address" rows="3" placeholder="Present Address *" required></textarea>
        </div>
        <button type="button" class="next-btn">Next</button>
        <button type="button" class="prev-btn">Previous</button>
      </div>

      <!-- Step 2.2 -->
      <div class="form-step">
        <h2>Personal Info</h2>
        <div class="form-group">
          <textarea id="permanent_address" name="permanent_address" rows="3" placeholder="Permanent Address *" required></textarea>
        </div>
        <div class="form-group">
          <input type="text" id="regUsername" name="username" placeholder="Username *" required>
        </div>
        <div class="form-group">
          <input type="email" id="regEmail" name="email" placeholder="Email *" required>
        </div>
        <div class="form-group">
          <input type="password" id="regPassword" name="password" placeholder="Password *" required>
        </div>
        <div class="form-group">
          <input type="file" id="photo" name="photo" accept="image/jpeg, image/png, image/gif">
        </div>
        
        <button type="button" class="prev-btn">Previous</button>
        <button type="submit" class="register-btn">Register</button>
      </div>
      </form>
        </div>
    </div>
</main>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
  const formSteps = document.querySelectorAll(".form-step");
  const nextBtns = document.querySelectorAll(".next-btn");
  const prevBtns = document.querySelectorAll(".prev-btn");
  let currentStep = 0;

  // Function to check if all required fields in the current step are filled
  function validateStep() {
    const currentFormStep = formSteps[currentStep];
    const requiredFields = currentFormStep.querySelectorAll("input[required], select[required], textarea[required]");
    let allFieldsFilled = true;

    requiredFields.forEach(field => {
      if (!field.value.trim()) {
        allFieldsFilled = false;
      }
    });

    nextBtns[currentStep].disabled = !allFieldsFilled;
  }

  // Show the next step
  nextBtns.forEach(button => {
    button.addEventListener("click", () => {
      if (currentStep < formSteps.length - 1) {
        formSteps[currentStep].classList.remove("active");
        currentStep++;
        formSteps[currentStep].classList.add("active");
        validateStep();
      }
    });
  });

  // Show the previous step
  prevBtns.forEach(button => {
    button.addEventListener("click", () => {
      if (currentStep > 0) {
        formSteps[currentStep].classList.remove("active");
        currentStep--;
        formSteps[currentStep].classList.add("active");
        validateStep();
      }
    });
  });

  // Validate the current step on input change
  formSteps.forEach(formStep => {
    const requiredFields = formStep.querySelectorAll("input[required], select[required], textarea[required]");
    requiredFields.forEach(field => {
      field.addEventListener("input", validateStep);
    });
  });

  // Initial validation for the first step
  validateStep();
});

loginBtn.addEventListener('click', function(event) {
    event.preventDefault(); // Prevent immediate navigation
    document.querySelector('main').classList.add('turning');
    setTimeout(() => {
        window.location.href = 'loginpage.php';
    }, 1000); // Duration of the page turn transition
});

  </script>
  <script>// Toggle mobile menu
    document.getElementById('hamburger').addEventListener('click', function() {
        document.querySelector('nav').classList.toggle('active');
    });
</script>
</body>
</html>
