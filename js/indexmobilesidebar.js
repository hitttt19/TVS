document.addEventListener("DOMContentLoaded", () => {
    const hamburger = document.getElementById("hamburger");
    const sidebarr = document.getElementById("sidebarr");

    // Toggle sidebar visibility
    hamburger.addEventListener("click", () => {
        sidebarr.classList.toggle("active");
    });

    // Close sidebar when clicking outside
    document.addEventListener("click", (event) => {
        if (!sidebarr.contains(event.target) && !hamburger.contains(event.target)) {
            sidebarr.classList.remove("active");
        }
    });

    // Handle sidebar item clicks (close sidebar after selecting item)
    const sidebarLinks = sidebarr.querySelectorAll("a");
    sidebarLinks.forEach(link => {
        link.addEventListener("click", () => {
            sidebarr.classList.remove("active"); // Close sidebar on link click
        });
    });

    // Function to handle window resizing
    const handleResize = () => {
        if (window.innerWidth > 768) { // Hide sidebar in full screen
            sidebarr.classList.remove("active");
        }
    };

    // Event listener for window resize
    window.addEventListener("resize", handleResize);

    // Initial call to set sidebar state on page load
    handleResize();
});
