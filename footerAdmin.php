
<?php

if (defined('ADMIN_FOOTER_DONE')) return;
define('ADMIN_FOOTER_DONE', true);
?>


</div> <!-- /#wrapper -->

<!-- Offcanvas Notificaciones (hijo directo de <body>) -->
<div class="offcanvas offcanvas-end text-bg-dark"
  id="offcanvasNoti" tabindex="-1" aria-labelledby="offcanvasNotiLabel"
  style="width:380px">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasNotiLabel">Notificaciones (Q&A pendientes)</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
  </div>
  <div class="offcanvas-body">
    <div id="noti-list" class="list-group list-group-flush small">
      <div class="text-secondary">Cargando…</div>
    </div>
  </div>
</div>

<!-- jQuery (solo si lo necesitas para DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- Bootstrap BUNDLE (único) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js (si lo usas) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- DataTables (si lo usas) -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- JS común del admin -->
<script src="<?= BASE_URL ?>Assets/js/admin/noti.js?v=<?= time(); ?>" defer></script>

<!-- JS específico de la página (DEDUP global) -->
<?php
if (!empty($data['page_functions_js'])) {
  $scripts = is_array($data['page_functions_js']) ? $data['page_functions_js'] : [$data['page_functions_js']];
  // normaliza y elimina duplicados exactos dentro del request
  $scripts = array_values(array_unique(array_map(fn($s)=>ltrim($s,'/'), $scripts)));

  // dedup global por si algún include imprime otra vez el mismo JS
  $GLOBALS['_ADMIN_JS_LOADED'] ??= [];

  foreach ($scripts as $s) {
    if (isset($GLOBALS['_ADMIN_JS_LOADED'][$s])) continue;
    $GLOBALS['_ADMIN_JS_LOADED'][$s] = true;

    // etiqueta de origen para localizar duplicados externos (vista/partial)
    echo '<script data-from="footerAdmin" src="'.BASE_URL.'Assets/js/'.$s.'?v='.time().'" defer></script>'."\n";
  }
}
?>





<!-- Alertas de sesión -->
<?php if (!empty($_SESSION['alert'])): ?>
  <script>
    Swal.fire({
      icon: '<?= $_SESSION['alert']['icon'] ?>',
      title: '<?= $_SESSION['alert']['title'] ?>',
      text: '<?= $_SESSION['alert']['text'] ?>',
      confirmButtonColor: '#3085d6'
    });
  </script>
  <?php unset($_SESSION['alert']); ?>
<?php endif; ?>


</body>

</html>