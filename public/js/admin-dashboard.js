document.addEventListener('DOMContentLoaded', function() {
    const dynamicContent = document.getElementById('dynamic-content');
    
    console.log('Sistema detectado:', isNewSystem() ? 'Nuevo (Query Parameters)' : 'Legacy (URLs Amigables)');
    console.log('Base URL:', getBaseUrl());

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
                        loadContent(URLS.adminDashboard());
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
                    const url = URLS.adminToggleUserStatus();
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
                            loadContent(URLS.adminDashboard());
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

            // Cerrar popup al hacer clic en el overlay
            overlay.addEventListener('click', () => {
                document.body.removeChild(popup);
                document.body.removeChild(overlay);
            });

            // Evitar que el popup se cierre al hacer clic en el contenido
            popup.addEventListener('click', (e) => {
                e.stopPropagation();
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
                            loadContent(URLS.adminDashboard());
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
                    const deleteUrl = type === 'user' ? URLS.adminDeleteUser() : 
                                     type === 'product' ? URLS.adminDeleteProduct() : 
                                     URLS.adminDeleteCpc();
                    fetch(deleteUrl, {
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
                            loadContent(URLS.adminDashboard());
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
    loadContent(URLS.adminDashboard());
});