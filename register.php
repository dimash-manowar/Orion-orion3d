<div class="container mt-5">
    <h2 class="text-center text-light mb-4">Registro en Orion3D</h2>
    <form action="<?= BASE_URL ?>Auth/register" method="POST" enctype="multipart/form-data" novalidate><?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label text-light">Nombre</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label text-light">Apellido</label>
            <input type="text" name="apellido" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label text-light">Nombre de usuario</label>
            <input type="text" name="nombre_usuario" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label text-light">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label text-light">Contraseña</label>
            <input type="password" name="password" class="form-control" required
                pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}"
                title="Mínimo 8 caracteres con mayúscula, minúscula, número y símbolo.">
            <div class="form-text text-secondary">8+ con mayúscula, minúscula, número y símbolo.</div>
        </div>
        <div class="mb-3">
            <label class="form-label text-light">Foto de perfil (opcional)</label>
            <input type="file" name="foto" class="form-control" accept="image/*">
        </div>
        
        <button type="submit" class="btn btn-success w-100">Registrarse</button>
    </form>
    <p class="mt-3 text-light text-center">
        ¿Ya tienes cuenta? <a href="<?= BASE_URL ?>Auth" class="text-info">Inicia sesión aquí</a>
    </p>
</div>