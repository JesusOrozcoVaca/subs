// unified-tabs.js - Sistema simplificado sin conflictos CSS

document.addEventListener('DOMContentLoaded', function() {
    console.log('Unified tabs system initialized');
    
    // Inicializar sistema PYR global
    initializePYRSystem();
    
    // Inicializar tabs informativos
    initializeInfoTabs();
    
    // Inicializar enlaces de fase
    initializePhaseLinks();
});

function initializeInfoTabs() {
    console.log('Initializing info tabs...');
    
    const tabLinks = document.querySelectorAll('.unified-tabs .tab-links a');
    const tabs = document.querySelectorAll('.unified-tabs .tab');
    
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetTab = this.getAttribute('data-tab');
            console.log('Info tab clicked:', targetTab);
            
            // Ocultar contenido de fase si existe
            const phaseContainer = document.getElementById('phase-content');
            if (phaseContainer) {
                phaseContainer.style.display = 'none';
            }
            
            // Mostrar el contenedor de tabs unificado
            const unifiedTabs = document.querySelector('.unified-tabs');
            if (unifiedTabs) {
                unifiedTabs.style.display = 'block';
            }
            
            // Remover clase active de todos los li y tabs
            document.querySelectorAll('.unified-tabs .tab-links li').forEach(li => li.classList.remove('active'));
            tabs.forEach(t => t.classList.remove('active'));
            
            // Agregar clase active al li padre y tab actual
            this.parentElement.classList.add('active');
            const targetTabElement = document.getElementById('tab-' + targetTab);
            if (targetTabElement) {
                targetTabElement.classList.add('active');
            }
        });
    });
}

function initializePhaseLinks() {
    console.log('Initializing phase links...');
    
    // Solo ejecutar si estamos en la página del producto
    const isProductPage = window.location.pathname.includes('/view-product/') || 
                         window.location.href.includes('participant_view_product');
    
    if (!isProductPage) {
        console.log('Not on product page, skipping phase links initialization');
        console.log('Current URL:', window.location.href);
        console.log('Current pathname:', window.location.pathname);
        return;
    }
    
    const phaseLinks = document.querySelectorAll('.process-phases .phase-link');
    console.log('Found phase links:', phaseLinks.length);

    phaseLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const phase = this.getAttribute('data-phase');
            console.log('Phase link clicked:', phase);
            
            // Cargar contenido de la fase
            loadPhaseContent(phase);
        });
    });
}

function loadPhaseContent(phase) {
    console.log('Loading phase content for:', phase);
    
    // Obtener el área de contenido principal
    const mainContent = document.querySelector('.main-content');
    if (!mainContent) {
        console.error('Main content area not found');
        return;
    }
    
    // Crear o obtener el contenedor para el contenido de la fase
    let phaseContainer = document.getElementById('phase-content');
    if (!phaseContainer) {
        phaseContainer = document.createElement('div');
        phaseContainer.id = 'phase-content';
        phaseContainer.style.display = 'none';
        mainContent.appendChild(phaseContainer);
    }
    
    // Ocultar el contenedor de tabs unificado
    const unifiedTabs = document.querySelector('.unified-tabs');
    if (unifiedTabs) {
        unifiedTabs.style.display = 'none';
    }
    
    // Mostrar contenido de fase
    phaseContainer.style.display = 'block';
    phaseContainer.innerHTML = '<div class="loading">Cargando contenido...</div>';
    
    // Construir URL para la fase
    const productId = getProductIdFromURL();
    
    // Detectar si estamos en producción (URL contiene index.php o estamos en dominio de producción)
    const isProduction = window.location.pathname.includes('index.php') || 
                        window.location.hostname.includes('hjconsulting.com.ec');
    
    let url;
    if (isProduction) {
        // En producción: usar query parameters
        url = `/subs/index.php?action=participant_phase&phase=${phase}&producto_id=${productId}`;
    } else {
        // En local: usar URLs amigables
        url = `/subs/participant/phase/${phase}?producto_id=${productId}`;
    }
    
    console.log('=== PHASE LOADING DEBUG ===');
    console.log('Current URL:', window.location.href);
    console.log('Current pathname:', window.location.pathname);
    console.log('Is production detected:', isProduction);
    console.log('Phase:', phase);
    console.log('Product ID:', productId);
    console.log('Generated URL:', url);
    console.log('================================');
    
    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            console.log('Content loaded successfully for:', phase);
            phaseContainer.innerHTML = data.content;
            
            // Inicializar funcionalidad específica del contenido
            initializeContentSpecificFunctionality(phase, phaseContainer);
        } else {
            throw new Error(data.message || 'Error al cargar el contenido');
        }
    })
    .catch(error => {
        console.error('Error loading content for', phase, ':', error);
        phaseContainer.innerHTML = `
            <div class="error">
                <h3>Error al cargar el contenido</h3>
                <p>${error.message}</p>
                <button onclick="loadPhaseContent('${phase}')">Reintentar</button>
            </div>
        `;
    });
}

