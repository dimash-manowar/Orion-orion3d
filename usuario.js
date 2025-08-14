// Assets/js/usuario.js
document.addEventListener('click', async (e) => {


    const dataEl = document.getElementById('usuario-data');
    let payload = { progreso: 0, actividadLabels: ['L', 'M', 'X', 'J', 'V', 'S', 'D'], horas: [0, 0, 0, 0, 0, 0, 0] };
    if (dataEl) { try { payload = JSON.parse(dataEl.textContent || '{}'); } catch (e) { } }

    // KPIs demo
    const p = payload.progreso || 0;
    const kpiProg = document.getElementById('kpi-progreso');
    const kpiProgBar = document.getElementById('kpi-progreso-bar');
    if (kpiProg) kpiProg.textContent = p;
    if (kpiProgBar) kpiProgBar.style.width = p + '%';

    // Ring progreso
    if (window.Chart && document.getElementById('ringProgreso')) {
        new Chart(document.getElementById('ringProgreso'), {
            type: 'doughnut',
            data: { datasets: [{ data: [p, 100 - p], backgroundColor: ['#6ea8fe', '#1a243b'], borderWidth: 0 }] },
            options: { cutout: '72%', plugins: { legend: { display: false }, tooltip: { enabled: false } } }
        });
    }

    // Actividad semanal
    if (window.Chart && document.getElementById('chartActividad')) {
        const ctx = document.getElementById('chartActividad').getContext('2d');
        const grad = ctx.createLinearGradient(0, 0, 0, 200); grad.addColorStop(0, 'rgba(110,168,254,.35)'); grad.addColorStop(1, 'rgba(110,168,254,0)');
        new Chart(ctx, {
            type: 'line',
            data: { labels: payload.actividadLabels, datasets: [{ label: 'Horas', data: payload.horas, tension: .35, fill: true, backgroundColor: grad, borderColor: '#6ea8fe', pointBackgroundColor: '#b197fc', pointRadius: 3 }] },
            options: { plugins: { legend: { display: false } }, scales: { x: { grid: { color: 'rgba(255,255,255,.06)' } }, y: { grid: { color: 'rgba(255,255,255,.06)' }, ticks: { stepSize: 1 } } } }
        });
    }

    // DataTables (si decides activarlo)
    if (window.jQuery && jQuery().DataTable && document.getElementById('tablaCursos')) {
        $('#tablaCursos').DataTable({ paging: true, searching: false, info: false, order: [[0, 'asc']] });
    }
    document.querySelectorAll('[data-bs-toggle="collapse"][data-store]').forEach(trigger => {
        const id = trigger.getAttribute('href')?.replace('#', '');
        const key = 'usrSidebar:' + id;
        const el = document.getElementById(id);
        if (!id || !el) return;

        // restaurar
        const saved = localStorage.getItem(key);
        if (saved === '1') {
            try { new bootstrap.Collapse(el, { toggle: true }); } catch (e) { }
        }

        el.addEventListener('shown.bs.collapse', () => localStorage.setItem(key, '1'));
        el.addEventListener('hidden.bs.collapse', () => localStorage.removeItem(key));
    });
    // Toggle favoritos (delegado)
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-fav');
        if (!btn) return;

        e.preventDefault();
        if (!window.IS_AUTH) {
            if (window.Swal) Swal.fire('Inicia sesión', 'Necesitas iniciar sesión para usar favoritos.', 'info');
            return;
        }

        const tipo = btn.dataset.type;
        const id = parseInt(btn.dataset.id, 10);
        if (!tipo || !id) return;

        // UI feedback
        btn.disabled = true;

        try {
            const body = new URLSearchParams({ tipo, id, csrf: window.CSRF });
            const r = await fetch(`${window.BASE_URL}Usuario/toggleFavorito`, {  // 👈 await permitido
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body
            });

            const data = await r.json();
            if (!data.success) throw new Error('No se pudo actualizar');

            // Icono
            const i = btn.querySelector('i');
            const fav = !!data.fav;
            btn.setAttribute('aria-pressed', fav ? 'true' : 'false');
            i?.classList.toggle('bi-star', !fav);
            i?.classList.toggle('bi-star-fill', fav);

            // Badges del sidebar
            const bFav = document.getElementById('badge-favoritos');
            if (bFav && data.counts && typeof data.counts.favoritos !== 'undefined') {
                const n = data.counts.favoritos;
                bFav.textContent = n;
                bFav.style.display = n > 0 ? 'inline-block' : 'none';
            }

            if (window.Swal) Swal.fire({
                toast: true, position: 'top-end', timer: 1800, showConfirmButton: false,
                icon: fav ? 'success' : 'info', title: fav ? 'Añadido a favoritos' : 'Quitado de favoritos'
            });
        } catch (err) {
            if (window.Swal) Swal.fire('Error', 'No se pudo actualizar favoritos.', 'error');
        } finally {
            btn.disabled = false;
        }
    });

});
