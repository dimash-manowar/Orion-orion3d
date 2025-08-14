document.addEventListener('DOMContentLoaded', () => {
  // ---------- Q U I L L  ----------
  const editorEl = document.getElementById('qna-editor');
  // Si esta vista no tiene editor, salimos (evita "Invalid container")
  if (editorEl) {
    // Evitar doble init si el script se incluye dos veces
    if (editorEl.dataset.quillInit !== '1') {
      const initQuill = () => {
        try {
          const q = new Quill(editorEl, {
            theme: 'snow',
            placeholder: 'Escribe tu duda con detalles…',
            modules: {
              toolbar: [
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link', 'code-block'],
                ['clean']
              ]
            }
          });
          editorEl.dataset.quillInit = '1';
          attachQnaHandlers(q);
        } catch (e) {
          console.error('Error iniciando Quill:', e);
        }
      };

      // Si Quill no está cargado aún, lo cargamos dinámicamente (fallback)
      if (!window.Quill) {
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js';
        s.onload = initQuill;
        document.head.appendChild(s);
      } else {
        initQuill();
      }
    }
  }

  // ---------- R E S T O  ----------
  function attachQnaHandlers(q) {
    // Elementos
    const btnSend = document.getElementById('qna-send');
    const file = document.getElementById('qna-img');
    const list = document.getElementById('qna-list');
    const root = document.getElementById('leccion-root');

    // IDs desde data-*
    const cursoId = parseInt(root?.dataset.cursoId || '0', 10);
    const leccionId = parseInt(root?.dataset.leccionId || '0', 10);

    // BASE_URL global (asegúrate de setearla en el layout)
    const BASE = (window.BASE_URL || '/Orion3D/'); // Debe terminar en '/'

    // --- Enviar pregunta ---
    btnSend?.addEventListener('click', async () => {
      const html = q?.root?.innerHTML?.trim() || '';
      if (!cursoId || !leccionId) {
        return Swal?.fire('Falta contexto', 'cursoId o leccionId no definidos.', 'error');
      }
      if (!html || html === '<p><br></p>') {
        return Swal?.fire('Vacío', 'Escribe tu pregunta antes de enviar.', 'info');
      }

      const fd = new FormData();
      fd.append('csrf', window.CSRF || '');
      fd.append('curso_id', cursoId);
      fd.append('leccion_id', leccionId);
      fd.append('contenido_html', html);
      if (file?.files?.[0]) fd.append('imagen', file.files[0]);

      btnSend.disabled = true;
      try {
        const r = await fetch(`${BASE}Cursos/preguntar`, {
          method: 'POST',
          body: fd,
          credentials: 'same-origin',
          headers: { 'Accept': 'application/json' }
        });

        // Parse seguro
        const text = await r.text();
        let data;
        try { data = JSON.parse(text); } catch { data = null; }

        if (!r.ok || !data || !data.success) {
          console.error('Respuesta:', text);
          throw new Error(data?.msg || 'server');
        }

        const wrap = document.createElement('div');
        wrap.className = 'p-3 mb-2 bg-black rounded border';
        wrap.innerHTML = `
        <div class="small text-secondary mb-1">Tú · ahora</div>
        <div class="qna-content">${data.item.contenido_html}</div>
        ${data.item.imagen ? `<div class="mt-2"><img src="${BASE}${data.item.imagen}" class="img-fluid rounded"></div>` : ''}
      `;
        list?.prepend(wrap);
        q?.setContents([]);
        if (file) file.value = '';
        Swal?.fire({ toast: true, position: 'top-end', timer: 1500, showConfirmButton: false, icon: 'success', title: 'Pregunta enviada' });
      } catch (e) {
        console.error(e);
        Swal?.fire('Error', 'No se pudo enviar tu pregunta.', 'error');
      } finally {
        btnSend.disabled = false;
      }
    });

    // --- Toggle Completada ---
    const chk = document.getElementById('chkDone');
    chk?.addEventListener('change', async () => {
      const estado = chk.checked ? 1 : 0;
      try {
        const body = new URLSearchParams({
          csrf: (window.CSRF || ''),
          leccion_id: String(leccionId),
          estado: String(estado)
        });

        const r = await fetch(`${BASE}Cursos/toggleLeccion`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
          body,
          credentials: 'same-origin'
        });

        const data = await r.json();
        if (!data || !data.success) throw new Error();

        // ... (tu actualización de barras/contadores tal cual)
        // (lo dejo igual que tenías)

        Swal?.fire({
          toast: true, position: 'top-end', timer: 1000, showConfirmButton: false,
          icon: estado ? 'success' : 'info',
          title: estado ? 'Lección completada' : 'Lección desmarcada'
        });
      } catch {
        Swal?.fire('Error', 'No se pudo actualizar la lección.', 'error');
        chk.checked = !chk.checked;
      }
    });
  }

});
