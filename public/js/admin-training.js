(function () {
    document.querySelectorAll('.btn-toggle-ins').forEach((btn) => {
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
})();
