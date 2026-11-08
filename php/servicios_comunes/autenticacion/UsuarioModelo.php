<?php
require_once '../conexion_BD/conexion.php';

class UsuarioModelo
{

    // Buscar un funcionario en la tabla USUARIO por su email
    public static function buscarPorEmail($email)
    {
        global $conexion;

        try {
            $sql = "SELECT id_usuario, nombre_usuario, contrasena_hash, nombre, apellido, email 
                    FROM USUARIO 
                    WHERE email = :email AND activo = 1 
                    LIMIT 1";

            $stmt = $conexion->prepare($sql);
            $stmt->execute([':email' => $email]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en UsuarioModelo::buscarPorEmail -> " . $e->getMessage());
            return false;
        }
    }

    // Registrar la fecha actual en la columna ultimo_login
    public static function registrarUltimoLogin($id_usuario)
    {
        global $conexion;
        try {
            $sql = "UPDATE USUARIO SET ultimo_login = CURRENT_DATE WHERE id_usuario = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id' => $id_usuario]);
        } catch (PDOException $e) {
            error_log("Error al registrar ultimo_login: " . $e->getMessage());
        }
    }
}
