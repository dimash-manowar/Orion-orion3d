document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('form[action$="Auth/register"]');
  if (!form) return;

  form.addEventListener('submit', (e) => {
    const email = form.querySelector('input[name="email"]');
    const user  = form.querySelector('input[name="nombre_usuario"]');
    const pass  = form.querySelector('input[name="password"]');

    const emailOk = !!email.value && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value);
    const userOk  = !!user.value && /^[a-zA-Z0-9_]{3,20}$/.test(user.value);
    const passOk  = !!pass.value && /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/.test(pass.value);

    if (!emailOk || !userOk || !passOk) {
      e.preventDefault();
      let msg = !emailOk ? 'Email inválido.' :
                !userOk  ? 'Usuario inválido (3-20, letras/números/_).' :
                'La contraseña no cumple los requisitos.';
      if (window.Swal) Swal.fire('Revisa el formulario', msg, 'warning');
      else alert(msg);
    }
  });
});
