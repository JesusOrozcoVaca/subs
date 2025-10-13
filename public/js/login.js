// Configuración de URL base
const baseUrl = window.location.pathname.includes('/subs/') ? '/subs/' : '/';

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

        // Enviar datos al servidor
        fetch(baseUrl + 'login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.text();
        })
        .then(data => {
            try {
                const jsonData = JSON.parse(data);
                if (jsonData.success) {
                    window.location.href = jsonData.redirect;
                } else {
                    showError(jsonData.message || 'Error de inicio de sesión. Por favor, inténtelo de nuevo.');
                }
            } catch (error) {
                // Si la respuesta no es JSON, asumimos que es una redirección HTML
                document.open();
                document.write(data);
                document.close();
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