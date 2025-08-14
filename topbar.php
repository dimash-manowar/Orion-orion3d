<nav id="admin-topbar" class="navbar navbar-expand-lg navbar-dark">
  <!-- botón hamburguesa móvil -->
  <button class="btn btn-outline-light me-3 d-lg-none" id="toggleSidebar"><i class="bi bi-list"></i></button>

  <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>admin">
    <img src="<?= BASE_URL ?>Assets/imagen/Logo.png" height="30" class="me-2" alt="Orion3D"> Orion3D Admin
  </a>

  <ul class="navbar-nav ms-auto align-items-center">
    <li class="nav-item me-3">
      <button id="btn-noti" class="nav-link btn btn-link position-relative" aria-controls="offcanvasNoti">
        <i class="bi bi-bell fs-5"></i>
        <span id="noti-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none">0</span>
      </button>
    </li>

    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <img src="<?= BASE_URL ?>Assets/imagen/frontal.jpg" class="rounded-circle me-2" width="32" height="32" alt="Usuario">
        <?= htmlspecialchars($_SESSION['user']['nombre'] ?? $_SESSION['user']['nombre_usuario'] ?? 'Usuario', ENT_QUOTES, 'UTF-8') ?>
      </a>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
        <li><a class="dropdown-item" href="<?= BASE_URL ?>Admin/perfil">Perfil</a></li>
        <li><a class="dropdown-item" href="<?= BASE_URL ?>Admin/configuracion">Configuración</a></li>
        <li>
          <hr class="dropdown-divider">
        </li>
        <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>Auth/logout">Cerrar Sesión</a></li>
      </ul>
    </li>
  </ul>
</nav>