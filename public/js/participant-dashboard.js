// participant-dashboard.js

document.addEventListener('DOMContentLoaded', function() {
    const mainContent = document.getElementById('dynamic-content');
    const pageTitle = document.querySelector('h1');

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
        initializeTabs();
        initializePhaseLinks();
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
        const userCPCsList = document.getElementById('user-cpcs-list');
        const cpcSelect = document.querySelector('select[name="cpc_id"]');

        if (userCPCsList && cpcSelect) {
            userCPCsList.innerHTML = data.userCPCs.map(cpc => `
                <li>
                    ${cpc.codigo} - ${cpc.descripcion}
                    <form class="ajax-form" action="${URLS.participantProfile()}" method="POST">
                        <input type="hidden" name="action" value="remove_cpc">
                        <input type="hidden" name="cpc_id" value="${cpc.id}">
                        <button type="submit" class="btn btn-small btn-danger">Eliminar</button>
                    </form>
                </li>
            `).join('');

            cpcSelect.innerHTML = data.availableCPCs.map(cpc => `
                <option value="${cpc.id}">${cpc.codigo} - ${cpc.descripcion}</option>
            `).join('');

            initializeAjaxForms();
        }
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

    window.addEventListener('popstate', function() {
        loadContent(window.location.pathname);
    });

    initializePageFunctionality();
    loadContent(window.location.pathname);
});