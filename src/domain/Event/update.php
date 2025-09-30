<?php
session_start();
header('Content-Type: application/json');
include '../../infrastructure/persistence/conection.php';

function send_json_response($status_code, $status, $message) {
    http_response_code($status_code);
    echo json_encode(['status' => $status, 'message' => $message]);
    exit();
}

// Verificar si el usuario está logueado y si la solicitud es POST
if (!isset($_SESSION['usuario_id']) || $_SERVER["REQUEST_METHOD"] != "POST") {
    send_json_response(401, 'error', 'No autorizado.');
}

// Validar que los campos no estén vacíos
if (empty($_POST['id']) || empty($_POST['nombre']) || empty($_POST['email'])) {
    send_json_response(400, 'error', 'Error: Todos los campos son obligatorios.');
}

$id = (int)$_POST['id'];
$nombre = $_POST['nombre'];
$email = $_POST['email'];

// Preparar la consulta UPDATE para evitar inyección SQL
$stmt = $conexion->prepare("UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?");
if ($stmt === false) {
    send_json_response(500, 'error', 'Error interno del servidor al preparar la consulta.');
}

$stmt->bind_param("ssi", $nombre, $email, $id);

if ($stmt->execute()) {
    send_json_response(200, 'success', 'Usuario actualizado correctamente.');
} else {
    send_json_response(500, 'error', 'Error al actualizar el usuario: ' . $stmt->error);
}

$stmt->close();
$conexion->close();
?>