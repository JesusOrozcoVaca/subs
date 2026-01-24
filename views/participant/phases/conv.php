<h2>Convalidación de Errores</h2>
<div id="convalidacion-errores-container">
    <form id="convalidacion-form" method="POST" enctype="multipart/form-data" class="convalidacion-form">
        <label for="convalidacion-texto" class="convalidacion-label">Detalle de la convalidación</label>
        <textarea id="convalidacion-texto" name="respuesta_convalidacion" placeholder="Escriba su respuesta de convalidación aquí" required></textarea>

        <div class="file-upload-section">
            <input type="file" name="documentos_convalidacion[]" id="convalidacion-file-input" accept=".pdf,.doc,.docx" multiple style="display: none;">
            <label for="convalidacion-file-input" class="file-label">
                <span class="file-icon">📎</span>
                <span class="file-text">Seleccionar archivos (PDF, DOC, DOCX - Máx 5MB cada uno)</span>
            </label>
            <div class="file-info">
                <span id="convalidacion-file-count">0 archivos seleccionados</span>
                <span id="convalidacion-file-size">Tamaño total: 0 KB</span>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-success" id="convalidacion-submit-btn">Enviar Convalidación</button>
        </div>
    </form>

    <div id="convalidacion-summary" class="convalidacion-summary hidden"></div>
    <div id="convalidacion-files" class="convalidacion-files"></div>
</div>

<script>
console.log('[CONV] Script loaded');

window.convalidationState = {
    submitted: false,
    summary: null,
    files: []
};

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

function generateFileUrl(path) {
    const isProduction = window.location.pathname.includes('index.php') ||
                        window.location.hostname.includes('hjconsulting.com.ec');
    const baseUrl = isProduction ? '/subs/' : '/subs/';
    return `${baseUrl}index.php?action=view_file&path=${encodeURIComponent(path)}`;
}

function updateFileInfo(files, countElement, sizeElement) {
    console.log('[CONV] updateFileInfo', { filesCount: files ? files.length : 0 });
    if (!countElement || !sizeElement) {
        return;
    }

    const totalSize = Array.from(files).reduce((acc, file) => acc + (file.size || 0), 0);
    const totalSizeKB = (totalSize / 1024).toFixed(2);

    countElement.textContent = `${files.length} archivo(s) seleccionado(s)`;
    sizeElement.textContent = `Tamaño total: ${totalSizeKB} KB`;
}

function renderSummary(summary) {
    console.log('[CONV] renderSummary', summary);
    const summaryContainer = document.getElementById('convalidacion-summary');
    if (!summaryContainer) {
        return;
    }

    if (!summary) {
        summaryContainer.classList.add('hidden');
        summaryContainer.innerHTML = '';
        return;
    }

    const isProduction = window.location.pathname.includes('index.php') ||
                        window.location.hostname.includes('hjconsulting.com.ec');
    const downloadUrl = isProduction ?
        `/subs/index.php?action=participant_download_convalidation_pdf&producto_id=<?php echo $product['id']; ?>` :
        `/subs/participant/download-convalidation-pdf?producto_id=<?php echo $product['id']; ?>`;

    summaryContainer.classList.remove('hidden');
    summaryContainer.innerHTML = `
        <h3>Convalidación de errores entregada</h3>
        <ul>
            <li><strong>Fecha:</strong> ${escapeHtml(summary.created_at_formatted || '')}</li>
            <li><strong>Detalle:</strong> ${escapeHtml(summary.detalle_texto || '')}</li>
        </ul>
        <div style="margin-top: 15px;">
            <a href="${downloadUrl}" class="btn btn-primary" style="text-decoration: none; display: inline-block;" target="_blank">
                Descargar PDF de Convalidación
            </a>
        </div>
    `;
}

function renderFiles(files) {
    console.log('[CONV] renderFiles', Array.isArray(files) ? files.length : files);
    const filesContainer = document.getElementById('convalidacion-files');
    if (!filesContainer) {
        return;
    }

    if (!files || !Array.isArray(files) || files.length === 0) {
        filesContainer.innerHTML = '<p>No hay archivos cargados.</p>';
        return;
    }

    let html = '<h3>Archivos cargados</h3><div class="ofertas-grid">';
    files.forEach(file => {
        const fileUrl = generateFileUrl(file.ruta_archivo);
        html += `
            <div class="oferta-item">
                <div class="oferta-info">
                    <strong>${escapeHtml(file.nombre_archivo || '')}</strong>
                    <span class="oferta-fecha">${file.fecha_carga ? new Date(file.fecha_carga).toLocaleString('es-EC', { timeZone: 'America/Guayaquil' }) : ''}</span>
                </div>
                <div class="oferta-actions">
                    <a href="${fileUrl}" target="_blank" class="btn btn-small">Ver</a>
                </div>
            </div>
        `;
    });
    html += '</div>';
    filesContainer.innerHTML = html;
}

function updateConvalidationUI(summary, files) {
    console.log('[CONV] updateConvalidationUI', { summary, filesCount: Array.isArray(files) ? files.length : 0 });
    window.convalidationState.summary = summary || null;
    window.convalidationState.files = files || [];
    window.convalidationState.submitted = !!summary;

    const form = document.getElementById('convalidacion-form');
    if (form) {
        form.style.display = summary ? 'none' : 'block';
    }

    renderSummary(summary);
    renderFiles(files);
}

