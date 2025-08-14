<?php
include 'Views/Admin/headerAdmin.php';
include 'Views/Admin/sidebar.php';
?>
<div id="page-content-wrapper" class="flex-grow-1">
  <?php include 'Views/Admin/topbar.php'; ?>

  <div class="container-fluid text-light">
    <h2 class="mt-4 mb-3">Mensajes de alumnos (Q&A)</h2>

    <!-- Formulario de respuesta (ADMIN) -->
    <div class="bg-dark border rounded-3 p-3 mb-4" id="admin-qna-form">
      <h5 class="mb-3">Responder al alumno</h5>
      <form id="admin-form-responder" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <input type="hidden" name="pregunta_id" id="admin-pregunta-id" value="">
        <div id="admin-qna-context" class="small text-secondary mb-2" style="min-height:1.5rem">
          Selecciona una tarjeta para responder…
        </div>
        <div class="mb-2">
          <div id="admin-qna-editor" class="bg-white text-dark rounded" style="min-height:140px"></div>
        </div>
        <div class="d-flex align-items-center gap-2">
          <input type="file" name="imagen" id="admin-resp-img" accept="image/*" class="form-control form-control-sm" style="max-width:280px">
          <button class="btn btn-primary btn-sm" id="admin-btn-resp" type="submit" disabled>Responder</button>
        </div>
        <small class="text-secondary d-block mt-1">Adjunta una imagen (máx 2MB).</small>
      </form>
    </div>

    <!-- Tarjetas (ADMIN) -->
    <div class="row g-3" id="admin-qna-grid">
      <?php foreach (($qna ?? []) as $p):
        $avatar = !empty($p['foto']) ? BASE_URL.'Assets/imagen/users/'.$p['foto'] : BASE_URL.'Assets/imagen/usuario.png';
      ?>
      <div class="col-md-6 col-xl-4">
        <div class="card bg-dark border-secondary h-100 admin-card-qna"
             data-id="<?= (int)$p['id'] ?>"
             data-autor="<?= htmlspecialchars($p['nombre_usuario'] ?? $p['nombre'] ?? 'Alumno', ENT_QUOTES, 'UTF-8') ?>"
             data-leccion="<?= htmlspecialchars($p['leccion_titulo'] ?? 'Lección', ENT_QUOTES, 'UTF-8') ?>">
          <div class="card-body">
            <div class="d-flex align-items-center mb-2">
              <img src="<?= $avatar ?>" class="rounded-circle me-2" width="36" height="36" alt="">
              <div>
                <div class="fw-semibold"><?= htmlspecialchars($p['nombre_usuario'] ?? $p['nombre'] ?? 'Alumno') ?></div>
                <div class="small text-secondary"><?= date('d/m/Y H:i', strtotime($p['creado_at'])) ?></div>
              </div>
              <span class="ms-auto badge <?= $p['estado']==='abierta'?'bg-warning text-dark': ($p['estado']==='respondida'?'bg-info text-dark':'bg-secondary') ?>">
                <?= ucfirst($p['estado']) ?>
              </span>
            </div>
            <div class="small text-secondary mb-1"><?= htmlspecialchars($p['leccion_titulo'] ?? 'Lección') ?></div>
            <div class="qna-content small mb-2"><?= $p['contenido_html'] ?></div>
            <?php if (!empty($p['imagen'])): ?>
              <div class="mt-2"><img src="<?= BASE_URL . $p['imagen'] ?>" class="img-fluid rounded"></div>
            <?php endif; ?>
          </div>
          <div class="card-footer bg-transparent border-secondary d-flex justify-content-between">
            <button class="btn btn-outline-light btn-sm btn-responder">Responder</button>
            <a class="btn btn-outline-info btn-sm" href="<?= BASE_URL ?>Admin/preguntas?focus=<?= (int)$p['id'] ?>">Ver hilo</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</div>
<?php include 'Views/Admin/footerAdmin.php'; ?>

