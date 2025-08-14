document.addEventListener('DOMContentLoaded', () => {
  const grid = document.getElementById('mis-cursos-grid');
  if (!grid) return;

  // Búsqueda rápida en cliente (si no quieres recargar)
  const qInput = document.querySelector('input[name="q"]');
  const catSel = document.querySelector('select[name="cat"]');

  function applyFilters(){
    const q = (qInput?.value || '').toLowerCase().trim();
    const cat = (catSel?.value || '').trim();

    grid.querySelectorAll('.card').forEach(card => {
      const title = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
      const c = card.dataset.cat || '';
      const okCat = !cat || c === cat;
      const okQ   = !q || title.includes(q);
      card.parentElement.style.display = (okCat && okQ) ? '' : 'none';
    });
  }

  qInput?.addEventListener('input', applyFilters);
  catSel?.addEventListener('change', applyFilters);
});
