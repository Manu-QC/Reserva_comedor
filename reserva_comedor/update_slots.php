<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $total_slots = $_POST['total_slots'];

    $servername = "localhost";
    $username = "root";
    $password = ""; // Añade aquí tu contraseña si tienes una
    $dbname = "comedor_universitario";
    $port = 3307; // Asegúrate de que el puerto sea 3307

    $conn = new mysqli($servername, $username, $password, $dbname, $port);

    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    $sql = "UPDATE configuracion SET cupos_disponibles = ? WHERE id = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $total_slots);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "<script>alert('Número de cupos actualizado con éxito');</script>";
    } else {
        echo "<script>alert('Error al actualizar el número de cupos');</script>";
    }

    $stmt->close();
    $conn->close();

    header("Refresh:0; url=admin_panel.php");
    exit();
}
?>
