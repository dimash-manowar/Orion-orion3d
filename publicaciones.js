document.addEventListener('DOMContentLoaded', () => {
    

    // Publicar / despublicar
    document.querySelectorAll('.toggle-estado').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const estado = btn.getAttribute('data-estado');
            const action = estado === "1" ? "publicar" : "despublicar";

            fetch(`<?= BASE_URL ?>adminPublicaciones/${action}/${id}`)
                .then(res => res.json())
                .then(res => {
                    Swal.fire({
                        title: res.success ? 'Actualizado' : 'Error',
                        text: res.message,
                        icon: res.success ? 'success' : 'error'
                    }).then(() => location.reload());
                });
        });
    });

    // Eliminar publicación
    document.querySelectorAll('.eliminar-post').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            Swal.fire({
                title: '¿Eliminar publicación?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch(`<?= BASE_URL ?>adminPublicaciones/eliminar/${id}`)
                        .then(res => res.json())
                        .then(res => {
                            Swal.fire({
                                title: res.success ? 'Eliminado' : 'Error',
                                text: res.message,
                                icon: res.success ? 'success' : 'error'
                            }).then(() => location.reload());
                        });
                }
            });
        });
    });
});