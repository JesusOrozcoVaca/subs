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
    console.log('=== INITIALIZING EOF CONTENT ===');
    console.log('Container:', container);
    console.log('Container HTML:', container.innerHTML.substring(0, 200) + '...');
    
    // SOLUCIÓN PRINCIPAL: Inicializar directamente desde aquí
    console.log('=== INITIALIZING EOF DIRECTLY (MAIN SOLUTION) ===');
    initializeEOFDirectly(container);
    
    // NO ejecutar el script de eof.php para evitar duplicación de funcionalidad
    console.log('Skipping eof.php script execution to prevent conflicts');
}

function initializeEOFDirectly(container) {
    console.log('=== INITIALIZING EOF DIRECTLY ===');
    
    const form = container.querySelector('#oferta-form');
    const fileInput = container.querySelector('#file-input');
    const fileCount = container.querySelector('#file-count');
    const fileSize = container.querySelector('#file-size');
    const uploadBtn = container.querySelector('#upload-btn');
    const processBtn = container.querySelector('#process-btn');
    const listaOfertas = container.querySelector('#lista-ofertas');
    
    console.log('Form element:', form);
    console.log('File input element:', fileInput);
    console.log('Upload button element:', uploadBtn);
    
    if (!form || !fileInput || !uploadBtn) {
        console.error('Required elements not found!');
        return;
    }
    
    console.log('All elements found, setting up event listeners...');
    
    // Variables de estado
    let uploadedFiles = [];
    let isProcessed = false;
    
    // Event listener para cambio de archivos
    fileInput.addEventListener('change', function() {
        console.log('File input changed');
        const files = Array.from(this.files);
        const maxFiles = 5;
        const maxSize = 512 * 1024; // 512KB
        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        
        console.log('Files selected:', files.length);
        
        // Validar cantidad
        if (files.length > maxFiles) {
            alert(`Solo se permiten máximo ${maxFiles} archivos`);
            this.value = '';
            return;
        }
        
        // Validar tipos y tamaños
        let totalSize = 0;
        for (let file of files) {
            if (!allowedTypes.includes(file.type)) {
                alert(`El archivo "${file.name}" no es un tipo permitido (PDF, JPG, PNG)`);
                this.value = '';
                return;
            }
            
            if (file.size > maxSize) {
                alert(`El archivo "${file.name}" excede el tamaño máximo de 512KB`);
                this.value = '';
                return;
            }
            
            totalSize += file.size;
        }
        
        // Actualizar información
        if (fileCount) fileCount.textContent = `${files.length} archivo(s) seleccionado(s)`;
        if (fileSize) fileSize.textContent = `Tamaño total: ${(totalSize / 1024).toFixed(1)} KB`;
        
        // Mostrar/ocultar botones
        if (files.length > 0) {
            uploadBtn.style.display = 'inline-block';
        } else {
            uploadBtn.style.display = 'none';
        }
    });
    
    // Event listener para botón de subir archivos
    uploadBtn.addEventListener('click', function(e) {
        console.log('=== UPLOAD BUTTON CLICKED ===');
        e.preventDefault();
        
        // Prevenir clicks múltiples
        if (uploadBtn.disabled) {
            console.log('Upload button is disabled, ignoring click');
            return;
        }
        
        if (isProcessed) {
            alert('Ya se ha procesado la entrega de ofertas');
            return;
        }
        
        const files = Array.from(fileInput.files);
        if (files.length === 0) {
            alert('Por favor, seleccione al menos un archivo');
            return;
        }
        
        // Deshabilitar botón durante la subida
        uploadBtn.disabled = true;
        uploadBtn.textContent = 'Subiendo...';
        
        console.log('Starting file upload process');
        uploadFilesDirectly(files);
    });
    
    // Event listener para botón de procesar
    if (processBtn) {
        processBtn.addEventListener('click', function() {
            console.log('=== PROCESS BUTTON CLICKED ===');
            
            // Verificar si hay archivos no procesados en la lista
            const ofertaItems = document.querySelectorAll('.oferta-item');
            let hasUnprocessedFiles = false;
            
            ofertaItems.forEach(item => {
                const procesadoBadge = item.querySelector('.procesado-badge');
                if (!procesadoBadge) {
                    hasUnprocessedFiles = true;
                }
            });
            
            if (!hasUnprocessedFiles) {
                alert('No hay archivos para procesar');
                return;
            }
            
            // Detectar si estamos en producción
            const isProduction = window.location.pathname.includes('index.php') || 
                                window.location.hostname.includes('hjconsulting.com.ec');
            
            const processUrl = isProduction ? 
                '/subs/index.php?action=participant_process_offer' : 
                '/subs/participant/process-offer';
            
            console.log('Processing offer at:', processUrl);
            
            fetch(processUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `producto_id=${getProductIdFromURL()}`
            })
            .then(response => response.json())
            .then(data => {
                console.log('Process response:', data);
                if (data.success) {
                    isProcessed = true;
                    // Solo después de procesar se ocultan ambos botones
                    processBtn.style.display = 'none';
                    uploadBtn.style.display = 'none';
                    fileInput.disabled = true;
                    alert('Entrega de ofertas procesada exitosamente');
                    loadOfertasDirectly();
                } else {
                    alert('Error al procesar: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Process error:', error);
                alert('Error al procesar la entrega');
            });
        });
    }
    
    console.log('EOF initialized successfully!');
}

