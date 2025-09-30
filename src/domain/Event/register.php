<?php
session_start();
header('Content-Type: application/json');
include 'conection.php'; // Incluir el archivo de conexión

function send_json_response($status_code, $status, $message) {
    http_response_code($status_code);
    echo json_encode(['status' => $status, 'message' => $message]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Validar que todos los campos requeridos estén presentes y no vacíos
    if (empty($_POST['nombre']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['carrera'])) {
        send_json_response(400, 'error', 'Todos los campos son obligatorios para el registro.');
    }

    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $id_carrera = (int)$_POST['carrera'];

    // 2. Verificar si el correo electrónico ya existe
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        send_json_response(409, 'error', 'El correo electrónico ya está registrado. Por favor, utiliza otro.');
    }
    $stmt->close();

    // 3. Insertar el nuevo usuario en la base de datos usando sentencias preparadas
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, email, contraseña, id_carrera) VALUES (?, ?, ?, ?)");
    if ($stmt === false) {
        send_json_response(500, 'error', 'Error interno del servidor. Inténtalo de nuevo más tarde.');
    }

    // Asumiendo que la columna en la tabla usuarios es id_carrera
    $stmt->bind_param("sssi", $nombre, $email, $password, $id_carrera);

    if ($stmt->execute()) {
        send_json_response(201, 'success', '¡Registro exitoso! Ahora puedes iniciar sesión.');
    } else {
        send_json_response(500, 'error', 'Hubo un error durante el registro. Por favor, inténtalo de nuevo.');
    }

    $stmt->close();
    $conexion->close();
} else {
    // Redirigir si se accede al archivo directamente sin enviar datos
    send_json_response(405, 'error', 'Método no permitido.');
}
?>