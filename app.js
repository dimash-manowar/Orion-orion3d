document.addEventListener('DOMContentLoaded', () => {
    const bPend = document.getElementById('badge-pendientes');
  const bFav  = document.getElementById('badge-favoritos');
  if (!bPend && !bFav) return;

  fetch(`${window.BASE_URL}Usuario/badges`, { credentials: 'same-origin' })
    .then(r => r.ok ? r.json() : null)
    .then(d => {
      if (!d) return;
      if (bPend && d.pendientes > 0) { bPend.textContent = d.pendientes; bPend.style.display = 'inline-block'; }
      if (bFav  && d.favoritos  > 0) { bFav.textContent  = d.favoritos;  bFav.style.display  = 'inline-block'; }
    })
    .catch(()=>{});
    // Bloquea menús si no hay sesión y muestra aviso
    document.addEventListener('click', (e) => {
        const a = e.target.closest('a.need-auth');
        if (a && !window.IS_AUTH) {
            e.preventDefault();
            const target = a.getAttribute('data-target') || 'esta sección';
            if (window.Swal) {
                Swal.fire({
                    title: 'Inicia sesión',
                    text: `Para acceder a ${target}, primero inicia sesión o regístrate.`,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Ir a iniciar sesión',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#6ea8fe'
                }).then(r => {
                    if (r.isConfirmed) location.href = window.BASE_URL + 'Auth/login';
                });
            } else {
                location.href = window.BASE_URL + 'Auth/login';
            }
        }
    });

    // Bienvenida tras login/registro (?bienvenido=1)
    window.addEventListener('DOMContentLoaded', () => {
        try {
            const params = new URLSearchParams(location.search);
            if ((window.SHOW_WELCOME || params.has('bienvenido')) && window.WELCOME_NAME && window.Swal) {
                Swal.fire({
                    title: `¡Bienvenido, ${window.WELCOME_NAME}!`,
                    text: 'Ya puedes explorar Orion3D.',
                    toast: true, position: 'top-end', timer: 2800, showConfirmButton: false, icon: 'success'
                });
            }
        } catch (e) { }
    });
});
