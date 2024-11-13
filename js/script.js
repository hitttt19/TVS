    // Script to handle sidebar toggle
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const header = document.querySelector('.header');
        const mainContent = document.querySelector('.main-content');
        const iconsToMove = document.querySelectorAll('.nav-item[data-target="settings"] .icon, .nav-item[data-target="user-list"] .icon, .nav-item[data-target="offenses-list"] .icon');

        sidebar.classList.toggle('collapsed');
        header.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');

        // Adjust the width dynamically based on collapsed state
        if (sidebar.classList.contains('collapsed')) {
            sidebar.style.width = '90px';  // Width when collapsed
            header.style.left = '90px';
            header.style.width = `calc(100% - 90px)`;
            mainContent.style.marginLeft = '90px';

            // Move icons up and ensure they remain clickable when collapsed
            iconsToMove.forEach(icon => {
                icon.style.transform = 'translateY(-40px)';
            });
        } else {
            sidebar.style.width = '250px';  // Default width when expanded
            header.style.left = '250px';
            header.style.width = `calc(100% - 250px)`;
            mainContent.style.marginLeft = '250px';

            // Reset icons position when expanded
            iconsToMove.forEach(icon => {
                icon.style.transform = 'translateY(0)';
            });
        }
    }

    // Script to handle section navigation
    const navItems = document.querySelectorAll('.nav-item');
    const sections = document.querySelectorAll('.content-section');

    navItems.forEach(item => {
        item.addEventListener('click', () => {
            // Toggle active class for navigation items
            navItems.forEach(i => i.classList.remove('active'));
            item.classList.add('active');

            const target = item.getAttribute('data-target');
            sections.forEach(section => {
                section.classList.remove('active');
                if (section.id === target) {
                    section.classList.add('active');
                }
            });
        });
    });

    // Optional: Close sidebar if a nav item is clicked (for mobile view)
    navItems.forEach(item => {
        item.addEventListener('click', () => {
            const sidebar = document.querySelector('.sidebar');
            const header = document.querySelector('.header');
            const mainContent = document.querySelector('.main-content');

            // Close the sidebar for mobile view when a nav item is clicked
            if (window.innerWidth <= 768 && !sidebar.classList.contains('collapsed')) {
                sidebar.classList.add('collapsed');
                header.classList.add('collapsed');
                mainContent.classList.add('collapsed');

                // Update layout when sidebar is collapsed on small screens
                sidebar.style.width = '60px';
                header.style.left = '60px';
                header.style.width = `calc(100% - 60px)`;
                mainContent.style.marginLeft = '60px';
            }
        });
    });
