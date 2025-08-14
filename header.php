<?php if (!isset($pageTitle)) {
  $pageTitle = "Orion3D";
} ?>

<?php
$user     = $_SESSION['user'] ?? null;
$isAuth   = !empty($user);
$isAdmin  = ($user['rol'] ?? 'user') === 'admin';
$nombre   = $user['nombre'] ?? $user['name'] ?? null;
// Usas carpeta 'imagen' en Assets; dejamos esa convención
$avatar   = !empty($user['foto'])
  ? BASE_URL . 'Assets/imagen/users/' . $user['foto']
  : BASE_URL . 'Assets/imagen/usuario.png';
// Si no hay sesión, añadimos class y desactivamos href
$lockCls  = $isAuth ? '' : ' need-auth';
$lockHref = $isAuth ? null : '#';
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>

  <!-- CSS de terceros -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" rel="stylesheet">
  <!-- Quill (si el alumno usa editor en preguntas) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">
  <script defer src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>


  <!-- Estilos propios -->
  <link rel="stylesheet" href="<?= BASE_URL ?>Assets/css/orion3d.css">

  <!-- Variables globales (aquí, no en el footer) -->
  <script>
    window.BASE_URL = "<?= rtrim(BASE_URL, '/') ?>/";
    window.IS_AUTH = <?= $isAuth ? 'true' : 'false' ?>;
    window.WELCOME_NAME = <?= json_encode($nombre) ?>;
    window.SHOW_WELCOME = <?= isset($_GET['bienvenido']) ? 'true' : 'false' ?>;
    window.ROL = <?= json_encode($user['rol'] ?? 'user') ?>;
    window.CSRF = "<?= csrf_token() ?>"; // 👈 nuevo
  </script>
  <!-- JS global de la app (bloqueo menús + bienvenida) -->
  <script src="<?= BASE_URL ?>Assets/js/app.js?v=<?= time() ?>" defer></script>

  <?php if (!empty($page_functions_css)): ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>Assets/css/<?= htmlspecialchars($page_functions_css) ?>?v=<?= time() ?>">
  <?php endif; ?>
  
</head>

