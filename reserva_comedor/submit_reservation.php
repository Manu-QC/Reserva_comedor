<?php
$servername = "localhost";
$username = "root";
$password = ""; // Añade aquí tu contraseña si tienes una
$dbname = "comedor_universitario";
$port = 3307; // Asegúrate de que el puerto sea 3307

try {
    // Crear la conexión
    $conn = new mysqli($servername, $username, $password, $dbname, $port);

    // Verificar la conexión
    if ($conn->connect_error) {
        throw new Exception("Conexión fallida: " . $conn->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $student_code = $_POST['student_code'];
        $student_password = $_POST['student_password'];

        $sql = "SELECT * FROM estudiantes WHERE codigo = ? AND contraseña = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $student_code, $student_password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Obtener los datos del estudiante
            $student = $result->fetch_assoc();

            // Obtener los cupos disponibles
            $sql_disponibles = "SELECT cupos_disponibles FROM configuracion WHERE id = 1";
            $result_disponibles = $conn->query($sql_disponibles);
            $row_disponibles = $result_disponibles->fetch_assoc();
            $disponibles = $row_disponibles['cupos_disponibles'];

            // Calcular los cupos asignados por grupo
            $sql_count = "SELECT 
                SUM(CASE WHEN percentil_academico IN ('Q', 'D') AND condicion_socioeconomica IN ('PE', 'P') THEN 1 ELSE 0 END) AS cupos_qd_pe_p,
                SUM(CASE WHEN percentil_academico = 'T' AND condicion_socioeconomica IN ('PE', 'P') THEN 1 ELSE 0 END) AS cupos_t_pe_p,
                SUM(CASE WHEN NOT (percentil_academico IN ('Q', 'D') AND condicion_socioeconomica IN ('PE', 'P')) AND NOT (percentil_academico = 'T' AND condicion_socioeconomica IN ('PE', 'P')) THEN 1 ELSE 0 END) AS cupos_rest
                FROM estudiantes WHERE CupoAsignado = 1";

            $result_count = $conn->query($sql_count);
            $row_count = $result_count->fetch_assoc();

            $cupos_qd_pe_p = $row_count['cupos_qd_pe_p'];
            $cupos_t_pe_p = $row_count['cupos_t_pe_p'];
            $cupos_rest = $row_count['cupos_rest'];

            // Calcular los límites de cupos por grupo
            $limite_qd_pe_p = ceil($disponibles * 0.40);
            $limite_t_pe_p = ceil($disponibles * 0.20);
            $limite_rest = floor($disponibles * 0.40);

            $asignar_cupo = false;

            // Verificar el grupo del estudiante y los límites de cupos
            if (in_array($student['percentil_academico'], ['Q', 'D']) && in_array($student['condicion_socioeconomica'], ['PE', 'P'])) {
                if ($cupos_qd_pe_p < $limite_qd_pe_p) {
                    $asignar_cupo = true;
                }
            } elseif ($student['percentil_academico'] == 'T' && in_array($student['condicion_socioeconomica'], ['PE', 'P'])) {
                if ($cupos_t_pe_p < $limite_t_pe_p) {
                    $asignar_cupo = true;
                }
            } else {
                if ($cupos_rest < $limite_rest) {
                    $asignar_cupo = true;
                }
            }

            if ($asignar_cupo) {
                if ($student['CupoAsignado'] == 0) {
                    // Asignar cupo al estudiante
                    $sql_update = "UPDATE estudiantes SET CupoAsignado = 1 WHERE codigo = ?";
                    $stmt_update = $conn->prepare($sql_update);
                    $stmt_update->bind_param("s", $student_code);
                    $stmt_update->execute();

                    // Redirigir a la página de confirmación con los datos del estudiante y la reserva
                    header("Location: reservation_confirmation.php?codigo=" . $student['codigo'] . "&nombres=" . $student['nombres'] . "&apellido_paterno=" . $student['apellido_paterno'] . "&apellido_materno=" . $student['apellido_materno'] . "&cupo=1");
                    exit();
                } else {
                    echo "<script>alert('Ya tienes un cupo asignado');</script>";
                    header("Refresh:0; url=index.html");
                    exit();
                }
            } else {
                echo "<script>alert('No hay cupos disponibles en tu grupo');</script>";
                header("Refresh:0; url=index.html");
                exit();
            }
        } else {
            echo "<script>alert('Código o contraseña incorrectos');</script>";
            header("Refresh:0; url=index.html");
            exit();
        }
    }

    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
