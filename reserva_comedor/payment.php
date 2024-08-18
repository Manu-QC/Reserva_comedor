<?php
$servername = "localhost";
$username = "root";
$password = ""; // Añade aquí tu contraseña si tienes una
$dbname = "comedor_universitario";
$port = 3307; // Asegúrate de que el puerto sea 3307

try {
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    
    if ($conn->connect_error) {
        throw new Exception("Conexión fallida: " . $conn->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $student_code = $_POST['student_code'];

        $sql = "SELECT * FROM estudiantes WHERE codigo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $student_code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $student = $result->fetch_assoc();
            if ($student['CupoAsignado'] == 1) {
                // Actualizar estado de pago
                $sql_update = "UPDATE estudiantes SET PagoRealizado = 1 WHERE codigo = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("s", $student_code);
                $stmt_update->execute();

                echo "<script>alert('Pago realizado con éxito');</script>";
            } else {
                echo "<script>alert('No tienes un cupo asignado');</script>";
            }
        } else {
            echo "<script>alert('Código incorrecto');</script>";
        }
    }

    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago de Cupo</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Pago de Cupo</h1>
        <form method="post" action="payment.php">
            <label for="student_code">Código de Estudiante:</label>
            <input type="text" id="student_code" name="student_code" required>
            <button type="submit">Pagar</button>
            <button type="button" onclick="window.location.href='index.html';">Regresar</button>
        </form>
    </div>
</body>
</html>
