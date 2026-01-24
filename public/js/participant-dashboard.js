// participant-dashboard.js

document.addEventListener('DOMContentLoaded', function() {
    const mainContent = document.getElementById('dynamic-content');
    const pageTitle = document.querySelector('h1');
    const selectedCpcIds = new Set();
    let availableCpcData = [];

    // Inicializar sistema PYR global
    initializePYRSystem();

    function loadContent(url) {
        fetch(url, { 
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (mainContent) {
                    mainContent.innerHTML = data.data.content;
                    if (data.data.title && pageTitle) pageTitle.textContent = data.data.title;
                    initializePageFunctionality();
                    updateActiveMenuItem(url);
                    
                    // El contenido de fase se maneja por sí mismo
                } else {
                    console.error('El elemento mainContent no se encontró');
                }
            } else {
                throw new Error(data.message || 'Error al cargar el contenido');
            }
        })
        .catch(error => {
            console.error('Error al cargar el contenido:', error);
            if (mainContent) {
                mainContent.innerHTML = `<p>Error al cargar el contenido: ${error.message}. Por favor, intente de nuevo.</p>`;
            }
        });
    }

    function initializePageFunctionality() {
        initializeAjaxForms();
        initializeSearchForm();
        initializeCpcPagination();
        initializeUserCpcSearch();
        initializeCpcModal();
        initializeTabs();
        initializePhaseLinks();
        initializeParticipantProductFilters();
        // PYR se inicializa cuando se carga el contenido dinámico
    }

    function initializeAjaxForms() {
        document.querySelectorAll('.ajax-form').forEach(form => {
            form.removeEventListener('submit', handleAjaxFormSubmit);
            form.addEventListener('submit', handleAjaxFormSubmit);
        });
    }

    function handleAjaxFormSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const action = form.getAttribute('action');

        fetch(action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (data.data && data.data.userCPCs) {
                    updateCPCLists(data.data);
                }
                alert('Operación realizada con éxito.');
            } else {
                throw new Error(data.message || 'Error en la operación');
            }
        })
        .catch(error => {
            console.error('Error en el envío del formulario:', error);
            alert(`Ocurrió un error al procesar la solicitud: ${error.message}`);
        });
    }

    function updateCPCLists(data) {
        const userCPCsBody = document.getElementById('user-cpcs-body');
        const cpcSelect = document.querySelector('select[name="cpc_id"]');

        if (userCPCsBody) {
            userCPCsBody.innerHTML = data.userCPCs.map(cpc => `
                <tr>
                    <td>${cpc.codigo}</td>
                    <td>${cpc.descripcion}</td>
                    <td>
                        <form class="ajax-form" action="${URLS.participantProfile()}" method="POST">
                            <input type="hidden" name="action" value="remove_cpc">
                            <input type="hidden" name="cpc_id" value="${cpc.id}">
                            <button type="submit" class="btn btn-small btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            `).join('');
            userCPCsBody.dataset.currentPage = '1';
        }

        if (cpcSelect && data.availableCPCs) {
            cpcSelect.innerHTML = data.availableCPCs.map(cpc => `
                <option value="${cpc.id}">${cpc.codigo} - ${cpc.descripcion}</option>
            `).join('');
        }

        if (data.availableCPCs) {
            setAvailableCpcData(data.availableCPCs);
        }

        initializeAjaxForms();
        initializeCpcPagination();
    }

    function initializeSearchForm() {
        const searchForm = document.getElementById('search-process-form');
        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                // Obtener la URL del formulario correctamente, evitando conflicto con name="action"
                const formAction = this.getAttribute('action');
                console.log('Form action URL:', formAction);
                
                fetch(formAction, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    const searchResultsContainer = document.getElementById('search-results');
                    if (searchResultsContainer) {
                        searchResultsContainer.innerHTML = data.success ? data.data.content : '<p>No se encontraron resultados.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error en la búsqueda:', error);
                    const searchResultsContainer = document.getElementById('search-results');
                    if (searchResultsContainer) {
                        searchResultsContainer.innerHTML = `<p>Error: ${error.message}</p>`;
                    }
                });
            });
        }
    }

    function initializeCpcPagination() {
        const list = document.getElementById('user-cpcs-body');
        const pagination = document.getElementById('user-cpcs-pagination');
        if (!list || !pagination) {
            return;
        }

        const searchInput = document.getElementById('user-cpcs-search');
        const needle = searchInput ? searchInput.value.trim().toLowerCase() : '';
        const items = Array.from(list.querySelectorAll('tr'));
        const filteredItems = needle === ''
            ? items
            : items.filter(row => {
                const descCell = row.cells[1];
                if (!descCell) {
                    return false;
                }
                return descCell.textContent.toLowerCase().includes(needle);
            });
        const pageSize = Math.max(1, parseInt(list.dataset.pageSize || '6', 10));
        const totalPages = Math.ceil(filteredItems.length / pageSize) || 1;
        let currentPage = parseInt(list.dataset.currentPage || '1', 10);
        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        const renderControls = () => {
            pagination.innerHTML = '';
            if (filteredItems.length <= pageSize) {
                return;
            }

            const prevBtn = document.createElement('button');
            prevBtn.type = 'button';
            prevBtn.className = 'pagination-link';
            prevBtn.textContent = 'Anterior';
            prevBtn.disabled = currentPage === 1;
            if (prevBtn.disabled) {
                prevBtn.classList.add('disabled');
            }
            prevBtn.addEventListener('click', () => renderPage(currentPage - 1));

            const info = document.createElement('span');
            info.className = 'pagination-info';
            info.textContent = `Página ${currentPage} de ${totalPages}`;

            const nextBtn = document.createElement('button');
            nextBtn.type = 'button';
            nextBtn.className = 'pagination-link';
            nextBtn.textContent = 'Siguiente';
            nextBtn.disabled = currentPage === totalPages;
            if (nextBtn.disabled) {
                nextBtn.classList.add('disabled');
            }
            nextBtn.addEventListener('click', () => renderPage(currentPage + 1));

            pagination.appendChild(prevBtn);
            pagination.appendChild(info);
            pagination.appendChild(nextBtn);
        };

        const renderPage = (page) => {
            const safePage = Math.min(Math.max(page, 1), totalPages);
            currentPage = safePage;
            list.dataset.currentPage = String(currentPage);

            const start = (currentPage - 1) * pageSize;
            const end = start + pageSize;
            items.forEach(item => {
                item.style.display = 'none';
            });

            filteredItems.slice(start, end).forEach(item => {
                item.style.display = '';
            });

            renderControls();
        };

        renderPage(currentPage);
    }

    function initializeUserCpcSearch() {
        const searchInput = document.getElementById('user-cpcs-search');
        const list = document.getElementById('user-cpcs-body');
        if (!searchInput || !list) {
            return;
        }

        searchInput.addEventListener('input', () => {
            list.dataset.currentPage = '1';
            initializeCpcPagination();
        });
    }

    function initializeCpcModal() {
        const modal = document.getElementById('cpc-modal');
        const openBtn = document.getElementById('open-cpc-modal');
        if (!modal || !openBtn) {
            return;
        }

        const isInitialized = modal.dataset.initialized === 'true';
        const closeBtn = document.getElementById('close-cpc-modal');
        const searchInput = document.getElementById('available-cpc-search');
        const addBtn = document.getElementById('add-selected-cpcs');
        const body = document.getElementById('available-cpcs-body');

        availableCpcData = collectAvailableCpcData();
        syncSelectedCpcs();
        renderAvailableCpcTable();

        const openModal = () => {
            modal.classList.add('is-open');
            document.body.classList.add('modal-open');
            renderAvailableCpcTable();
        };

        const closeModal = () => {
            modal.classList.remove('is-open');
            document.body.classList.remove('modal-open');
        };

        if (!isInitialized) {
            openBtn.addEventListener('click', openModal);
            if (closeBtn) {
                closeBtn.addEventListener('click', closeModal);
            }
            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    closeModal();
                }
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                    closeModal();
                }
            });

            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    if (body) {
                        body.dataset.currentPage = '1';
                    }
                    renderAvailableCpcTable();
                });
            }

            if (addBtn) {
                addBtn.addEventListener('click', submitSelectedCpcs);
            }

            modal.dataset.initialized = 'true';
        }
    }

    function collectAvailableCpcData() {
        const body = document.getElementById('available-cpcs-body');
        if (!body) {
            return [];
        }

        return Array.from(body.querySelectorAll('tr')).map(row => {
            const id = row.getAttribute('data-cpc-id') || '';
            const desc = row.getAttribute('data-cpc-desc') || '';
            const codeCell = row.cells[1];
            return {
                id: id.toString(),
                codigo: codeCell ? codeCell.textContent.trim() : '',
                descripcion: desc.toString()
            };
        });
    }

    function setAvailableCpcData(cpcs) {
        availableCpcData = Array.isArray(cpcs)
            ? cpcs.map(cpc => ({
                id: String(cpc.id),
                codigo: cpc.codigo,
                descripcion: cpc.descripcion
            }))
            : [];

        syncSelectedCpcs();

        const body = document.getElementById('available-cpcs-body');
        if (body) {
            body.dataset.currentPage = '1';
        }
        renderAvailableCpcTable();
    }

    function syncSelectedCpcs() {
        const availableIds = new Set(availableCpcData.map(cpc => cpc.id));
        Array.from(selectedCpcIds).forEach(id => {
            if (!availableIds.has(id)) {
                selectedCpcIds.delete(id);
            }
        });
    }

    function renderAvailableCpcTable() {
        const body = document.getElementById('available-cpcs-body');
        const pagination = document.getElementById('available-cpcs-pagination');
        const searchInput = document.getElementById('available-cpc-search');
        if (!body || !pagination) {
            return;
        }

        const needle = searchInput ? searchInput.value.trim().toLowerCase() : '';
        const filtered = needle === ''
            ? availableCpcData
            : availableCpcData.filter(cpc => cpc.descripcion.toLowerCase().includes(needle));

        const pageSize = Math.max(1, parseInt(body.dataset.pageSize || '6', 10));
        const totalPages = Math.ceil(filtered.length / pageSize) || 1;
        let currentPage = parseInt(body.dataset.currentPage || '1', 10);
        if (currentPage > totalPages) {
            currentPage = totalPages;
        }
        body.dataset.currentPage = String(currentPage);

        const start = (currentPage - 1) * pageSize;
        const end = start + pageSize;
        const pageItems = filtered.slice(start, end);

        if (pageItems.length === 0) {
            body.innerHTML = '<tr><td colspan="3" style="text-align:center; padding: 12px;">No hay CPCs disponibles.</td></tr>';
        } else {
            body.innerHTML = pageItems.map(cpc => `
                <tr data-cpc-id="${cpc.id}" data-cpc-desc="${cpc.descripcion}">
                    <td>
                        <input type="checkbox" class="cpc-select-checkbox" value="${cpc.id}" ${selectedCpcIds.has(cpc.id) ? 'checked' : ''}>
                    </td>
                    <td>${cpc.codigo}</td>
                    <td>${cpc.descripcion}</td>
                </tr>
            `).join('');
        }

        body.querySelectorAll('.cpc-select-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', (event) => {
                const id = event.target.value;
                if (event.target.checked) {
                    selectedCpcIds.add(id);
                } else {
                    selectedCpcIds.delete(id);
                }
            });
        });

        pagination.innerHTML = '';
        if (filtered.length > pageSize) {
            const prevBtn = document.createElement('button');
            prevBtn.type = 'button';
            prevBtn.className = 'pagination-link';
            prevBtn.textContent = 'Anterior';
            prevBtn.disabled = currentPage === 1;
            if (prevBtn.disabled) {
                prevBtn.classList.add('disabled');
            }
            prevBtn.addEventListener('click', () => {
                body.dataset.currentPage = String(currentPage - 1);
                renderAvailableCpcTable();
            });

            const info = document.createElement('span');
            info.className = 'pagination-info';
            info.textContent = `Página ${currentPage} de ${totalPages}`;

            const nextBtn = document.createElement('button');
            nextBtn.type = 'button';
            nextBtn.className = 'pagination-link';
            nextBtn.textContent = 'Siguiente';
            nextBtn.disabled = currentPage === totalPages;
            if (nextBtn.disabled) {
                nextBtn.classList.add('disabled');
            }
            nextBtn.addEventListener('click', () => {
                body.dataset.currentPage = String(currentPage + 1);
                renderAvailableCpcTable();
            });

            pagination.appendChild(prevBtn);
            pagination.appendChild(info);
            pagination.appendChild(nextBtn);
        }
    }

    function submitSelectedCpcs() {
        if (selectedCpcIds.size === 0) {
            alert('Seleccione al menos un CPC.');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'add_cpcs_bulk');
        selectedCpcIds.forEach(id => formData.append('cpc_ids[]', id));

        fetch(URLS.participantProfile(), {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (data.data && data.data.userCPCs) {
                    updateCPCLists(data.data);
                }
                selectedCpcIds.clear();
                const modal = document.getElementById('cpc-modal');
                if (modal) {
                    modal.classList.remove('is-open');
                    document.body.classList.remove('modal-open');
                }
                alert('CPCs agregados con éxito.');
            } else {
                throw new Error(data.message || 'Error en la operación');
            }
        })
        .catch(error => {
            console.error('Error al agregar CPCs:', error);
            alert(`Ocurrió un error al procesar la solicitud: ${error.message}`);
        });
    }

    function initializeParticipantProductFilters() {
        const container = document.getElementById('participant-products');
        if (!container) {
            return;
        }

        const searchInput = container.querySelector('#participant-product-search');
        const statusSelect = container.querySelector('#participant-status-filter');
        const table = container.querySelector('table');
        if (!searchInput || !statusSelect || !table) {
            return;
        }

        const rows = Array.from(table.querySelectorAll('tbody tr')).filter(row => !row.classList.contains('no-results-row'));

        const applyFilters = () => {
            const needle = searchInput.value.trim().toLowerCase();
            const estadoSeleccionado = statusSelect.value.trim();
            let visibleCount = 0;

            rows.forEach(row => {
                const objetoCell = row.cells[3];
                const estadoCell = row.cells[4];
                if (!objetoCell || !estadoCell) {
                    return;
                }

                const textoObjeto = objetoCell.textContent.toLowerCase();
                const textoEstado = estadoCell.textContent.trim();
                const coincideObjeto = needle === '' || textoObjeto.includes(needle);
                const coincideEstado = estadoSeleccionado === '' || textoEstado === estadoSeleccionado;
                const visible = coincideObjeto && coincideEstado;
                row.style.display = visible ? '' : 'none';
                if (visible) {
                    visibleCount++;
                }
            });

            const noResults = table.querySelector('.no-results-row');
            if ((needle !== '' || estadoSeleccionado !== '') && visibleCount === 0) {
                if (!noResults) {
                    const tr = document.createElement('tr');
                    tr.className = 'no-results-row';
                    tr.innerHTML = '<td colspan="6" style="text-align:center; padding: 16px;">No se encontraron procesos que coincidan.</td>';
                    table.tBodies[0].appendChild(tr);
                }
            } else if (noResults) {
                noResults.remove();
            }
        };

        searchInput.addEventListener('input', applyFilters);
        statusSelect.addEventListener('change', applyFilters);
    }

    function initializeTabs() {
        const tabLinks = document.querySelectorAll('.tab-links a');
        const tabContents = document.querySelectorAll('.tab-content .tab');

        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const tabId = this.getAttribute('href').substring(1);
                
                tabLinks.forEach(l => l.parentElement.classList.remove('active'));
                tabContents.forEach(tab => tab.classList.remove('active'));

                this.parentElement.classList.add('active');
                const targetTab = document.getElementById(tabId);
                if (targetTab) targetTab.classList.add('active');
            });
        });
    }

    function initializePhaseLinks() {
        console.log('Initializing phase links...');
        
        // Solo ejecutar si estamos en la página del producto
        if (!window.location.pathname.includes('/view-product/')) {
            console.log('Not on product page, skipping phase links initialization');
            return;
        }
        
        const phaseLinks = document.querySelectorAll('.process-phases .phase-link');
        const phaseContent = document.getElementById('phase-content');
        const productInfo = document.getElementById('product-info');
        const showDetailsBtn = document.getElementById('showDetailsBtn');

        console.log('Found phase links:', phaseLinks.length);
        console.log('Phase content element:', phaseContent);
        console.log('Product info element:', productInfo);
        console.log('Show details button:', showDetailsBtn);

        phaseLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // Interceptar TODOS los enlaces de fase para cargar contenido dinámico
                e.preventDefault();
                const phase = this.getAttribute('data-phase');
                console.log('Phase link clicked:', phase);
                if (phaseContent && productInfo) {
                    loadPhaseContent(phase, phaseContent, productInfo);
                    showDetailsBtn.style.display = 'inline-block'; // Muestra el botón
                } else {
                    console.error('Elementos de fase no encontrados');
                }
            });
        });

        if (showDetailsBtn) {
            showDetailsBtn.addEventListener('click', function() {
                productInfo.style.display = 'block';
                phaseContent.style.display = 'none';
                this.style.display = 'none'; // Oculta el botón
            });
        }
    }

    function loadPhaseContent(phase, phaseContainer, productInfoContainer) {
        console.log('Loading phase content for:', phase);
        console.log('URL:', URLS.participantPhase(phase));
        console.log('Phase container:', phaseContainer);
        console.log('Product info container:', productInfoContainer);
        
        fetch(URLS.participantPhase(phase), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Respuesta del servidor:', response.status, response.statusText, text);
                    throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                productInfoContainer.style.display = 'none';
                phaseContainer.innerHTML = data.content;
                phaseContainer.style.display = 'block';
                executeInlineScripts(phaseContainer);
            } else {
                throw new Error(data.message || 'Error al cargar la fase');
            }
        })
        .catch(error => {
            console.error('Error al cargar la fase:', error);
            phaseContainer.innerHTML = `<p>Error al cargar la fase: ${error.message}</p>`;
            phaseContainer.style.display = 'block';
        });
    }

    function executeInlineScripts(container) {
        if (!container) {
            return;
        }

        const scripts = container.querySelectorAll('script');
        scripts.forEach(script => {
            const newScript = document.createElement('script');
            if (script.src) {
                newScript.src = script.src;
            } else {
                newScript.textContent = script.textContent;
            }
            document.body.appendChild(newScript);
            document.body.removeChild(newScript);
        });
    }

    function updateActiveMenuItem(url) {
        document.querySelectorAll('.sidebar-menu a').forEach(link => {
            link.classList.toggle('active', link.getAttribute('href') === url);
        });
    }

    document.querySelectorAll('.sidebar-menu a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            loadContent(url);
            history.pushState(null, '', url);
        });
    });

    function initializePYRFunctionality() {
        console.log('PARTICIPANT-DASHBOARD - Initializing PYR functionality');
        
        // Buscar botones de debug
        const debugGetBtn = document.getElementById('debug-get-btn');
        const debugSubmitBtn = document.getElementById('debug-submit-btn');
        
        if (debugGetBtn) {
            console.log('PARTICIPANT-DASHBOARD - Debug GET button found');
            debugGetBtn.onclick = function() {
                console.log('PARTICIPANT-DASHBOARD - Debug GET button clicked');
                testGetQuestions();
            };
        }
        
        if (debugSubmitBtn) {
            console.log('PARTICIPANT-DASHBOARD - Debug Submit button found');
            debugSubmitBtn.onclick = function() {
                console.log('PARTICIPANT-DASHBOARD - Debug Submit button clicked');
                testSubmitQuestion();
            };
        }
        
        // Buscar formulario de pregunta
        const preguntaForm = document.getElementById('pregunta-form');
        if (preguntaForm) {
            console.log('PARTICIPANT-DASHBOARD - Pregunta form found');
            initializePreguntaForm();
        }
        
        // Cargar preguntas iniciales
        loadPreguntas(1);
    }
    
    function testGetQuestions() {
        console.log('PARTICIPANT-DASHBOARD - Testing GET questions...');
        const productId = getProductIdFromURL();
        const url = `/subs/participant/get-questions?producto_id=${productId}&page=1&limit=5`;
        console.log('PARTICIPANT-DASHBOARD - URL:', url);
        
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            console.log('PARTICIPANT-DASHBOARD - Response status:', response.status);
            return response.text();
        })
        .then(text => {
            console.log('PARTICIPANT-DASHBOARD - Response text:', text);
            const debugResult = document.getElementById('debug-result');
            if (debugResult) {
                debugResult.innerHTML = '<pre>' + text + '</pre>';
            }
        })
        .catch(error => {
            console.error('PARTICIPANT-DASHBOARD - Error:', error);
            const debugResult = document.getElementById('debug-result');
            if (debugResult) {
                debugResult.innerHTML = '<p>Error: ' + error.message + '</p>';
            }
        });
    }
    
    function testSubmitQuestion() {
        console.log('PARTICIPANT-DASHBOARD - Testing POST submit question...');
        const productId = getProductIdFromURL();
        const url = '/subs/participant/submit-question';
        const body = `producto_id=${productId}&pregunta=Test question from participant-dashboard.js`;
        console.log('PARTICIPANT-DASHBOARD - URL:', url);
        console.log('PARTICIPANT-DASHBOARD - Body:', body);
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body
        })
        .then(response => {
            console.log('PARTICIPANT-DASHBOARD - Response status:', response.status);
            return response.text();
        })
        .then(text => {
            console.log('PARTICIPANT-DASHBOARD - Response text:', text);
            const debugResult = document.getElementById('debug-result');
            if (debugResult) {
                debugResult.innerHTML = '<pre>' + text + '</pre>';
            }
        })
        .catch(error => {
            console.error('PARTICIPANT-DASHBOARD - Error:', error);
            const debugResult = document.getElementById('debug-result');
            if (debugResult) {
                debugResult.innerHTML = '<p>Error: ' + error.message + '</p>';
            }
        });
    }
    
    function getProductIdFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        const pathParts = window.location.pathname.split('/');
        const productId = pathParts[pathParts.length - 1];
        return productId || '1';
    }
    
    function initializePreguntaForm() {
        const form = document.getElementById('pregunta-form');
        const textarea = form ? form.querySelector('textarea') : null;
        const charCount = document.getElementById('char-count');
        
        if (!form || !textarea) {
            console.log('PARTICIPANT-DASHBOARD - Form or textarea not found');
            return;
        }
        
        console.log('PARTICIPANT-DASHBOARD - Initializing pregunta form');
        
        // Contador de caracteres
        textarea.oninput = function() {
            const count = this.value.length;
            if (charCount) {
                charCount.textContent = count;
                charCount.style.color = count > 450 ? '#ff6b6b' : '#666';
            }
        };
        
        // Envío del formulario
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
    
    function submitPregunta(pregunta) {
        console.log('PARTICIPANT-DASHBOARD - Submitting pregunta:', pregunta);
        const productId = getProductIdFromURL();
        const url = '/subs/participant/submit-question';
        const body = `producto_id=${productId}&pregunta=${encodeURIComponent(pregunta)}`;
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body
        })
        .then(response => {
            console.log('PARTICIPANT-DASHBOARD - Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('PARTICIPANT-DASHBOARD - Response data:', data);
            if (data.success) {
                alert('Pregunta enviada exitosamente');
                loadPreguntas(1);
            } else {
                alert('Error al enviar la pregunta: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('PARTICIPANT-DASHBOARD - Error:', error);
            alert('Error al enviar la pregunta: ' + error.message);
        });
    }
    
    function loadPreguntas(page = 1) {
        console.log('PARTICIPANT-DASHBOARD - Loading preguntas, page:', page);
        const productId = getProductIdFromURL();
        const url = `/subs/participant/get-questions?producto_id=${productId}&page=${page}&limit=5`;
        
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            console.log('PARTICIPANT-DASHBOARD - Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('PARTICIPANT-DASHBOARD - Response data:', data);
            if (data.success) {
                displayPreguntas(data.preguntas);
                displayPagination(data.pagination);
            } else {
                const listaPreguntas = document.getElementById('lista-preguntas');
                if (listaPreguntas) {
                    listaPreguntas.innerHTML = '<p>Error al cargar las preguntas</p>';
                }
            }
        })
        .catch(error => {
            console.error('PARTICIPANT-DASHBOARD - Error:', error);
            const listaPreguntas = document.getElementById('lista-preguntas');
            if (listaPreguntas) {
                listaPreguntas.innerHTML = '<p>Error al cargar las preguntas</p>';
            }
        });
    }
    
    function displayPreguntas(preguntas) {
        const listaPreguntas = document.getElementById('lista-preguntas');
        if (!listaPreguntas) return;
        
        if (preguntas.length === 0) {
            listaPreguntas.innerHTML = '<p>No hay preguntas aún. ¡Sé el primero en preguntar!</p>';
            return;
        }
        
        let html = '';
        preguntas.forEach(pregunta => {
            html += `
                <div class="pregunta-item">
                    <div class="pregunta-header">
                        <strong>${pregunta.nombre_usuario}</strong>
                        <span class="fecha">${new Date(pregunta.fecha_pregunta).toLocaleString()}</span>
                    </div>
                    <div class="pregunta-texto">${pregunta.pregunta}</div>
                    ${pregunta.respuesta ? `
                        <div class="respuesta">
                            <strong>Respuesta:</strong>
                            <div class="respuesta-texto">${pregunta.respuesta}</div>
                            <div class="fecha-respuesta">Respondida el: ${new Date(pregunta.fecha_respuesta).toLocaleString()}</div>
                        </div>
                    ` : '<div class="sin-respuesta">Sin respuesta aún</div>'}
                </div>
            `;
        });
        
        listaPreguntas.innerHTML = html;
    }
    
    function displayPagination(paginationData) {
        const pagination = document.getElementById('pagination');
        if (!pagination) return;
        
        if (paginationData.totalPages <= 1) {
            pagination.innerHTML = '';
            return;
        }
        
        let html = '<div class="pagination-controls">';
        
        if (paginationData.currentPage > 1) {
            html += `<button onclick="loadPreguntas(${paginationData.currentPage - 1})" class="btn-pagination">Anterior</button>`;
        }
        
        html += `<span>Página ${paginationData.currentPage} de ${paginationData.totalPages}</span>`;
        
        if (paginationData.currentPage < paginationData.totalPages) {
            html += `<button onclick="loadPreguntas(${paginationData.currentPage + 1})" class="btn-pagination">Siguiente</button>`;
        }
        
        html += '</div>';
        pagination.innerHTML = html;
    }
    
    // Hacer funciones globales
    window.loadPreguntas = loadPreguntas;

    window.addEventListener('popstate', function() {
        loadContent(window.location.pathname);
    });

    initializePageFunctionality();
    
    // NO cargar contenido automáticamente si estamos en la página de detalle del producto
    // porque eso interfiere con la carga de fases
    if (!window.location.pathname.includes('/view-product/')) {
    loadContent(window.location.pathname);
    }
});

