<?php
require_once '../../servicios_comunes/conexion_BD/conexion.php';
require_once '../modelo/documentacion_modelo.php';

// Ahora leemos el token criptográfico 't' en lugar del 'id' adivinable
$token = $_GET['t'] ?? null;
if (!$token) die("Enlace inválido o token no proporcionado.");

$docModelo = new Documento($conexion);
$documento = $docModelo->obtenerDocumentoPorToken($token);

// Validamos que exista, esté activo y tenga el archivo físico
if (!$documento || !file_exists($documento['archivo_url'])) {
    die("El documento físico no se encuentra disponible o fue retirado.");
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