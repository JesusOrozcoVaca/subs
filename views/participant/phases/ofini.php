<?php
$blockForNoOffer = isset($blockWithoutOffer) ? (bool)$blockWithoutOffer : false;
if ($blockForNoOffer) {
    echo '<div class="read-only-message"><p>Usted no cargo oferta para este proceso por lo tanto, no puede participar en el mismo.</p></div>';
    return;
}
$initialOfferSent = isset($initialOfferSent) ? (bool)$initialOfferSent : false;
$initialOfferDocument = $initialOfferDocument ?? null;
?>
<h2>Oferta Inicial</h2>
<div id="oferta-inicial-container">
    <form id="oferta-inicial-form" class="oferta-inicial-form" style="<?php echo $initialOfferSent ? 'display: none;' : ''; ?>">
        <label for="oferta-inicial-valor">Valor de su oferta inicial</label>
        <input
            type="text"
            id="oferta-inicial-valor"
            name="valor_oferta"
            placeholder="Ej: 1221,99"
            inputmode="decimal"
            autocomplete="off"
        >
        <small class="oferta-inicial-hint">
            Ingrese el valor sin separador de miles. Use coma para los centavos si aplica.
        </small>
        <div class="form-actions">
            <button type="submit" class="btn btn-success">Enviar Oferta Inicial</button>
        </div>
        <div id="oferta-inicial-message" class="error-message" style="display: none;" role="alert"></div>
    </form>
    <div id="oferta-inicial-status" class="oferta-inicial-status" style="<?php echo $initialOfferSent ? 'display: block;' : 'display: none;'; ?>">
        <p class="oferta-inicial-status-title">Usted ya ha enviado su oferta inicial</p>
        <p class="oferta-inicial-status-subtitle">
            Oferta inicial enviada:
            <span id="oferta-inicial-valor-enviado">
                <?php echo htmlspecialchars($initialOfferDocument['oferta_inicial'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
            </span>
        </p>
        <button type="button" id="oferta-inicial-download-btn" class="btn btn-primary">Descargar oferta inicial</button>
    </div>
</div>

<script>
(function() {
    const form = document.getElementById('oferta-inicial-form');
    const input = document.getElementById('oferta-inicial-valor');
    const messageEl = document.getElementById('oferta-inicial-message');
    const statusEl = document.getElementById('oferta-inicial-status');
    const downloadBtn = document.getElementById('oferta-inicial-download-btn');
    const productId = <?php echo json_encode($product['id'] ?? null); ?>;
    const initialOfferSent = <?php echo $initialOfferSent ? 'true' : 'false'; ?>;
    const initialDocumentData = <?php echo json_encode($initialOfferDocument); ?>;
    const sentValueEl = document.getElementById('oferta-inicial-valor-enviado');

    if (!form || !input || !productId) {
        return;
    }

    const submitUrl = generateUrl('participant_submit_initial_offer');

    const mismatchMessage = 'El valor ingresado no corresponde a su oferta ingresada al entregar la oferta. Inténtelo nuevamente.';

    const escapeHtml = (value) => {
        if (typeof value !== 'string') {
            return '';
        }
        return value
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    };

    const clearMessage = () => {
        if (!messageEl) {
            return;
        }
        messageEl.textContent = '';
        messageEl.style.display = 'none';
    };

    const showMessage = (message) => {
        if (!messageEl) {
            return;
        }
        messageEl.textContent = message;
        messageEl.style.display = 'block';
    };

    const closePopup = (overlay) => {
        if (overlay && document.body.contains(overlay)) {
            document.body.removeChild(overlay);
        }
        document.body.classList.remove('ofini-modal-open');
    };

    let currentDocumentData = initialDocumentData;

    const updateInitialOfferUI = (documentData) => {
        if (form) {
            form.style.display = 'none';
        }
        if (statusEl) {
            statusEl.style.display = 'block';
        }
        currentDocumentData = documentData || null;
        if (sentValueEl) {
            sentValueEl.textContent = currentDocumentData && currentDocumentData.oferta_inicial
                ? currentDocumentData.oferta_inicial
                : '';
        }
        if (downloadBtn) {
            downloadBtn.disabled = !currentDocumentData;
        }
    };

    const openPopup = (documentData) => {
        const overlay = document.createElement('div');
        overlay.className = 'ofini-modal-overlay';
        overlay.innerHTML = `
            <div class="ofini-modal">
                <div class="ofini-modal-toolbar">
                    <button type="button" class="btn btn-secondary ofini-close-btn">Cerrar</button>
                    <button type="button" class="btn btn-primary ofini-print-btn">Imprimir</button>
                </div>
                <div class="ofini-document">
                    <table class="ofini-header-table">
                        <tr>
                            <td class="ofini-label">EMPRESA</td>
                            <td>${escapeHtml(documentData.empresa || '')}</td>
                            <td class="ofini-label">FECHA:</td>
                            <td>${escapeHtml(documentData.fecha || '')}</td>
                        </tr>
                        <tr>
                            <td class="ofini-label">RUC:</td>
                            <td>${escapeHtml(documentData.ruc || '')}</td>
                            <td class="ofini-label">MODULO:</td>
                            <td>${escapeHtml(documentData.modulo || '')}</td>
                        </tr>
                        <tr>
                            <td class="ofini-label">USUARIO:</td>
                            <td colspan="3">${escapeHtml(documentData.usuario || '')}</td>
                        </tr>
                    </table>

                    <div class="ofini-section">
                        <div class="ofini-section-title">Proceso de Contratación</div>
                        <table class="ofini-section-table">
                            <tr>
                                <td class="ofini-label">Entidad Contratante</td>
                                <td>${escapeHtml(documentData.entidad_contratante || '')}</td>
                            </tr>
                            <tr>
                                <td class="ofini-label">Objeto de Proceso de Contratación</td>
                                <td>${escapeHtml(documentData.objeto_proceso || '')}</td>
                            </tr>
                            <tr>
                                <td class="ofini-label">Código</td>
                                <td>${escapeHtml(documentData.codigo || '')}</td>
                            </tr>
                            <tr>
                                <td class="ofini-label">Tipo de Compra</td>
                                <td>${escapeHtml(documentData.tipo_compra || '')}</td>
                            </tr>
                            <tr>
                                <td class="ofini-label">Tipo de Contratación</td>
                                <td>${escapeHtml(documentData.tipo_contratacion || '')}</td>
                            </tr>
                            <tr>
                                <td class="ofini-label">Estado del Proceso</td>
                                <td>${escapeHtml(documentData.estado_proceso || '')}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="ofini-highlight">
                        Su oferta económica enviada es: USD ${escapeHtml(documentData.oferta_inicial || '')}
                    </div>
                    <div class="ofini-code">Código: ${escapeHtml(documentData.codigo_aleatorio || '')}</div>
                    <div class="ofini-note">Recuerde imprimir este formulario es su respaldo</div>
                </div>
                <div class="ofini-footer">
                    Documento educativo sin validez legal - Copyright 2026 - HJ Consultiing Management C.Ltda.
                </div>
            </div>
        `;

        document.body.appendChild(overlay);
        document.body.classList.add('ofini-modal-open');

        const closeBtn = overlay.querySelector('.ofini-close-btn');
        const printBtn = overlay.querySelector('.ofini-print-btn');

        if (closeBtn) {
            closeBtn.addEventListener('click', () => closePopup(overlay));
        }

        if (printBtn) {
            printBtn.addEventListener('click', () => {
                document.body.classList.add('ofini-print-active');
                const cleanup = () => {
                    document.body.classList.remove('ofini-print-active');
                    window.removeEventListener('afterprint', cleanup);
                };
                window.addEventListener('afterprint', cleanup);
                window.print();
            });
        }

        overlay.addEventListener('click', (event) => {
            if (event.target === overlay) {
                closePopup(overlay);
            }
        });

    };

    if (initialOfferSent) {
        updateInitialOfferUI(initialDocumentData);
    }

    if (downloadBtn) {
        downloadBtn.addEventListener('click', () => {
            if (!currentDocumentData) {
                showMessage('No se pudo cargar el documento de oferta inicial.');
                return;
            }
            openPopup(currentDocumentData);
        });
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        clearMessage();

        const valorOferta = input.value.trim();
        if (!valorOferta) {
            showMessage('Debe ingresar el valor de su oferta inicial.');
            return;
        }

        const payload = new FormData();
        payload.append('producto_id', productId);
        payload.append('valor_oferta', valorOferta);

        fetch(submitUrl, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: payload
        })
        .then(response => response.json())
        .then(data => {
            if (!data || !data.success) {
                const serverMessage = data && data.message ? data.message : mismatchMessage;
                showMessage(serverMessage);
                return;
            }

            const documentData = data.data && data.data.document ? data.data.document : null;
            if (!documentData) {
                showMessage('No se pudo generar el documento de respaldo.');
                return;
            }

            updateInitialOfferUI(documentData);
            openPopup(documentData);
        })
        .catch(() => {
            showMessage('No se pudo validar la oferta. Inténtelo nuevamente.');
        });
    });
})();
</script>

