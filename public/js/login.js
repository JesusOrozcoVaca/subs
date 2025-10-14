// Cargar helper de URLs
// const baseUrl = getBaseUrl(); // Se obtiene del helper

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const username = usernameInput.value.trim();
        const password = passwordInput.value.trim();

        if (username === '' || password === '') {
            showError('Por favor, complete todos los campos.');
            return;
        }

        // Usar la función helper para obtener la URL correcta
        const loginUrl = URLS.login();

        // Enviar datos al servidor
        fetch(loginUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                showError(data.message || 'Error de inicio de sesión. Por favor, inténtelo de nuevo.');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            showError('Error de conexión. Por favor, inténtelo más tarde.');
        });
    });

    function showError(message) {
        const errorElement = document.querySelector('.error-message');
        if (errorElement) {
            errorElement.textContent = message;
        } else {
            const newErrorElement = document.createElement('p');
            newErrorElement.classList.add('error-message');
            newErrorElement.textContent = message;
            loginForm.insertBefore(newErrorElement, loginForm.firstChild);
        }
    }
});