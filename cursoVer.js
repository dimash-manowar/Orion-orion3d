document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('.container-fluid.p-4'); // donde está el índice
  if (!root) return;

  const cursoId = parseInt( (document.querySelector('h2.h6')?.textContent.match(/#(\d+)/)?.[1] || '0'), 10 );
  const bar  = document.getElementById('prog-curso-bar');
  const txt  = document.getElementById('prog-curso-text');
  const cnt  = document.getElementById('prog-curso-count');

  root.addEventListener('change', async (e) => {
    const chk = e.target.closest('.chk-leccion');
    if (!chk) return;

    const leccionId = parseInt(chk.dataset.leccionId || '0', 10);
    const estado    = chk.checked ? 1 : 0;

    try {
      const body = new URLSearchParams({ csrf: window.CSRF, leccion_id: leccionId, estado });
      const r = await fetch(`${window.BASE_URL}Cursos/toggleLeccion`, {
        method: 'POST',
        headers: { 'Content-Type':'application/x-www-form-urlencoded' },
        body
      });
      const data = await r.json();
      if (!data || !data.success) throw new Error();

      if (data.curso) {
        const pct  = data.curso.pct ?? 0;
        const comp = data.curso.comp ?? 0;
        const tot  = data.curso.tot ?? 0;
        if (bar) bar.style.width = pct + '%';
        if (txt) txt.textContent = `${pct}%`;
        if (cnt) cnt.textContent = `(${comp}/${tot})`;
      }

      Swal?.fire({toast:true, position:'top-end', timer:900, showConfirmButton:false,
        icon: estado ? 'success' : 'info',
        title: estado ? 'Lección completada' : 'Lección desmarcada'
      });
    } catch {
      Swal?.fire('Error','No se pudo actualizar la lección.','error');
      chk.checked = !chk.checked; // revertir visualmente
    }
  });
});
