<?php include 'Views/Principal/header.php'; ?>
<?php
$u = $_SESSION['user'] ?? [];
$foto = !empty($u['foto']) ? BASE_URL . 'Assets/imagen/users/' . $u['foto'] : BASE_URL . 'Assets/imagen/usuario.png';
?>
<?php include 'Views/Usuarios/topbar.php'; ?>
<div class="d-flex" id="wrapper">
  <?php include 'Views/Usuarios/sidebar.php'; ?>

  <div class="container-fluid p-4">

    <div class="bg-dark border rounded-3 p-3 mb-3">
      <div class="d-flex align-items-center justify-content-between">
        <h2 class="h6 m-0">Lecciones del curso #<?= (int)$curso_id ?></h2>
        <div class="text-end">
          <div class="text-secondary small">Progreso del curso</div>
          <div class="d-flex align-items-center gap-2">
            <div class="progress" style="width:180px;height:8px">
              <div id="prog-curso-bar" class="progress-bar" style="width: <?= (int)($progreso['pct'] ?? 0) ?>%"></div>
            </div>
            <span id="prog-curso-text"><?= (int)($progreso['pct'] ?? 0) ?>%</span>
            <span class="text-secondary small" id="prog-curso-count">(<?= (int)($progreso['comp'] ?? 0) ?>/<?= (int)($progreso['tot'] ?? 0) ?>)</span>

          </div>
        </div>
      </div>
    </div>

    <div class="bg-dark border rounded-3 p-3">
      <ul class="list-group list-group-flush">
        <?php foreach ($lecciones as $i => $l):
          $done = (int)($l['completada'] ?? 0); // 👈 evita warning
        ?>
          <li class="list-group-item bg-dark text-light d-flex align-items-center justify-content-between">
            <div>
              <span class="badge bg-secondary me-2"><?= $i + 1 ?></span>
              <?= htmlspecialchars($l['titulo']) ?>
            </div>
            <div class="form-check form-switch">
              <input class="form-check-input chk-leccion" type="checkbox"
                id="lx<?= (int)$l['id'] ?>"
                data-leccion-id="<?= (int)$l['id'] ?>"
                <?= ($done === 1) ? 'checked' : '' ?>>
              <label class="form-check-label small" for="lx<?= (int)$l['id'] ?>">Completada</label>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

  </div>
</div>
<?php include 'Views/Principal/footer.php'; ?>