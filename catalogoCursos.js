document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('catalogoForm');
  if (!form) return;

  const autoSubmit = (e) => { form.requestSubmit(); };
  form.querySelector('select[name="sort"]')?.addEventListener('change', autoSubmit);
  form.querySelector('select[name="per"]')?.addEventListener('change', autoSubmit);

  // Opcional: filtro en cliente cuando escribes (sin recargar)
  const grid = document.getElementById('catalogo-grid');
  const q = form.querySelector('input[name="q"]');
  const cat = form.querySelector('select[name="cat"]');
  const lvl = form.querySelector('select[name="lvl"]');

  function applyFiltersClient(){
    if (!grid) return;
    const fq = (q?.value||'').toLowerCase().trim();
    const fcat = (cat?.value||'').trim();
    const flvl = (lvl?.value||'').trim();
    grid.querySelectorAll('.card').forEach(card => {
      const title = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
      const okQ = !fq || title.includes(fq);
      const okCat = !fcat || (card.dataset.cat === fcat);
      const okLvl = !flvl || (card.dataset.lvl === flvl);
      card.parentElement.style.display = (okQ && okCat && okLvl) ? '' : 'none';
    });
  }
  q?.addEventListener('input', applyFiltersClient);
  cat?.addEventListener('change', applyFiltersClient);
  lvl?.addEventListener('change', applyFiltersClient);
});
