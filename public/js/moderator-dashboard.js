document.addEventListener('DOMContentLoaded', function() {
    const dynamicContent = document.getElementById('dynamic-content');

    console.log('Sistema detectado:', isNewSystem() ? 'Nuevo (Query Parameters)' : 'Legacy (URLs Amigables)');
    console.log('Base URL:', getBaseUrl());

    function loadContent(url) {
        console.log('Cargando contenido desde:', url);
        
        // Mostrar indicador de carga
        dynamicContent.innerHTML = '<div class="loading">Cargando...</div>';
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Respuesta del servidor:', response.status, response.statusText);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.text();
        })
        .then(html => {
            console.log('Contenido HTML recibido:', html.substring(0, 200) + '...');
            console.log('Longitud del contenido:', html.length);
            
            if (html.trim() === '') {
                throw new Error('El servidor devolvió contenido vacío');
            }
            
            dynamicContent.innerHTML = html;
            initListeners();
            
            // Actualizar el título de la página si es necesario
            updatePageTitle(url);
        })
        .catch(error => {
            console.error('Error al cargar el contenido:', error);
            dynamicContent.innerHTML = `
                <div class="error-message">
                    <h3>Error al cargar el contenido</h3>
                    <p>${error.message}</p>
                    <p>URL solicitada: ${url}</p>
                    <button onclick="loadContent('${url}')" class="btn">Reintentar</button>
                </div>
            `;
        });
    }

    // Configurar event listeners para el menú
    function setupMenuListeners() {
        const menuLinks = document.querySelectorAll('.sidebar-menu a');
        console.log('Enlaces del menú encontrados:', menuLinks.length);
        
        menuLinks.forEach((link, index) => {
            console.log(`Enlace ${index + 1}:`, link.textContent, 'href:', link.getAttribute('href'));
            
            // Remover todos los event listeners existentes
            const newLink = link.cloneNode(true);
            link.parentNode.replaceChild(newLink, link);
            
            // Agregar nuevo event listener
            newLink.addEventListener('click', handleMenuClick);
            
            // También agregar onclick como respaldo
            newLink.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                handleMenuClick.call(this, e);
                return false;
            };
        });
    }
    
    function handleMenuClick(e) {
            e.preventDefault();
        e.stopPropagation();
        
            const url = this.getAttribute('href');
        console.log('Click en menú:', this.textContent, 'URL:', url);
        
        // Actualizar clase activa
        document.querySelectorAll('.sidebar-menu a').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
        
            loadContent(url);
            history.pushState(null, '', url);
        
        return false; // Prevenir comportamiento por defecto adicional
    }
    
    // Configurar listeners iniciales
    setupMenuListeners();

    window.addEventListener('popstate', function() {
        loadContent(location.pathname);
    });

    function initListeners() {
        setupMenuListeners(); // Reconfigurar listeners del menú
        initFormListeners();
        initEditButtons();
        initDeleteButtons();
    }

    function initFormListeners() {
        const form = dynamicContent.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                
                // Obtener la URL del formulario correctamente, evitando conflicto con name="action"
                const formAction = this.getAttribute('action');
                console.log('Form action URL:', formAction);
                
                fetch(formAction, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        alert(data.message);
                        loadContent(URLS.moderatorManageCpcs());
                    } else {
                        alert(data.message || 'Error al procesar la solicitud');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud: ' + error.message);
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
                    // Obtener la URL del formulario correctamente, evitando conflicto con name="action"
                    const formAction = this.getAttribute('action');
                    console.log('Popup form action URL:', formAction);
                    
                    fetch(formAction, {
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
                            loadContent(URLS.moderatorManageCpcs());
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
                const confirmMessage = `¿Está seguro de que desea eliminar este ${type === 'cpc' ? 'CPC' : 'elemento'}?`;
                if (confirm(confirmMessage)) {
                    const deleteUrl = URLS.moderatorDeleteCpc();
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
                            loadContent(URLS.moderatorManageCpcs());
            } else {
                            alert(data.message || `Error al eliminar el ${type === 'cpc' ? 'CPC' : 'elemento'}`);
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




    // Función para actualizar el título de la página según la URL
    function updatePageTitle(url) {
        const pageTitle = document.querySelector('h1');
        if (pageTitle) {
            if (url.includes('manage-cpcs')) {
                pageTitle.textContent = 'Gestionar CPCs';
            } else if (url.includes('dashboard')) {
                pageTitle.textContent = 'Dashboard de Moderador';
            }
        }
    }

    // Cargar el contenido inicial del dashboard después de un pequeño delay
    setTimeout(() => {
        loadContent(URLS.moderatorDashboard());
    }, 100);
});