// ===== SISTEMA PYR GLOBAL =====
function initializePYRSystem() {
    console.log('PYR-SYSTEM - Initializing global PYR system');
    
    // Detectar cuando se carga contenido dinámico
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                // Verificar si se cargó contenido PYR
                const pyrContent = document.querySelector('#pyr-container');
                if (pyrContent) {
                    console.log('PYR-SYSTEM - PYR content detected, initializing...');
                    setTimeout(initializePYRContent, 100);
                }
            }
        });
    });
    
    // Observar cambios en el contenido dinámico
    const dynamicContent = document.getElementById('dynamic-content');
    if (dynamicContent) {
        observer.observe(dynamicContent, { childList: true, subtree: true });
    }
    
    // También verificar inmediatamente si ya hay contenido PYR
    setTimeout(checkForPYRContent, 200);
}

// Verificar si ya existe contenido PYR
function checkForPYRContent() {
    const pyrContent = document.querySelector('#pyr-container');
    if (pyrContent) {
        console.log('PYR-SYSTEM - PYR content already present, initializing...');
        initializePYRContent();
    }
}

// Inicializar contenido PYR
function initializePYRContent() {
    console.log('PYR-SYSTEM - Initializing PYR content');
    
    // Obtener ID del producto
    const productId = getProductIdFromURL();
    console.log('PYR-SYSTEM - Product ID:', productId);
    
    // Inicializar botones de debug
    initializeDebugButtons();
    
    // Inicializar formulario de pregunta
    initializePreguntaForm();
    
    // Cargar preguntas iniciales
    loadPreguntas(1);
}

