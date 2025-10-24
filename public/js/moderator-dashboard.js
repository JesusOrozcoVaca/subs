// Usar las funciones de url-helper.js que ya están disponibles

document.addEventListener('DOMContentLoaded', function() {
    const dynamicContent = document.getElementById('dynamic-content');

    console.log('=== MODERATOR DASHBOARD JS LOADED ===');
    console.log('Sistema detectado:', isNewSystem() ? 'Nuevo (Query Parameters)' : 'Legacy (URLs Amigables)');
    console.log('Base URL:', getBaseUrl());
    console.log('Dynamic content element:', dynamicContent);

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
            console.log('=== CONTENT LOADED, INITIALIZING LISTENERS ===');
            console.log('Dynamic content after load:', dynamicContent);
            console.log('Forms in dynamic content:', dynamicContent.querySelectorAll('form').length);
            
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
        // Solo cargar contenido si no estamos en una página de gestión de producto
        if (!location.pathname.includes('/manage-product/')) {
            loadContent(location.pathname);
        }
    });

    function initListeners() {
        console.log('=== INITIALIZING ALL LISTENERS ===');
        console.log('Dynamic content:', dynamicContent);
        console.log('Dynamic content HTML:', dynamicContent.innerHTML.substring(0, 500) + '...');
        
        setupMenuListeners(); // Reconfigurar listeners del menú
        initFormListeners();
        initEditButtons();
        initDeleteButtons();
        initAnswerQuestionsButtons();
        
        console.log('=== ALL LISTENERS INITIALIZED ===');
    }

    function initFormListeners() {
        // Buscar todos los formularios en el contenido dinámico
        const forms = dynamicContent.querySelectorAll('form');
        console.log('=== INIT FORM LISTENERS ===');
        console.log('Dynamic content:', dynamicContent);
        console.log('Forms found:', forms.length);
        
        if (forms.length === 0) {
            console.log('WARNING: No forms found in dynamic content');
            console.log('Dynamic content HTML:', dynamicContent.innerHTML);
            return;
        }
        
        forms.forEach((form, index) => {
            console.log(`=== FORM ${index + 1} ===`);
            console.log('Form element:', form);
            console.log('Form action:', form.getAttribute('action'));
            console.log('Form method:', form.getAttribute('method'));
            console.log('Form HTML:', form.outerHTML);
            
            // Remover listeners existentes
            const newForm = form.cloneNode(true);
            form.parentNode.replaceChild(newForm, form);
            
            // Agregar nuevo listener
            newForm.addEventListener('submit', function(e) {
                console.log('=== FORM SUBMIT EVENT TRIGGERED ===');
                console.log('Form:', this);
                console.log('Event:', e);
                e.preventDefault();
                e.stopPropagation();
                
                const formData = new FormData(this);
                const formAction = this.getAttribute('action');
                console.log('Form action URL:', formAction);
                console.log('Form data:', Array.from(formData.entries()));
                
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
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        alert(data.message);
                        // Redirigir al dashboard del moderador después del cambio exitoso
                        console.log('Redirecting to moderator dashboard...');
                        console.log('Dashboard URL:', URLS.moderatorDashboard());
                        loadContent(URLS.moderatorDashboard());
                    } else {
                        alert(data.message || 'Error al procesar la solicitud');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud: ' + error.message);
                });
            });
        });
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
                            // No recargar automáticamente, mantener el dashboard actual
                            // loadContent(URLS.moderatorManageCpcs());
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

    // Solo cargar el dashboard inicial si no estamos en una página específica
    // y si el contenido dinámico está vacío o solo contiene el indicador de carga
    if (!location.pathname.includes('/manage-product/') && !location.pathname.includes('/manage-cpcs')) {
        setTimeout(() => {
            // Verificar si ya hay contenido cargado
            const currentContent = dynamicContent.innerHTML.trim();
            if (currentContent === '' || currentContent === '<div class="loading">Cargando...</div>') {
                loadContent(URLS.moderatorDashboard());
            }
        }, 100);
    }

    // Funcionalidad para responder preguntas (copiada del admin-dashboard.js)
    function initAnswerQuestionsButtons() {
        console.log('=== INITIALIZING ANSWER QUESTIONS BUTTONS ===');
        
        const buttons = document.querySelectorAll('.btn-answer-questions');
        console.log('Found answer questions buttons:', buttons.length);
        
        buttons.forEach((button, index) => {
            console.log(`Button ${index + 1}:`, button);
            console.log(`Button ${index + 1} data-product-id:`, button.getAttribute('data-product-id'));
            console.log(`Button ${index + 1} data-product-code:`, button.getAttribute('data-product-code'));
            
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.getAttribute('data-product-id');
                const productCode = this.getAttribute('data-product-code');
                console.log('Answer questions clicked for product:', productId, productCode);
                openAnswerQuestionsModal(productId, productCode);
            });
        });
        
        console.log('Answer questions buttons initialized');
    }

    function openAnswerQuestionsModal(productId, productCode) {
        console.log('Opening answer questions modal for product:', productId);
        
        // Crear overlay
        const overlay = document.createElement('div');
        overlay.className = 'modal-overlay';
        overlay.setAttribute('data-product-id', productId);
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
        const url = generateUrl('moderator_get_unanswered_questions', {producto_id: productId});
        console.log('Fetching URL:', url);
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('questions-container');
            console.log('Questions response:', data);
            
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
                document.getElementById('save-answers').style.display = 'inline-block';
                
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
                console.log('No questions found or error:', data);
                container.innerHTML = '<p>No hay preguntas para este producto.</p>';
                document.getElementById('save-answers').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('questions-container').innerHTML = '<p>Error al cargar las preguntas</p>';
        });
    }

    // Event delegation para botones de responder preguntas
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('btn-answer-questions')) {
            e.preventDefault();
            const productId = e.target.getAttribute('data-product-id');
            const productCode = e.target.getAttribute('data-product-code');
            console.log('Answer questions clicked via delegation for product:', productId, productCode);
            openAnswerQuestionsModal(productId, productCode);
        }
    });

    // Event listener para el botón de guardar respuestas
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'save-answers') {
            const answers = {};
            const textareas = document.querySelectorAll('textarea[name^="answer_"]');
            
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

            const answerUrl = generateUrl('moderator_answer_questions');
            console.log('Sending POST request to:', answerUrl);
            console.log('Request body:', `answers=${encodeURIComponent(JSON.stringify(answers))}`);
            
            fetch(answerUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `answers=${encodeURIComponent(JSON.stringify(answers))}`
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
                        const productId = overlay.getAttribute('data-product-id');
                        console.log('Reloading questions for product:', productId);
                        if (productId) {
                            loadQuestions(productId);
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

    console.log('=== MODERATOR DASHBOARD JS FULLY LOADED ===');
    console.log('Event delegation set up for answer questions buttons');
});