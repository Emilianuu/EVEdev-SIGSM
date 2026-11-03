<?php
require_once '../../servicios_comunes/conexion_BD/conexion.php';
require_once '../modelo/documentacion_modelo.php';

$id = $_GET['id'] ?? null;
if (!$id) die("ID inválido.");

$docModelo = new Documento($conexion);
$documento = $docModelo->obtenerDocumentoPorId($id);

if (!$documento || !file_exists($documento['archivo_url'])) {
    die("El documento físico no se encuentra disponible.");
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($documento['titulo']) ?></title>
</head>

<body style="margin: 0; padding: 0; height: 100vh; overflow: hidden;">
    <iframe src="<?= htmlspecialchars($documento['archivo_url']) ?>" width="100%" height="100%" style="border: none;"></iframe>
</body>

</html>