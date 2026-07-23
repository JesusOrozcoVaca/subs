(function () {
    function money(value) {
        if (value === null || value === undefined || value === '') {
            return 'Sin pujas';
        }
        const n = Number(value);
        if (Number.isNaN(n)) {
            return '—';
        }
        return '$ ' + n.toLocaleString('es-EC', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function bindToggleButtons(root) {
        (root || document).querySelectorAll('.btn-toggle-ins').forEach((btn) => {
            if (btn.dataset.bound === '1') {
                return;
            }
            btn.dataset.bound = '1';
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                const activo = btn.getAttribute('data-activo');
                const url = (typeof generateUrl === 'function')
                    ? generateUrl('admin_training_toggle_inscripcion')
                    : (window.location.origin + '/index.php?action=admin_training_toggle_inscripcion');

                const body = new URLSearchParams();
                body.set('inscripcion_id', id);
                body.set('activo', activo);

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: body.toString()
                })
                    .then((r) => r.json())
                    .then((data) => {
                        if (data && data.success) {
                            window.location.reload();
                        } else {
                            alert((data && data.message) || 'No se pudo actualizar.');
                        }
                    })
                    .catch(() => alert('Error de red al actualizar inscripción.'));
            });
        });
    }

    function formatCountdown(ms) {
        if (ms === null || ms === undefined || Number.isNaN(ms)) {
            return '';
        }
        const total = Math.max(0, Math.floor(ms / 1000));
        const h = Math.floor(total / 3600);
        const m = Math.floor((total % 3600) / 60);
        const s = total % 60;
        const pad = (x) => String(x).padStart(2, '0');
        if (h > 0) {
            return pad(h) + ':' + pad(m) + ':' + pad(s);
        }
        return pad(m) + ':' + pad(s);
    }

    function updateCountdown(schedule, nowTs, estado) {
        const el = document.getElementById('live-countdown');
        if (!el || !schedule) {
            return;
        }
        const startMs = schedule.start_ts_ms != null ? Number(schedule.start_ts_ms) : null;
        const endMs = schedule.end_ts_ms != null ? Number(schedule.end_ts_ms) : null;
        if (estado === 'programada' && startMs != null) {
            el.textContent = 'Inicia en ' + formatCountdown(startMs - nowTs);
            return;
        }
        if (estado === 'en_curso' && endMs != null) {
            el.textContent = 'Finaliza en ' + formatCountdown(endMs - nowTs);
            return;
        }
        el.textContent = '';
    }

    function renderParticipants(participants) {
        const tbody = document.getElementById('inscritos-tbody');
        const countEl = document.getElementById('live-inscritos-count');
        if (!tbody) {
            return;
        }
        if (countEl) {
            countEl.textContent = '(' + participants.length + ')';
        }
        if (!participants.length) {
            tbody.innerHTML = '<tr class="inscritos-empty"><td colspan="5">Aún no hay inscritos.</td></tr>';
            return;
        }

        tbody.innerHTML = participants.map((p) => {
            const activo = !!p.activo;
            const bestBadge = p.es_mejor ? ' <span class="puja-winner-badge">Mejor</span>' : '';
            const ultima = p.ultima_puja !== null && p.ultima_puja !== undefined
                ? money(p.ultima_puja)
                : '—';
            return (
                '<tr data-inscripcion-id="' + p.inscripcion_id + '" class="' + (p.es_mejor ? 'is-best-bidder' : '') + '">' +
                    '<td>' + escapeHtml(p.nombre) + bestBadge + '</td>' +
                    '<td>' + money(p.oferta_inicial) + '</td>' +
                    '<td class="ins-ultima">' + ultima + '</td>' +
                    '<td class="ins-estado">' + (activo ? 'Activo' : 'Inactivo') + '</td>' +
                    '<td>' +
                        '<button type="button" class="btn btn-small btn-toggle-ins" data-id="' + p.inscripcion_id + '" data-activo="' + (activo ? '0' : '1') + '">' +
                            (activo ? 'Desactivar' : 'Activar') +
                        '</button>' +
                    '</td>' +
                '</tr>'
            );
        }).join('');

        bindToggleButtons(tbody);
    }

    function escapeHtml(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function applyLiveStatus(data) {
        const estadoEl = document.getElementById('live-estado');
        const lowestEl = document.getElementById('live-lowest');
        const bidderEl = document.getElementById('live-best-bidder');
        const totalEl = document.getElementById('live-total-pujas');

        if (estadoEl) {
            estadoEl.textContent = data.estado || '';
            estadoEl.setAttribute('data-estado', data.estado || '');
        }
        if (lowestEl) {
            lowestEl.textContent = data.lowest_bid != null ? money(data.lowest_bid) : 'Sin pujas';
        }
        if (bidderEl) {
            bidderEl.textContent = data.best_bidder ? ('Por: ' + data.best_bidder) : '';
        }
        if (totalEl) {
            totalEl.textContent = String(data.total_pujas != null ? data.total_pujas : 0);
        }

        if (data.schedule) {
            const startEl = document.getElementById('live-start');
            const endEl = document.getElementById('live-end');
            if (startEl && data.schedule.start) {
                startEl.innerHTML = escapeHtml(data.schedule.start) +
                    (data.schedule.timezone ? ' <small>' + escapeHtml(data.schedule.timezone) + '</small>' : '');
            }
            if (endEl && data.schedule.end) {
                endEl.textContent = data.schedule.end;
            }
        }

        updateCountdown(data.schedule, data.now_ts_ms, data.estado);
        renderParticipants(data.participants || []);
    }

    function startLivePolling() {
        const root = document.getElementById('training-admin-live');
        if (!root || root.getAttribute('data-poll') !== '1') {
            return;
        }

        const rondaId = root.getAttribute('data-ronda-id');
        if (!rondaId) {
            return;
        }

        const statusUrl = (typeof generateUrl === 'function')
            ? generateUrl('admin_training_ronda_status', { id: rondaId })
            : (window.location.origin + '/index.php?action=admin_training_ronda_status&id=' + encodeURIComponent(rondaId));

        let endedHandled = false;
        let inFlight = false;

        const fetchStatus = () => {
            if (inFlight || endedHandled) {
                return;
            }
            inFlight = true;
            fetch(statusUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then((r) => r.json())
                .then((payload) => {
                    if (!payload || !payload.success || !payload.data) {
                        return;
                    }
                    applyLiveStatus(payload.data);
                    if (payload.data.ended) {
                        endedHandled = true;
                        const indicator = document.getElementById('live-indicator');
                        if (indicator) {
                            indicator.hidden = true;
                        }
                        // Recargar para mostrar resumen/acciones finales.
                        window.location.reload();
                    }
                })
                .catch(() => { /* silencioso: reintenta en el siguiente tick */ })
                .finally(() => { inFlight = false; });
        };

        fetchStatus();
        setInterval(fetchStatus, 1000);
    }

    bindToggleButtons(document);
    startLivePolling();
})();
