<?php
$codigo = $_GET['codigo'];
$nombres = $_GET['nombres'];
$apellido_paterno = $_GET['apellido_paterno'];
$apellido_materno = $_GET['apellido_materno'];
$cupo = $_GET['cupo'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Reserva</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Confirmación de Reserva</h1>
        <p><strong>Código de Estudiante:</strong> <?php echo $codigo; ?></p>
        <p><strong>Nombre Completo:</strong> <?php echo $nombres . " " . $apellido_paterno . " " . $apellido_materno; ?></p>
        <p><strong>Número de Cupo:</strong> <?php echo $cupo; ?></p>
        <p>¡Reserva completada con éxito!</p>
        <button onclick="window.location.href='index.html';">Regresar a Inicio</button>
    </div>
</body>
</html>
