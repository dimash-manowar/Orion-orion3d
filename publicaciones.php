
<div id="wrapper">
    <?php include 'Views/Admin/sidebar.php'; ?>
    <div id="page-content-wrapper">
        <?php include 'Views/Admin/topbar.php'; ?>

        <div class="container-fluid text-light">
            <h2 class="mt-4 mb-4">📝 <?= $page_title ?></h2>

            <div class="table-responsive bg-dark p-3 rounded shadow-sm">
                <table id="tablaPosts" class="table table-dark table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Título</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($publicaciones as $post): ?>
                        <tr>
                            <td><?= $post['id'] ?></td>
                            <td><?= $post['titulo'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($post['creado_at'])) ?></td>
                            <td>
                                <?php if ($post['publicado']): ?>
                                    <span class="badge bg-success">Publicado</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Oculto</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($post['publicado']): ?>
                                    <button class="btn btn-sm btn-warning toggle-estado" data-id="<?= $post['id'] ?>" data-estado="0">Ocultar</button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-success toggle-estado" data-id="<?= $post['id'] ?>" data-estado="1">Publicar</button>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-danger eliminar-post" data-id="<?= $post['id'] ?>">Eliminar</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>




