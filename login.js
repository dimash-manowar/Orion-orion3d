document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('form[action$="Auth/login"]');
  if (!form) return;
  form.addEventListener('submit', (e) => {
    const ident = form.querySelector('input[name="nombre_usuario"]');
    const pass  = form.querySelector('input[name="password"]');
    if (!ident.value || !pass.value || pass.value.length < 8) {
      e.preventDefault();
      if (window.Swal) Swal.fire('Campos incompletos', 'Introduce tus credenciales (contraseña mínima 8).', 'info');
      else alert('Introduce tus credenciales (contraseña mínima 8).');
    }
  });
});