// Obtener ID del producto desde la URL
function getProductIdFromURL() {
    const pathParts = window.location.pathname.split('/');
    const productId = pathParts[pathParts.length - 1];
    return productId || '1';
}

// Inicializar botones de debug
function initializeDebugButtons() {
    
    // Botón GET Questions
    const debugGetBtn = document.getElementById('debug-get-btn');
    if (debugGetBtn) {
        console.log('PYR-SYSTEM - Debug GET button found');
        debugGetBtn.onclick = function() {
            console.log('PYR-SYSTEM - Debug GET button clicked');
            testGetQuestions();
        };
    }
    
    // Botón Submit Question
    const debugSubmitBtn = document.getElementById('debug-submit-btn');
    if (debugSubmitBtn) {
        console.log('PYR-SYSTEM - Debug Submit button found');
        debugSubmitBtn.onclick = function() {
            console.log('PYR-SYSTEM - Debug Submit button clicked');
            testSubmitQuestion();
        };
    }
}

// Inicializar formulario de pregunta
function initializePreguntaForm() {
    const form = document.getElementById('pregunta-form');
    const textarea = form ? form.querySelector('textarea') : null;
    const charCount = document.getElementById('char-count');
    
    if (!form || !textarea) {
        console.log('PYR-SYSTEM - Form or textarea not found');
        return;
    }
    
    console.log('PYR-SYSTEM - Initializing pregunta form');
    
    // Contador de caracteres
    textarea.oninput = function() {
        const count = this.value.length;
        if (charCount) {
            charCount.textContent = count;
            charCount.style.color = count > 450 ? '#ff6b6b' : '#666';
        }
    };
    
    // Envío del formulario
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

// Test GET Questions
function testGetQuestions() {
    console.log('PYR-SYSTEM - Testing GET questions...');
    const productId = getProductIdFromURL();
    const url = `/subs/participant/get-questions?producto_id=${productId}&page=1&limit=5`;
    
    showDebugLog('Testing GET questions...');
    showDebugLog('GET URL: ' + url);
    
    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        console.log('PYR-SYSTEM - GET Response status:', response.status);
        showDebugLog('GET Response status: ' + response.status);
        return response.text();
    })
    .then(text => {
        console.log('PYR-SYSTEM - GET Response text:', text);
        showDebugLog('GET Response text: ' + text.substring(0, 200) + '...');
        try {
            const data = JSON.parse(text);
            if (data.success) {
                console.log('PYR-SYSTEM - GET Questions successful:', data.data);
                showDebugLog('GET Questions successful: ' + data.data.questions.length + ' questions');
                displayPreguntas(data.data.questions);
                displayPagination(data.data.pagination);
            } else {
                console.error('PYR-SYSTEM - GET Questions failed:', data.message);
                showDebugLog('GET Questions failed: ' + data.message);
            }
        } catch (e) {
            console.error('PYR-SYSTEM - GET Response parse error:', e);
            showDebugLog('GET Response parse error: ' + e.message);
        }
    })
    .catch(error => {
        console.error('PYR-SYSTEM - GET Questions error:', error);
        showDebugLog('GET Questions error: ' + error.message);
    });
}

