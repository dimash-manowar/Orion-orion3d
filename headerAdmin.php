<?php if (!isset($page_title)) $page_title = 'Orion3D Admin'; ?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($page_title) ?></title>

  <!-- CSS de tu panel -->
  <link rel="stylesheet" href="<?= BASE_URL ?>Assets/css/admin/orion3d_dashboard.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>Assets/css/orion3d.css">

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

  <!-- Quill opcional -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">
  <script defer src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>

  <!-- Globals -->
  <script>
    window.BASE_URL = "<?= rtrim(BASE_URL,'/') ?>/";
    window.CSRF = "<?= csrf_token() ?>";
  </script>
</head>
<body class="bg-dark text-light" data-scope="admin">
<div id="wrapper" class="d-flex">