function initializeContentSpecificFunctionality(phase, container) {
    console.log('Initializing specific functionality for:', phase);
    
    switch(phase) {
        case 'pyr':
            initializePYRContent(container);
            break;
        case 'eof':
            initializeEOFContent(container);
            break;
        // Agregar más casos según sea necesario
    }
}

function getProductIdFromURL() {
    // Primero intentar extraer de parámetros de URL (para producción)
    const urlParams = new URLSearchParams(window.location.search);
    const productIdFromParams = urlParams.get('id');
    
    if (productIdFromParams) {
        return productIdFromParams;
    }
    
    // Si no hay parámetros, intentar extraer del pathname (para local)
    const pathParts = window.location.pathname.split('/');
    const productIdFromPath = pathParts[pathParts.length - 1];
    
    // Verificar que sea un número (ID válido)
    if (productIdFromPath && !isNaN(productIdFromPath)) {
        return productIdFromPath;
    }
    
    return '1'; // Fallback
}

// Sistema PYR específico
function initializePYRSystem() {
    console.log('PYR System initialized globally');
    
    // Hacer funciones PYR disponibles globalmente
    window.loadPreguntas = loadPreguntas;
    window.submitPregunta = submitPregunta;
}

function initializePYRContent(container) {
    console.log('Initializing PYR content in container');
    
    const productId = getProductIdFromURL();
    console.log('Product ID for PYR:', productId);
    
    // Inicializar formulario de pregunta
    const form = container.querySelector('#pregunta-form');
    const textarea = form ? form.querySelector('textarea') : null;
    const charCount = container.querySelector('#char-count');
    
    if (form && textarea) {
        console.log('Initializing pregunta form');
        
        textarea.oninput = function() {
            const count = this.value.length;
            if (charCount) {
                charCount.textContent = count;
                charCount.style.color = count > 450 ? '#ff6b6b' : '#666';
            }
        };
        
        form.onsubmit = function(e) {
            e.preventDefault();
            const pregunta = textarea.value.trim();
            
            if (pregunta.length === 0) {
                alert('Por favor, escriba una pregunta');
                return;
            }
            
            if (pregunta.length > 500) {
                alert('La pregunta no puede exceder 500 caracteres');
                return;
            }
            
            submitPregunta(pregunta);
            textarea.value = '';
            if (charCount) {
                charCount.textContent = '0';
                charCount.style.color = '#666';
            }
        };
    }
    
    // Cargar preguntas iniciales
    loadPreguntas(1);
}

