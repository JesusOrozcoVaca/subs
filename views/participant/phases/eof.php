<h2>Entrega de Ofertas</h2>
<div id="entrega-ofertas-container">
    <form id="oferta-form" enctype="multipart/form-data" class="oferta-form">
        <div class="file-upload-section">
            <input type="file" name="documento_oferta" id="file-input" accept=".pdf,.jpg,.png" multiple>
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
            <button type="submit" class="btn btn-primary" id="upload-btn">Subir Archivos</button>
            <button type="button" class="btn btn-success" id="process-btn" class="hidden">Procesar</button>
        </div>
    </form>
    
    <div id="lista-ofertas" class="ofertas-lista">
        <!-- Aquí se mostrarán las ofertas subidas -->
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('oferta-form');
    const fileInput = document.getElementById('file-input');
    const fileCount = document.getElementById('file-count');
    const fileSize = document.getElementById('file-size');
    const uploadBtn = document.getElementById('upload-btn');
    const processBtn = document.getElementById('process-btn');
    const listaOfertas = document.getElementById('lista-ofertas');
    
    let uploadedFiles = [];
    let isProcessed = false;
    
    // Validación de archivos
    fileInput.addEventListener('change', function() {
        const files = Array.from(this.files);
        const maxFiles = 5;
        const maxSize = 512 * 1024; // 512KB
        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        
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
    
    // Envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (isProcessed) {
            alert('Ya se ha procesado la entrega de ofertas');
            return;
        }
        
        const files = Array.from(fileInput.files);
        if (files.length === 0) {
            alert('Por favor, seleccione al menos un archivo');
            return;
        }
        
        // Subir archivos uno por uno
        uploadFiles(files);
    });
    
    // Procesar entrega
    processBtn.addEventListener('click', function() {
        if (uploadedFiles.length === 0) {
            alert('No hay archivos para procesar');
            return;
        }
        
        fetch('/subs/participant/process-offer', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `producto_id=<?php echo $product['id']; ?>`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                isProcessed = true;
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
            console.error('Error:', error);
            alert('Error al procesar la entrega');
        });
    });
    
    // Subir archivos
    function uploadFiles(files) {
        let uploadCount = 0;
        const totalFiles = files.length;
        
        files.forEach((file, index) => {
            const formData = new FormData();
            formData.append('producto_id', '<?php echo $product['id']; ?>');
            formData.append('documento_oferta', file);
            
            fetch('/subs/participant/upload-offer', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                uploadCount++;
                
                if (data.success) {
                    uploadedFiles.push({
                        id: data.file_id,
                        name: file.name,
                        size: file.size
                    });
                } else {
                    alert(`Error al subir "${file.name}": ${data.message}`);
                }
                
                // Si es el último archivo
                if (uploadCount === totalFiles) {
                    if (uploadedFiles.length > 0) {
                        processBtn.style.display = 'inline-block';
                        uploadBtn.style.display = 'none';
                        loadOfertas();
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(`Error al subir "${file.name}"`);
            });
        });
    }
    
    // Cargar ofertas
    function loadOfertas() {
        fetch(`/subs/participant/get-offers?producto_id=<?php echo $product['id']; ?>`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayOfertas(data.ofertas);
            } else {
                listaOfertas.innerHTML = '<p>Error al cargar las ofertas</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            listaOfertas.innerHTML = '<p>Error al cargar las ofertas</p>';
        });
    }
    
    // Mostrar ofertas
    function displayOfertas(ofertas) {
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
                        <a href="${oferta.ruta_archivo}" target="_blank" class="btn btn-small">Ver</a>
                        ${!oferta.procesado ? `<button onclick="deleteOferta(${oferta.id})" class="btn btn-small btn-danger">Eliminar</button>` : ''}
                    </div>
                </div>
            `;
        });
        html += '</div>';
        
        listaOfertas.innerHTML = html;
    }
    
    // Eliminar oferta
    window.deleteOferta = function(fileId) {
        if (confirm('¿Está seguro de que desea eliminar este archivo?')) {
            fetch('/subs/participant/delete-offer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `file_id=${fileId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadOfertas();
                } else {
                    alert('Error al eliminar: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al eliminar el archivo');
            });
        }
    };
    
    // Cargar ofertas al inicio
    loadOfertas();
});
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

.file-input {
    display: none;
}

.file-label {
    display: block;
    padding: 20px;
    border: 2px dashed #ddd;
    border-radius: 5px;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.3s;
}

.file-label:hover {
    border-color: #007bff;
}

.file-icon {
    font-size: 24px;
    display: block;
    margin-bottom: 10px;
}

.file-text {
    font-size: 14px;
    color: #666;
}

.file-info {
    margin-top: 10px;
    font-size: 12px;
    color: #666;
    display: flex;
    justify-content: space-between;
}

.form-actions {
    text-align: center;
    margin-top: 15px;
}

.ofertas-lista {
    margin-top: 20px;
}

.ofertas-grid {
    display: grid;
    gap: 15px;
}

.oferta-item {
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
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
    gap: 10px;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 3px;
    cursor: pointer;
}

.btn-danger:hover {
    background-color: #c82333;
}
</style>