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
            // ... lógica, validaciones y llamado a la base de datos ...
            echo json_encode(["status" => "success", "mensaje" => "Guardado."]);
            exit(); // CLAVE: Corta el script para no imprimir el HTML de abajo

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
