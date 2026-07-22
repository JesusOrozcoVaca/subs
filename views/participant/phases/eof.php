<?php
$blockForNoOffer = isset($blockWithoutOffer) ? (bool)$blockWithoutOffer : false;
if ($blockForNoOffer) {
    echo '<div class="read-only-message"><p>Usted no cargo oferta para este proceso por lo tanto, no puede participar en el mismo.</p></div>';
    return;
}
?>
<h2>Entrega de Ofertas</h2>
<div id="entrega-ofertas-container">
    <form id="oferta-form" enctype="multipart/form-data" class="oferta-form" action="#" method="POST">
        <div class="file-upload-section">
            <input type="file" name="documento_oferta" id="file-input" accept=".pdf,.jpg,.png" multiple style="display: none;">
            <label for="file-input" class="file-label">
                <span class="file-icon">📁</span>
                <span class="file-text">Seleccionar archivos (PDF, JPG, PNG - Máx 512KB cada uno)</span>
            </label>
            <div class="file-info">
                <span id="file-count">0 archivos seleccionados</span>
                <span id="file-size">Tamaño total: 0 KB</span>
            </div>
        </div>
        <div class="form-actions">
            <button type="button" class="btn btn-primary" id="upload-btn">Subir Archivos</button>
            <button type="button" class="btn btn-success" id="process-btn" class="hidden">Procesar</button>
        </div>
    </form>
    
    <div id="offer-summary" class="offer-summary hidden">
        <!-- Resumen de oferta procesada -->
    </div>

    <div id="lista-ofertas" class="ofertas-lista">
        <!-- Aquí se mostrarán las ofertas subidas -->
    </div>
</div>

<script>
// SOLUCIÓN RADICAL: Event Delegation + Polling
window.eofInlineInitialized = true;

window.eofInlineConfig = window.eofInlineConfig || {};
window.eofInlineConfig.presupuestoReferencial = <?php echo json_encode((float)$product['presupuesto_referencial']); ?>;
window.eofInlineConfig.plazoEntregaProducto = <?php echo json_encode($product['plazo_entrega'] ?? ''); ?>;
window.eofInlineConfig.vigenciaOfertaProducto = <?php echo json_encode($product['vigencia_oferta'] ?? ''); ?>;
console.log('=== EOF SCRIPT STARTING (NEW APPROACH) ===');

// Variables globales para el estado
if (!window.eofState) {
    window.eofState = {
        uploadedFiles: [],
        isProcessed: false,
        initialized: false,
        offerSummary: null,
        loading: false
    };
} else {
    window.eofState.uploadedFiles = [];
    window.eofState.isProcessed = false;
    window.eofState.initialized = false;
    window.eofState.offerSummary = null;
    window.eofState.loading = false;
}

