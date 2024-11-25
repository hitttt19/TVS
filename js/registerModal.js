document.addEventListener("DOMContentLoaded", function() {
    // Modal functionality
    var registerModal = document.getElementById("registerModal");
    var loginModal = document.getElementById("loginModal");
    var registerBtn = document.getElementById("registerBtn");
    var loginBtn = document.getElementById("loginBtn");
    var registerNavBtn = document.getElementById("registerNavBtn");
    var loginNavBtn = document.getElementById("loginNavBtn");
    var closeRegister = document.getElementById("closeRegister");
    var closeLogin = document.getElementById("closeLogin");

    // Function to fade in and slide down the modal
    function fadeIn(modal) {
        modal.style.display = "block"; // Ensure the modal is visible
        modal.style.opacity = 0; // Start with opacity 0
        modal.style.transform = "translateY(-30px)"; // Start above the screen
        modal.style.transition = "opacity 0.5s ease, transform 0.5s ease"; // Smooth transition
        setTimeout(() => {
            modal.style.opacity = 1; // Transition to full opacity
            modal.style.transform = "translateY(0)"; // Slide into place
        }, 20); // Delay slightly to allow initial styles to apply
    }

    // Function to fade out and slide up the modal
    function fadeOut(modal) {
        modal.style.opacity = 1; // Start with full opacity
        modal.style.transform = "translateY(0)"; // Reset transform
        modal.style.transition = "opacity 0.5s ease, transform 0.5s ease"; // Smooth transition

        setTimeout(() => {
            modal.style.opacity = 0; // Transition to opacity 0
            modal.style.transform = "translateY(-30px)"; // Slide up
        }, 20); // Delay slightly to allow initial styles to apply

        // Hide the modal after fade out completes
        setTimeout(() => {
            modal.style.display = "none"; // Hide modal
        }, 500); // Wait for the transition duration
    }

    // Open modals
    registerBtn.onclick = function() {
        fadeOut(loginModal); // Close login modal if open
        fadeIn(registerModal); // Fade in and slide down the register modal
    }

    loginBtn.onclick = function() {
        fadeOut(registerModal); // Close register modal if open
        fadeIn(loginModal); // Fade in and slide down the login modal
    }

    // registerNavBtn.onclick = function() {
    //     fadeOut(loginModal); // Close login modal if open
    //     fadeIn(registerModal); // Fade in and slide down the register modal
    // }

    // loginNavBtn.onclick = function() {
    //     fadeOut(registerModal); // Close register modal if open
    //     fadeIn(loginModal); // Fade in and slide down the login modal
    // }

    // Close modals
    closeRegister.onclick = function() {
        fadeOut(registerModal); // Fade out and slide up register modal
    }

    closeLogin.onclick = function() {
        fadeOut(loginModal); // Fade out and slide up login modal
    }

    // Close modals when clicking outside the modal content
    window.onclick = function(event) {
        if (event.target === registerModal) {
            fadeOut(registerModal); // Fade out and slide up register modal
        }
        if (event.target === loginModal) {
            fadeOut(loginModal); // Fade out and slide up login modal
        }
    }
});