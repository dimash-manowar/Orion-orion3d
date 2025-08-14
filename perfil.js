// Assets/js/perfil.js
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form[action$="Usuario/perfil"]');
    if (!form) return;

    const nombreEl = form.querySelector('input[name="nombre"]');
    const apellidoEl = form.querySelector('input[name="apellido"]');
    const emailEl = form.querySelector('input[name="email"]');
    const passEl = form.querySelector('input[name="password"]');
    const fotoEl = form.querySelector('input[name="foto"]');

    form.addEventListener('submit', (e) => {
        const nombre = (nombreEl?.value || '').trim();
        const apellido = (apellidoEl?.value || '').trim();
        const email = (emailEl?.value || '').trim();
        const pass = passEl?.value || '';

        // Nombre y apellido (solo letras, espacios y guiones, 2–60)
        if (!/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s\-]{2,60}$/.test(nombre)) {
            return stop(e, 'Nombre inválido. Usa solo letras, espacios o guiones (2–60).');
        }
        if (!/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s\-]{2,60}$/.test(apellido)) {
            return stop(e, 'Apellido inválido. Usa solo letras, espacios o guiones (2–60).');
        }

        // Email válido
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            return stop(e, 'Email inválido.');
        }

        // Password (solo si el usuario escribió algo)
        if (pass.length) {
            const strong = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
            if (!strong.test(pass)) {
                return stop(e, 'La nueva contraseña debe tener 8+ caracteres con mayúscula, minúscula, número y símbolo.');
            }
        }

        // Foto (opcional): tipo y tamaño ≤ 2MB
        if (fotoEl && fotoEl.files && fotoEl.files[0]) {
            const f = fotoEl.files[0];
            const okType = ['image/jpeg', 'image/png', 'image/webp'].includes(f.type);
            const okSize = f.size <= 2 * 1024 * 1024;
            if (!okType) return stop(e, 'Formato de imagen no permitido. Solo JPG, PNG o WebP.');
            if (!okSize) return stop(e, 'La imagen no puede superar los 2MB.');
        }
    });

    function stop(e, msg) {
        e.preventDefault();
        if (window.Swal) Swal.fire('Revisa el formulario', msg, 'warning');
        else alert(msg);
    }
    // ... dentro de DOMContentLoaded
    if (emailEl) {
        let t;
        emailEl.addEventListener('input', () => {
            clearTimeout(t);
            const email = emailEl.value.trim();
            if (!email) return;
            // Debounce 400ms
            t = setTimeout(async () => {
                try {
                    const url = `${window.BASE_URL}Perfil/checkEmail?email=${encodeURIComponent(email)}`;
                    const r = await fetch(url, { credentials: 'same-origin' });
                    if (!r.ok) return;
                    const { exists } = await r.json();
                    emailEl.classList.toggle('is-invalid', !!exists);
                    emailEl.classList.toggle('is-valid', !exists);
                    if (exists && window.Swal) {
                        Swal.fire({ icon: 'warning', title: 'Email en uso', text: 'Ese email ya está registrado por otro usuario.' });
                    }
                } catch (e) { }
            }, 400);
        });
    }

});