<body>

  <nav class="navbar navbar-expand-lg navbar-dark bg-black py-3 shadow-sm">
    <div class="container">

      <!-- Logo -->
      <a class="navbar-brand fw-bold text-primary" href="<?= BASE_URL ?>">Orion3D</a>

      <!-- Toggler móvil -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarOrion3D" aria-controls="navbarOrion3D" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Menú -->
      <div class="collapse navbar-collapse" id="navbarOrion3D">
        <ul class="navbar-nav ms-auto">

          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>">Inicio</a></li>

          <!-- Cursos Unity -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle<?= $lockCls ?>" href="<?= $lockHref ?? '#' ?>" id="unityMenu"
              role="button" data-bs-toggle="dropdown" aria-expanded="false" data-target="Cursos Unity">
              Cursos Unity
            </a>
            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="unityMenu">
              <li>
                <a class="dropdown-item<?= $lockCls ?>"
                  href="<?= $isAuth ? BASE_URL . 'Cursos/ver/' . $ID_UNITY_3D : '#' ?>"
                  data-target="Unity 3D">Unity 3D</a>
              </li>
              <li>
                <a class="dropdown-item<?= $lockCls ?>"
                  href="<?= $isAuth ? BASE_URL . 'Cursos/ver/' . $ID_UNITY_2D : '#' ?>"
                  data-target="Unity 2D">Unity 2D</a>
              </li>
              <li>
                <a class="dropdown-item<?= $lockCls ?>"
                  href="<?= $isAuth ? BASE_URL . 'Cursos/ver/' . $ID_CSHARP : '#' ?>"
                  data-target="Programación C#">Programación C#</a>
              </li>
            </ul>
          </li>


          <!-- Programación Web -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle<?= $lockCls ?>" href="<?= $lockHref ?? '#' ?>" id="progWebMenu"
              role="button" data-bs-toggle="dropdown" aria-expanded="false" data-target="Programación Web">
              Programación Web
            </a>
            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="progWebMenu">
              <li><a class="dropdown-item<?= $lockCls ?>" href="<?= $isAuth ? BASE_URL . 'Cursos/ver/' . CID_WEB_HTMLCSS : '#' ?>" data-target="HTML & CSS">HTML & CSS</a></li>
              <li><a class="dropdown-item<?= $lockCls ?>" href="<?= $isAuth ? BASE_URL . 'Cursos/ver/' . CID_WEB_JS      : '#' ?>" data-target="JavaScript">JavaScript</a></li>
              <li><a class="dropdown-item<?= $lockCls ?>" href="<?= $isAuth ? BASE_URL . 'Cursos/ver/' . CID_WEB_PHP     : '#' ?>" data-target="PHP & MySQL">PHP & MySQL</a></li>
            </ul>
          </li>

          <!-- Blender -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle<?= $lockCls ?>" href="<?= $lockHref ?? '#' ?>" id="blenderMenu"
              role="button" data-bs-toggle="dropdown" aria-expanded="false" data-target="Blender">
              Modelado 3D con Blender
            </a>
            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="blenderMenu">
              <li><a class="dropdown-item<?= $lockCls ?>" href="<?= $isAuth ? BASE_URL . 'Cursos/ver/' . CID_BLENDER_MODELADO    : '#' ?>" data-target="Modelado 3D">Modelado</a></li>
              <li><a class="dropdown-item<?= $lockCls ?>" href="<?= $isAuth ? BASE_URL . 'Cursos/ver/' . CID_BLENDER_TEXTURIZADO : '#' ?>" data-target="Texturizado">Texturizado</a></li>
              <li><a class="dropdown-item<?= $lockCls ?>" href="<?= $isAuth ? BASE_URL . 'Cursos/ver/' . CID_BLENDER_ANIMACION   : '#' ?>" data-target="Animación">Animación</a></li>
            </ul>
          </li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>Cursos">Catálogo</a></li>


          <!-- Blog -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle<?= $lockCls ?>" href="<?= $lockHref ?? '#' ?>" id="blogMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-target="Blog / Noticias">Blog / Noticias</a>
            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="blogMenu">
              <li><a class="dropdown-item<?= $lockCls ?>" href="<?= $isAuth ? BASE_URL . 'blog/ultimas' : '#' ?>" data-target="Últimas Noticias">Últimas Noticias</a></li>
              <li><a class="dropdown-item<?= $lockCls ?>" href="<?= $isAuth ? BASE_URL . 'blog/destacados' : '#' ?>" data-target="Artículos Destacados">Artículos Destacados</a></li>
            </ul>
          </li>

          <!-- Contacto -->
          <li class="nav-item">
            <a class="nav-link<?= $lockCls ?>" href="<?= $isAuth ? BASE_URL . 'contacto' : '#' ?>" data-target="Contacto">Contacto</a>
          </li>

          <?php if ($isAuth): ?>
            <!-- Mi panel (usuario) -->
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>Usuario">Mi panel</a></li>
            <!-- Admin solo si rol=admin -->
            <?php if ($isAdmin): ?>
              <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>Admin">Admin</a></li>
            <?php endif; ?>
          <?php endif; ?>

        </ul>

        <!-- Derecha: login o usuario -->
        <div class="d-flex align-items-center ms-lg-3 mt-3 mt-lg-0">
          <?php if (!$isAuth): ?>
            <a href="<?= BASE_URL ?>Auth/login" class="btn btn-outline-light btn-sm me-2">Iniciar sesión</a>
            <a href="<?= BASE_URL ?>Auth/register" class="btn btn-primary btn-sm">Registrarse</a>
          <?php else: ?>
            <img src="<?= htmlspecialchars($avatar) ?>" alt="avatar" width="32" height="32" class="rounded-circle border me-2">
            <span class="text-light small me-3">Hola, <?= htmlspecialchars($nombre) ?></span>
            <a href="<?= BASE_URL ?>Auth/logout" class="btn btn-outline-light btn-sm">Salir</a>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </nav>