function loadPreguntas(page) {
    console.log('Loading preguntas, page:', page);
    const productId = getProductIdFromURL();
    
    // Detectar si estamos en producción
    const isProduction = window.location.pathname.includes('index.php') || 
                        window.location.hostname.includes('hjconsulting.com.ec');
    
    let url;
    if (isProduction) {
        url = `/subs/index.php?action=participant_get_questions&producto_id=${productId}&page=${page}&limit=5`;
    } else {
        url = `/subs/participant/get-questions?producto_id=${productId}&page=${page}&limit=5`;
    }
    
    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        console.log('Load Response status:', response.status);
        return response.text();
    })
    .then(text => {
        console.log('Load Response text:', text);
        try {
            const data = JSON.parse(text);
            if (data.success) {
                console.log('Questions loaded successfully:', data.data);
                displayPreguntas(data.data.questions);
                displayPagination(data.data.pagination);
            } else {
                console.error('Load questions failed:', data.message);
            }
        } catch (e) {
            console.error('Load Response parse error:', e);
        }
    })
    .catch(error => {
        console.error('Load questions error:', error);
    });
}

function submitPregunta(pregunta) {
    console.log('Submitting pregunta:', pregunta);
    const productId = getProductIdFromURL();
    
    // Detectar si estamos en producción
    const isProduction = window.location.pathname.includes('index.php') || 
                        window.location.hostname.includes('hjconsulting.com.ec');
    
    let url;
    if (isProduction) {
        url = '/subs/index.php?action=participant_submit_question';
    } else {
        url = '/subs/participant/submit-question';
    }
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `producto_id=${productId}&pregunta=${encodeURIComponent(pregunta)}`
    })
    .then(response => {
        console.log('Submit Response status:', response.status);
        return response.text();
    })
    .then(text => {
        console.log('Submit Response text:', text);
        try {
            const data = JSON.parse(text);
            if (data.success) {
                console.log('Pregunta enviada exitosamente');
                alert('Pregunta enviada exitosamente');
                loadPreguntas(1);
            } else {
                console.error('Error al enviar pregunta:', data.message);
                alert('Error al enviar pregunta: ' + data.message);
            }
        } catch (e) {
            console.error('Submit Response parse error:', e);
            alert('Error al procesar respuesta del servidor');
        }
    })
    .catch(error => {
        console.error('Submit error:', error);
        alert('Error de conexión al enviar pregunta');
    });
}

function displayPreguntas(questions) {
    console.log('Displaying questions:', questions);
    const container = document.getElementById('preguntas-container');
    if (!container) {
        console.log('Questions container not found');
        return;
    }
    
    if (questions.length === 0) {
        container.innerHTML = '<p>No hay preguntas aún.</p>';
        return;
    }
    
    let html = '';
    questions.forEach(question => {
        html += `
            <div class="pregunta-item">
                <div class="pregunta-header">
                    <strong>${question.nombre_usuario}</strong>
                    <span class="fecha">${question.fecha_pregunta}</span>
                </div>
                <div class="pregunta-texto">${question.pregunta}</div>
                ${question.respuesta ? `
                    <div class="respuesta">
                        <strong>Respuesta:</strong> ${question.respuesta}
                    </div>
                ` : '<div class="sin-respuesta">Sin respuesta aún</div>'}
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function displayPagination(pagination) {
    console.log('Displaying pagination:', pagination);
    const container = document.getElementById('pagination-container');
    if (!container) {
        console.log('Pagination container not found');
        return;
    }
    
    if (pagination.totalPages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<div class="pagination">';
    
    if (pagination.currentPage > 1) {
        html += `<button onclick="loadPreguntas(${pagination.currentPage - 1})">Anterior</button>`;
    }
    
    for (let i = 1; i <= pagination.totalPages; i++) {
        if (i === pagination.currentPage) {
            html += `<button class="active">${i}</button>`;
        } else {
            html += `<button onclick="loadPreguntas(${i})">${i}</button>`;
        }
    }
    
    if (pagination.currentPage < pagination.totalPages) {
        html += `<button onclick="loadPreguntas(${pagination.currentPage + 1})">Siguiente</button>`;
    }
    
    html += '</div>';
    container.innerHTML = html;
}

function initializeEOFContent(container) {
    console.log('Initializing EOF content in container');
    // Implementar funcionalidad EOF aquí
}