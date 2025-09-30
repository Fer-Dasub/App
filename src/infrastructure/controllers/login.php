<?php
session_start();
header('Content-Type: application/json'); // Indicar que la respuesta es JSON
include '../persistence/conection.php'; // Incluir el archivo de conexión

function send_json_response($status_code, $status, $message, $data = null) {
    http_response_code($status_code);
    $response = ['status' => $status, 'message' => $message];
    if ($data) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit();
}

if (!empty($_POST['correo']) && !empty($_POST['contrasena'])) {
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];

    // Preparamos la consulta para evitar inyección SQL y seleccionamos las columnas necesarias
    $stmt = $conexion->prepare("SELECT id, nombre, email, contraseña, id_carrera FROM usuarios WHERE email = ?");
    if ($stmt === false) {
        send_json_response(500, 'error', 'Error interno del servidor al preparar la consulta.');
    }

    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $usuario = $result->fetch_assoc(); // Obtenemos los datos del usuario

        if ($contrasena === $usuario['contraseña']) {
            $_SESSION['usuario_id'] = $usuario['id']; // Guardar ID del usuario en sesión
            $_SESSION['usuario_nombre'] = $usuario['nombre']; // Guardar nombre del usuario en sesión
            unset($usuario['contraseña']); // No devolver la contraseña en la respuesta
            send_json_response(200, 'success', 'Inicio de sesión exitoso.', $usuario);
        } else {
            // Contraseña incorrecta
            send_json_response(401, 'error', 'Correo o contraseña incorrectos.');
        }
    }

    // Si no se encontró ningún usuario con ese correo
    send_json_response(401, 'error', 'Correo o contraseña incorrectos.');

} else {
    // Si no se enviaron los datos del formulario, redirigir al formulario de inicio de sesión
    send_json_response(400, 'error', 'Por favor, completa todos los campos.');
}

?>