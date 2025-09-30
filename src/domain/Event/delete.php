<?php
session_start();
header('Content-Type: application/json');
include '../../infrastructure/persistence/conection.php';

function send_json_response($status_code, $status, $message) {
    http_response_code($status_code);
    echo json_encode(['status' => $status, 'message' => $message]);
    exit();
}

// 1. Seguridad: Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    send_json_response(401, 'error', 'No autorizado.');
}

// 2. Validar entrada: Verificar que se proporcionó un ID numérico
// El método debe ser DELETE para seguir las convenciones REST.
if ($_SERVER["REQUEST_METHOD"] != "DELETE") {
    send_json_response(405, 'error', 'Método no permitido.');
}

// Para leer el body de una petición DELETE
parse_str(file_get_contents("php://input"), $delete_vars);
$id_to_delete = isset($delete_vars['id']) && is_numeric($delete_vars['id']) ? (int)$delete_vars['id'] : null;

if ($id_to_delete === null) {
    send_json_response(400, 'error', 'Petición incorrecta. Se requiere un ID de usuario válido en el cuerpo de la solicitud.');
}
$logged_in_user_id = (int)$_SESSION['usuario_id'];

// 3. Preparar y ejecutar la sentencia DELETE de forma segura
$stmt = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
if ($stmt === false) {
    send_json_response(500, 'error', 'Error interno del servidor al preparar la consulta.');
}

$stmt->bind_param("i", $id_to_delete);

if ($stmt->execute()) {
    $stmt->close();
    $conexion->close();

    // 4. Si el usuario borró su propia cuenta, se destruye la sesión.
    if ($id_to_delete === $logged_in_user_id) {
        session_unset();
        session_destroy();
        send_json_response(200, 'success', 'Tu cuenta ha sido eliminada correctamente. Se ha cerrado la sesión.');
    } else {
        send_json_response(200, 'success', 'Usuario eliminado correctamente.');
    }
} else {
    $error_message = $stmt->error;
    $stmt->close();
    $conexion->close();
    send_json_response(500, 'error', 'Error al eliminar el usuario: ' . $error_message);
}
?>