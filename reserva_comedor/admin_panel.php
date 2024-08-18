<?php
session_start();
if (!isset($_SESSION['admin_user'])) {
    header("Location: admin_login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = ""; // Añade aquí tu contraseña si tienes una
$dbname = "comedor_universitario";
$port = 3307; // Asegúrate de que el puerto sea 3307

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Inicializar la tabla configuracion si está vacía
$sql_check = "SELECT COUNT(*) AS count FROM configuracion";
$result_check = $conn->query($sql_check);
$row_check = $result_check->fetch_assoc();

if ($row_check['count'] == 0) {
    $sql_init = "INSERT INTO configuracion (cupos_disponibles, cupos_asignados, cupos_restantes) VALUES (0, 0, 0)";
    $conn->query($sql_init);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_cupos'])) {
    $new_cupos_disponibles = $_POST['cupos_disponibles'];
    
    if ($new_cupos_disponibles >= 0) {
        // Obtener el número de estudiantes con cupo asignado
        $sql_estudiantes_asignados = "SELECT COUNT(*) AS total_asignados FROM estudiantes WHERE CupoAsignado = 1";
        $result_estudiantes_asignados = $conn->query($sql_estudiantes_asignados);
        $row_estudiantes_asignados = $result_estudiantes_asignados->fetch_assoc();
        $total_asignados = $row_estudiantes_asignados['total_asignados'];

        // Calcular cupos restantes
        $cupos_restantes = $new_cupos_disponibles - $total_asignados;

        // Asegurarse de que los cupos restantes no sean negativos
        if ($cupos_restantes < 0) {
            $cupos_restantes = 0;
        }

        $sql_update = "UPDATE configuracion SET cupos_disponibles = ?, cupos_asignados = ?, cupos_restantes = ? WHERE id = 1";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("iii", $new_cupos_disponibles, $total_asignados, $cupos_restantes);
        $stmt_update->execute();
        $stmt_update->close();
    }
}

// Obtener los cupos disponibles, asignados y restantes de la tabla configuracion
$sql_cupos = "SELECT cupos_disponibles, cupos_asignados, cupos_restantes FROM configuracion WHERE id = 1";
$result_cupos = $conn->query($sql_cupos);
$row_cupos = $result_cupos->fetch_assoc();
$cupos_disponibles = isset($row_cupos['cupos_disponibles']) ? $row_cupos['cupos_disponibles'] : 0;
$cupos_asignados = isset($row_cupos['cupos_asignados']) ? $row_cupos['cupos_asignados'] : 0;
$cupos_restantes = isset($row_cupos['cupos_restantes']) ? $row_cupos['cupos_restantes'] : 0;

$sql_estudiantes = "SELECT codigo, apellido_paterno, apellido_materno, nombres, PagoRealizado FROM estudiantes WHERE CupoAsignado = 1";
$result_estudiantes = $conn->query($sql_estudiantes);

// Consultas para la distribución de cupos
$sql_qd_pe_p = "SELECT COUNT(*) AS cupos_qd_pe_p
                FROM estudiantes
                WHERE percentil_academico IN ('Q', 'D') AND condicion_socioeconomica IN ('PE', 'P') AND CupoAsignado = 1";
$result_qd_pe_p = $conn->query($sql_qd_pe_p);
$row_qd_pe_p = $result_qd_pe_p->fetch_assoc();
$cupos_qd_pe_p = $row_qd_pe_p['cupos_qd_pe_p'];

$sql_t_pe_p = "SELECT COUNT(*) AS cupos_t_pe_p
               FROM estudiantes
               WHERE percentil_academico = 'T' AND condicion_socioeconomica IN ('PE', 'P') AND CupoAsignado = 1";
$result_t_pe_p = $conn->query($sql_t_pe_p);
$row_t_pe_p = $result_t_pe_p->fetch_assoc();
$cupos_t_pe_p = $row_t_pe_p['cupos_t_pe_p'];

$sql_rest = "SELECT COUNT(*) AS cupos_rest
             FROM estudiantes
             WHERE NOT (percentil_academico IN ('Q', 'D') AND condicion_socioeconomica IN ('PE', 'P'))
             AND NOT (percentil_academico = 'T' AND condicion_socioeconomica IN ('PE', 'P')) AND CupoAsignado = 1";
$result_rest = $conn->query($sql_rest);
$row_rest = $result_rest->fetch_assoc();
$cupos_rest = $row_rest['cupos_rest'];

// Calcular los porcentajes
$total_asignados = $cupos_qd_pe_p + $cupos_t_pe_p + $cupos_rest;
$percent_qd_pe_p = ($cupos_qd_pe_p / $cupos_disponibles) * 100;
$percent_t_pe_p = ($cupos_t_pe_p / $cupos_disponibles) * 100;
$percent_rest = ($cupos_rest / $cupos_disponibles) * 100;

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
        }
        header {
            background: #35424a;
            color: #ffffff;
            padding-top: 30px;
            min-height: 70px;
            border-bottom: #e8491d 3px solid;
        }
        header a {
            color: #ffffff;
            text-decoration: none;
            text-transform: uppercase;
            font-size: 16px;
        }
        header ul {
            padding: 0;
            list-style: none;
        }
        header li {
            float: left;
            display: inline;
            padding: 0 20px 0 20px;
        }
        header #branding {
            float: left;
        }
        header #branding h1 {
            margin: 0;
        }
        header nav {
            float: right;
            margin-top: 10px;
        }
        #main {
            padding: 20px;
            background: #ffffff;
            margin-top: 20px;
        }
        footer {
            background: #35424a;
            color: #ffffff;
            text-align: center;
            padding: 30px;
            margin-top: 30px;
        }
        table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }
        table th, table td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }
        th, td {
            text-align: center;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div id="branding">
                <h1>Panel Administrativo</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="admin_logout.php">Cerrar Sesión</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div id="main" class="container">
        <h2>Cupos Disponibles</h2>
        <form action="admin_panel.php" method="post">
            <label for="cupos_disponibles">Número de cupos disponibles:</label>
            <input type="number" id="cupos_disponibles" name="cupos_disponibles" value="<?php echo $cupos_disponibles; ?>" min="0">
            <br>
            <button type="submit" name="update_cupos">Actualizar Cupos</button>
        </form>

        <h3>Cupos Asignados: <?php echo $cupos_asignados; ?></h3>
        <h3>Cupos Restantes: <?php echo $cupos_restantes; ?></h3>

        <h2>Distribución de Cupos</h2>
        <p>40% para percentil 'Q' o 'D' y condición 'PE' o 'P': <?php echo $percent_qd_pe_p; ?>%</p>
        <p>20% para percentil 'T' y condición 'PE' o 'P': <?php echo $percent_t_pe_p; ?>%</p>
        <p>40% para los demás: <?php echo $percent_rest; ?>%</p>

        <h2>Estudiantes con Cupo Asignado</h2>
        <table>
            <tr>
                <th>Código</th>
                <th>Apellido Paterno</th>
                <th>Apellido Materno</th>
                <th>Nombres</th>
                <th>Pago Realizado</th>
            </tr>
            <?php
            if ($result_estudiantes->num_rows > 0) {
                while ($row = $result_estudiantes->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["codigo"] . "</td>";
                    echo "<td>" . $row["apellido_paterno"] . "</td>";
                    echo "<td>" . $row["apellido_materno"] . "</td>";
                    echo "<td>" . $row["nombres"] . "</td>";
                    echo "<td>" . ($row["PagoRealizado"] ? 'Sí' : 'No') . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No hay estudiantes con cupo asignado</td></tr>";
            }
            ?>
        </table>
    </div>

    <footer>
        <p>Comedor Universitario &copy; 2024</p>
    </footer>
</body>
</html>

<?php
$conn->close();
?>
