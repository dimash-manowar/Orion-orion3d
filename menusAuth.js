// Assets/js/menusAuth.js
(function () {
  document.addEventListener('click', function (e) {
    if (window.IS_AUTH) return; // si ya está logueado, no interceptamos

    const a = e.target.closest('a.need-auth');
    if (!a) return;

    e.preventDefault();
    const destino = a.dataset.target || a.textContent.trim() || 'esta sección';

    if (window.Swal) {
      Swal.fire({
        icon: 'info',
        title: 'Necesitas iniciar sesión',
        html: `Para acceder a <b>${destino}</b> debes iniciar sesión o crear una cuenta.`,
        showDenyButton: true,
        showCancelButton: true,
        confirmButtonText: 'Entrar',
        denyButtonText: 'Registrarse',
        cancelButtonText: 'Seguir explorando',
        reverseButtons: true
      }).then(res => {
        if (res.isConfirmed) {
          window.location.href = window.BASE_URL + 'Auth';
        } else if (res.isDenied) {
          window.location.href = window.BASE_URL + 'Auth/register';
        }
      });
    } else {
      // fallback sin SweetAlert
      if (confirm('Necesitas iniciar sesión. ¿Ir a la página de login?')) {
        window.location.href = window.BASE_URL + 'Auth';
      }
    }
  });
})();
