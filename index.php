<?php 
$page_title = "Panel de Administración - Orion3D"; 

?>
<?php
$urlActual = $_GET['url'] ?? '';
$seccion = explode('/', strtolower($urlActual))[1] ?? 'admin'; // obtiene "programacionweb", "mensajes", etc.
?>

<div id="wrapper">
    <?php include 'Views/Admin/headerAdmin.php';?>
    <?php include 'Views/Admin/sidebar.php'; ?>

    <div id="page-content-wrapper">
        <?php include 'Views/Admin/topbar.php'; ?>

        <div class="container-fluid text-light">
            <h1 class="mb-4 mt-3">Bienvenido al Panel de Administración</h1>

            <div class="row g-4">

                <!-- Tarjeta: Mensajes -->
                <div class="col-md-4">
                    <a href="<?= BASE_URL ?>admin/mensajes" class="text-decoration-none">
                        <div class="card bg-dark border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="card-title text-light">Mensajes nuevos</h5>
                                <p class="card-text display-6 text-primary"><?= $mensajes ?></p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Tarjeta: Publicaciones -->
                <div class="col-md-4">
                    <a href="<?= BASE_URL ?>admin/publicaciones" class="text-decoration-none">
                        <div class="card bg-dark border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="card-title text-light">Publicaciones</h5>
                                <p class="card-text display-6 text-success"><?= $mensajes ?></p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Tarjeta: Usuarios -->
                <div class="col-md-4">
                    <a href="<?= BASE_URL ?>admin/usuarios" class="text-decoration-none">
                        <div class="card bg-dark border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="card-title text-light">Usuarios activos</h5>
                                <p class="card-text display-6 text-warning"><?= $mensajes ?></p>
                            </div>
                        </div>
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
<?php include 'Views/Admin/footerAdmin.php'; ?>