<style>
.oferta-inicial-form {
    max-width: 520px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    background-color: #f9f9f9;
}

.oferta-inicial-form label {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
}

.oferta-inicial-form input[type="text"] {
    width: 100%;
    padding: 10px;
    border-radius: 4px;
    border: 1px solid #ccc;
    box-sizing: border-box;
}

.oferta-inicial-hint {
    display: block;
    margin-top: 6px;
    font-size: 12px;
    color: #555;
}

.oferta-inicial-status {
    padding: 16px;
    border: 1px solid #b3e5fc;
    background-color: #e6f7ff;
    border-radius: 6px;
    max-width: 520px;
}

.oferta-inicial-status-title {
    font-weight: 600;
    margin-bottom: 4px;
}

.oferta-inicial-status-subtitle {
    margin-bottom: 10px;
    color: #333;
}

.ofini-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    z-index: 9999;
    overflow: auto;
}

.ofini-modal {
    background: #fff;
    width: 100%;
    max-width: 900px;
    border-radius: 6px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
    overflow: hidden;
}

.ofini-modal-toolbar {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 10px 15px;
    border-bottom: 1px solid #e0e0e0;
    background-color: #f5f7fb;
}

.ofini-document {
    padding: 12px 16px 8px;
    font-family: Arial, sans-serif;
    color: #222;
}