// Función para actualizar la visibilidad del botón de subir archivos
function updateUploadButtonVisibility() {
    console.log('=== UPDATE UPLOAD BUTTON VISIBILITY ===');
    
    const uploadBtn = document.querySelector('#upload-btn');
    const processBtn = document.querySelector('#process-btn');
    const fileInput = document.querySelector('#file-input');
    
    if (!uploadBtn || !processBtn || !fileInput) {
        console.log('Required elements not found for button visibility update');
        return;
    }
    
    // Verificar si hay archivos no procesados
    const ofertaItems = document.querySelectorAll('.oferta-item');
    let hasUnprocessedFiles = false;
    
    ofertaItems.forEach(item => {
        const procesadoBadge = item.querySelector('.procesado-badge');
        if (!procesadoBadge) {
            hasUnprocessedFiles = true;
        }
    });
    
    console.log('Has unprocessed files:', hasUnprocessedFiles);
    console.log('File input has files:', fileInput.files.length > 0);
    
    // Mostrar botón de subir archivos si:
    // 1. Hay archivos no procesados O
    // 2. Hay archivos seleccionados en el input
    const hasSelectedFiles = fileInput.files.length > 0;
    
    if (hasUnprocessedFiles || hasSelectedFiles) {
        uploadBtn.style.display = 'inline-block';
        console.log('Showing upload button - hasUnprocessedFiles:', hasUnprocessedFiles, 'hasSelectedFiles:', hasSelectedFiles);
    } else {
        uploadBtn.style.display = 'none';
        console.log('Hiding upload button - no unprocessed files and no selected files');
    }
    
    // Mostrar botón de procesar si hay archivos no procesados
    if (hasUnprocessedFiles) {
        processBtn.style.display = 'inline-block';
        console.log('Showing process button');
    } else {
        processBtn.style.display = 'none';
        console.log('Hiding process button');
    }
}

function uploadFilesDirectly(files) {
    console.log('=== UPLOAD FILES DIRECTLY ===');
    let uploadCount = 0;
    const totalFiles = files.length;
    
    files.forEach((file, index) => {
        const formData = new FormData();
        formData.append('producto_id', getProductIdFromURL());
        formData.append('documento_oferta', file);
        
        // Detectar si estamos en producción
        const isProduction = window.location.pathname.includes('index.php') || 
                            window.location.hostname.includes('hjconsulting.com.ec');
        
        const uploadUrl = isProduction ? 
            '/subs/index.php?action=participant_upload_offer' : 
            '/subs/participant/upload-offer';
        
        console.log('Uploading file:', file.name, 'to:', uploadUrl);
        
        fetch(uploadUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            uploadCount++;
            console.log('Upload response:', data);
            
            if (data.success) {
                console.log('File uploaded successfully:', file.name);
            } else {
                alert(`Error al subir "${file.name}": ${data.message}`);
            }
            
            // Si es el último archivo
            if (uploadCount === totalFiles) {
                const processBtn = document.querySelector('#process-btn');
                const uploadBtn = document.querySelector('#upload-btn');
                const fileInput = document.querySelector('#file-input');
                const fileCount = document.querySelector('#file-count');
                const fileSize = document.querySelector('#file-size');
                
                if (processBtn) processBtn.style.display = 'inline-block';
                
                // Re-habilitar botón de subir archivos
                if (uploadBtn) {
                    uploadBtn.disabled = false;
                    uploadBtn.textContent = 'Subir Archivos';
                    console.log('Upload button re-enabled after upload completion');
                }
                
                // Limpiar el input de archivos después de subir exitosamente
                if (fileInput) {
                    fileInput.value = '';
                    console.log('File input cleared after successful upload');
                }
                
                // Actualizar la información de archivos
                if (fileCount) fileCount.textContent = '0 archivo(s) seleccionado(s)';
                if (fileSize) fileSize.textContent = 'Tamaño total: 0 KB';
                
                // Actualizar visibilidad de botones
                updateUploadButtonVisibility();
                
                loadOfertasDirectly();
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            alert(`Error al subir "${file.name}"`);
            
            // Re-habilitar botón en caso de error
            const uploadBtn = document.querySelector('#upload-btn');
            if (uploadBtn) {
                uploadBtn.disabled = false;
                uploadBtn.textContent = 'Subir Archivos';
                console.log('Upload button re-enabled after error');
            }
        });
    });
}

