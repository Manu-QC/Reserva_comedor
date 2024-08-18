<?php
$servername = "localhost";
$username = "root";
$password = ""; // Añade aquí tu contraseña si tienes una
$port = 3307; // Asegúrate de que el puerto sea 3307

try {
    // Crear la conexión
    $conn = new mysqli($servername, $username, $password, null, $port);

    // Verificar la conexión
    if ($conn->connect_error) {
        throw new Exception("Conexión fallida: " . $conn->connect_error);
    }

    echo "Conexión exitosa";
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
