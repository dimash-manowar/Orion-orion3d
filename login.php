<div class="container mt-5">
  <h2 class="text-center text-light mb-4">Iniciar sesión en Orion3D</h2>
  <form action="<?= BASE_URL ?>Auth/login" method="POST" novalidate><?= csrf_field() ?>
    <div class="mb-3">
      <label class="form-label text-light">Email o nombre de usuario</label>
      <input type="text" name="nombre_usuario" class="form-control" required autocomplete="off">
    </div>
    <div class="mb-3">
      <label class="form-label text-light">Contraseña</label>
      <input type="password" name="password" class="form-control" required minlength="8" autocomplete="off">
      <div class="form-text text-secondary">Mínimo 8 caracteres.</div>
    </div>
    <button type="submit" class="btn btn-primary w-100">Entrar</button>
  </form>
  <p class="mt-3 text-light text-center">
    ¿No tienes cuenta? <a href="<?= BASE_URL ?>Auth/register" class="text-info">Regístrate aquí</a>
  </p>
</div>
