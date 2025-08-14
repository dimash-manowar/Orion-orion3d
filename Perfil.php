<?php
class Perfil extends Controller
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        parent::__construct();
        // 👇 Forzamos UserModel (tus métodos existen aquí)
        $this->loadModel('UserModel');
    }

    public function index()
    {
        if (empty($_SESSION['user'])) {
            header("Location: " . BASE_URL . "Auth");
            exit;
        }
        $data['page_title'] = "Editar Perfil - Orion3D";
        $data['usuario'] = $_SESSION['user'];
        $data['page_functions_js'] = 'admin/perfil.js';
        $this->view('Usuarios/perfil', $data);
    }

    public function actualizar()
    {
        if (empty($_SESSION['user'])) {
            header("Location: " . BASE_URL . "Auth");
            exit;
        }
        if (!csrf_verify($_POST['csrf'] ?? '')) {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Sesión', 'text' => 'Caducó la sesión.'];
            header("Location: " . BASE_URL . "Usuario/perfil");
            exit;
        }


        $id       = (int)$_SESSION['user']['id'];
        $nombre   = trim($_POST['nombre']   ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $nuevaFoto = null;

        // Validaciones básicas
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))  return $this->fail('Email inválido.');
        if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s\-]{2,60}$/u', $nombre))  return $this->fail('Nombre inválido.');
        if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s\-]{2,60}$/u', $apellido)) return $this->fail('Apellido inválido.');

        // Email único (excluyendo mi propio id)
        if ($this->model->existsEmailExceptId($email, $id)) {
            return $this->fail('Ese email ya está en uso por otro usuario.');
        }

        // Password opcional: si viene, debe ser fuerte
        $passwordHash = null;
        if (!empty($password)) {
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
                return $this->fail('La nueva contraseña debe tener 8+ con mayúscula, minúscula, número y símbolo.');
            }
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        }

        // Foto opcional
        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) return $this->fail('Formato de imagen no permitido (JPG/PNG/WebP).');
            if ($_FILES['foto']['size'] > 2 * 1024 * 1024)       return $this->fail('La imagen no puede superar 2MB.');
            $nuevaFoto = uniqid('user_') . '.' . $ext;
            @mkdir('Assets/imagen/users/', 0775, true);
            move_uploaded_file($_FILES['foto']['tmp_name'], 'Assets/imagen/users/' . $nuevaFoto);
        }

        // Actualizar
        $ok = $this->model->actualizarPerfil($id, $nombre, $apellido, $email, $passwordHash, $nuevaFoto);
        if ($ok) {
            // Refrescar sesión con datos actuales
            $_SESSION['user'] = $this->model->getById($id);
            $_SESSION['alert'] = ['icon' => 'success', 'title' => 'Perfil actualizado', 'text' => 'Tus datos se han guardado correctamente.'];
        } else {
            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Error', 'text' => 'Hubo un problema al actualizar tu perfil.'];
        }

        header("Location: " . BASE_URL . "Usuario/perfil");
        exit;
    }

    // (Opcional) Endpoint para validar email en vivo
    public function checkEmail()
    {
        if (empty($_SESSION['user'])) {
            http_response_code(401);
            exit;
        }
        $email = strtolower(trim($_GET['email'] ?? ''));
        $id    = (int)$_SESSION['user']['id'];
        $exists = $email && $this->model->existsEmailExceptId($email, $id);
        header('Content-Type: application/json');
        echo json_encode(['exists' => $exists]);
    }

    private function fail(string $msg)
    {
        $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Validación', 'text' => $msg];
        header("Location: " . BASE_URL . "Usuario/perfil");
        exit;
    }
}