.ofini-header-table,
.ofini-section-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.ofini-header-table td,
.ofini-section-table td {
    border: 1px solid #bfbfbf;
    padding: 6px 8px;
    vertical-align: top;
}

.ofini-label {
    font-weight: 700;
    color: #1e4d9b;
    width: 22%;
    background-color: #f3f6fb;
}

.ofini-section {
    margin-top: 12px;
}

.ofini-section-title {
    border: 1px solid #bfbfbf;
    border-bottom: none;
    padding: 6px 8px;
    font-weight: 700;
    color: #1e4d9b;
    background-color: #f3f6fb;
}

.ofini-highlight {
    margin: 14px 0 8px;
    padding: 10px;
    border: 1px solid #bfbfbf;
    text-align: center;
    font-weight: 700;
    color: #1e4d9b;
    background-color: #f8fbff;
}

.ofini-code {
    text-align: center;
    font-size: 12px;
    margin-bottom: 6px;
    color: #333;
}

.ofini-note {
    text-align: center;
    font-size: 12px;
    color: #555;
    margin-bottom: 8px;
}

.ofini-footer {
    background-color: #1e4d9b;
    color: #fff;
    text-align: center;
    padding: 8px 12px;
    font-size: 12px;
}

body.ofini-modal-open {
    overflow: hidden;
}

@media print {
    @page {
        size: A4;
        margin: 12mm;
    }

    body.ofini-print-active {
        margin: 0;
        background: #fff;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    body.ofini-print-active > *:not(.ofini-modal-overlay) {
        display: none !important;
    }

    body.ofini-print-active .ofini-modal {
        position: relative;
        top: auto;
        left: auto;
        width: 100%;
        max-width: 900px;
        margin: 0 auto;
        box-shadow: none;
    }

    body.ofini-print-active .ofini-modal-toolbar {
        display: none;
    }

    body.ofini-print-active .ofini-modal-overlay {
        display: block !important;
        background: transparent;
        padding: 0;
        position: fixed;
        inset: 0;
        width: 100%;
        height: 100%;
    }

    body.ofini-print-active .ofini-label,
    body.ofini-print-active .ofini-section-title {
        background-color: #f3f6fb !important;
        color: #1e4d9b !important;
    }

    body.ofini-print-active .ofini-highlight {
        background-color: #f8fbff !important;
        color: #1e4d9b !important;
    }

    body.ofini-print-active .ofini-footer {
        background-color: #1e4d9b !important;
        color: #fff !important;
    }
}
</style>