document.getElementById('login-form').addEventListener('submit', async function(event) {
    event.preventDefault();

    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    // Simple client-side validation
    if (!username || !password) {
        document.getElementById('error-message').textContent = 'Username and password are required.';
        return;
    }

    // Send login request to the server
    try {
        const response = await fetch('login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                username: username,
                password: password
            })
        });

        const result = await response.json();

        if (result.success) {
            // Redirect to Index.html
            window.location.href = 'Index.html';
        } else {
            document.getElementById('error-message').textContent = result.message || 'Login failed.';
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('error-message').textContent = 'An error occurred. Please try again.';
    }
});

