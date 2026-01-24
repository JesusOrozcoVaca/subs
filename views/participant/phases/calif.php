<h2>Calificación</h2>
<div id="calificacion-container">
    <p>Aquí el usuario verá la calificación que se le ha asignado para el presente proceso.</p>
    <div id="calificacion-detalle" class="calificacion-detalle">
        Cargando calificación...
    </div>
</div>

<script>
(function() {
    const container = document.getElementById('calificacion-detalle');
    if (!container) {
        return;
    }

    function escapeHtml(value) {
        if (typeof value !== 'string') {
            return '';
        }
        return value
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    const productId = <?php echo (int)$product['id']; ?>;
    const url = generateUrl('participant_get_offer_rating', { producto_id: productId });

    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            container.innerHTML = '<p>Aún no se ha publicado una calificación para su oferta.</p>';
            return;
        }

        const rating = data.data && data.data.rating ? data.data.rating : null;
        if (!rating) {
            container.innerHTML = '<p>Aún no se ha publicado una calificación para su oferta.</p>';
            return;
        }

        const comentario = rating.comentario ? escapeHtml(rating.comentario) : 'Sin comentario';
        container.innerHTML = `
            <div class="card" style="padding: 15px;">
                <p><strong>Estado:</strong> ${escapeHtml(rating.calificacion || '')}</p>
                <p><strong>Comentario:</strong> ${comentario}</p>
            </div>
        `;
    })
    .catch(() => {
        container.innerHTML = '<p>Aún no se ha publicado una calificación para su oferta.</p>';
    });
})();
</script>