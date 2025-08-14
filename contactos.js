document.addEventListener('DOMContentLoaded', function () {
    // Capturar el envío del formulario
    document.getElementById('formContacto').addEventListener('submit', function (e) {
        e.preventDefault();

        let form = this;
        let formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                Swal.fire({
                    icon: data.status,
                    title: data.status === 'success' ? '¡Enviado!' : (data.status === 'warning' ? 'Atención' : 'Error'),
                    text: data.message
                });

                if (data.status === 'success') {
                    form.reset();
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Hubo un problema al enviar el mensaje.'
                });
            });
    });
});