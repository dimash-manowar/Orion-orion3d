<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Contacto extends Controller
{
    public function __construct()
    {
        parent::__construct();
        // Cargar modelo ContactoModel
        $this->model = new ContactoModel();
    }

    public function index()
    {
        $data['page_title'] = 'Contacto - Orion3D';
        $data['page_functions_js'] = "contactos.js";
        $this->view('Contacto/contacto', $data);
    }

    public function enviar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre  = trim($_POST['nombre'] ?? '');
            $email   = trim($_POST['email'] ?? '');
            $mensaje = trim($_POST['mensaje'] ?? '');

            if (empty($nombre) || empty($email) || empty($mensaje)) {
                $this->respuestaJson('error', 'Todos los campos son obligatorios.');
                return;
            }

            // Guardar en la base de datos
            $id = $this->model->insertarMensaje($nombre, $email, $mensaje);

            if ($id > 0) {
                // Enviar correo con PHPMailer
                if ($this->enviarCorreo($nombre, $email, $mensaje)) {
                    $this->respuestaJson('success', 'Mensaje enviado y guardado correctamente.');
                } else {
                    $this->respuestaJson('warning', 'Mensaje guardado, pero no se pudo enviar el email.');
                }
            } else {
                $this->respuestaJson('error', 'Error al guardar el mensaje.');
            }
        }
    }

    private function enviarCorreo($nombre, $email, $mensaje)
    {
        require_once __DIR__ . '/../vendor/autoload.php'; // Asegúrate de instalar PHPMailer con Composer

        $mail = new PHPMailer(true);

        try {
            // Configuración SMTP
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST; // Cambia por tu servidor SMTP
            $mail->SMTPAuth   = true;
            $mail->Username   = EMAIL_REMITENTE;
            $mail->Password   = PASSWORD_EMAIL;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Remitente y destinatario
            $mail->setFrom(EMAIL_REMITENTE, 'Formulario Orion3D');
            $mail->addAddress(EMAIL_REMITENTE, 'Administrador');

            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = 'Nuevo mensaje de contacto';
            $mail->Body    = "
                <h3>Nuevo mensaje recibido</h3>
                <p><strong>Nombre:</strong> {$nombre}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Mensaje:</strong><br>{$mensaje}</p>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error al enviar correo: " . $mail->ErrorInfo);
            return false;
        }
    }

    private function respuestaJson($status, $message)
    {
        header('Content-Type: application/json');
        echo json_encode(['status' => $status, 'message' => $message]);
    }
}
