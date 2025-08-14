
<div id="wrapper">
  <?php include 'Views/Admin/sidebar.php'; ?>
  <div id="page-content-wrapper">
    <?php include 'Views/Admin/topbar.php'; ?>

    <div class="container-fluid text-light">
      <h2 class="mt-4 mb-3"><?= $page_title ?></h2>

      <!-- Filtros -->
      <div class="bg-dark border rounded-3 p-3 mb-3">
        <form class="row g-2" method="GET" action=""><?= csrf_field(); ?>
          <div class="col-md-3">
            <select name="estado" class="form-select bg-black text-light">
              <option value="">Todos los estados</option>
              <?php foreach (['abierta'=>'Abiertas','respondida'=>'Respondidas','cerrada'=>'Cerradas'] as $k=>$v): ?>
                <option value="<?= $k ?>" <?= (($_GET['estado'] ?? '')===$k?'selected':'') ?>><?= $v ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <input type="text" name="q" class="form-control bg-black text-light" placeholder="Buscar por alumno o lección" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
          </div>
          <div class="col-md-3 text-end">
            <button class="btn btn-outline-light"><i class="bi bi-search"></i> Buscar</button>
          </div>
        </form>
      </div>

      <div class="row g-3">
        <!-- Columna izquierda: tarjetas -->
        <div class="col-lg-5">
          <div class="list-group">
            <?php foreach ($qna as $item): 
              $avatar = !empty($item['foto']) ? BASE_URL.'Assets/imagen/users/'.$item['foto'] : BASE_URL.'Assets/imagen/usuario.png';
            ?>
              <a href="#" class="list-group-item list-group-item-action bg-dark text-light border-secondary qna-item"
                 data-id="<?= (int)$item['id'] ?>">
                <div class="d-flex w-100 align-items-center">
                  <img src="<?= $avatar ?>" class="rounded-circle me-3" width="42" height="42" alt="">
                  <div class="w-100">
                    <div class="d-flex justify-content-between">
                      <h6 class="mb-1"><?= htmlspecialchars($item['nombre_usuario'] ?? $item['nombre'] ?? 'Alumno') ?></h6>
                      <small class="text-secondary"><?= date('d/m/Y H:i', strtotime($item['creado_at'])) ?></small>
                    </div>
                    <div class="small text-secondary">
                      <?= htmlspecialchars($item['leccion_titulo'] ?? 'Lección') ?>
                    </div>
                    <div class="mt-2">
                      <span class="badge <?= $item['estado']==='abierta'?'bg-warning text-dark': ($item['estado']==='respondida'?'bg-info text-dark':'bg-secondary') ?>">
                        <?= ucfirst($item['estado']) ?>
                      </span>
                      <?php if (!(int)$item['leido']): ?>
                        <span class="badge bg-success ms-1">Nuevo</span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Columna derecha: detalle -->
        <div class="col-lg-7">
          <div class="bg-dark border rounded-3 p-3" id="qna-detail" data-csrf="<?= csrf_token() ?>">
            <div class="text-secondary">Selecciona una pregunta para ver el detalle.</div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