// Test Submit Question
function testSubmitQuestion() {
    console.log('PYR-SYSTEM - Testing POST submit question...');
    const productId = getProductIdFromURL();
    const url = '/subs/participant/submit-question';
    
    showDebugLog('Testing POST submit question...');
    showDebugLog('POST URL: ' + url);
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `producto_id=${productId}&pregunta=Test question from PYR-SYSTEM`
    })
    .then(response => {
        console.log('PYR-SYSTEM - POST Response status:', response.status);
        showDebugLog('POST Response status: ' + response.status);
        return response.text();
    })
    .then(text => {
        console.log('PYR-SYSTEM - POST Response text:', text);
        showDebugLog('POST Response text: ' + text);
        try {
            const data = JSON.parse(text);
            if (data.success) {
                console.log('PYR-SYSTEM - POST Submit successful:', data.message);
                showDebugLog('POST Submit successful: ' + data.message);
            } else {
                console.error('PYR-SYSTEM - POST Submit failed:', data.message);
                showDebugLog('POST Submit failed: ' + data.message);
            }
        } catch (e) {
            console.error('PYR-SYSTEM - POST Response parse error:', e);
            showDebugLog('POST Response parse error: ' + e.message);
        }
    })
    .catch(error => {
        console.error('PYR-SYSTEM - POST Submit error:', error);
        showDebugLog('POST Submit error: ' + error.message);
    });
}

