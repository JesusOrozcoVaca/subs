document.addEventListener('DOMContentLoaded', function() {
    const dynamicContent = document.getElementById('dynamic-content');
    // Calcular la URL base dinámicamente para desarrollo y producción
    let baseUrl;
    const pathParts = window.location.pathname.split('/').filter(part => part !== '');
    
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        // Desarrollo local - incluir el directorio del proyecto
        baseUrl = window.location.origin + '/' + (pathParts.length > 0 ? pathParts[0] + '/' : '');
    } else {
        // Producción - usar la raíz del dominio
        baseUrl = window.location.origin + '/';
    }
    
    console.log('Base URL calculada:', baseUrl);
    console.log('Entorno detectado:', window.location.hostname === 'localhost' ? 'Desarrollo' : 'Producción');

    function loadContent(url) {
        console.log('Cargando contenido desde:', url);
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            console.log('Contenido HTML recibido:', html.substring(0, 100) + '...');
            dynamicContent.innerHTML = html;
            initListeners();
        })
        .catch(error => {
            console.error('Error al cargar el contenido:', error);
            dynamicContent.innerHTML = '<p>Error al cargar el contenido. Por favor, intente de nuevo.</p>';
        });
    }

    document.querySelectorAll('.sidebar-menu a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            loadContent(url);
            history.pushState(null, '', url);
        });
    });

    window.addEventListener('popstate', function() {
        loadContent(location.pathname);
    });

    function initListeners() {
        initFormListeners();
        initEditButtons();
        initToggleStatusButtons();
        initManageButtons();
        initDeleteButtons();
    }

    function initFormListeners() {
        const form = dynamicContent.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        loadContent(baseUrl + 'admin/dashboard');
                    } else {
                        alert(data.message || 'Error al procesar la solicitud');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud');
                });
            });
        }
    }

    function initEditButtons() {
        dynamicContent.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                openPopup(url);
            });
        });
    }

    function initToggleStatusButtons() {
        dynamicContent.querySelectorAll('.btn-deactivate, .btn-activate').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const userId = this.getAttribute('data-user-id');
                const action = this.classList.contains('btn-deactivate') ? 'desactivar' : 'activar';
                if (!userId) {
                    console.error('No user ID found on button');
                    alert('Error: No se pudo identificar el usuario');
                    return;
                }
                if (confirm(`¿Está seguro de que desea ${action} este usuario?`)) {
                    const url = baseUrl + 'admin/toggle-user-status';
                    console.log(`Sending request to: ${url} for user ID: ${userId}`);
                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: `user_id=${encodeURIComponent(userId)}`
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            alert(data.message);
                            loadContent(baseUrl + 'admin/dashboard');
                        } else {
                            alert(data.message || 'Error al cambiar el estado del usuario');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al procesar la solicitud: ' + error.message);
                    });
                }
            });
        });
    }

    function initManageButtons() {
        dynamicContent.querySelectorAll('.btn-manage').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                loadContent(url);
            });
        });
    }

    function openPopup(url) {
        console.log('Abriendo popup para URL:', url);
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            console.log('Contenido del popup recibido:', html.substring(0, 100) + '...');
            
            // Crear el overlay (fondo oscuro)
            const overlay = document.createElement('div');
            overlay.className = 'popup-overlay';
            document.body.appendChild(overlay);
            
            // Crear el popup
            const popup = document.createElement('div');
            popup.className = 'popup';
            popup.innerHTML = `
                <div class="popup-content">
                    <span class="close">&times;</span>
                    ${html}
                </div>
            `;
            document.body.appendChild(popup);

            const closeBtn = popup.querySelector('.close');
            closeBtn.addEventListener('click', () => {
                document.body.removeChild(popup);
                document.body.removeChild(overlay);
            });

            const form = popup.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            document.body.removeChild(popup);
                            document.body.removeChild(overlay);
                            loadContent(baseUrl + 'admin/dashboard');
                        } else {
                            alert(data.message || 'Error al procesar la solicitud');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al procesar la solicitud');
                    });
                });
            }
        })
        .catch(error => {
            console.error('Error al abrir el popup:', error);
            alert('Error al cargar el formulario de edición');
        });
    }


    function initDeleteButtons() {
        dynamicContent.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                const type = this.getAttribute('data-type');
                const confirmMessage = `¿Está seguro de que desea eliminar este ${type === 'user' ? 'usuario' : type === 'product' ? 'producto' : 'CPC'}?`;
                if (confirm(confirmMessage)) {
                    fetch(`${baseUrl}admin/delete-${type}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: `id=${encodeURIComponent(id)}`
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            loadContent(baseUrl + 'admin/dashboard');
                        } else {
                            alert(data.message || `Error al eliminar el ${type === 'user' ? 'usuario' : type === 'product' ? 'producto' : 'CPC'}`);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al procesar la solicitud: ' + error.message);
                    });
                }
            });
        });
    }

    // Cargar el contenido inicial del dashboard
                            loadContent(baseUrl + 'admin/dashboard');
});