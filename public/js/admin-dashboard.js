// Usar las funciones de url-helper.js que ya están disponibles

document.addEventListener('DOMContentLoaded', function() {
    console.log('=== ADMIN DASHBOARD JS LOADED ===');
    console.log('DOM Content Loaded event fired');
    
    const dynamicContent = document.getElementById('dynamic-content');
    console.log('Dynamic content element:', dynamicContent);
    
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
            console.log('Content loaded, initializing listeners...');
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
    
    // Configurar event delegation para botones de editar
    setupEventDelegation();

    window.addEventListener('popstate', function() {
        // Solo cargar contenido si no estamos en una página de gestión de producto
        if (!location.pathname.includes('/manage-product/') && !location.pathname.includes('/edit-product/') && !location.pathname.includes('/create-product/')) {
        loadContent(location.pathname);
        }
    });

    function initListeners() {
        console.log('=== INITIALIZING ALL LISTENERS ===');
        console.log('Dynamic content:', dynamicContent);
        console.log('Dynamic content HTML length:', dynamicContent.innerHTML.length);
        
        setupMenuListeners(); // Reconfigurar listeners del menú
        initFormListeners();
        initEditButtons();
        initToggleStatusButtons();
        initManageButtons();
        initDeleteButtons();
        
        console.log('=== ALL LISTENERS INITIALIZED ===');
    }

    function initFormListeners() {
        const forms = dynamicContent.querySelectorAll('form');
        forms.forEach(form => {
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
                    console.log('Response headers:', response.headers);
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        alert(data.message);
                        // Si es un formulario de gestión de producto, recargar el contenido de gestión
                        if (formAction.includes('manage-product')) {
                            loadContent(formAction);
                        } else {
                        loadContent(URLS.adminDashboard());
                        }
                    } else {
                        alert(data.message || 'Error al procesar la solicitud');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud');
                });
            });
        });
    }

    function initEditButtons() {
        console.log('=== INITIALIZING EDIT BUTTONS ===');
        console.log('Dynamic content element:', dynamicContent);
        console.log('Dynamic content innerHTML preview:', dynamicContent.innerHTML.substring(0, 200) + '...');
        
        const editButtons = dynamicContent.querySelectorAll('.btn-edit');
        console.log('Edit buttons found:', editButtons.length);
        
        if (editButtons.length === 0) {
            console.log('NO EDIT BUTTONS FOUND - This is the problem!');
            console.log('Available buttons:', dynamicContent.querySelectorAll('button, a'));
            console.log('Available links:', dynamicContent.querySelectorAll('a'));
            console.log('Available elements with btn class:', dynamicContent.querySelectorAll('.btn'));
            return;
        }
        
        editButtons.forEach((button, index) => {
            console.log(`Edit button ${index + 1}:`, button);
            console.log(`Edit button ${index + 1} href:`, button.getAttribute('href'));
            
            // Remover listeners existentes para evitar duplicados
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
            
            newButton.addEventListener('click', function(e) {
                console.log('Edit button clicked!');
                e.preventDefault();
                e.stopPropagation();
                const url = this.getAttribute('href');
                console.log('Opening popup for URL:', url);
                
                // SOLUCIÓN ULTRA-SIMPLE - POPUP BÁSICO
                createSimplePopup(url);
            });
        });
        
        console.log('Edit buttons initialized successfully');
    }
    
    // SOLUCIÓN ALTERNATIVA: Event delegation para capturar todos los clics
    function setupEventDelegation() {
        console.log('=== SETTING UP EVENT DELEGATION ===');
        
        // Usar event delegation en el documento para capturar todos los clics
        document.addEventListener('click', function(e) {
            // Verificar si el elemento clickeado tiene la clase btn-edit
            if (e.target.classList.contains('btn-edit')) {
                console.log('Edit button clicked via delegation!');
                e.preventDefault();
                e.stopPropagation();
                
                const url = e.target.getAttribute('href');
                console.log('Opening popup for URL:', url);
                
                // SOLUCIÓN ULTRA-SIMPLE - POPUP BÁSICO
                createSimplePopup(url);
            }
        });
        
        console.log('Event delegation set up successfully');
    }
    
    function createSimplePopup(url) {
        console.log('Creating simple popup for:', url);
        
        // Crear overlay
        const overlay = document.createElement('div');
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 99999;
            display: flex;
            justify-content: center;
            align-items: center;
        `;
        
        // Crear popup
        const popup = document.createElement('div');
        popup.style.cssText = `
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
        `;
        
        // Botón de cierre
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '&times;';
        closeBtn.style.cssText = `
            position: absolute;
            top: 10px;
            right: 15px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
        `;
        
        // Contenido de carga
        const content = document.createElement('div');
        content.innerHTML = '<div style="text-align: center; padding: 20px;">Cargando...</div>';
        
        // Ensamblar popup
        popup.appendChild(closeBtn);
        popup.appendChild(content);
        overlay.appendChild(popup);
        document.body.appendChild(overlay);
        
        console.log('Popup created and added to DOM');
        
        // Event listeners
        closeBtn.onclick = () => {
            console.log('Close button clicked');
            document.body.removeChild(overlay);
        };
        
        overlay.onclick = (e) => {
            if (e.target === overlay) {
                console.log('Overlay clicked');
                document.body.removeChild(overlay);
            }
        };
        
        // Cargar contenido
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Fetch response:', response.status);
            return response.text();
        })
        .then(html => {
            console.log('Content loaded, updating popup');
            content.innerHTML = html;
            
            // Manejar formularios
            const forms = content.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    console.log('Form submitted');
                    
                    const formData = new FormData(this);
                    const formAction = this.getAttribute('action');
                    
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
                            document.body.removeChild(overlay);
                            loadContent(URLS.adminDashboard());
                        } else {
                            alert(data.message || 'Error al procesar la solicitud');
                        }
                    })
                    .catch(error => {
                        console.error('Form error:', error);
                        alert('Error al procesar la solicitud');
                    });
                });
            });
        })
        .catch(error => {
            console.error('Fetch error:', error);
            content.innerHTML = '<div style="text-align: center; padding: 20px; color: red;">Error al cargar el contenido</div>';
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

    // Función openPopup removida - ahora se usa el sistema centralizado


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

    // Función para actualizar el título de la página según la URL
    function updatePageTitle(url) {
        const pageTitle = document.querySelector('h1');
        if (pageTitle) {
            if (url.includes('create-user')) {
                pageTitle.textContent = 'Crear Usuario';
            } else if (url.includes('create-product')) {
                pageTitle.textContent = 'Crear Producto';
            } else if (url.includes('create-cpc')) {
                pageTitle.textContent = 'Crear CPC';
            } else if (url.includes('dashboard')) {
                pageTitle.textContent = 'Dashboard de Administrador';
            }
        }
    }

    let currentAnswersProductId = null;

    // Manejar botón "Responder Preguntas"
    function initAnswerQuestionsButtons() {
        document.querySelectorAll('.btn-answer-questions').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                openAnswerQuestionsModal(productId);
            });
        });
    }

    function openAnswerQuestionsModal(productId) {
        console.log('Opening modal for product:', productId);
        
        // Obtener el código del producto
        const productCode = document.querySelector(`[data-product-id="${productId}"]`)?.getAttribute('data-product-code') || 'Producto';
        
        // Crear overlay
        const overlay = document.createElement('div');
        overlay.className = 'modal-overlay';
        overlay.setAttribute('data-product-id', productId);
        currentAnswersProductId = productId;
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: center;
        `;

        // Crear modal
        const modal = document.createElement('div');
        modal.style.cssText = `
            background: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 90%;
            max-height: 90%;
            width: 800px;
            overflow-y: auto;
            position: relative;
        `;

        modal.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>Responder Preguntas - ${productCode}</h3>
                <button class="close-modal" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
            </div>
            <div id="questions-container">
                Cargando preguntas...
            </div>
            <div style="margin-top: 20px; text-align: right;">
                <button id="save-answers" class="btn btn-primary" style="display: none;">Publicar Respuestas</button>
                <button class="btn btn-secondary close-modal">Cerrar</button>
            </div>
        `;

        overlay.appendChild(modal);
        document.body.appendChild(overlay);

        // Cerrar modal
        const closeModal = () => {
            document.body.removeChild(overlay);
            currentAnswersProductId = null;
        };

        overlay.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', closeModal);
        });

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closeModal();
        });

        // Cargar preguntas
        loadQuestions(productId);
    }

    function loadQuestions(productId) {
        const url = generateUrl('admin_get_unanswered_questions', {producto_id: productId});
        console.log('Fetching URL:', url);
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Questions response:', data);
            
            // Buscar el contenedor dentro del modal actual
            const modal = document.querySelector('.modal-overlay');
            const container = modal ? modal.querySelector('#questions-container') : document.getElementById('questions-container');
            
            if (!container) {
                console.error('Container not found!');
                return;
            }
            
            if (data.success && data.data && data.data.questions) {
                let html = '';
                let hasUnansweredQuestions = false;
                
                data.data.questions.forEach(question => {
                    const hasAnswer = question.respuesta && question.respuesta.trim() !== '';
                    const answerDisplay = hasAnswer ? question.respuesta : '';
                    const textareaDisplay = hasAnswer ? 'none' : 'block';
                    const answerTextDisplay = hasAnswer ? 'block' : 'none';
                    
                    if (!hasAnswer) {
                        hasUnansweredQuestions = true;
                    }
                    
                    html += `
                        <div class="question-item" style="margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px;">
                            <div class="question-header" style="margin-bottom: 10px;">
                                <strong>${question.nombre_usuario}</strong>
                                <span style="color: #666; font-size: 12px; margin-left: 10px;">${new Date(question.fecha_pregunta).toLocaleString()}</span>
                            </div>
                            <div class="question-text" style="margin-bottom: 15px; line-height: 1.5;">
                                ${question.pregunta}
                            </div>
                            <div class="answer-section">
                                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Respuesta:</label>
                                <div id="answer-text-${question.id}" style="display: ${answerTextDisplay}; padding: 10px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 3px; margin-bottom: 10px; white-space: pre-wrap;">${answerDisplay}</div>
                                <textarea name="answer_${question.id}" id="answer-textarea-${question.id}" style="width: 100%; min-height: 80px; padding: 10px; border: 1px solid #ddd; border-radius: 3px; resize: vertical; display: ${textareaDisplay};" placeholder="Escriba su respuesta aquí...">${answerDisplay}</textarea>
                                ${hasAnswer ? `<button type="button" class="edit-answer-btn" data-question-id="${question.id}" style="margin-top: 5px; padding: 5px 10px; background-color: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer;">Editar respuesta</button>` : ''}
                            </div>
                        </div>
                    `;
                });
                
                container.innerHTML = html;
                
                // El botón "Publicar Respuestas" debe permanecer siempre visible
                const saveButton = modal ? modal.querySelector('#save-answers') : document.getElementById('save-answers');
                if (saveButton) {
                    saveButton.style.display = 'inline-block';
                }
                
                // Agregar event listeners para botones de editar
                document.querySelectorAll('.edit-answer-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const questionId = this.getAttribute('data-question-id');
                        const textarea = document.getElementById(`answer-textarea-${questionId}`);
                        const answerText = document.getElementById(`answer-text-${questionId}`);
                        
                        // Mostrar textarea y ocultar texto
                        textarea.style.display = 'block';
                        answerText.style.display = 'none';
                        this.style.display = 'none';
                    });
                });
            } else {
                container.innerHTML = '<p>No hay preguntas para este producto.</p>';
                const saveButton = modal ? modal.querySelector('#save-answers') : document.getElementById('save-answers');
                if (saveButton) {
                    saveButton.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<p>Error al cargar las preguntas</p>';
        });
    }

    // Guardar respuestas
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'save-answers') {
            const answers = {};
            const textareas = document.querySelectorAll('textarea[name^="answer_"]');
            const overlay = document.querySelector('.modal-overlay');
            let productId = overlay ? overlay.getAttribute('data-product-id') : '';
            if (!productId && currentAnswersProductId) {
                productId = currentAnswersProductId;
            }
            
            console.log('Textareas found:', textareas.length);
            textareas.forEach(textarea => {
                const questionId = textarea.name.replace('answer_', '');
                const answer = textarea.value.trim();
                console.log(`Question ${questionId}: "${answer}"`);
                if (answer) {
                    answers[questionId] = answer;
                }
            });

            console.log('Answers to send:', answers);
            if (Object.keys(answers).length === 0) {
                alert('Por favor, escriba al menos una respuesta');
                return;
            }

            if (!productId) {
                alert('No se pudo identificar el producto para generar el acta de PyR.');
                return;
            }

            const answerUrl = generateUrl('admin_answer_questions');
            const payload = new URLSearchParams();
            payload.append('producto_id', productId);
            payload.append('answers', JSON.stringify(answers));

            console.log('Sending POST request to:', answerUrl);
            console.log('Request body:', payload.toString());
            
            fetch(answerUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: payload.toString()
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    alert('Respuestas publicadas exitosamente');
                    // Recargar las preguntas para mostrar las respuestas actualizadas
                    const overlay = document.querySelector('.modal-overlay');
                    if (overlay) {
                        let overlayProductId = overlay.getAttribute('data-product-id');
                        if (!overlayProductId && currentAnswersProductId) {
                            overlayProductId = currentAnswersProductId;
                        }
                        console.log('Reloading questions for product:', overlayProductId);
                        if (overlayProductId) {
                            loadQuestions(overlayProductId);
                        }
                    } else {
                        console.log('Modal overlay not found');
                    }
                } else {
                    alert('Error al publicar las respuestas: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al publicar las respuestas');
            });
        }
    });

    // Inicializar botones cuando se carga el contenido
    function initListeners() {
        initAnswerQuestionsButtons();
    }

    // Inicializar listeners para páginas específicas
    if (location.pathname.includes('/manage-product/') || location.pathname.includes('/edit-product/') || location.pathname.includes('/create-product/')) {
        console.log('Initializing listeners for specific page...');
        setTimeout(() => {
            initListeners();
        }, 100);
    } else {
        // Solo cargar el dashboard inicial si no estamos en una página específica
        // y si el contenido dinámico está vacío o solo contiene el indicador de carga
    setTimeout(() => {
            // Verificar si ya hay contenido cargado
            const currentContent = dynamicContent.innerHTML.trim();
            console.log('Current content on load:', currentContent.substring(0, 100) + '...');
            if (currentContent === '' || currentContent === '<div class="loading">Cargando...</div>') {
                console.log('Loading dashboard content...');
        loadContent(URLS.adminDashboard());
            } else {
                console.log('Content already loaded, initializing listeners...');
                initListeners();
            }
    }, 100);
    }
});