// Enviar pregunta
function submitPregunta(pregunta) {
    console.log('PYR-SYSTEM - Submitting pregunta:', pregunta);
    const productId = getProductIdFromURL();
    const url = '/subs/participant/submit-question';
    
    showDebugLog('Submitting pregunta: ' + pregunta);
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `producto_id=${productId}&pregunta=${encodeURIComponent(pregunta)}`
    })
    .then(response => {
        console.log('PYR-SYSTEM - Submit Response status:', response.status);
        showDebugLog('Submit Response status: ' + response.status);
        return response.text();
    })
    .then(text => {
        console.log('PYR-SYSTEM - Submit Response text:', text);
        showDebugLog('Submit Response text: ' + text);
        try {
            const data = JSON.parse(text);
            if (data.success) {
                console.log('PYR-SYSTEM - Pregunta enviada exitosamente');
                showDebugLog('Pregunta enviada exitosamente');
                alert('Pregunta enviada exitosamente');
                // Recargar preguntas
                loadPreguntas(1);
            } else {
                console.error('PYR-SYSTEM - Error al enviar pregunta:', data.message);
                showDebugLog('Error al enviar pregunta: ' + data.message);
                alert('Error al enviar pregunta: ' + data.message);
            }
        } catch (e) {
            console.error('PYR-SYSTEM - Submit Response parse error:', e);
            showDebugLog('Submit Response parse error: ' + e.message);
            alert('Error al procesar respuesta del servidor');
        }
    })
    .catch(error => {
        console.error('PYR-SYSTEM - Submit error:', error);
        showDebugLog('Submit error: ' + error.message);
        alert('Error de conexión al enviar pregunta');
    });
}

