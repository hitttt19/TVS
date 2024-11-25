document.addEventListener('DOMContentLoaded', () => {
        const body = document.body;

        // Add a fade-in animation on page load
        body.classList.add('fade-in');

        // Add fade-out on link clicks
        const links = document.querySelectorAll('a.link');
        links.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const href = link.getAttribute('href');
                body.classList.add('fade-out');
                setTimeout(() => {
                    window.location.href = href;
                }, 500); // Match transition duration
            });
        });
    });
