(function () {
  const BASE = (window.BASE_URL || '/Orion3D/');
  const detail = document.getElementById('qna-detail');
  if (!detail) return;

  function htmlSafe(s) { return s ?? ''; }

  async function loadDetail(id) {
    try {
      detail.innerHTML = `<div class="text-secondary">Cargando…</div>`;
      const r = await fetch(`${BASE}Admin/verQna/${id}`, { credentials: 'same-origin' });
      const hilo = await r.json();

      const p = hilo.pregunta || {};
      const rs = Array.isArray(hilo.respuestas) ? hilo.respuestas : [];

      const imgP = p.imagen ? `<div class="mt-2"><img src="${BASE}${p.imagen}" class="img-fluid rounded"></div>` : '';
      const respuestasHTML = rs.map(x => `
        <div class="border-top pt-3 mt-3">
          <div class="small text-secondary">${(x.creado_at || '')}</div>
          <div class="qna-content">${htmlSafe(x.contenido_html)}</div>
          ${x.imagen ? `<div class="mt-2"><img src="${BASE}${x.imagen}" class="img-fluid rounded"></div>` : ''}
        </div>
      `).join('');

      detail.innerHTML = `
        <div>
          <div class="d-flex justify-content-between align-items-start">
            <h5 class="mb-1">${htmlSafe(p.leccion_titulo || 'Lección')}</h5>
            <span class="badge ${p.estado === 'abierta' ? 'bg-warning text-dark' : (p.estado === 'respondida' ? 'bg-info text-dark' : 'bg-secondary')}">
              ${(p.estado || 'abierta').charAt(0).toUpperCase() + (p.estado || 'abierta').slice(1)}
            </span>
          </div>
          <div class="small text-secondary mb-2">${htmlSafe(p.nombre_usuario || 'Alumno')} · ${(p.creado_at || '')}</div>
          <div class="qna-content">${htmlSafe(p.contenido_html)}</div>
          ${imgP}
          <div id="qna-respuestas">${respuestasHTML || '<div class="text-secondary mt-3">Sin respuestas todavía.</div>'}</div>

          <hr class="my-3">
          <form id="form-responder" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="${(window.CSRF || '')}">
            <input type="hidden" name="pregunta_id" value="${p.id}">
            <div class="mb-2">
              <div id="qna-editor" class="bg-white text-dark rounded" style="min-height:120px"></div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <input type="file" name="imagen" id="resp-img" accept="image/*" class="form-control form-control-sm" style="max-width:280px">
              <button class="btn btn-primary btn-sm" id="btnEnviarResp" type="submit">Responder</button>
            </div>
            <small class="text-secondary d-block mt-1">Puedes adjuntar una imagen (máx 2MB).</small>
          </form>
        </div>
      `;

      // Inicializa Quill si está cargado
      let q = null;
      if (window.Quill) {
        q = new Quill('#qna-editor', { theme: 'snow' });
      } else {
        // Fallback: contenteditable simple
        const ed = document.getElementById('qna-editor');
        ed.setAttribute('contenteditable', 'true');
      }

      // Enviar respuesta
      const form = document.getElementById('form-responder');
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const html = q ? q.root.innerHTML.trim() : document.getElementById('qna-editor').innerHTML.trim();
        if (!html || html === '<p><br></p>') {
          return Swal?.fire('Vacío', 'Escribe tu respuesta.', 'info');
        }
        const fd = new FormData(form);
        fd.append('contenido_html', html);

        const btn = document.getElementById('btnEnviarResp');
        btn.disabled = true;
        try {
          const r = await fetch(`${BASE}Admin/responderQna`, { method: 'POST', body: fd, credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
          const data = await r.json();
          if (!r.ok || !data.success) throw new Error(data.msg || 'server');

          const cont = document.getElementById('qna-respuestas');
          const nuevo = document.createElement('div');
          nuevo.className = 'border-top pt-3 mt-3';
          nuevo.innerHTML = `
            <div class="small text-secondary">Ahora</div>
            <div class="qna-content">${data.item.contenido_html}</div>
            ${data.item.imagen ? `<div class="mt-2"><img src="${BASE}${data.item.imagen}" class="img-fluid rounded"></div>` : ''}
          `;
          cont.prepend(nuevo);
          if (q) q.setContents([]); else document.getElementById('qna-editor').innerHTML = '';
          document.getElementById('resp-img').value = '';
          Swal?.fire({ toast: true, position: 'top-end', timer: 1200, showConfirmButton: false, icon: 'success', title: 'Respuesta enviada' });
        } catch (err) {
          console.error(err);
          Swal?.fire('Error', 'No se pudo enviar tu respuesta.', 'error');
        } finally {
          btn.disabled = false;
        }
      });

    } catch (e) {
      console.error(e);
      detail.innerHTML = `<div class="text-danger">No se pudo cargar el detalle.</div>`;
    }
  }

  // Click en lista (usa .qna-item data-id)
  document.querySelectorAll('.qna-item[data-id]').forEach(a => {
    a.addEventListener('click', (e) => {
      e.preventDefault();
      const id = a.getAttribute('data-id');
      if (id) loadDetail(id);
    });
  });

  // Si viene focus en la URL (?focus=ID), cargar de una
  const params = new URLSearchParams(location.search);
  const focusId = params.get('focus');
  if (focusId) loadDetail(focusId);
})();
