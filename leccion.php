<?php
$info       = $info ?? [];
$video      = $info['video_url'] ?? '';
$desc       = $info['descripcion'] ?? '';
$cursoId    = (int)($curso_id ?? $cursoId ?? 0);
$leccionId  = (int)($leccion_id ?? $leccionId ?? 0);
$done       = (int)($completada ?? 0);
$pct        = (int)($progreso['pct'] ?? 0);
$comp       = (int)($progreso['comp'] ?? 0);
$tot        = (int)($progreso['tot'] ?? 0);
?>
<?php include 'Views/Principal/header.php'; ?>
<?php include 'Views/Usuarios/topbar.php'; ?>
<div class="d-flex" id="wrapper">
  <?php include 'Views/Usuarios/sidebar.php'; ?>

  <!-- root con data-* para JS -->
  <div class="container-fluid p-4"
    id="leccion-root"
    data-curso-id="<?= $cursoId ?>"
    data-leccion-id="<?= $leccionId ?>"
    data-completada="<?= $done ?>">

    <div class="row g-3">
      <!-- Vídeo + descripción -->
      <div class="col-lg-8">
        <div class="bg-dark border rounded-3 p-2">
          <!-- Cabecera: título + switch completada + mini progreso -->
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-2">
            <h1 class="h6 m-0"><?= htmlspecialchars($info['titulo']) ?></h1>
            <div class="d-flex align-items-center gap-3">
              <div class="form-check form-switch m-0">
                <input class="form-check-input" type="checkbox" id="chkDone" <?= $done ? 'checked' : '' ?>>
                <label class="form-check-label small" for="chkDone">Completada</label>
              </div>
              <div class="d-flex align-items-center gap-2">
                <div class="progress" style="width:160px;height:6px">
                  <div id="prog-minibar" class="progress-bar" style="width: <?= $pct ?>%"></div>
                </div>
                <span class="small" id="prog-mini-text"><?= $pct ?>% (<?= $comp ?>/<?= $tot ?>)</span>
              </div>
            </div>
          </div>

          <!-- Player -->
          <?php
          function yt_id($u)
          {
            if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([A-Za-z0-9_-]{6,})~', $u, $m)) {
              return $m[1];
            }
            return null;
          }
          $yt = yt_id($video);
          ?>
          <div class="ratio ratio-16x9 bg-black rounded">
            <?php if ($yt): ?>
              <iframe
                src="https://www.youtube.com/embed/<?= htmlspecialchars($yt) ?>"
                allowfullscreen class="rounded" referrerpolicy="strict-origin-when-cross-origin">
              </iframe>
            <?php elseif (!empty($video)): ?>
              <video src="<?= htmlspecialchars($video) ?>" controls class="w-100 h-100 rounded"></video>
            <?php else: ?>
              <div class="d-flex align-items-center justify-content-center text-secondary">
                Vídeo no disponible
              </div>
            <?php endif; ?>
          </div>

          <!-- Descripción -->
          <div class="p-3">
            <h2 class="h6 mb-2">Descripción</h2>
            <p class="text-secondary mb-0"><?= nl2br(htmlspecialchars($desc)) ?></p>
          </div>
        </div>

        <!-- Q&A (se mantiene como lo tenías) -->
         <div id="leccion-root" data-curso-id="<?= (int)$curso['id'] ?>" data-leccion-id="<?= (int)$leccion['id'] ?>">
          <div class="bg-dark border rounded-3 p-3 mt-3">
            <h2 class="h6 mb-3">💬 Dudas al profesor / admin</h2>

            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">
            <div id="qna-editor" class="bg-white text-dark rounded"></div>
            <div class="mt-2 d-flex align-items-center gap-2">
              <input type="file" id="qna-img" accept="image/*" class="form-control form-control-sm" style="max-width:280px">
              <button id="qna-send" class="btn btn-primary btn-sm">Enviar</button>
            </div>
            <small class="text-secondary d-block mt-1">Puedes adjuntar una imagen (máx 2MB).</small>
            <hr class="border-secondary my-3">

            <div id="qna-list">
              <?php foreach ($preguntas ?? [] as $p): ?>
                <div class="p-3 mb-2 bg-black rounded border">
                  <div class="small text-secondary mb-1">
                    <?= htmlspecialchars($p['nombre_usuario'] ?? $p['nombre'] ?? 'Usuario') ?> ·
                    <?= date('d/m/Y H:i', strtotime($p['creado_at'])) ?>
                  </div>
                  <div class="qna-content"><?= $p['contenido_html'] ?></div>
                  <?php if (!empty($p['imagen'])): ?>
                    <div class="mt-2"><img src="<?= BASE_URL . $p['imagen'] ?>" class="img-fluid rounded"></div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Playlist con checks -->
      <div class="col-lg-4">
        <div class="bg-dark border rounded-3 p-3">
          <h2 class="h6">Lecciones del curso</h2>
          <ol class="list-group list-group-numbered list-group-flush mt-2">
            <?php foreach ($lecciones as $l): ?>
              <li class="list-group-item bg-dark text-light d-flex justify-content-between align-items-center">
                <a class="link-light text-decoration-none"
                  href="<?= BASE_URL ?>Cursos/leccion/<?= $cursoId ?>/<?= (int)$l['id'] ?>">
                  <?= htmlspecialchars($l['titulo']) ?>
                </a>
                <?php if ((int)$l['completada'] === 1): ?>
                  <span class="badge bg-success">✓</span>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ol>
          <div class="text-end mt-3">
            <a class="btn btn-sm btn-outline-light" href="<?= BASE_URL ?>Cursos/ver/<?= $cursoId ?>">
              <i class="bi bi-list-check me-1"></i> Índice del curso
            </a>
          </div>
        </div>
      </div>

    </div>

  </div>
</div>
<?php include 'Views/Principal/footer.php'; ?>