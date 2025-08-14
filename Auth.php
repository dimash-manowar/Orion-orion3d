<?php
class Auth extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->loadModel('UserModel');
    }

    public function index()
    {
        $data['page_title'] = 'Login - Orion3D';
        $this->view('auth/login', $data);
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ident = trim($_POST['nombre_usuario'] ?? '');
            $password = $_POST['password'] ?? '';

            if (filter_var($ident, FILTER_VALIDATE_EMAIL)) {
                $user = $this->model->getByEmail($ident);
            } else {
                $user = $this->model->getByUsername($ident);
            }

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user'] = [
                    'id'             => (int)$user['id'],
                    'nombre'         => $user['nombre'] ?? $user['name'] ?? $user['nombre_usuario'],
                    'apellido'       => $user['apellido'] ?? '',
                    'nombre_usuario' => $user['nombre_usuario'] ?? '',
                    'email'          => $user['email'] ?? '',
                    'foto'           => $user['foto'] ?? null,
                    'rol'            => $user['rol'] ?? 'usuario',
                ];
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['login']   = true;

                session_regenerate_id(true);
                unset($_SESSION['csrf']);
                csrf_token();

                $_SESSION['alert'] = ['icon' => 'success', 'title' => 'Bienvenido', 'text' => 'Has iniciado sesión correctamente.'];
                header('Location: ' . BASE_URL . '?bienvenido=1');
                exit;
            }

            $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Login fallido', 'text' => 'Credenciales inválidas.'];
            header('Location: ' . BASE_URL . 'Auth');
            exit;
        }

        $data['page_title'] = 'Iniciar sesión - Orion3D';
        $data['page_functions_js'] = 'login.js';
        $this->view('auth/login', $data);
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre'] ?? '');
            $apellido = trim($_POST['apellido'] ?? '');
            $username = trim($_POST['nombre_usuario'] ?? '');
            $email = strtolower(trim($_POST['email'] ?? ''));
            $passwordPlain = $_POST['password'] ?? '';
            if (!csrf_verify($_POST['csrf'] ?? '')) {
                $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Sesión', 'text' => 'Caducó la sesión. Intenta de nuevo.'];
                header('Location: ' . BASE_URL . 'Auth');
                exit;
            }

            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-]{2,60}$/u', $nombre))  return $this->fail('Nombre inválido.');
            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-]{2,60}$/u', $apellido)) return $this->fail('Apellido inválido.');
            if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username))             return $this->fail('Usuario inválido (3-20, letras/números/_).');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL))                    return $this->fail('Email inválido.');

            $passOK = preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $passwordPlain);
            if (!$passOK) return $this->fail('La contraseña no cumple los requisitos.');

            if ($this->model->existsEmail($email))        return $this->fail('Ese email ya está registrado.');
            if ($this->model->existsUsername($username))  return $this->fail('Ese nombre de usuario ya existe.');

            $fotoNombre = null;
            if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === 0) {
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) return $this->fail('Formato de imagen no permitido.');
                if ($_FILES['foto']['size'] > 2 * 1024 * 1024) return $this->fail('La imagen no puede superar 2MB.');
                $fotoNombre = uniqid('user_') . '.' . $ext;
                @mkdir('Assets/imagen/users/', 0775, true);
                move_uploaded_file($_FILES['foto']['tmp_name'], 'Assets/imagen/users/' . $fotoNombre);
            }

            $password = password_hash($passwordPlain, PASSWORD_DEFAULT);

            $ok = $this->model->createUser($nombre, $apellido, $username, $email, $password, $fotoNombre);
            if ($ok) {
                $_SESSION['alert'] = ['icon' => 'success', 'title' => 'Registro exitoso', 'text' => 'Tu cuenta fue creada. Inicia sesión para continuar.'];
                header('Location: ' . BASE_URL . 'Auth');
                exit;
            }
            return $this->fail('No se pudo registrar. ¿Usuario o email duplicado?');
        }

        $data['page_title'] = 'Registro - Orion3D';
        $data['page_functions_js'] = 'registro.js';
        $this->view('auth/register', $data);
    }

    private function fail(string $msg)
    {
        $_SESSION['alert'] = ['icon' => 'error', 'title' => 'Validación', 'text' => $msg];
        header('Location: ' . BASE_URL . 'Auth/register');
        exit;
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        session_start();
        $_SESSION['alert'] = [
            'icon'  => 'success',
            'title' => 'Sesión cerrada',
            'text'  => 'Has cerrado sesión correctamente.'
        ];
        header('Location: ' . BASE_URL . 'Home');
        exit;
    }
}