function escapeHtml(string) {
    if (typeof string !== 'string') {
        return '';
    }
    return string
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function renderOfferSummary(summary) {
    const summaryContainer = document.getElementById('offer-summary');
    if (!summaryContainer) {
        return;
    }

    if (summary) {
        summaryContainer.classList.remove('hidden');
        
        // Detectar si estamos en producción
        const downloadUrl = generateUrl('participant_download_offer_pdf', { producto_id: <?php echo (int)$product['id']; ?> });
        
        summaryContainer.innerHTML = `
            <h3>Resumen de la oferta procesada</h3>
            <ul>
                <li><strong>Tiempo de entrega:</strong> ${escapeHtml(summary.tiempo_entrega || '')}</li>
                <li><strong>Plazo de la oferta:</strong> ${escapeHtml(summary.plazo_oferta || '')}</li>
                <li><strong>Oferta inicial:</strong> $${parseFloat(summary.oferta_inicial_user || 0).toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</li>
                <li><strong>Descripción:</strong> ${escapeHtml(summary.descripcion || '')}</li>
                <li><strong>Fecha de registro:</strong> ${summary.created_at ? new Date(summary.created_at).toLocaleString() : 'N/D'}</li>
            </ul>
            <div style="margin-top: 15px;">
                <a href="${downloadUrl}" id="download-offer-pdf-btn" class="btn btn-primary" style="text-decoration: none; display: inline-block;" target="_blank">
                    Descargar PDF de Oferta
                </a>
            </div>
        `;
    } else {
        summaryContainer.classList.add('hidden');
        summaryContainer.innerHTML = '';
    }
}

function updateProcessedUI(processed, summary) {
    window.eofState.isProcessed = !!processed;
    window.eofState.offerSummary = summary || null;

    const processBtn = document.getElementById('process-btn');
    const uploadBtn = document.getElementById('upload-btn');
    const fileInput = document.getElementById('file-input');
    const fileUploadSection = document.querySelector('.file-upload-section');

    if (window.eofState.loading) {
        if (processBtn) processBtn.style.display = 'none';
        if (uploadBtn) uploadBtn.style.display = 'none';
        if (fileInput) fileInput.disabled = true;
        if (fileUploadSection) fileUploadSection.style.display = 'none';
        renderOfferSummary(summary);
        return;
    }

    if (processed) {
        if (processBtn) processBtn.style.display = 'none';
        if (uploadBtn) uploadBtn.style.display = 'none';
        if (fileInput) fileInput.disabled = true;
        if (fileUploadSection) fileUploadSection.style.display = 'none';
    } else {
        if (processBtn) {
            processBtn.style.display = window.eofState.uploadedFiles.length > 0 ? 'inline-block' : 'none';
        }
        if (uploadBtn) {
            uploadBtn.style.display = window.eofState.uploadedFiles.length > 0 ? 'none' : 'inline-block';
        }
        if (fileInput) fileInput.disabled = false;
        if (fileUploadSection) fileUploadSection.style.display = 'block';
    }

    renderOfferSummary(summary);
}

var presupuestoReferencial = window.eofInlineConfig.presupuestoReferencial;
var plazoEntregaProducto = window.eofInlineConfig.plazoEntregaProducto;
var vigenciaOfertaProducto = window.eofInlineConfig.vigenciaOfertaProducto;
var maxOfertaInicial = Math.max(0, (presupuestoReferencial * 100 - 1) / 100);

function toCents(value) {
    const normalized = String(value).replace(',', '.').trim();
    const numberValue = Number(normalized);
    if (Number.isNaN(numberValue)) {
        return null;
    }
    return Math.round(numberValue * 100);
}

function normalizeNumeric(value) {
    const normalized = String(value).replace(',', '.').trim();
    if (normalized === '' || Number.isNaN(Number(normalized))) {
        return null;
    }
    const cleaned = normalized.includes('.') ? normalized.replace(/\.?0+$/, '') : normalized;
    return cleaned === '' ? '0' : cleaned;
}

function openOfferDetailsModal(existingData = null, onSubmit = null) {
    const overlay = document.createElement('div');
    overlay.className = 'offer-modal-overlay';

    const initialTiempo = existingData && existingData.tiempo_entrega ? existingData.tiempo_entrega : '';
    const initialPlazo = existingData && existingData.plazo_oferta ? existingData.plazo_oferta : '';
    const initialDescripcion = existingData && existingData.descripcion ? existingData.descripcion : '';
    const initialOfertaInicial = existingData && existingData.oferta_inicial_user ? existingData.oferta_inicial_user : '';

    overlay.innerHTML = `
        <div class="offer-modal">
            <h3>Confirmar entrega de oferta</h3>
            <p>Ingrese la información solicitada. Una vez que procese la oferta no podrá modificar estos datos ni los archivos.</p>
            <label for="modal-tiempo-entrega">Tiempo de entrega</label>
            <input type="text" id="modal-tiempo-entrega" maxlength="100" value="${escapeHtml(initialTiempo)}" />
            <label for="modal-plazo-oferta">Plazo de la oferta</label>
            <input type="text" id="modal-plazo-oferta" maxlength="100" value="${escapeHtml(initialPlazo)}" />
            <label for="modal-oferta-inicial">Oferta inicial</label>
            <input type="number" id="modal-oferta-inicial" step="0.01" min="0" max="${maxOfertaInicial}" value="${escapeHtml(initialOfertaInicial)}" />
            <label for="modal-descripcion">Descripción</label>
            <textarea id="modal-descripcion" maxlength="1000">${escapeHtml(initialDescripcion)}</textarea>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary modal-cancel">Cancelar</button>
                <button type="button" class="btn btn-success modal-confirm">Confirmar</button>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    const tiempoField = overlay.querySelector('#modal-tiempo-entrega');
    if (tiempoField) {
        tiempoField.focus();
    }

    const cleanup = () => {
        if (document.body.contains(overlay)) {
            document.body.removeChild(overlay);
        }
    };

    overlay.querySelector('.modal-cancel').addEventListener('click', () => {
        cleanup();
    });

    overlay.querySelector('.modal-confirm').addEventListener('click', () => {
        const tiempoEntrega = overlay.querySelector('#modal-tiempo-entrega').value.trim();
        const plazoOferta = overlay.querySelector('#modal-plazo-oferta').value.trim();
        const ofertaInicial = overlay.querySelector('#modal-oferta-inicial').value.trim();
        const descripcion = overlay.querySelector('#modal-descripcion').value.trim();

        if (!tiempoEntrega || !plazoOferta || !ofertaInicial || !descripcion) {
            alert('Todos los campos son obligatorios.');
            return;
        }

        if (tiempoEntrega.length > 100 || plazoOferta.length > 100) {
            alert('Los campos de tiempo de entrega y plazo de la oferta no pueden exceder 100 caracteres.');
            return;
        }

        const plazoEntregaNorm = normalizeNumeric(tiempoEntrega);
        const vigenciaOfertaNorm = normalizeNumeric(plazoOferta);
        const plazoEntregaProductoNorm = normalizeNumeric(plazoEntregaProducto);
        const vigenciaOfertaProductoNorm = normalizeNumeric(vigenciaOfertaProducto);
        if (!plazoEntregaNorm || !vigenciaOfertaNorm) {
            alert('Los valores de tiempo de entrega y plazo de la oferta deben ser numéricos.');
            return;
        }
        if (plazoEntregaNorm !== plazoEntregaProductoNorm || vigenciaOfertaNorm !== vigenciaOfertaProductoNorm) {
            alert('Los valores de tiempo de entrega y plazo de la oferta deben coincidir con los definidos para el producto.');
            return;
        }

        if (isNaN(parseFloat(ofertaInicial)) || parseFloat(ofertaInicial) < 0) {
            alert('La oferta inicial debe ser un número válido mayor o igual a 0.');
            return;
        }
        const ofertaCents = toCents(ofertaInicial);
        const presupuestoCents = toCents(presupuestoReferencial);
        if (ofertaCents === null || presupuestoCents === null) {
            alert('No se pudo validar el valor de la oferta inicial.');
            return;
        }
        if (ofertaCents >= presupuestoCents) {
            alert('La oferta inicial debe ser menor al presupuesto referencial del producto (al menos 0.01 menos).');
            return;
        }

        if (descripcion.length > 1000) {
            alert('La descripción no puede exceder 1000 caracteres.');
            return;
        }

        cleanup();

        if (typeof onSubmit === 'function') {
            onSubmit({
                tiempoEntrega,
                plazoOferta,
                ofertaInicial,
                descripcion
            });
        }
    });
}

// Función para inicializar cuando el contenido esté listo
function initializeEOF() {
    console.log('=== INITIALIZING EOF ===');

    if (window.eofState.initialized) {
        console.log('EOF already initialized, skipping re-bind');
        return true;
    }
    
    const form = document.getElementById('oferta-form');
    const fileInput = document.getElementById('file-input');
    const fileCount = document.getElementById('file-count');
    const fileSize = document.getElementById('file-size');
    const uploadBtn = document.getElementById('upload-btn');
    const processBtn = document.getElementById('process-btn');
    const listaOfertas = document.getElementById('lista-ofertas');
    
    console.log('Form element:', form);
    console.log('File input element:', fileInput);
    console.log('Upload button element:', uploadBtn);
    
    if (!form || !fileInput || !uploadBtn) {
        console.log('Elements not ready yet, will retry...');
        return false;
    }
    
    console.log('All elements found, setting up event listeners...');
    
    // Validación de archivos
    if (!fileInput.dataset.bound) {
        fileInput.dataset.bound = 'true';
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
        fileCount.textContent = `${files.length} archivo(s) seleccionado(s)`;
        fileSize.textContent = `Tamaño total: ${(totalSize / 1024).toFixed(1)} KB`;
        
        // Mostrar/ocultar botones
        if (files.length > 0) {
            uploadBtn.style.display = 'inline-block';
        } else {
            uploadBtn.style.display = 'none';
        }
        });
    }
    
    // Event listener del botón de subir archivos
    console.log('Adding click event listener to upload button');
    if (!uploadBtn.dataset.bound) {
        uploadBtn.dataset.bound = 'true';
        uploadBtn.addEventListener('click', function(e) {
        console.log('=== UPLOAD BUTTON CLICKED ===');
        console.log('Event:', e);
        console.log('Files selected:', fileInput.files.length);
        
        e.preventDefault();
        console.log('Default prevented');
        
        if (window.eofState.isProcessed) {
            console.log('Already processed, showing alert');
            alert('Ya se ha procesado la entrega de ofertas');
            return;
        }
        
        const files = Array.from(fileInput.files);
        console.log('Files array:', files);
        
        if (files.length === 0) {
            console.log('No files selected, showing alert');
            alert('Por favor, seleccione al menos un archivo');
            return;
        }
        
        console.log('Starting file upload process');
        // Subir archivos uno por uno
        uploadFiles(files);
        });
    }
    
    // Event listener del botón de procesar
    if (processBtn && !processBtn.dataset.bound) {
        processBtn.dataset.bound = 'true';
        processBtn.addEventListener('click', function() {
            console.log('=== PROCESS BUTTON CLICKED ===');
            if (window.eofState.uploadedFiles.length === 0) {
                alert('No hay archivos para procesar');
                return;
            }
            
            // Detectar si estamos en producción
            const processUrl = generateUrl('participant_process_offer');
            
            console.log('Processing offer at:', processUrl);
            
            setTimeout(() => {
                openOfferDetailsModal(window.eofState.offerSummary, (formValues) => {
                    const payload = new URLSearchParams();
                    payload.append('producto_id', '<?php echo $product['id']; ?>');
                    payload.append('tiempo_entrega', formValues.tiempoEntrega);
                    payload.append('plazo_oferta', formValues.plazoOferta);
                    payload.append('oferta_inicial_user', formValues.ofertaInicial);
                    payload.append('descripcion', formValues.descripcion);

                    fetch(processUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: payload.toString()
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Process response:', data);
                        if (data.success) {
                            const summary = data.data ? data.data.offer_summary : null;
                            updateProcessedUI(true, summary);
                            fileInput.value = '';
                            alert('Entrega de ofertas procesada exitosamente');
                            loadOfertas();
                        } else {
                            alert('Error al procesar: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Process error:', error);
                        alert('Error al procesar la entrega');
                    });
                });
            }, 50);
        });
    }
    
    console.log('Upload button event listener added successfully');
    
    updateProcessedUI(window.eofState.isProcessed, window.eofState.offerSummary);

    // Marcar como inicializado
    window.eofState.initialized = true;

    return true; // Inicialización exitosa
}

// Función para subir archivos
function uploadFiles(files) {
    console.log('=== UPLOAD FILES FUNCTION CALLED ===');
    let uploadCount = 0;
    const totalFiles = files.length;
    
    files.forEach((file, index) => {
        const formData = new FormData();
        formData.append('producto_id', '<?php echo $product['id']; ?>');
        formData.append('documento_oferta', file);
        
        // Detectar si estamos en producción
        const uploadUrl = generateUrl('participant_upload_offer');
        
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
                window.eofState.uploadedFiles.push({
                    id: data.file_id,
                    name: file.name,
                    size: file.size
                });
                console.log('File uploaded successfully:', file.name);
            } else {
                alert(`Error al subir "${file.name}": ${data.message}`);
            }
            
            // Si es el último archivo
            if (uploadCount === totalFiles) {
                if (window.eofState.uploadedFiles.length > 0) {
                    const processBtn = document.getElementById('process-btn');
                    const uploadBtn = document.getElementById('upload-btn');
                    if (processBtn) processBtn.style.display = 'inline-block';
                    if (uploadBtn) uploadBtn.style.display = 'none';
                    loadOfertas();
                }
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            alert(`Error al subir "${file.name}"`);
        });
    });
}

// Función para cargar ofertas
function loadOfertas() {
    console.log('=== LOAD OFFERS FUNCTION CALLED ===');
    window.eofState.loading = true;
    updateProcessedUI(window.eofState.isProcessed, window.eofState.offerSummary);
    // Detectar si estamos en producción
    const getOffersUrl = generateUrl('participant_get_offers', { producto_id: <?php echo (int)$product['id']; ?> });
    
    console.log('Loading offers from:', getOffersUrl);
    
    fetch(getOffersUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Offers response:', data);
        window.eofState.loading = false;
        if (data.success) {
            const payload = data.data || {};
            const ofertas = payload.ofertas ? payload.ofertas : [];
            const processed = payload.processed || false;
            const summary = payload.offer_summary || null;
            console.log('Ofertas to display (eof.php):', ofertas);
            displayOfertas(ofertas);
            updateProcessedUI(processed, summary);
        } else {
            const listaOfertas = document.getElementById('lista-ofertas');
            if (listaOfertas) {
                listaOfertas.innerHTML = '<p>Error al cargar las ofertas: ' + (data.message || 'Error desconocido') + '</p>';
            }
            updateProcessedUI(window.eofState.isProcessed, window.eofState.offerSummary);
        }
    })
    .catch(error => {
        console.error('Error loading offers:', error);
        window.eofState.loading = false;
        const listaOfertas = document.getElementById('lista-ofertas');
        if (listaOfertas) {
            listaOfertas.innerHTML = '<p>Error al cargar las ofertas</p>';
        }
        updateProcessedUI(window.eofState.isProcessed, window.eofState.offerSummary);
    });
}

// Función para mostrar ofertas
function displayOfertas(ofertas) {
    console.log('=== DISPLAY OFFERS FUNCTION CALLED ===');
    console.log('Ofertas received (eof.php):', ofertas);
    console.log('Ofertas type (eof.php):', typeof ofertas);
    console.log('Ofertas is array (eof.php):', Array.isArray(ofertas));
    
    const listaOfertas = document.getElementById('lista-ofertas');
    if (!listaOfertas) {
        console.error('Lista ofertas element not found');
        return;
    }
    
    // Verificar que ofertas sea un array válido
    if (!ofertas || !Array.isArray(ofertas)) {
        console.error('Ofertas is not a valid array (eof.php):', ofertas);
        listaOfertas.innerHTML = '<p>Error: No se pudieron cargar las ofertas</p>';
        return;
    }
    
    if (ofertas.length === 0) {
        window.eofState.uploadedFiles = [];
        listaOfertas.innerHTML = '<p>No hay archivos subidos aún</p>';
        return;
    }
    
    // Usar función helper para generar URLs dinámicas (siguiendo documentación)
    function generateUrl(path) {
        return generateUrl('view_file', { path });
    }
    
    let html = '<div class="ofertas-grid">';
    window.eofState.uploadedFiles = ofertas.slice();

    ofertas.forEach(oferta => {
        // Generar URL usando función helper dinámica
        const fileUrl = generateUrl(oferta.ruta_archivo);
        console.log('Generating file URL (eof.php):', fileUrl, 'for file:', oferta.nombre_archivo);
        
        html += `
            <div class="oferta-item">
                <div class="oferta-info">
                    <strong>${oferta.nombre_archivo}</strong>
                    <span class="oferta-fecha">${new Date(oferta.fecha_carga).toLocaleString()}</span>
                </div>
                <div class="oferta-actions">
                    <a href="${fileUrl}" target="_blank" class="btn btn-small">Ver</a>
                    ${!oferta.procesado ? `<button onclick="deleteOferta(${oferta.id})" class="btn btn-small btn-danger">Eliminar</button>` : ''}
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    listaOfertas.innerHTML = html;
}

// Función para eliminar oferta
window.deleteOferta = function(fileId) {
    console.log('=== DELETE OFFER FUNCTION CALLED ===', fileId);
    if (confirm('¿Está seguro de que desea eliminar este archivo?')) {
        // Detectar si estamos en producción
        const deleteUrl = generateUrl('participant_delete_offer');
        
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
                loadOfertas();
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

// POLLING: Intentar inicializar cada 200ms hasta que funcione
if (window.eofInitInterval) {
    clearInterval(window.eofInitInterval);
}
window.eofInitAttempts = 0;
window.eofMaxAttempts = 50; // 10 segundos máximo

window.eofInitInterval = setInterval(function() {
    window.eofInitAttempts++;
    console.log(`Initialization attempt ${window.eofInitAttempts}/${window.eofMaxAttempts}`);
    
    if (initializeEOF()) {
        console.log('EOF initialized successfully!');
        clearInterval(window.eofInitInterval);
    } else if (window.eofInitAttempts >= window.eofMaxAttempts) {
        console.error('Failed to initialize EOF after maximum attempts');
        clearInterval(window.eofInitInterval);
    }
}, 200);

// Cargar ofertas al inicio
loadOfertas();
</script>

<style>
.oferta-form {
    margin-bottom: 20px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: #f9f9f9;
}

.file-upload-section {
    margin-bottom: 15px;
}

.file-label {
    display: block;
    padding: 20px;
    border: 2px dashed #007bff;
    border-radius: 5px;
    text-align: center;
    cursor: pointer;
    background-color: #f8f9fa;
    transition: background-color 0.3s;
}

.file-label:hover {
    background-color: #e9ecef;
}

.file-icon {
    font-size: 24px;
    display: block;
    margin-bottom: 10px;
}

.file-text {
    font-size: 16px;
    color: #007bff;
    font-weight: 500;
}

.file-info {
    margin-top: 10px;
    display: flex;
    justify-content: space-between;
    font-size: 14px;
    color: #666;
}

.form-actions {
    text-align: center;
    margin-top: 15px;
}

.btn {
    padding: 10px 20px;
    margin: 0 5px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    display: inline-block;
    transition: background-color 0.3s;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-primary:hover {
    background-color: #0056b3;
}

.btn-success {
    background-color: #28a745;
    color: white;
}

.btn-success:hover {
    background-color: #1e7e34;
}

.btn-small {
    padding: 5px 10px;
    font-size: 12px;
    margin: 0 2px;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn-danger:hover {
    background-color: #c82333;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

.ofertas-lista {
    margin-top: 20px;
}

.offer-summary {
    margin-bottom: 20px;
    padding: 15px;
    border: 1px solid #17a2b8;
    border-radius: 5px;
    background-color: #e9f7fb;
}

.offer-summary h3 {
    margin-top: 0;
    margin-bottom: 10px;
    color: #0c5460;
}

.offer-summary ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.offer-summary li {
    margin-bottom: 5px;
    color: #0c5460;
}

.ofertas-grid {
    display: grid;
    gap: 10px;
}

.oferta-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background-color: white;
}

.oferta-info {
    flex: 1;
}

.oferta-info strong {
    display: block;
    margin-bottom: 5px;
}

.oferta-fecha {
    font-size: 12px;
    color: #666;
}

.oferta-actions {
    display: flex;
    gap: 5px;
}

.offer-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    padding: 20px;
}

.offer-modal {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    max-width: 500px;
    width: 100%;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

.offer-modal h3 {
    margin-top: 0;
    margin-bottom: 10px;
}

.offer-modal p {
    margin-top: 0;
    color: #555;
}

.offer-modal label {
    display: block;
    margin-top: 10px;
    font-weight: 500;
}

.offer-modal input,
.offer-modal textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    margin-top: 5px;
    box-sizing: border-box;
}

.offer-modal textarea {
    min-height: 120px;
    resize: vertical;
}

.modal-actions {
    margin-top: 20px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.hidden {
    display: none !important;
}
</style>