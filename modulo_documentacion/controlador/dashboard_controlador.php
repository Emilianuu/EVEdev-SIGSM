<?php
session_start();

// Verificación de sesión obligatoria
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../servicios_comunes/autenticacion/login_controlador.php");
    exit();
}

// 1. SI ES UNA PETICIÓN POST (Llamadas desde tu fetch en Javascript)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
        case 'alta_documento':
            require_once '../../servicios_comunes/conexion_BD/conexion.php';
            require_once '../modelo/documentacion_modelo.php';

            $titulo = $_POST['titulo'] ?? '';
            $id_categoria = $_POST['id_categoria'] ?? '';
            $id_usuario = $_SESSION['id_usuario']; // El usuario logueado

            if (empty($titulo) || empty($id_categoria) || empty($_FILES['archivo'])) {
                echo json_encode(["status" => "error", "mensaje" => "Faltan datos obligatorios."]);
                exit();
            }

            $archivo = $_FILES['archivo'];

            // Validar que sea un PDF
            if ($archivo['type'] !== 'application/pdf') {
                echo json_encode(["status" => "error", "mensaje" => "Solo se permiten archivos PDF."]);
                exit();
            }

            // Crear carpeta si no existe (por seguridad)
            $directorio_destino = '../archivos/';
            if (!is_dir($directorio_destino)) {
                mkdir($directorio_destino, 0777, true);
            }

            // Generar un nombre único para evitar que se sobreescriban archivos con el mismo nombre
            $nombre_archivo = time() . "_" . basename($archivo['name']);
            $ruta_fisica = $directorio_destino . $nombre_archivo;

            // Esta es la ruta relativa que guardaremos en la base de datos
            $ruta_bd = '../archivos/' . $nombre_archivo;

            // Mover el archivo subido a nuestra carpeta física
            if (move_uploaded_file($archivo['tmp_name'], $ruta_fisica)) {

                // Guardar en la base de datos
                $docModelo = new Documento($conexion);
                $resultado = $docModelo->registrarDocumento($titulo, $ruta_bd, $id_categoria, $id_usuario);

                if ($resultado) {
                    echo json_encode(["status" => "success", "mensaje" => "Guardado correctamente."]);
                } else {
                    echo json_encode(["status" => "error", "mensaje" => "Error al insertar en la base de datos."]);
                }
            } else {
                echo json_encode(["status" => "error", "mensaje" => "Fallo al mover el archivo en el servidor."]);
            }
            exit();

        case 'eliminar_documento':
            // ... lógica de baja ...
            echo json_encode(["status" => "success", "mensaje" => "Eliminado."]);
            exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['accion']) && $_GET['accion'] === 'listar_documentos') {

    // 1. Importar la conexión a la BD para que exista la variable $conexion
    require_once '../../servicios_comunes/conexion_BD/conexion.php';

    // 2. Importar el modelo
    require_once '../modelo/documentacion_modelo.php';

    // 3. Instanciar pasándole la conexión válida
    $docModelo = new Documento($conexion);

    $listaDocs = $docModelo->obtenerTodosLosDocumentos();

    // Imprimimos el JSON y cortamos la ejecución para no imprimir el HTML de la vista
    echo json_encode(["status" => "success", "data" => $listaDocs]);
    exit();
}

require_once '../vista/panel-de-administracion.php';
