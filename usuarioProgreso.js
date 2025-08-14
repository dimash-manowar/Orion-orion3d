document.addEventListener('DOMContentLoaded', () => {
    // Leer payload
    const el = document.getElementById('progreso-data');
    let data = null;
    try { data = JSON.parse(el?.textContent || '{}'); } catch (e) { data = null; }
    if (!data) data = { resumen: { cursos: 0, lecciones_tot: 0, lecciones_comp: 0, progreso_pct: 0, horas_semana: 0 }, actividad: { labels: ['L', 'M', 'X', 'J', 'V', 'S', 'D'], horas: [0, 0, 0, 0, 0, 0, 0] } };

    // KPIs (si quieres animar/proteger, ya vienen pintados por PHP)
    const prog = data.resumen.progreso_pct || 0;
    const bar = document.getElementById('kpi-progreso-bar');
    if (bar) bar.style.width = prog + '%';

    // Chart de actividad
    const canvas = document.getElementById('chartProgreso');
    if (canvas && window.Chart) {
        const ctx = canvas.getContext('2d');
        const grad = ctx.createLinearGradient(0, 0, 0, 200);
        grad.addColorStop(0, 'rgba(110,168,254,.35)');
        grad.addColorStop(1, 'rgba(110,168,254,0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.actividad.labels,
                datasets: [{
                    label: 'Horas',
                    data: data.actividad.horas,
                    tension: .35,
                    fill: true,
                    backgroundColor: grad,
                    borderColor: '#6ea8fe',
                    pointBackgroundColor: '#b197fc',
                    pointRadius: 3
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: 'rgba(255,255,255,.06)' } },
                    y: { grid: { color: 'rgba(255,255,255,.06)' }, beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    }
    const bPend = document.getElementById('badge-pendientes');
    const bFav = document.getElementById('badge-favoritos');
    if (!bPend && !bFav) return;

    fetch(`${window.BASE_URL}Usuario/badges`, { credentials: 'same-origin' })
        .then(r => r.ok ? r.json() : null)
        .then(d => {
            if (!d) return;
            if (bPend && d.pendientes > 0) { bPend.textContent = d.pendientes; bPend.style.display = 'inline-block'; }
            if (bFav && d.favoritos > 0) { bFav.textContent = d.favoritos; bFav.style.display = 'inline-block'; }
        })
        .catch(() => { });
    document.addEventListener('DOMContentLoaded', async () => {
        const tbody = document.querySelector('#tablaFavoritos tbody');
        if (!tbody) return;

        try {
            const r = await fetch(`${window.BASE_URL}Usuario/listarFavoritos`, { credentials: 'same-origin' });
            const { items } = await r.json();

            if (!Array.isArray(items)) return;

            tbody.innerHTML = items.map(it => {
                const tipo = it.tipo;
                const ref = it.ref_id;
                const fecha = it.created_at ? new Date(it.created_at).toLocaleString() : '-';
                // Ajusta URL según tu routing real
                let url = '#';
                if (tipo === 'curso') url = `${window.BASE_URL}cursos/ver/${ref}`;
                if (tipo === 'leccion') url = `${window.BASE_URL}cursos/leccion/${ref}`;
                if (tipo === 'post') url = `${window.BASE_URL}blog/post/${ref}`;

                return `
        <tr>
          <td class="text-capitalize">${tipo}</td>
          <td><a class="link-light" href="${url}">#${ref}</a></td>
          <td>${fecha}</td>
          <td>
            <button class="btn btn-sm btn-outline-warning btn-fav" data-type="${tipo}" data-id="${ref}">
              <i class="bi bi-star-fill"></i>
            </button>
          </td>
        </tr>`;
            }).join('');

            if (window.jQuery && jQuery().DataTable) {
                $('#tablaFavoritos').DataTable({ paging: true, searching: false, info: false, order: [[2, 'desc']] });
            }
        } catch (e) {
            // Silencio o toast opcional
        }
    });

});