function loadConvalidation() {
    console.log('[CONV] loadConvalidation start');
    const isProduction = window.location.pathname.includes('index.php') ||
                        window.location.hostname.includes('hjconsulting.com.ec');
    const url = isProduction ?
        `/subs/index.php?action=participant_get_convalidation&producto_id=<?php echo $product['id']; ?>` :
        `/subs/participant/get-convalidation?producto_id=<?php echo $product['id']; ?>`;

    console.log('[CONV] loadConvalidation url', url);
    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => {
        console.log('[CONV] loadConvalidation response', response.status, response.statusText);
        return response.json();
    })
    .then(data => {
        console.log('[CONV] loadConvalidation payload', data);
        if (data.success) {
            const payload = data.data || {};
            updateConvalidationUI(payload.summary || null, payload.files || []);
        } else {
            console.error('Error al cargar convalidación:', data.message || 'Error desconocido');
        }
    })
    .catch(error => {
        console.error('Error al cargar convalidación:', error);
    });
}

function initializeConvalidation() {
    console.log('[CONV] initializeConvalidation start');
    const form = document.getElementById('convalidacion-form');
    const fileInput = document.getElementById('convalidacion-file-input');
    const fileCount = document.getElementById('convalidacion-file-count');
    const fileSize = document.getElementById('convalidacion-file-size');

    if (!form || !fileInput) {
        console.warn('[CONV] initializeConvalidation missing elements', { form, fileInput });
        return false;
    }

    fileInput.addEventListener('change', function() {
        console.log('[CONV] file input changed', fileInput.files);
        updateFileInfo(fileInput.files, fileCount, fileSize);
    });

    form.addEventListener('submit', function(e) {
        console.log('[CONV] form submit intercepted');
        e.preventDefault();

        const detalle = (document.getElementById('convalidacion-texto').value || '').trim();
        console.log('[CONV] detalle length', detalle.length);
        if (!detalle) {
            alert('Debe ingresar el detalle de la convalidación');
            return;
        }

        if (!fileInput.files || fileInput.files.length === 0) {
            alert('Debe adjuntar al menos un archivo');
            return;
        }

        const formData = new FormData();
        formData.append('producto_id', '<?php echo $product['id']; ?>');
        formData.append('respuesta_convalidacion', detalle);

        Array.from(fileInput.files).forEach(file => {
            console.log('[CONV] appending file', { name: file.name, size: file.size, type: file.type });
            formData.append('documentos_convalidacion[]', file);
        });

        const isProduction = window.location.pathname.includes('index.php') ||
                            window.location.hostname.includes('hjconsulting.com.ec');
        const submitUrl = isProduction ?
            '/subs/index.php?action=participant_submit_convalidation' :
            '/subs/participant/submit-convalidation';

        console.log('[CONV] submit url', submitUrl);
        fetch(submitUrl, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(response => {
            console.log('[CONV] submit response', response.status, response.statusText);
            return response.json();
        })
        .then(data => {
            console.log('[CONV] submit payload', data);
            if (data.success) {
                const payload = data.data || {};
                updateConvalidationUI(payload.summary || null, payload.files || []);
            } else {
                alert(data.message || 'Error al enviar la convalidación');
            }
        })
        .catch(error => {
            console.error('Error al enviar convalidación:', error);
            alert('Error al enviar la convalidación');
        });
    });

    return true;
}

let convInitAttempts = 0;
const convInitInterval = setInterval(function() {
    convInitAttempts++;
    console.log('[CONV] init attempt', convInitAttempts);
    if (initializeConvalidation()) {
        console.log('[CONV] initializeConvalidation success');
        clearInterval(convInitInterval);
    } else if (convInitAttempts >= 50) {
        console.warn('[CONV] initializeConvalidation failed after max attempts');
        clearInterval(convInitInterval);
    }
}, 200);

setTimeout(function() {
    console.log('[CONV] delayed loadConvalidation');
    loadConvalidation();
}, 500);
</script>

<style>
.convalidacion-form {
    margin-bottom: 20px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: #f9f9f9;
}

.convalidacion-label {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
}

#convalidacion-texto {
    width: 100%;
    min-height: 120px;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 4px;
    border: 1px solid #ccc;
}

.file-upload-section {
    margin-bottom: 15px;
}

.file-label {
    display: block;
    padding: 16px;
    border: 2px dashed #28a745;
    border-radius: 5px;
    text-align: center;
    cursor: pointer;
    background-color: #f8f9fa;
}

.file-label:hover {
    background-color: #e9ecef;
}

.file-icon {
    font-size: 20px;
    display: block;
    margin-bottom: 8px;
}

.file-text {
    font-size: 15px;
    color: #28a745;
    font-weight: 500;
}

.file-info {
    margin-top: 8px;
    font-size: 13px;
    color: #555;
    display: flex;
    justify-content: space-between;
}

.form-actions {
    display: flex;
    gap: 10px;
}

.convalidacion-summary {
    margin-top: 20px;
    padding: 15px;
    border: 1px solid #b3e5fc;
    background-color: #e6f7ff;
    border-radius: 5px;
}

.convalidacion-summary ul {
    list-style: none;
    padding-left: 0;
    margin: 0;
}

.convalidacion-summary li {
    margin-bottom: 6px;
}

.convalidacion-files {
    margin-top: 15px;
}

.ofertas-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.oferta-item {
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 10px;
    background: #fff;
    min-width: 220px;
}

.oferta-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.oferta-fecha {
    font-size: 12px;
    color: #777;
}

.oferta-actions {
    margin-top: 8px;
}

.hidden {
    display: none;
}
</style>