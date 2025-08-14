(function () {
  'use strict';
  // Evita doble ejecución si por error se inyecta dos veces
  if (window.__ADMIN_MENSAJES_LOADED__) {
    console.warn('[mensajes.js] duplicate load suppressed');
    // importante: no sigas
    // return;  // ⬅️ descomenta si quieres parar del todo la 2ª carga
  }
  window.__ADMIN_MENSAJES_LOADED__ = true;

  // (opcional) ejecuta sólo en admin y en /Admin/mensajes
  if (document.body?.getAttribute('data-scope') !== 'admin') return;
  if (!/\/admin\/mensajes\b/i.test(location.pathname)) return;

  

  const BASE = (window.BASE_URL || '/Orion3D/');

  const form = document.getElementById('admin-form-responder');
  const ctx = document.getElementById('admin-qna-context');
  const pid = document.getElementById('admin-pregunta-id');
  const btn = document.getElementById('admin-btn-resp');
  const img = document.getElementById('admin-resp-img');
  const grid = document.getElementById('admin-qna-grid');

  if (!form || !grid) {
    console.warn('[admin-mensajes] Falta form o grid en la vista Admin/mensajes.');
    return;
  }

  // Inicializa Quill o fallback
  let q = null;
  const editorSel = '#admin-qna-editor';
  if (window.Quill) {
    q = new Quill(editorSel, { theme: 'snow' });
  } else {
    const ed = document.querySelector(editorSel);
    if (ed) ed.setAttribute('contenteditable', 'true');
  }

  // Delegación: click en botón "Responder" de cada tarjeta
  grid.addEventListener('click', (e) => {
    const btnResp = e.target.closest('.btn-responder');
    if (!btnResp) return;
    const card = e.target.closest('.admin-card-qna');
    if (!card) return;
    const id = card.dataset.id;
    const autor = card.dataset.autor || 'Alumno';
    const lec = card.dataset.leccion || 'Lección';

    pid.value = id || '';
    ctx.innerHTML = `<b>Responder a:</b> ${autor} · <span class="text-secondary">${lec}</span>`;
    btn.disabled = !id;
    document.getElementById('admin-qna-form')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });

  // Envío de respuesta
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!pid.value) return Swal?.fire('Selecciona', 'Elige primero una pregunta', 'info');

    const html = q ? q.root.innerHTML.trim()
      : (document.querySelector(editorSel)?.innerHTML.trim() || '');

    if (!html || html === '<p><br></p>') {
      return Swal?.fire('Vacío', 'Escribe una respuesta', 'info');
    }

    const fd = new FormData(form);
    fd.append('contenido_html', html);

    btn.disabled = true;
    try {
      const r = await fetch(`${BASE}Admin/responderQna`, {
        method: 'POST', body: fd, credentials: 'same-origin', headers: { 'Accept': 'application/json' }
      });
      const data = await r.json();
      if (!r.ok || !data.success) throw new Error(data.msg || 'server');

      // Limpieza + feedback
      if (q) q.setContents([]); else { const ed = document.querySelector(editorSel); if (ed) ed.innerHTML = ''; }
      if (img) img.value = '';
      Swal?.fire({ toast: true, position: 'top-end', timer: 1200, showConfirmButton: false, icon: 'success', title: 'Respuesta enviada' });

      // Marca tarjeta como respondida
      const card = grid.querySelector(`.admin-card-qna[data-id="${pid.value}"]`);
      if (card) {
        const badge = card.querySelector('.badge');
        badge?.classList.remove('bg-warning', 'text-dark');
        badge?.classList.add('bg-info', 'text-dark');
        if (badge) badge.textContent = 'Respondida';
      }

      pid.value = ''; btn.disabled = true; ctx.textContent = 'Selecciona una tarjeta para responder…';
    } catch (err) {
      console.error(err);
      Swal?.fire('Error', 'No se pudo enviar la respuesta', 'error');
    } finally {
      btn.disabled = false;
    }
  });

  // Auto-focus desde ?focus=ID
  const focusId = new URLSearchParams(location.search).get('focus');
  if (focusId) {
    const card = grid.querySelector(`.admin-card-qna[data-id="${focusId}"]`);
    if (card) {
      const autor = card.dataset.autor || 'Alumno';
      const lec = card.dataset.leccion || 'Lección';
      pid.value = String(focusId);
      ctx.innerHTML = `<b>Responder a:</b> ${autor} · <span class="text-secondary">${lec}</span>`;
      btn.disabled = false;
      document.getElementById('admin-qna-form')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
      card.classList.add('border-primary'); setTimeout(() => card.classList.remove('border-primary'), 1500);
    }
  }
})();
