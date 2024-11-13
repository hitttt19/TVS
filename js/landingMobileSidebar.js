document.addEventListener("DOMContentLoaded", () => {
    const hamburger = document.getElementById("hamburger");
    const sidebar = document.getElementById("sidebar");

    // Toggle sidebar visibility
    hamburger.addEventListener("click", () => {
        sidebar.classList.toggle("active");
    });

    // Close sidebar when clicking outside
    document.addEventListener("click", (event) => {
        if (!sidebar.contains(event.target) && !hamburger.contains(event.target)) {
            sidebar.classList.remove("active");
        }
    });

    // Handle sidebar item clicks (if needed)
    const sidebarLinks = sidebar.querySelectorAll("a");
    sidebarLinks.forEach(link => {
        link.addEventListener("click", () => {
            sidebar.classList.remove("active"); // Close sidebar on link click
        });
    });

    // Function to handle window resizing
    const handleResize = () => {
        if (window.innerWidth > 768) { // Adjust the width threshold as needed
            sidebar.classList.remove("active"); // Hide the sidebar in full screen
        }
    };

    // Event listener for window resize
    window.addEventListener("resize", handleResize);

    // Initial call to set sidebar state on page load
    handleResize();
});

// js/registerModal.js
document.getElementById('registerNavBtn').onclick = function() {
    document.getElementById('registerModal').style.display = 'block';
}

document.getElementById('closeRegister').onclick = function() {
    document.getElementById('registerModal').style.display = 'none';
}

// js/login.js
document.getElementById('loginNavBtn').onclick = function() {
    document.getElementById('loginModal').style.display = 'block';
}

document.getElementById('closeLogin').onclick = function() {
    document.getElementById('loginModal').style.display = 'none';
}

// Close modal on outside click
window.onclick = function(event) {
    if (event.target === document.getElementById('registerModal') || event.target === document.getElementById('loginModal')) {
        document.getElementById('registerModal').style.display = 'none';
        document.getElementById('loginModal').style.display = 'none';
    }
}

