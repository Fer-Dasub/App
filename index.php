<!DOCTYPE html>
<html>
<head>
    <?php session_start(); ?>
    <meta charset="UTF-8">
    <title>Listado de Usuarios</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 font-sans">
    <?php
        include "src/infrastructure/persistence/conection.php";
    ?>

    <div class="container mx-auto p-4 md:p-8">

        <?php
            // Consulta para obtener las carreras académicas (para el formulario de registro)
            $sql_carreras = $conexion->query("SELECT * FROM carreras_academicas");
            $carreras = [];
            while ($datos_carrera = $sql_carreras->fetch_object()) {
                $carreras[] = $datos_carrera;
            }
        ?>
        
        <div id="formulario-login" class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md mb-12">
            <h2 class="text-2xl font-bold mb-6 text-center">Iniciar Sesión</h2>
            <form id="login-form">
                <div class="mb-4">
                    <label for="correo" class="block text-gray-700 font-bold mb-2">Correo:</label>
                    <input type="email" id="correo" name="correo" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-6">
                    <label for="contrasena" class="block text-gray-700 font-bold mb-2">Contraseña:</label>
                    <input type="password" id="contrasena" name="contrasena" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="flex items-center justify-between">
                    <input type="submit" value="Enviar" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline cursor-pointer">
                    <a href="#" id="show-register-link" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                        Registrarse
                    </a>
                </div>
            </form>
        </div>

        <div id="notification-area" class="max-w-md mx-auto"></div>

        <div id="formulario-registro" class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md mb-12" style="display: none;">
            <h2 class="text-2xl font-bold mb-6 text-center">Crear Cuenta</h2>
            <form id="register-form">
                <div class="mb-4">
                    <label for="nombre" class="block text-gray-700 font-bold mb-2">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label for="email_registro" class="block text-gray-700 font-bold mb-2">Email:</label>
                    <input type="email" id="email_registro" name="email" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label for="password_registro" class="block text-gray-700 font-bold mb-2">Contraseña:</label>
                    <input type="password" id="password_registro" name="password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-6">
                    <label for="carrera" class="block text-gray-700 font-bold mb-2">Carrera:</label>
                    <select id="carrera" name="carrera" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <?php foreach ($carreras as $carrera) { ?>
                            <option value="<?= $carrera->id ?>"><?= $carrera->nombre ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="flex items-center justify-between">
                    <input type="submit" value="Registrarse" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline cursor-pointer">
                    <a href="#" id="show-login-link" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                        Ya tengo una cuenta
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('formulario-login');
            const registerForm = document.getElementById('formulario-registro');
            const showRegisterLink = document.getElementById('show-register-link');
            const showLoginLink = document.getElementById('show-login-link');
            const notificationArea = document.getElementById('notification-area');

            // --- Helper para mostrar notificaciones ---
            function showNotification(message, type = 'error') {
                const bgColor = type === 'success' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-red-100 border-red-500 text-red-700';
                const title = type === 'success' ? 'Éxito' : 'Error';
                notificationArea.innerHTML = `
                    <div class="border-l-4 p-4 mb-6 ${bgColor}" role="alert">
                        <p class="font-bold">${title}</p>
                        <p>${message}</p>
                    </div>`;
                // Limpiar la notificación después de 5 segundos
                setTimeout(() => {
                    notificationArea.innerHTML = '';
                }, 5000);
            }

            // --- Lógica para cambiar entre formularios ---
            showRegisterLink.addEventListener('click', function(e) {
                e.preventDefault();
                loginForm.style.display = 'none';
                registerForm.style.display = 'block';
                notificationArea.innerHTML = ''; // Limpiar notificaciones al cambiar
            });

            showLoginLink.addEventListener('click', function(e) {
                e.preventDefault();
                registerForm.style.display = 'none';
                loginForm.style.display = 'block';
                notificationArea.innerHTML = ''; // Limpiar notificaciones al cambiar
            });

            // --- Lógica de envío de formularios con Fetch API ---
            document.getElementById('login-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch('src/infrastructure/controllers/login.php', { method: 'POST', body: formData })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            window.location.href = 'dashboard.php';
                        } else {
                            showNotification(data.message, 'error');
                        }
                    }).catch(error => showNotification('Ocurrió un error de red.', 'error'));
            });

            document.getElementById('register-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch('src/infrastructure/controllers/register.php', { method: 'POST', body: formData })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showNotification(data.message, 'success');
                            this.reset(); // Limpiar el formulario
                            showLoginLink.click(); // Volver al login
                        } else {
                            showNotification(data.message, 'error');
                        }
                    }).catch(error => showNotification('Ocurrió un error de red.', 'error'));
            });
        });
    </script>
</body>
</html>