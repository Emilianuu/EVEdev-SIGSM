<?php
session_start();

date_default_timezone_set('America/Montevideo');

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

            $titulo_raw = $_POST['titulo'] ?? '';
            $titulo = htmlspecialchars(trim($titulo_raw), ENT_QUOTES, 'UTF-8');
            $id_categoria = $_POST['id_categoria'] ?? '';
            $id_usuario = $_SESSION['id_usuario']; // El usuario logueado

            if (empty($titulo) || empty($id_categoria) || empty($_FILES['archivo'])) {
                echo json_encode(["status" => "error", "mensaje" => "Faltan datos obligatorios."]);
                exit();
            }

            $archivo = $_FILES['archivo'];

            // 1. Validar el tamaño máximo
            $max_size = 15 * 1024 * 1024; // 15 MB en bytes
            if ($archivo['size'] > $max_size) {
                echo json_encode(["status" => "error", "mensaje" => "El archivo supera el tamaño máximo permitido de 15 MB."]);
                exit();
            }

            // 2. Validar el tipo MIME de forma estricta leyendo la cabecera real del archivo
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_real = $finfo->file($archivo['tmp_name']);

            if ($mime_real !== 'application/pdf') {
                echo json_encode(["status" => "error", "mensaje" => "El archivo físico no es un PDF válido o está corrupto."]);
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
            require_once '../../servicios_comunes/conexion_BD/conexion.php';
            require_once '../modelo/documentacion_modelo.php';

            $id_documento = $_POST['id_documento'] ?? '';

            if (empty($id_documento)) {
                echo json_encode(["status" => "error", "mensaje" => "ID no proporcionado."]);
                exit();
            }

            $docModelo = new Documento($conexion);

            // 1. Obtener la ruta del archivo para borrar el PDF físico
            $documento = $docModelo->obtenerDocumentoPorId($id_documento);
            if ($documento && !empty($documento['archivo_url'])) {
                // Si el archivo físico existe en la ruta, lo eliminamos con unlink()
                if (file_exists($documento['archivo_url'])) {
                    unlink($documento['archivo_url']);
                }
            }

            // 2. Eliminar de la base de datos
            $resultado = $docModelo->eliminarDocumento($id_documento);

            if ($resultado) {
                echo json_encode(["status" => "success", "mensaje" => "Documento y archivo eliminados correctamente."]);
            } else {
                echo json_encode(["status" => "error", "mensaje" => "Error al eliminar de la base de datos."]);
            }
            exit();

        case 'editar_documento':
            require_once '../../servicios_comunes/conexion_BD/conexion.php';
            require_once '../modelo/documentacion_modelo.php';

            $id_documento = $_POST['id_documento'] ?? '';
            $titulo_raw = $_POST['titulo'] ?? '';
            $titulo = htmlspecialchars(trim($titulo_raw), ENT_QUOTES, 'UTF-8');
            $id_categoria = $_POST['id_categoria'] ?? '';

            if (empty($id_documento) || empty($titulo) || empty($id_categoria)) {
                echo json_encode(["status" => "error", "mensaje" => "Faltan datos obligatorios."]);
                exit();
            }

            $ruta_bd = null; // Por defecto asumimos que no cambiaremos el archivo

            // Verificamos si el usuario adjuntó un archivo nuevo
            if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
                $archivo = $_FILES['archivo'];

                // Validar el tamaño máximo
                $max_size = 15 * 1024 * 1024; // 15 MB en bytes
                if ($archivo['size'] > $max_size) {
                    echo json_encode(["status" => "error", "mensaje" => "El archivo supera el tamaño máximo permitido de 15 MB."]);
                    exit();
                }

                // Validar el tipo MIME de forma estricta
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime_real = $finfo->file($archivo['tmp_name']);

                $directorio_destino = '../archivos/';
                if (!is_dir($directorio_destino)) {
                    mkdir($directorio_destino, 0777, true);
                }

                // Generar un nombre único
                $nombre_archivo = time() . "_" . basename($archivo['name']);
                $ruta_fisica = $directorio_destino . $nombre_archivo;

                // Mover el archivo nuevo
                if (move_uploaded_file($archivo['tmp_name'], $ruta_fisica)) {
                    $ruta_bd = '../archivos/' . $nombre_archivo;
                } else {
                    echo json_encode(["status" => "error", "mensaje" => "Fallo al mover el archivo en el servidor."]);
                    exit();
                }
            }

            // Instanciar modelo y ejecutar actualización
            $docModelo = new Documento($conexion);
            $resultado = $docModelo->editarDocumento($id_documento, $titulo, $id_categoria, $ruta_bd);

            if ($resultado) {
                echo json_encode(["status" => "success", "mensaje" => "Documento actualizado correctamente."]);
            } else {
                echo json_encode(["status" => "error", "mensaje" => "Error al actualizar la base de datos."]);
            }
            exit();

        case 'actualizar_expiracion_qr':
            require_once '../../servicios_comunes/conexion_BD/conexion.php';
            require_once '../modelo/documentacion_modelo.php';

            $id_documento = $_POST['id_documento'] ?? '';
            $horas = $_POST['horas'] ?? 'null';

            if (empty($id_documento)) {
                echo json_encode(["status" => "error", "mensaje" => "ID no proporcionado."]);
                exit();
            }

            $fecha_expiracion = null;
            // Calculamos la fecha y hora exacta sumando las horas enviadas
            if ($horas !== 'null' && is_numeric($horas)) {
                $fecha_expiracion = date('Y-m-d H:i:s', strtotime("+$horas hours"));
            }

            $docModelo = new Documento($conexion);
            $resultado = $docModelo->actualizarExpiracionQr($id_documento, $fecha_expiracion);

            if ($resultado) {
                echo json_encode(["status" => "success"]);
            } else {
                echo json_encode(["status" => "error", "mensaje" => "Error al actualizar la expiración."]);
            }
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
