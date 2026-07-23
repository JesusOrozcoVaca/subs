<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Práctica - <?php echo htmlspecialchars($ronda['sala_codigo'] ?? 'Puja'); ?></title>
    <link rel="stylesheet" href="<?php echo css('styles.css'); ?>">
</head>
<body>
    <header class="main-header">
        <div class="logo-container">
            <img src="<?php echo image('logoHJ.png'); ?>" alt="Logo Izquierdo" class="logo">
            <h1>Sistema de Simulacion de Contratacion Publica</h1>
            <img src="<?php echo image('logoHJ.png'); ?>" alt="Logo Derecho" class="logo">
        </div>
        <div class="user-info">
            <span><?php echo date('Y-m-d H:i:s'); ?></span>
            <span><?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? ''); ?></span>
            <a href="<?php echo BASE_URL; ?>index.php?action=participant_training_list" class="btn-return">Regresar</a>
        </div>
    </header>

    <style>
        .puja-page {
            padding: 20px 30px 40px;
        }
        .puja-title {
            margin: 0 0 20px;
            font-size: 26px;
            font-weight: 700;
        }
        .puja-section-title {
            margin: 18px 0 8px;
            font-size: 16px;
            font-weight: 700;
            color: #0a3b86;
        }
        .puja-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #c9c9c9;
            background: #ffffff;
        }
        .puja-table td {
            border: 1px solid #c9c9c9;
            padding: 8px 10px;
            font-size: 13px;
        }
        .puja-table .label {
            width: 26%;
            background: #efefef;
            font-weight: 600;
        }
        .puja-table .value {
            width: 49%;
        }
        .puja-table .helper {
            width: 25%;
            background: #fffbdc;
            color: #1b1b1b;
        }
        .puja-offer-box {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .puja-offer-box input {
            max-width: 180px;
            padding: 6px 8px;
        }
        .puja-input {
            width: 180px;
            padding: 6px 8px;
            appearance: textfield;
        }
        .puja-input::-webkit-outer-spin-button,
        .puja-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .puja-input[type=number] {
            -moz-appearance: textfield;
        }
        .puja-offer-box .btn {
            padding: 6px 14px;
        }
        .puja-status-dot {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #6dbf2a;
            margin: 0 6px -1px 6px;
        }
        .puja-status-text {
            color: #c00000;
        }
        .puja-status-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .puja-status-label .puja-status-dot {
            margin: 0;
        }
        .puja-inline-link {
            color: #c00000;
            text-decoration: underline;
            font-weight: 600;
        }
        .puja-time-row {
            color: #c00000;
            font-weight: 600;
            margin: 10px 0 0;
            font-size: 13px;
        }
    </style>

    <main class="main-content puja-page">
        <h2 class="puja-title">Práctica de Puja - <?php echo htmlspecialchars($ronda['sala_codigo'] ?? ''); ?> (Ronda #<?php echo (int)$ronda['numero']; ?>)</h2>

        <?php
            $formatAmount = function ($value) {
                $value = is_numeric($value) ? (float)$value : 0.0;
                return number_format($value, 2, ',', '.');
            };
            $lastBidValue = $userLastBid['valor'] ?? $initialOfferValue ?? 0;
            $isUserBest = !empty($status['is_user_best']);
            $statusText = !empty($isUserBest)
                ? 'Actualmente su oferta economica es la mejor.'
                : 'Existe una mejor oferta económica que la de su representada.';
            $statusColor = !empty($isUserBest) ? '#6dbf2a' : '#d9534f';
            $pujaSchedule = $schedule ?? null;
        ?>

        <?php if (!empty($blockPuja)): ?>
            <div class="read-only-message">
                <p><?php echo htmlspecialchars($pujaBlockMessage ?? ''); ?></p>
            </div>
        <?php else: ?>
            <div class="puja-section">
                <div class="puja-section-title">Sala de práctica</div>
                <table class="puja-table">
                    <tr>
                        <td class="label">Sala</td>
                        <td class="value" colspan="2"><?php echo htmlspecialchars($ronda['sala_titulo'] ?? ''); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Código</td>
                        <td class="value" colspan="2"><?php echo htmlspecialchars($ronda['sala_codigo'] ?? ''); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Presupuesto referencial</td>
                        <td class="value" colspan="2">$ <?php echo htmlspecialchars($formatAmount($ronda['presupuesto_referencial'] ?? 0)); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Su oferta inicial</td>
                        <td class="value" colspan="2">$ <?php echo htmlspecialchars($formatAmount($initialOfferValue ?? 0)); ?></td>
                    </tr>
                </table>
            </div>

            <div class="puja-section">
                <div class="puja-section-title">Ingreso de Pujas</div>
                <table class="puja-table">
                    <tr>
                        <td class="label">Su ultima oferta fue:</td>
                        <td class="value" colspan="2">
                            <strong>USD</strong> <strong id="last-offer-value"><?php echo htmlspecialchars($formatAmount($lastBidValue)); ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">
                            <div class="puja-status-label">
                                <span>Estado actual <a href="#" class="puja-inline-link" id="puja-refresh-link">clic aqui:</a></span>
                                <span class="puja-status-dot" id="status-dot" style="background: <?php echo htmlspecialchars($statusColor); ?>;"></span>
                            </div>
                        </td>
                        <td class="value">
                            <strong id="status-text" class="puja-status-text"><?php echo htmlspecialchars($statusText); ?></strong>
                        </td>
                        <td class="helper">Clic en "clic aqui" para actualizar su estado</td>
                    </tr>
                    <tr>
                        <td class="label">Valor de la Oferta: $</td>
                        <td class="value">
                            <div class="puja-offer-box">
                                <input type="text" id="offer-input" class="puja-input" inputmode="decimal" autocomplete="off" placeholder="">
                                <button type="button" id="submit-offer" class="btn btn-primary">Enviar Oferta</button>
                            </div>
                        </td>
                        <td class="helper">Ingrese su oferta economica y clic en Enviar oferta.</td>
                    </tr>
                    <tr>
                        <td class="label">Fecha fin de puja:</td>
                        <td class="value">
                            <?php echo !empty($pujaSchedule['end']) ? htmlspecialchars($pujaSchedule['end']) : 'Pendiente'; ?>
                        </td>
                        <td class="helper">Fecha en que finaliza la puja</td>
                    </tr>
                    <tr>
                        <td class="label">Variacion minima de la Oferta durante la Puja</td>
                        <td class="value">
                            <strong><?php echo htmlspecialchars((string)($ronda['variacion_minima'] ?? '0')); ?>%;</strong><br>
                            <strong>Tipo Variacion:</strong> Precio total;<br>
                            <strong>Variacion entre pujas:</strong> <span id="variation-amount">$<?php echo htmlspecialchars($formatAmount($variationAmount ?? 0)); ?></span>
                        </td>
                        <td class="helper">Porcentaje y tipo de variacion entre pujas.</td>
                    </tr>
                </table>
                <div class="puja-time-row" id="puja-countdown">
                    <?php echo htmlspecialchars(date('d/m/Y H:i:s')); ?>
                </div>
            </div>

            <div class="puja-section" style="margin-top: 18px;">
                <div class="puja-section-title">Historial de sus pujas (esta ronda)</div>
                <table class="puja-table" id="live-my-bids-table">
                    <thead>
                    <tr>
                        <td class="label" style="width:10%;">#</td>
                        <td class="value" style="width:35%;">Valor</td>
                        <td class="value" style="width:25%;">Δ bajada</td>
                        <td class="helper" style="width:30%;">Fecha / hora</td>
                    </tr>
                    </thead>
                    <tbody id="live-my-bids-body">
                    <?php if (empty($misPujas)): ?>
                        <tr><td class="value" colspan="4">Aún no ha enviado pujas. Su oferta inicial es la base.</td></tr>
                    <?php else: ?>
                        <?php foreach ($misPujas as $p): ?>
                            <tr>
                                <td class="label"><?php echo (int)$p['n']; ?></td>
                                <td class="value"><strong>$ <?php echo htmlspecialchars($p['valor_fmt']); ?></strong></td>
                                <td class="value"><?php echo $p['delta_fmt'] !== null ? '$ ' . htmlspecialchars($p['delta_fmt']) : '—'; ?></td>
                                <td class="helper"><?php echo htmlspecialchars($p['fecha']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
    <?php if (empty($blockPuja)): ?>
    <script src="<?php echo js('url-helper.js'); ?>?v=20260722t"></script>
    <script>
        (function() {
            const offerInput = document.getElementById('offer-input');
            const lastOfferValue = document.getElementById('last-offer-value');
            const statusDot = document.getElementById('status-dot');
            const statusText = document.getElementById('status-text');
            const variationAmountEl = document.getElementById('variation-amount');
            const refreshLink = document.getElementById('puja-refresh-link');
            const submitButton = document.getElementById('submit-offer');
            const countdownEl = document.getElementById('puja-countdown');

            const rondaId = <?php echo (int)$ronda['id']; ?>;
            const submitUrl = (typeof generateUrl === 'function')
                ? generateUrl('participant_training_submit_bid')
                : '<?php echo BASE_URL; ?>index.php?action=participant_training_submit_bid';
            const statusUrl = (typeof generateUrl === 'function')
                ? generateUrl('participant_training_puja_status', { id: rondaId })
                : '<?php echo BASE_URL; ?>index.php?action=participant_training_puja_status&id=<?php echo (int)$ronda['id']; ?>';
            const summaryUrl = (typeof generateUrl === 'function')
                ? generateUrl('participant_training_summary', { id: rondaId })
                : '<?php echo BASE_URL; ?>index.php?action=participant_training_summary&id=<?php echo (int)$ronda['id']; ?>';
            const presupuesto = <?php echo isset($ronda['presupuesto_referencial']) ? (float)$ronda['presupuesto_referencial'] : 0; ?>;
            const variationAmount = <?php echo isset($variationAmount) ? json_encode((float)$variationAmount) : '0'; ?>;
            const initialOfferValue = <?php echo isset($initialOfferValue) ? json_encode((float)$initialOfferValue) : '0'; ?>;
            const startTs = <?php echo isset($pujaSchedule['start_ts_ms']) ? json_encode((int)$pujaSchedule['start_ts_ms']) : 'null'; ?>;
            const endTs = <?php echo isset($pujaSchedule['end_ts_ms']) ? json_encode((int)$pujaSchedule['end_ts_ms']) : 'null'; ?>;
            let userLastBid = <?php echo isset($userLastBid['valor']) ? json_encode((float)$userLastBid['valor']) : 'null'; ?>;

            function formatCurrency(value) {
                if (value === null || typeof value === 'undefined' || isNaN(value)) {
                    return '0,00';
                }
                const fixed = Number(value).toFixed(2);
                return fixed.replace('.', ',');
            }

            function formatCurrencyWithThousands(value) {
                if (value === null || typeof value === 'undefined' || isNaN(value)) {
                    return '0,00';
                }
                const fixed = Number(value).toFixed(2);
                const parts = fixed.split('.');
                const integerPart = parts[0];
                const decimalPart = parts[1] || '00';
                const withThousands = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                return `${withThousands},${decimalPart}`;
            }

            function showModal(message, confirmText = 'Aceptar') {
                return new Promise((resolve) => {
                    const existing = document.getElementById('puja-modal-overlay');
                    if (existing) {
                        existing.remove();
                    }

                    const overlay = document.createElement('div');
                    overlay.id = 'puja-modal-overlay';
                    overlay.style.cssText = `
                        position: fixed;
                        inset: 0;
                        background: rgba(0, 0, 0, 0.45);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        z-index: 9999;
                    `;

                    const modal = document.createElement('div');
                    modal.style.cssText = `
                        background: #2f2f36;
                        color: #ffffff;
                        width: min(420px, 90%);
                        padding: 20px 22px;
                        border-radius: 10px;
                        box-shadow: 0 12px 30px rgba(0,0,0,0.25);
                        font-size: 14px;
                    `;

                    const messageEl = document.createElement('div');
                    messageEl.style.cssText = 'margin-bottom: 18px; line-height: 1.4;';
                    messageEl.textContent = message;

                    const actions = document.createElement('div');
                    actions.style.cssText = 'display: flex; justify-content: flex-end;';

                    const okButton = document.createElement('button');
                    okButton.type = 'button';
                    okButton.textContent = confirmText;
                    okButton.className = 'btn btn-primary';

                    let closed = false;
                    const closeModal = () => {
                        if (closed) {
                            return;
                        }
                        closed = true;
                        window.removeEventListener('keydown', handleKeyDown, true);
                        if (overlay.parentNode) {
                            overlay.parentNode.removeChild(overlay);
                        }
                        resolve(true);
                    };

                    const handleKeyDown = (event) => {
                        if (event.key === 'Enter') {
                            event.preventDefault();
                            event.stopPropagation();
                            closeModal();
                        }
                    };

                    okButton.addEventListener('click', closeModal);
                    window.addEventListener('keydown', handleKeyDown, true);

                    actions.appendChild(okButton);
                    modal.appendChild(messageEl);
                    modal.appendChild(actions);
                    overlay.appendChild(modal);
                    document.body.appendChild(overlay);
                    okButton.focus();
                });
            }

            function sanitizeInput(value) {
                let cleaned = value.replace(/[^\d,]/g, '');
                const parts = cleaned.split(',');
                if (parts.length > 2) {
                    cleaned = parts[0] + ',' + parts.slice(1).join('');
                }
                if (parts.length >= 2) {
                    cleaned = parts[0] + ',' + parts[1].slice(0, 2);
                }
                return cleaned;
            }

            function parseInputToNumber(value) {
                const normalized = value.replace(/\./g, '').replace(',', '.');
                return parseFloat(normalized);
            }

            function updateLastOfferFromInput() {
                if (!offerInput) {
                    return;
                }
                const cleaned = sanitizeInput(offerInput.value);
                if (offerInput.value !== cleaned) {
                    offerInput.value = cleaned;
                }
            }

            function updateStatus(isBest) {
                if (!statusDot || !statusText) {
                    return;
                }
                if (isBest) {
                    statusDot.style.background = '#6dbf2a';
                    statusText.textContent = 'Actualmente su oferta economica es la mejor.';
                } else {
                    statusDot.style.background = '#d9534f';
                    statusText.textContent = 'Existe una mejor oferta económica que la de su representada.';
                }
            }

            function updateClock() {
                if (!countdownEl) {
                    return;
                }
                if (startTs && endTs) {
                    const nowTs = Date.now();
                    if (nowTs > endTs) {
                        countdownEl.textContent = '¡El tiempo de puja ha terminado!';
                        return;
                    }
                }
                const now = new Date();
                const pad = (value) => String(value).padStart(2, '0');
                const day = pad(now.getDate());
                const month = pad(now.getMonth() + 1);
                const year = now.getFullYear();
                const hours = pad(now.getHours());
                const minutes = pad(now.getMinutes());
                const seconds = pad(now.getSeconds());
                countdownEl.textContent = `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
            }

            function renderMyBids(bids) {
                const tbody = document.getElementById('live-my-bids-body');
                if (!tbody || !Array.isArray(bids)) {
                    return;
                }
                if (!bids.length) {
                    tbody.innerHTML = '<tr><td class="value" colspan="4">Aún no ha enviado pujas. Su oferta inicial es la base.</td></tr>';
                    return;
                }
                tbody.innerHTML = bids.map((p) => (
                    '<tr>' +
                        '<td class="label">' + (p.n || '') + '</td>' +
                        '<td class="value"><strong>$ ' + (p.valor_fmt || '') + '</strong></td>' +
                        '<td class="value">' + (p.delta_fmt != null ? ('$ ' + p.delta_fmt) : '—') + '</td>' +
                        '<td class="helper">' + (p.fecha || '') + '</td>' +
                    '</tr>'
                )).join('');
            }

            function applyStatusData(data) {
                if (!data) {
                    return;
                }
                if (data.ended || data.ronda_estado === 'finalizada') {
                    window.location.href = summaryUrl;
                    return;
                }
                if (typeof data.user_last_bid === 'number') {
                    userLastBid = data.user_last_bid;
                    if (offerInput && document.activeElement !== offerInput) {
                        lastOfferValue.textContent = formatCurrencyWithThousands(data.user_last_bid);
                    }
                }

                const isBest = data.is_user_best;
                updateStatus(isBest);
                if (data.my_bids) {
                    renderMyBids(data.my_bids);
                }
            }

            function fetchStatus() {
                fetch(statusUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.success) {
                            applyStatusData(data.data || {});
                        }
                    })
                    .catch(() => {});
            }

            if (offerInput) {
                offerInput.addEventListener('input', updateLastOfferFromInput);
            }

            if (refreshLink) {
                refreshLink.addEventListener('click', (event) => {
                    event.preventDefault();
                    window.location.reload();
                });
            }

            if (submitButton && offerInput) {
                submitButton.addEventListener('click', async () => {
                    const cleaned = sanitizeInput(offerInput.value);
                    offerInput.value = cleaned;
                    if (cleaned === '') {
                        await showModal('Ingrese un valor para la puja.');
                        return;
                    }

                    const numericValue = parseInputToNumber(cleaned);
                    if (isNaN(numericValue) || numericValue <= 0) {
                        await showModal('El valor de la puja es inválido.');
                        return;
                    }

                    const confirmValue = formatCurrencyWithThousands(numericValue);
                    await showModal(`¿Estás seguro de enviar el valor $ ${confirmValue}?`);

                    if (startTs && endTs) {
                        const nowTs = Date.now();
                        if (nowTs < startTs) {
                            await showModal('La puja aún no ha iniciado.');
                            return;
                        }
                        if (nowTs > endTs) {
                            await showModal('La puja ha finalizado.');
                            return;
                        }
                    }

                    if (presupuesto > 0 && numericValue > presupuesto) {
                        await showModal('El valor ingresado es mayor al presupuesto referencial.');
                        return;
                    }

                    const baseBid = userLastBid !== null ? userLastBid : initialOfferValue;
                    if (baseBid > 0 && variationAmount > 0) {
                        const maxAllowed = baseBid - variationAmount;
                        if (numericValue > maxAllowed) {
                            await showModal('El valor ingresado no cumple la variación mínima permitida.');
                            return;
                        }
                    }

                    const formData = new FormData();
                    formData.append('ronda_id', String(rondaId));
                    formData.append('valor', cleaned);

                    try {
                        const response = await fetch(submitUrl, {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const data = await response.json();
                        if (data && data.success) {
                            await showModal('Valor enviado correctamente.');
                            window.location.reload();
                        } else {
                            await showModal((data && data.message) ? data.message : 'No se pudo registrar la puja.');
                        }
                    } catch (error) {
                        await showModal('Error al registrar la puja.');
                    }
                });
            }

            updateLastOfferFromInput();
            fetchStatus();
            setInterval(fetchStatus, 1000);
            updateClock();
            setInterval(updateClock, 2000);
        })();
    </script>
    <?php endif; ?>
</body>
</html>
