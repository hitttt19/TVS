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
    <!-- Sidebar (Initially hidden) -->
    <div id="sidebar" class="sidebar">
    <!-- Logo and Header Text -->
    <div class="sidebar-header">
        <img src="logo/Reglogo.png" alt="Logo" class="sidebar-logo">
        <h2 class="sidebar-title">REGULADRIVE</h2>
    </div>

    <!-- Navigation Links -->
    <ul>
        <li><a href="loginpage.php">Login</a></li>
        <li><a href="registerpage.php">Register</a></li>
        <li><a href="announcementpage.php">Announcements</a></li>
        <li><a href="about-uspage.php">About Us</a></li>
    </ul>
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

<main>
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
          <input type="text" id="license_id" name="license_id" placeholder="License ID or Other ID *" required>
        </div>
        <div class="form-group">
          <select id="license_type" name="license_type" required>
            <option value="" disabled selected>Select License Type (if applicable)</option>
            <option value="Student Permit">Student Permit</option>
            <option value="Non-Professional">Non-Professional</option>
            <option value="Professional">Professional</option>
            <option value="Other ID">Other ID</option>
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
          <span id="contact_number_error" style="color: red; display: none;"></span>
        </div>
        <div class="form-group">
          <textarea id="present_address" name="present_address" rows="3" placeholder="Present Address *" required></textarea>
        </div>
        <button type="button" class="prev-btn">Previous</button>
        <button type="button" class="next-btn">Next</button>
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
          <label class="file-upload">
            <i class="fas fa-upload"></i> Upload Your Photo
            <input type="file" name="photo" accept="image/*" required onchange="updateFileName(this, '#photo-name')">
            <span id="photo-name"></span> <!-- This will display the selected file name -->
          </label>
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

      // Check if we are in Step 2.1 and validate contact number
      if (currentStep === 1) { // Step 2.1 is the second step (index 1)
        const contactNumberField = document.getElementById('contact_number');
        const contactNumberError = document.getElementById('contact_number_error'); // We’ll display the error here

        if (contactNumberField) {
          const contactNumber = contactNumberField.value.trim();

          // If the contact number is not 11 digits, show an error message
          if (contactNumber.length !== 11 || !/^\d{11}$/.test(contactNumber)) {
            allFieldsFilled = false;
            // Show error message
            if (contactNumberError) {
              contactNumberError.textContent = "Please enter a valid 11-digit contact number.";
              contactNumberError.style.display = "block"; // Make the error message visible
            }
          } else {
            // Clear the error message if the input is valid
            if (contactNumberError) {
              contactNumberError.textContent = "";
              contactNumberError.style.display = "none";
            }
          }
        }
      }

      // Disable the "Next" button if the current step is incomplete
      nextBtns[currentStep].disabled = !allFieldsFilled;
    }

    // Show the next step
    nextBtns.forEach(button => {
      button.addEventListener("click", () => {
        // Proceed only if the current step is valid (all required fields are filled and contact number is valid)
        if (currentStep < formSteps.length - 1 && !nextBtns[currentStep].disabled) {
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


  // Function to update the file name displayed beside the icon
  function updateFileName(input, nameDisplayId) {
    const fileName = input.files[0] ? input.files[0].name : ''; // Get the file name
    const nameDisplay = document.querySelector(nameDisplayId); // Find the span element to display the name
    nameDisplay.textContent = fileName ? ` ${fileName}` : ''; // Display the file name
  }
</script>
<script src="js/landingMobileSidebar.js"></script>
</body>
</html>
