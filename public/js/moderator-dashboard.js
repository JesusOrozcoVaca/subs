document.addEventListener('DOMContentLoaded', function() {
    const dynamicContent = document.getElementById('dynamic-content');

    function loadContent(url) {
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const dynamicContent = doc.querySelector('#dynamic-content');
            if (dynamicContent) {
                document.getElementById('dynamic-content').innerHTML = dynamicContent.innerHTML;
            } else {
                document.getElementById('dynamic-content').innerHTML = html;
            }
            initListeners();
        })
        .catch(error => {
            console.error('Error al cargar el contenido:', error);
            document.getElementById('dynamic-content').innerHTML = '<p>Error al cargar el contenido. Por favor, intente de nuevo.</p>';
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

    function initEditCPCListeners() {
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                loadContent(url);
            });
        });
    }

    function initDeleteCPCListeners() {
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                if (confirm('¿Está seguro de eliminar este CPC?')) {
                    deleteCPC(id);
                }
            });
        });
    }
    
    function deleteCPC(id) {
        fetch(window.BASE_URL + 'moderator/manage-cpcs', {  // Cambiado de '/subs/moderator/delete-cpc'
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `id=${id}&action=delete`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                loadContent(window.BASE_URL + 'moderator/manage-cpcs');
            } else {
                alert(data.message || 'Error al eliminar el CPC');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    }

    function initAddCPCForm() {
        const form = document.getElementById('add-cpc-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                
                fetch(window.BASE_URL + 'moderator/manage-cpcs', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        loadContent(window.BASE_URL + 'moderator/manage-cpcs');
                    } else {
                        alert(data.message || 'Error al agregar el CPC');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud');
                });
            });
        }
    }



    function initListeners() {
        // Aquí puedes agregar inicializaciones específicas para el moderador
        initEditCPCListeners();
        initDeleteCPCListeners();
        initAddCPCForm();
    }

    // No necesitamos cargar el contenido inicial aquí, ya que se carga en PHP
});