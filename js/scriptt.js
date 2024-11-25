document.addEventListener("DOMContentLoaded", () => {
    const sidebar = document.querySelector(".mobile-sidebar");
    const hamburger = document.getElementById("hamburger");
    const overlay = document.querySelector(".overlay");

    // Check if elements are found
    if (!sidebar || !hamburger || !overlay) {
        console.error("Required elements not found in the DOM");
        return;
    }

    // Add event listener to the sidebar menu items
    const navItems = sidebar.querySelectorAll(".nav-item");
    if (navItems.length > 0) {
        navItems.forEach((item) => {
            item.addEventListener("click", () => {
                console.log("Nav item clicked!");
                if (sidebar.classList.contains("show")) {
                    toggleSidebar();
                }
            });
        });
    } else {
        console.error("No nav-items found in the mobile sidebar.");
    }

    // Toggle sidebar function
    function toggleSidebar() {
        sidebar.classList.toggle("show");
        overlay.classList.toggle("show");
        hamburger.classList.toggle("hidden");
    }

    overlay.addEventListener("click", toggleSidebar);
    hamburger.addEventListener("click", toggleSidebar);
});
