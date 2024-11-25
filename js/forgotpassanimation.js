document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;

    // Add fade-in effect on page load
    body.classList.add('fade-in');

    // Add fade-out effect for link clicks
    const links = document.querySelectorAll('a');
    links.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const href = link.getAttribute('href');
            body.classList.add('fade-out');
            setTimeout(() => {
                window.location.href = href;
            }, 500); // Duration matches CSS transition
        });
    });
})