// Cargar preguntas
function loadPreguntas(page) {
    console.log('PYR-SYSTEM - Loading preguntas, page:', page);
    const productId = getProductIdFromURL();
    const url = `/subs/participant/get-questions?producto_id=${productId}&page=${page}&limit=5`;
    
    showDebugLog('Loading preguntas, page: ' + page);
    
    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        console.log('PYR-SYSTEM - Load Response status:', response.status);
        showDebugLog('Load Response status: ' + response.status);
        return response.text();
    })
    .then(text => {
        console.log('PYR-SYSTEM - Load Response text:', text);
        showDebugLog('Load Response text: ' + text.substring(0, 200) + '...');
        try {
            const data = JSON.parse(text);
            if (data.success) {
                console.log('PYR-SYSTEM - Questions loaded successfully:', data.data);
                showDebugLog('Questions loaded successfully: ' + data.data.questions.length + ' questions');
                displayPreguntas(data.data.questions);
                displayPagination(data.data.pagination);
            } else {
                console.error('PYR-SYSTEM - Load questions failed:', data.message);
                showDebugLog('Load questions failed: ' + data.message);
            }
        } catch (e) {
            console.error('PYR-SYSTEM - Load Response parse error:', e);
            showDebugLog('Load Response parse error: ' + e.message);
        }
    })
    .catch(error => {
        console.error('PYR-SYSTEM - Load questions error:', error);
        showDebugLog('Load questions error: ' + error.message);
    });
}

