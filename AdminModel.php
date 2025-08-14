<?php
class AdminModel extends Mysql
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Obtiene todos los mensajes (opcional, útil para el dashboard)
     */
    public function obtenerMensajes()
    {
        $sql = "SELECT id, nombre, email, mensaje, leido, creado_at FROM contactos ORDER BY creado_at DESC";
        return $this->select($sql);
    }
    // Contar mensajes no leídos
    public function contarNoLeidos()
    {
        $sql = "SELECT COUNT(*) as total FROM contactos WHERE leido = 0";
        $result = $this->select_one($sql);
        return $result['total'];
    }
    public function marcarLeido($id)
    {
        $sql = "UPDATE contactos SET leido = 1 WHERE id = ?";
        return $this->update($sql, [$id]);
    }

    public function eliminarMensaje($id)
    {
        $sql = "DELETE FROM contactos WHERE id = ?";
        return $this->delete($sql, [$id]);
    }
    // Contar usuarios activos
    public function contarUsuarios()
    {
        $sql = "SELECT COUNT(*) as total FROM usuarios";
        $result = $this->select_one($sql);
        return $result['total'];
    }
    // Contar publicaciones activas
    public function contarPublicaciones()
    {
        $sql = "SELECT COUNT(*) as total FROM posts WHERE publicado = 1";
        $result = $this->select_one($sql);
        return $result['total'];
    }
    public function obtenerPublicaciones()
    {
        $sql = "SELECT id, titulo, creado_at, publicado FROM posts ORDER BY creado_at DESC";
        return $this->select($sql);
    }

    public function eliminarPublicacion($id)
    {
        $sql = "DELETE FROM posts WHERE id = ?";
        return $this->delete($sql, [$id]);
    }

    public function cambiarEstado($id, $estado)
    {
        $sql = "UPDATE posts SET publicado = ? WHERE id = ?";
        return $this->update($sql, [$estado, $id]);
    }
    // --- QnA: listado resumido para tarjetas
    public function obtenerPreguntasQnA(?string $estado = null, ?string $q = null): array
    {
        $params = [];
        $filter = "";
        if ($estado && in_array($estado, ['abierta', 'respondida', 'cerrada'])) {
            $filter .= " AND p.estado = ?";
            $params[] = $estado;
        }
        if ($q) {
            $filter .= " AND (u.nombre_usuario LIKE ? OR u.nombre LIKE ? OR l.titulo LIKE ?)";
            $params[] = "%$q%";
            $params[] = "%$q%";
            $params[] = "%$q%";
        }

        $sql = "SELECT p.id, p.curso_id, p.leccion_id, p.contenido_html, p.imagen, p.creado_at, p.estado, p.leido,
                   u.nombre_usuario, u.nombre, u.foto,
                   l.titulo AS leccion_titulo
            FROM preguntas p
            JOIN users u ON u.id = p.usuario_id
            LEFT JOIN lecciones l ON l.id = p.leccion_id
            WHERE 1=1 $filter
            ORDER BY p.leido ASC, p.creado_at DESC
            LIMIT 200";
        return $this->select($sql, $params);
    }

    // --- QnA: hilo completo (pregunta + respuestas)
    public function obtenerHiloQnA(int $preguntaId): array
    {
        $preg = $this->select_one(
            "SELECT p.*, u.nombre_usuario,u.email AS alumno_email, u.nombre, u.foto, l.titulo AS leccion_titulo
         FROM preguntas p
         JOIN users u ON u.id = p.usuario_id
         LEFT JOIN lecciones l ON l.id = p.leccion_id
         WHERE p.id = ?",
            [$preguntaId]
        );

        $resps = $this->select(
            "SELECT r.*, a.nombre_usuario AS admin_usuario, a.nombre AS admin_nombre
         FROM respuestas_preguntas r
         JOIN users a ON a.id = r.admin_id
         WHERE r.pregunta_id = ?
         ORDER BY r.id ASC",
            [$preguntaId]
        );

        return ['pregunta' => $preg, 'respuestas' => $resps];
    }

    // --- acciones
    public function marcarLeidoPregunta(int $id, int $leido = 1): bool
    {
        return $this->update("UPDATE preguntas SET leido=? WHERE id=?", [$leido, $id]);
    }
    public function cambiarEstadoPregunta(int $id, string $estado): bool
    {
        if (!in_array($estado, ['abierta', 'respondida', 'cerrada'])) return false;
        return $this->update("UPDATE preguntas SET estado=? WHERE id=?", [$estado, $id]);
    }
    public function responderPreguntaQnA(int $preguntaId, int $adminId, string $html, ?string $img): int
    {
        return (int)$this->insert(
            "INSERT INTO respuestas_preguntas (pregunta_id, admin_id, contenido_html, imagen) VALUES (?,?,?,?)",
            [$preguntaId, $adminId, $html, $img]
        );
    }
    // Cuenta preguntas sin respuesta
    public function contarQnaPendientes(): int
    {
        $row = $this->select_one("
        SELECT COUNT(*) AS total
        FROM qna_preguntas q
        LEFT JOIN qna_respuestas r ON r.pregunta_id = q.id
        WHERE r.id IS NULL
    ");
        return (int)($row['total'] ?? 0);
    }

    // Últimas preguntas sin respuesta
    public function ultimasQnaPendientes(int $limit = 10): array
    {
        $limit = max(1, (int)$limit); // 👈 cast y saneo
        $sql = "SELECT 
              q.id, q.contenido_html, q.imagen, q.creado_at,
              u.nombre_usuario,
              c.titulo   AS curso,
              l.titulo   AS leccion
            FROM qna_preguntas q
            JOIN users u    ON u.id = q.usuario_id
            JOIN cursos c   ON c.id = q.curso_id
            JOIN lecciones l ON l.id = q.leccion_id
            LEFT JOIN qna_respuestas r ON r.pregunta_id = q.id
            WHERE r.id IS NULL
            ORDER BY q.id DESC
            LIMIT $limit";         // 👈 sin placeholder
        return $this->select($sql);    // 👈 sin params
    }
     public function crear(int $usuarioId, string $titulo, string $cuerpo=null, string $link=null): int {
        return (int)$this->insert(
            "INSERT INTO notificaciones (usuario_id, titulo, cuerpo, link) VALUES (?,?,?,?)",
            [$usuarioId, $titulo, $cuerpo, $link]
        );
    }

    public function listar(int $usuarioId, int $limit=20): array {
        return $this->select(
            "SELECT id, titulo, cuerpo, link, leido, creado_at
             FROM notificaciones
             WHERE usuario_id = ?
             ORDER BY leido ASC, creado_at DESC
             LIMIT $limit", [$usuarioId]
        );
    }

    public function contarNoLeidas(int $usuarioId): int {
        $r = $this->select_one("SELECT COUNT(*) c FROM notificaciones WHERE usuario_id=? AND leido=0", [$usuarioId]);
        return (int)($r['c'] ?? 0);
    }

    public function marcarLeida(int $usuarioId, int $id): bool {
        return $this->update("UPDATE notificaciones SET leido=1 WHERE id=? AND usuario_id=?", [$id, $usuarioId]);
    }

    public function marcarTodasLeidas(int $usuarioId): bool {
        return $this->update("UPDATE notificaciones SET leido=1 WHERE usuario_id=? AND leido=0", [$usuarioId]);
    }
}
