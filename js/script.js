document.getElementById('hamburger').addEventListener('click', function() {
    toggleMobileSidebar();
    document.querySelector('.overlay').classList.toggle('show'); // Show the overlay
    this.classList.add('hidden'); // Hide the hamburger icon when clicked
});

function toggleMobileSidebar() {
    const sidebar = document.querySelector('.mobile-sidebar');
    sidebar.classList.toggle('show');
    
    // Close the mobile sidebar when the overlay is clicked
    document.querySelector('.overlay').addEventListener('click', function() {
        toggleMobileSidebar();
        this.classList.remove('show'); // Hide the overlay when clicked
        document.getElementById('hamburger').classList.remove('hidden'); // Show the hamburger icon again
    });
}
