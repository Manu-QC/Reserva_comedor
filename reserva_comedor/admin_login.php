<?php
session_start();
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
        $admin_user = $_POST['admin_user'];
        $admin_password = $_POST['admin_password'];

        $sql = "SELECT * FROM administrativos WHERE usuario = ? AND contraseña = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $admin_user, $admin_password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['admin_user'] = $admin_user;
            header("Location: admin_panel.php");
            exit();
        } else {
            echo "<script>alert('Usuario o contraseña incorrectos');</script>";
        }
    }

    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inicio de Sesión Administrativa</title>
</head>
<body>
    <h2>Inicio de Sesión Administrativa</h2>
    <form method="post" action="">
        <label for="admin_user">Usuario:</label>
        <input type="text" id="admin_user" name="admin_user" required><br><br>
        <label for="admin_password">Contraseña:</label>
        <input type="password" id="admin_password" name="admin_password" required><br><br>
        <input type="submit" value="Ingresar">
    </form>
</body>
</html>