// Mostrar preguntas
function displayPreguntas(questions) {
    console.log('PYR-SYSTEM - Displaying questions:', questions);
    const container = document.getElementById('preguntas-container');
    if (!container) {
        console.log('PYR-SYSTEM - Questions container not found');
        showDebugLog('Questions container not found');
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

// Mostrar paginación
function displayPagination(pagination) {
    console.log('PYR-SYSTEM - Displaying pagination:', pagination);
    const container = document.getElementById('pagination-container');
    if (!container) {
        console.log('PYR-SYSTEM - Pagination container not found');
        showDebugLog('Pagination container not found');
        return;
    }
    
    if (pagination.totalPages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<div class="pagination">';
    
    // Botón anterior
    if (pagination.currentPage > 1) {
        html += `<button onclick="loadPreguntas(${pagination.currentPage - 1})">Anterior</button>`;
    }
    
    // Números de página
    for (let i = 1; i <= pagination.totalPages; i++) {
        if (i === pagination.currentPage) {
            html += `<button class="active">${i}</button>`;
        } else {
            html += `<button onclick="loadPreguntas(${i})">${i}</button>`;
        }
    }
    
    // Botón siguiente
    if (pagination.currentPage < pagination.totalPages) {
        html += `<button onclick="loadPreguntas(${pagination.currentPage + 1})">Siguiente</button>`;
    }
    
    html += '</div>';
    container.innerHTML = html;
}

// Función para mostrar logs en la interfaz
function showDebugLog(message) {
    const debugLog = document.getElementById('debug-log');
    if (debugLog) {
        const timestamp = new Date().toLocaleTimeString();
        debugLog.innerHTML += '<div>' + timestamp + ': ' + message + '</div>';
        debugLog.scrollTop = debugLog.scrollHeight;
    }
}

// Hacer funciones globales
window.testGetQuestions = testGetQuestions;
window.testSubmitQuestion = testSubmitQuestion;
window.submitPregunta = submitPregunta;
window.loadPreguntas = loadPreguntas;
window.showDebugLog = showDebugLog;

console.log('PYR-SYSTEM - Global functions registered');