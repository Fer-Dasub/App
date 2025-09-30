<?php
session_start();

// Si el usuario no ha iniciado sesión, redirigirlo a la página de login.
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

include "src/infrastructure/persistence/conection.php";

// Obtenemos los datos específicos del usuario logueado
$usuario_id = $_SESSION['usuario_id'];
$stmt = $conexion->prepare("
    SELECT u.id, u.nombre, u.email, u.id_carrera, c.nombre AS carrera_nombre
    FROM usuarios u
    LEFT JOIN carreras_academicas c ON u.id_carrera = c.id
    WHERE u.id = ?
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado_usuario = $stmt->get_result()->fetch_assoc(); // Ahora incluye id y id_carrera
$stmt->close();

// Si el usuario no tiene una carrera asignada, no intentamos buscar compañeros
$colleagues = [];
if (isset($resultado_usuario['id_carrera']) && $resultado_usuario['id_carrera'] !== null) {
    // Obtenemos todos los usuarios que tienen la misma id_carrera, excluyendo al usuario logueado
    $stmt_colleagues = $conexion->prepare("
        SELECT u.id, u.nombre, u.email, c.nombre AS carrera_nombre
        FROM usuarios u
        LEFT JOIN carreras_academicas c ON u.id_carrera = c.id
        WHERE u.id_carrera = ? AND u.id != ?
    ");
    $stmt_colleagues->bind_param("ii", $resultado_usuario['id_carrera'], $usuario_id);
    $stmt_colleagues->execute();
    $colleagues = $stmt_colleagues->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_colleagues->close();
}

$conexion->close(); // Cerramos la conexión después de todas las consultas
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 font-sans">

    <div class="container mx-auto p-4 md:p-8">
        <div id="notification-area" class="max-w-4xl mx-auto mb-6"></div>

        <div class="max-w-4xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold">Bienvenido, <?= htmlspecialchars($resultado_usuario['nombre']) ?></h1>
                <a href="#" id="logout-link" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Cerrar Sesión</a>
            </div>

            <h2 class="text-2xl font-bold text-gray-800 mb-4">Tu Perfil</h2>
            <div class="overflow-x-auto bg-white rounded-lg shadow-md">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Carrera</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($resultado_usuario['nombre']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($resultado_usuario['email']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($resultado_usuario['carrera_nombre'] ?? 'No asignada') ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button data-id="<?= $usuario_id ?>" data-nombre="<?= htmlspecialchars($resultado_usuario['nombre']) ?>" data-email="<?= htmlspecialchars($resultado_usuario['email']) ?>" class="edit-btn text-indigo-600 hover:text-indigo-900 mr-4">Editar</button>
                                <a href="src/domain/Event/delete.php?id=<?= $usuario_id ?>" class="delete-btn text-red-600 hover:text-red-900">Borrar</a>
                            </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="max-w-4xl mx-auto mt-12">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Compañeros de Carrera</h2>
                <div class="overflow-x-auto bg-white rounded-lg shadow-md">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Carrera</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($colleagues)): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No hay otros usuarios en tu carrera.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($colleagues as $colleague): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($colleague['nombre']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($colleague['email']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($colleague['carrera_nombre'] ?? 'No asignada') ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button data-id="<?= $colleague['id'] ?>" data-nombre="<?= htmlspecialchars($colleague['nombre']) ?>" data-email="<?= htmlspecialchars($colleague['email']) ?>" class="edit-btn text-indigo-600 hover:text-indigo-900 mr-4">Editar</button>
                                            <a href="src/domain/Event/delete.php?id=<?= $colleague['id'] ?>" class="delete-btn text-red-600 hover:text-red-900">Borrar</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal de Edición -->
    <div id="editModal" class="fixed z-10 inset-0 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Fondo del modal -->
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <!-- Contenido del modal -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="edit-form">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Editar Usuario</h3>
                        <input type="hidden" name="id" id="edit-id">
                        <div class="mb-4">
                            <label for="edit-nombre" class="block text-gray-700 text-sm font-bold mb-2">Nombre:</label>
                            <input type="text" name="nombre" id="edit-nombre" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        </div>
                        <div>
                            <label for="edit-email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                            <input type="email" name="email" id="edit-email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Guardar Cambios
                        </button>
                        <button type="button" id="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('editModal');
            const editForm = document.getElementById('edit-form');
            const closeModalBtn = document.getElementById('closeModal');
            const notificationArea = document.getElementById('notification-area');

            // --- Helper para mostrar notificaciones ---
            function showNotification(message, type = 'error') {
                const bgColor = type === 'success' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-red-100 border-red-500 text-red-700';
                notificationArea.innerHTML = `<div class="border-l-4 p-4 ${bgColor}" role="alert"><p>${message}</p></div>`;
                setTimeout(() => {
                    notificationArea.innerHTML = '';
                }, 5000);
            }

            // --- Delegación de eventos para botones de editar y borrar ---
            document.body.addEventListener('click', function(event) {
                const target = event.target;

                // Botón Editar
                if (target.classList.contains('edit-btn')) {
                    const id = target.dataset.id;
                    const nombre = target.dataset.nombre;
                    const email = target.dataset.email;

                    document.getElementById('edit-id').value = id;
                    document.getElementById('edit-nombre').value = nombre;
                    document.getElementById('edit-email').value = email;

                    modal.style.display = 'block';
                }

                // Botón Borrar
                if (target.classList.contains('delete-btn')) {
                    event.preventDefault();
                    if (confirm('¿Estás seguro de que quieres borrar este registro?')) {
                        const idToDelete = target.closest('tr').querySelector('.edit-btn').dataset.id;
                        fetch('src/domain/Event/delete.php', { 
                            method: 'DELETE',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `id=${idToDelete}`
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    if (data.message.includes('cerrado la sesión')) {
                                        window.location.href = 'index.php';
                                    } else {
                                        showNotification(data.message, 'success');
                                        setTimeout(() => window.location.reload(), 2000); // Recargar después de 2 segundos
                                    }
                                } else {
                                    showNotification(data.message, 'error');
                                }
                            })
                            .catch(error => showNotification('Error de red.', 'error'));
                    }
                }
            });

            // --- Lógica para el Modal y Formulario de Edición ---
            closeModalBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });

            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch('src/domain/Event/update.php', { method: 'POST', body: formData })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            modal.style.display = 'none'; // Cerrar el modal
                            showNotification(data.message, 'success');
                            setTimeout(() => window.location.reload(), 2000); // Recargar después de 2 segundos
                        } else {
                            // Podríamos mostrar el error dentro del modal
                            alert('Error al actualizar: ' + data.message);
                        }
                    })
                    .catch(error => alert('Error de red.'));
            });

            // --- Lógica para Cerrar Sesión ---
            document.getElementById('logout-link').addEventListener('click', function(e) {
                e.preventDefault();
                fetch('src/infrastructure/controllers/logout.php', { method: 'GET' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            window.location.href = 'index.php';
                        }
                    })
                    .catch(error => alert('Error de red.'));
            });
        });
    </script>
</body>
</html>