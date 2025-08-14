// Assets/js/dashboardUsuarios.js
document.addEventListener('DOMContentLoaded', () => {
  // Leer paquete de datos de la vista
  const dataEl = document.getElementById('dashboard-data');
  let payload = { progreso: 0, actividadLabels: ['L','M','X','J','V','S','D'], horas: [0,0,0,0,0,0,0] };
  if (dataEl) {
    try { payload = JSON.parse(dataEl.textContent || '{}'); } catch (e) { console.warn('JSON dashboard inválido', e); }
  }

  // 1) Ring de progreso
  const ring = document.getElementById('ringProgreso');
  if (ring && window.Chart) {
    new Chart(ring, {
      type: 'doughnut',
      data: { datasets: [{ data: [payload.progreso, 100 - payload.progreso], backgroundColor:['#6ea8fe','#1a243b'], borderWidth:0 }] },
      options: { cutout: '72%', plugins: { legend: { display: false }, tooltip: { enabled: false } } }
    });
  }

  // 2) Gráfico actividad
  const act = document.getElementById('chartActividad');
  if (act && window.Chart) {
    const ctx = act.getContext('2d');
    const grad = ctx.createLinearGradient(0,0,0,200);
    grad.addColorStop(0,'rgba(110,168,254,.35)');
    grad.addColorStop(1,'rgba(110,168,254,0)');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: payload.actividadLabels || [],
        datasets: [{
          label: 'Horas',
          data: payload.horas || [],
          tension: .35, fill: true,
          backgroundColor: grad, borderColor: '#6ea8fe',
          pointBackgroundColor: '#b197fc', pointRadius: 3
        }]
      },
      options: {
        scales: {
          x: { grid: { color: 'rgba(255,255,255,.06)' } },
          y: { grid: { color: 'rgba(255,255,255,.06)' }, ticks: { stepSize: 1 } }
        },
        plugins: { legend: { display: false } }
      }
    });
  }

  // 3) SweetAlert: crear meta
  const btnMeta = document.getElementById('btnNuevaMeta');
  if (btnMeta && window.Swal) {
    btnMeta.addEventListener('click', async () => {
      const { value: horas } = await Swal.fire({
        title: 'Nueva meta semanal',
        input: 'number',
        inputLabel: 'Horas a dedicar',
        inputAttributes: { min: 1 },
        showCancelButton: true, confirmButtonText: 'Guardar',
        confirmButtonColor: '#6ea8fe'
      });
      if (horas) {
        // TODO: fetch(`${BASE_URL}metas/crear`, { method:'POST', body: JSON.stringify({ horas }) })
        Swal.fire('¡Listo!','Meta guardada: '+horas+'h','success');
      }
    });
  }

  // 4) Efecto “halo” en tarjetas de curso
  document.querySelectorAll('.course-card').forEach(card => {
    card.addEventListener('mousemove', e => {
      const r = card.getBoundingClientRect();
      card.style.setProperty('--x', (e.clientX - r.left) + 'px');
      card.style.setProperty('--y', (e.clientY - r.top) + 'px');
    });
  });
});
