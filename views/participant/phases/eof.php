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
    
    <div id="lista-ofertas" class="ofertas-lista">
        <!-- Aquí se mostrarán las ofertas subidas -->
    </div>
</div>

<script>
// SOLUCIÓN RADICAL: Event Delegation + Polling
console.log('=== EOF SCRIPT STARTING (NEW APPROACH) ===');

// Variables globales para el estado
window.eofState = {
    uploadedFiles: [],
    isProcessed: false,
    initialized: false
};

// Función para inicializar cuando el contenido esté listo
function initializeEOF() {
    console.log('=== INITIALIZING EOF ===');
    
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
    
    // Marcar como inicializado
    window.eofState.initialized = true;
    
    // Validación de archivos
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
    
    // Event listener del botón de subir archivos
    console.log('Adding click event listener to upload button');
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
    
    // Event listener del botón de procesar
    if (processBtn) {
        processBtn.addEventListener('click', function() {
            console.log('=== PROCESS BUTTON CLICKED ===');
            if (window.eofState.uploadedFiles.length === 0) {
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
                body: `producto_id=<?php echo $product['id']; ?>`
            })
            .then(response => response.json())
            .then(data => {
                console.log('Process response:', data);
                if (data.success) {
                    window.eofState.isProcessed = true;
                    processBtn.style.display = 'none';
                    uploadBtn.style.display = 'none';
                    fileInput.disabled = true;
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
    }
    
    console.log('Upload button event listener added successfully');
    
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
    // Detectar si estamos en producción
    const isProduction = window.location.pathname.includes('index.php') || 
                        window.location.hostname.includes('hjconsulting.com.ec');
    
    const getOffersUrl = isProduction ? 
        `/subs/index.php?action=participant_get_offers&producto_id=<?php echo $product['id']; ?>` : 
        `/subs/participant/get-offers?producto_id=<?php echo $product['id']; ?>`;
    
    console.log('Loading offers from:', getOffersUrl);
    
    fetch(getOffersUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Offers response:', data);
        if (data.success) {
            // La estructura correcta es data.data.ofertas
            const ofertas = data.data && data.data.ofertas ? data.data.ofertas : [];
            console.log('Ofertas to display (eof.php):', ofertas);
            displayOfertas(ofertas);
        } else {
            const listaOfertas = document.getElementById('lista-ofertas');
            if (listaOfertas) {
                listaOfertas.innerHTML = '<p>Error al cargar las ofertas: ' + (data.message || 'Error desconocido') + '</p>';
            }
        }
    })
    .catch(error => {
        console.error('Error loading offers:', error);
        const listaOfertas = document.getElementById('lista-ofertas');
        if (listaOfertas) {
            listaOfertas.innerHTML = '<p>Error al cargar las ofertas</p>';
        }
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
        listaOfertas.innerHTML = '<p>No hay archivos subidos aún</p>';
        return;
    }
    
    let html = '<div class="ofertas-grid">';
    ofertas.forEach(oferta => {
        html += `
            <div class="oferta-item">
                <div class="oferta-info">
                    <strong>${oferta.nombre_archivo}</strong>
                    <span class="oferta-fecha">${new Date(oferta.fecha_carga).toLocaleString()}</span>
                </div>
                <div class="oferta-actions">
                    <a href="/subs/${oferta.ruta_archivo}" target="_blank" class="btn btn-small">Ver</a>
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
let initAttempts = 0;
const maxAttempts = 50; // 10 segundos máximo

const initInterval = setInterval(function() {
    initAttempts++;
    console.log(`Initialization attempt ${initAttempts}/${maxAttempts}`);
    
    if (initializeEOF()) {
        console.log('EOF initialized successfully!');
        clearInterval(initInterval);
    } else if (initAttempts >= maxAttempts) {
        console.error('Failed to initialize EOF after maximum attempts');
        clearInterval(initInterval);
    }
}, 200);

// Cargar ofertas al inicio
setTimeout(function() {
    loadOfertas();
}, 500);
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

.ofertas-lista {
    margin-top: 20px;
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

.hidden {
    display: none !important;
}
</style>