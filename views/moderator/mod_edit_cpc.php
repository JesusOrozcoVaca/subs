<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar CPC - Moderador</title>
    <link rel="stylesheet" href="<?php echo css('styles.css'); ?>">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Moderación</h2>
            <ul class="sidebar-menu">
                <li><a href="<?php echo url('moderator/dashboard'); ?>" data-target="dashboard">Dashboard</a></li>
                <li><a href="<?php echo url('moderator/manage-cpcs'); ?>" data-target="manage-cpcs">Gestionar CPCs</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <header class="dashboard-header">
                <h1>Editar CPC</h1>
                <form action="<?php echo logout_url(); ?>" method="POST">
                    <button type="submit" class="logout-btn">Cerrar sesión</button>
                </form>
            </header>

            <div id="dynamic-content">
                <?php include __DIR__ . '/mod_edit_cpc_content.php'; ?>
            </div>
        </main>
    </div>

    <script src="<?php echo js('url-helper.js'); ?>"></script>
    <script src="<?php echo js('moderator-dashboard.js'); ?>"></script>
    
    <script>
    // Función para manejar el formulario de edición de CPC
    function setupEditForm() {
        console.log('Configurando formulario de edición de CPC...');
        
        const editForm = document.getElementById('edit-cpc-form');
        console.log('Formulario encontrado:', editForm);
        
        if (editForm) {
            // Remover event listeners existentes
            const newForm = editForm.cloneNode(true);
            editForm.parentNode.replaceChild(newForm, editForm);
            
            newForm.addEventListener('submit', function(e) {
                console.log('Formulario enviado, previniendo envío por defecto...');
                e.preventDefault();
                e.stopPropagation();
                
                const formData = new FormData(this);
                const submitButton = this.querySelector('button[type="submit"]');
                
                console.log('Enviando datos AJAX...');
                
                // Deshabilitar botón durante el envío
                submitButton.disabled = true;
                submitButton.textContent = 'Actualizando...';
                
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    console.log('Respuesta recibida:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Datos JSON:', data);
                    if (data.success) {
                        alert(data.message);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud. Por favor, intente de nuevo.');
                })
                .finally(() => {
                    // Rehabilitar botón
                    submitButton.disabled = false;
                    submitButton.textContent = 'Actualizar CPC';
                });
                
                return false;
            });
            
            console.log('Event listener agregado al formulario');
        } else {
            console.error('No se encontró el formulario con ID edit-cpc-form');
        }
    }
    
    // Ejecutar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupEditForm);
    } else {
        setupEditForm();
    }
    </script>
</body>
</html>