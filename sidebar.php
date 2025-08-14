<div class="bg-dark border-end sidebar-wrapper d-none d-lg-block" id="sidebar-wrapper" style="min-width:260px">
  <div class="sidebar-heading text-center py-4 text-white">
    <img src="<?= BASE_URL ?>Assets/imagen/frontal.jpg" class="rounded-circle mb-2" width="80" height="80" alt="Usuario">
    <h6 class="mb-0"><?= htmlspecialchars($_SESSION['user']['nombre'] ?? $_SESSION['user']['nombre_usuario'] ?? 'Usuario', ENT_QUOTES, 'UTF-8') ?></h6>
    <small class="text-muted"><?= htmlspecialchars($_SESSION['user']['rol'] ?? 'usuario', ENT_QUOTES, 'UTF-8') ?></small>
  </div>

  <div class="list-group list-group-flush" id="sidebarAccordion">
    <a href="<?= BASE_URL ?>Admin/index" class="list-group-item list-group-item-action bg-dark text-white">🏠 Dashboard</a>

    <!-- Cursos Unity -->
    <a class="list-group-item list-group-item-action bg-dark text-white d-flex justify-content-between align-items-center"
       data-bs-toggle="collapse" href="#submenuUnityUser" role="button" aria-expanded="false" aria-controls="submenuUnityUser">
      🎮 Cursos Unity <i class="bi bi-chevron-down small"></i>
    </a>
    <div class="collapse ps-3" id="submenuUnityUser" data-bs-parent="#sidebarAccordion">
      <a href="<?= BASE_URL ?>Cursos/ver/<?= CID_UNITY_3D ?>" class="list-group-item bg-secondary text-white">Unity 3D</a>
      <a href="<?= BASE_URL ?>Cursos/ver/<?= CID_UNITY_2D ?>" class="list-group-item bg-secondary text-white">Unity 2D</a>
      <a href="<?= BASE_URL ?>Cursos/ver/<?= CID_CSHARP   ?>" class="list-group-item bg-secondary text-white">Programación C#</a>
    </div>

    <!-- Cursos Web -->
    <a class="list-group-item list-group-item-action bg-dark text-white d-flex justify-content-between align-items-center"
       data-bs-toggle="collapse" href="#submenuWebUser" role="button" aria-expanded="false" aria-controls="submenuWebUser">
      💻 Cursos Web <i class="bi bi-chevron-down small"></i>
    </a>
    <div class="collapse ps-3" id="submenuWebUser" data-bs-parent="#sidebarAccordion">
      <a href="<?= BASE_URL ?>Cursos/ver/<?= CID_WEB_HTMLCSS ?>" class="list-group-item bg-secondary text-white">HTML & CSS</a>
      <a href="<?= BASE_URL ?>Cursos/ver/<?= CID_WEB_JS      ?>" class="list-group-item bg-secondary text-white">JavaScript</a>
      <a href="<?= BASE_URL ?>Cursos/ver/<?= CID_WEB_PHP     ?>" class="list-group-item bg-secondary text-white">PHP & MySQL</a>
    </div>

    <!-- Blender -->
    <a class="list-group-item list-group-item-action bg-dark text-white d-flex justify-content-between align-items-center"
       data-bs-toggle="collapse" href="#submenuBlenderUser" role="button" aria-expanded="false" aria-controls="submenuBlenderUser">
      🧊 Cursos Blender <i class="bi bi-chevron-down small"></i>
    </a>
    <div class="collapse ps-3" id="submenuBlenderUser" data-bs-parent="#sidebarAccordion">
      <a href="<?= BASE_URL ?>Cursos/ver/<?= CID_BLENDER_MODELADO    ?>" class="list-group-item bg-secondary text-white">Modelado</a>
      <a href="<?= BASE_URL ?>Cursos/ver/<?= CID_BLENDER_TEXTURIZADO ?>" class="list-group-item bg-secondary text-white">Texturizado</a>
      <a href="<?= BASE_URL ?>Cursos/ver/<?= CID_BLENDER_ANIMACION   ?>" class="list-group-item bg-secondary text-white">Animación</a>
    </div>

    <!-- Gestión -->
    
    <a href="<?= BASE_URL ?>Admin/publicaciones" class="list-group-item list-group-item-action bg-dark text-white">📰 Publicaciones</a>
    <a href="<?= BASE_URL ?>Admin/usuarios"      class="list-group-item list-group-item-action bg-dark text-white">👥 Usuarios</a>
    <a href="<?= BASE_URL ?>Admin/configuracion" class="list-group-item list-group-item-action bg-dark text-white">⚙️ Configuración</a>
  </div>
</div>
