(function () {
  console.info('[noti.js] cargado');

  const BASE = (window.BASE_URL || '/Orion3D/');
  const $id = (id) => document.getElementById(id);

  function renderList(items) {
    console.info('[noti.js] renderList items=', items?.length);
    const list = $id('noti-list'); if (!list) return;
    if (!Array.isArray(items) || !items.length) {
      list.innerHTML = `<div class="p-3 text-secondary">Sin notificaciones.</div>`;
      return;
    }
    list.innerHTML = items.map(n => `
      <a href="${BASE}Admin/mensajes?focus=${n.id}" class="list-group-item list-group-item-action d-flex align-items-start gap-2">
        <i class="bi bi-bell mt-1"></i>
        <div class="flex-grow-1">
          <div class="fw-semibold text-truncate">${(n.nombre_usuario || 'Usuario')} · ${(n.curso || '')} · ${(n.leccion || '')}</div>
          <small class="text-secondary d-block">${String(n.creado_at || '').replace(' ', ' · ')}</small>
        </div>
      </a>
    `).join('');
  }

  async function refresh() {
    console.info('[noti.js] refresh()');
    try {
      const r = await fetch(`${BASE}Admin/notificaciones`, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin', cache: 'no-store' });
      const d = await r.json();
      console.info('[noti.js] datos=', d);
      const badge = $id('noti-badge');
      if (d && d.success !== false) {
        const c = parseInt(d.count || 0, 10);
        if (badge) { badge.textContent = c; badge.style.display = c > 0 ? 'inline-block' : 'none'; }
        renderList(d.items || []);
      } else {
        if (badge) badge.style.display = 'none';
      }
    } catch (e) {
      console.warn('[noti.js] error', e);
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    console.info('[noti.js] DOM listo');
    const btn = $id('btn-noti');
    const el = $id('offcanvasNoti');
    console.info('[noti.js] btn=', !!btn, 'offcanvas=', !!el);
    if (btn && el && window.bootstrap?.Offcanvas) {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        let oc = bootstrap.Offcanvas.getInstance(el);
        if (!oc) oc = new bootstrap.Offcanvas(el, { backdrop: true, scroll: false, keyboard: true });
        oc.show();
      });
      el.addEventListener('show.bs.offcanvas', refresh);
    }
    refresh(); setInterval(refresh, 60000);
  });
})();
