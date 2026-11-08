<?php

class Documento
{
    private $conexion;

    // Constructor que recibe la conexión a la base de datos (PDO)
    public function __construct($db)
    {
        $this->conexion = $db;
    }

    // 1. ALTA DE DOCUMENTOS (Create)
    public function registrarDocumento($titulo, $archivo_url, $id_categoria, $id_usuario_carga)
    {
        // Generar un token único, largo e impredecible de 32 caracteres (Ej: 8f3a2b...)
        $token = bin2hex(random_bytes(16));

        $sql = "INSERT INTO DOCUMENTO (titulo, archivo_url, codigo_qr, id_categoria, id_usuario_carga, fecha_carga, activo) 
                VALUES (:titulo, :archivo_url, :codigo_qr, :id_categoria, :id_usuario_carga, NOW(), 1)";

        $stmt = $this->conexion->prepare($sql);

        // La vinculación de parámetros evita ataques de Inyección SQL
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':archivo_url', $archivo_url);
        $stmt->bindParam(':codigo_qr', $token);
        $stmt->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
        $stmt->bindParam(':id_usuario_carga', $id_usuario_carga, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // 2. LISTADO DE DOCUMENTOS (Read)
    // Obtiene los documentos uniéndolos con su categoría para mostrar el nombre
    public function obtenerTodosLosDocumentos()
    {
        $sql = "SELECT d.id_documento, d.titulo, d.archivo_url, d.codigo_qr, d.fecha_carga, d.activo, c.nombre AS categoria 
                FROM DOCUMENTO d
                JOIN CATEGORIA c ON d.id_categoria = c.id_categoria
                ORDER BY d.fecha_carga DESC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();

        // Retorna los datos como un arreglo asociativo
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. OBTENER UN DOCUMENTO ESPECÍFICO (Para la vista del QR)
    public function obtenerDocumentoPorId($id_documento)
    {
        $sql = "SELECT d.id_documento, d.titulo, d.archivo_url, d.codigo_qr, d.fecha_carga, d.activo, c.nombre AS categoria 
                FROM DOCUMENTO d
                JOIN CATEGORIA c ON d.id_categoria = c.id_categoria
                WHERE d.id_documento = :id";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $id_documento, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 4. ACTUALIZAR CÓDIGO QR (Update)
    // Función específica para guardar el QR cuando se genere
    public function actualizarQrDocumento($id_documento, $codigo_qr)
    {
        $sql = "UPDATE DOCUMENTO 
                SET codigo_qr = :codigo_qr 
                WHERE id_documento = :id";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':codigo_qr', $codigo_qr);
        $stmt->bindParam(':id', $id_documento, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // 5. BAJA LÓGICA DE DOCUMENTO (Update - Delete alternativo)
    // En sistemas clínicos es mejor desactivar en lugar de borrar físicamente
    public function cambiarEstadoDocumento($id_documento, $activo)
    {
        $sql = "UPDATE DOCUMENTO 
                SET activo = :activo 
                WHERE id_documento = :id";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':activo', $activo, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id_documento, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // 6. ELIMINACIÓN FÍSICA DE DOCUMENTO (Delete)
    public function eliminarDocumento($id_documento)
    {
        $sql = "DELETE FROM DOCUMENTO WHERE id_documento = :id";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $id_documento, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // 7. EDITAR DOCUMENTO (Update)
    public function editarDocumento($id_documento, $titulo, $id_categoria, $archivo_url = null)
    {
        // Verificamos si vino un archivo nuevo
        if ($archivo_url !== null) {
            $sql = "UPDATE DOCUMENTO 
                    SET titulo = :titulo, id_categoria = :id_categoria, archivo_url = :archivo_url 
                    WHERE id_documento = :id";
        } else {
            // Si no hay archivo nuevo, solo actualizamos los datos de texto
            $sql = "UPDATE DOCUMENTO 
                    SET titulo = :titulo, id_categoria = :id_categoria 
                    WHERE id_documento = :id";
        }

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id_documento, PDO::PARAM_INT);

        if ($archivo_url !== null) {
            $stmt->bindParam(':archivo_url', $archivo_url);
        }

        return $stmt->execute();
    }
    // 8. OBTENER DOCUMENTO POR TOKEN (Para visor público)
    public function obtenerDocumentoPorToken($token)
    {
        // Agregamos d.fecha_expiracion_qr al SELECT
        $sql = "SELECT d.id_documento, d.titulo, d.archivo_url, d.codigo_qr, d.fecha_carga, d.activo, c.nombre AS categoria, d.fecha_expiracion_qr 
                FROM DOCUMENTO d
                JOIN CATEGORIA c ON d.id_categoria = c.id_categoria
                WHERE d.codigo_qr = :token AND d.activo = 1";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 9. ACTUALIZAR EXPIRACIÓN DEL QR
    public function actualizarExpiracionQr($id_documento, $fecha_expiracion)
    {
        $sql = "UPDATE DOCUMENTO 
                SET fecha_expiracion_qr = :fecha_expiracion 
                WHERE id_documento = :id";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':fecha_expiracion', $fecha_expiracion);
        $stmt->bindParam(':id', $id_documento, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