function loadOfertasDirectly() {
    console.log('=== LOAD OFFERS DIRECTLY ===');
    // Detectar si estamos en producción
    const isProduction = window.location.pathname.includes('index.php') || 
                        window.location.hostname.includes('hjconsulting.com.ec');
    
    const getOffersUrl = isProduction ? 
        `/subs/index.php?action=participant_get_offers&producto_id=${getProductIdFromURL()}` : 
        `/subs/participant/get-offers?producto_id=${getProductIdFromURL()}`;
    
    console.log('Loading offers from:', getOffersUrl);
    
    fetch(getOffersUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Offers response:', data);
        console.log('Data structure:', JSON.stringify(data, null, 2));
        
        if (data.success) {
            // La estructura correcta es data.data.ofertas
            const ofertas = data.data && data.data.ofertas ? data.data.ofertas : [];
            console.log('Ofertas to display:', ofertas);
            displayOfertasDirectly(ofertas);
        } else {
            const listaOfertas = document.querySelector('#lista-ofertas');
            if (listaOfertas) {
                listaOfertas.innerHTML = '<p>Error al cargar las ofertas: ' + (data.message || 'Error desconocido') + '</p>';
            }
        }
    })
    .catch(error => {
        console.error('Error loading offers:', error);
        const listaOfertas = document.querySelector('#lista-ofertas');
        if (listaOfertas) {
            listaOfertas.innerHTML = '<p>Error al cargar las ofertas</p>';
        }
    });
}

function displayOfertasDirectly(ofertas) {
    console.log('=== DISPLAY OFFERS DIRECTLY ===');
    console.log('Ofertas received:', ofertas);
    console.log('Ofertas type:', typeof ofertas);
    console.log('Ofertas is array:', Array.isArray(ofertas));
    
    const listaOfertas = document.querySelector('#lista-ofertas');
    if (!listaOfertas) {
        console.error('Lista ofertas element not found');
        return;
    }
    
    // Verificar que ofertas sea un array válido
    if (!ofertas || !Array.isArray(ofertas)) {
        console.error('Ofertas is not a valid array:', ofertas);
        listaOfertas.innerHTML = '<p>Error: No se pudieron cargar las ofertas</p>';
        return;
    }
    
    if (ofertas.length === 0) {
        listaOfertas.innerHTML = '<p>No hay archivos subidos aún</p>';
        return;
    }
    
    let html = '<div class="ofertas-grid">';
    ofertas.forEach(oferta => {
        const isProcessed = oferta.procesado == 1 || oferta.procesado === true;
        html += `
            <div class="oferta-item">
                <div class="oferta-info">
                    <strong>${oferta.nombre_archivo}</strong>
                    <span class="oferta-fecha">${new Date(oferta.fecha_carga).toLocaleString()}</span>
                    ${isProcessed ? '<span class="procesado-badge">Procesado</span>' : ''}
                </div>
                <div class="oferta-actions">
                    <a href="${oferta.ruta_archivo}" target="_blank" class="btn btn-small">Ver</a>
                    ${!isProcessed ? `<button onclick="deleteOfertaDirectly(${oferta.id})" class="btn btn-small btn-danger">Eliminar</button>` : ''}
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    listaOfertas.innerHTML = html;
    
    // Actualizar visibilidad de botones después de mostrar ofertas
    updateUploadButtonVisibility();
}

window.deleteOfertaDirectly = function(fileId) {
    console.log('=== DELETE OFFER DIRECTLY ===', fileId);
    if (confirm('¿Está seguro de que desea eliminar este archivo?')) {
        // Detectar si estamos en producción
        const isProduction = window.location.pathname.includes('index.php') || 
                            window.location.hostname.includes('hjconsulting.com.ec');
        
        const deleteUrl = isProduction ? 
            '/subs/index.php?action=participant_delete_offer' : 
            '/subs/participant/delete-offer';
        
        fetch(deleteUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `file_id=${fileId}`
        })
        .then(response => response.json())
        .then(data => {
            console.log('Delete response:', data);
            if (data.success) {
                loadOfertasDirectly();
                // Después de eliminar, verificar si debemos mostrar el botón de subir archivos
                updateUploadButtonVisibility();
            } else {
                alert('Error al eliminar: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            alert('Error al eliminar el archivo');
        });
    }
};