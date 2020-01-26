<?php

if (isset($_POST['usuario'])) {
    $usuario = filter_var($_POST['usuario'], FILTER_SANITIZE_STRING);
}
if (isset($_POST['estado'])) {
    $estado = $_POST['estado'];
}
if (isset($_POST['accion'])) {
    $accion = filter_var($_POST['accion'], FILTER_SANITIZE_STRING);
}

// importar la conexion
include '../funciones/conexion.php';

if($accion === 'cambiarEstado'){
    try {

        // Realizar actualización en la base de datos
        $stmt = $conn->prepare("UPDATE usuarios SET estado= ? WHERE cedula = ?");
        $stmt->bind_param('is', $estado, $usuario);
        $stmt->execute();
        if($stmt->affected_rows > 0) {
            $respuesta = array(
                'respuesta' => 'correcto'
            );
        }  else {
            $respuesta = array(
                'respuesta' => 'error'
            );
        }
        $stmt->close();
        $conn->close();

    } catch(Exception $e) {
        // En caso de un error, tomar la exepcion
        $respuesta = array(
            'error' => $e->getMessage()
        );
    }

    echo json_encode($respuesta);
}