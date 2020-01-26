<?php

// Obtener el ID de la URL
if (isset($_POST['cedula'])) {
    $cedula = filter_var($_POST['cedula'], FILTER_SANITIZE_STRING);
} 
if (isset($_POST['fecha'])) {
    $fecha = filter_var($_POST['fecha'], FILTER_SANITIZE_STRING);
} 


function console_log( $data ){
    echo '<script>';
    echo 'console.log('. json_encode( $data ) .')';
    echo '</script>';
  }

list($anio, $mes, $dia) = explode("-", $fecha);

$cantidad_Dias = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);

// // Conexión a la base de datos
include '../funciones/conexion.php';

try {
    // Consulta datos del usuario
    $stmt = $conn->prepare("SELECT usuarios.cedula, usuarios.nombre1, usuarios.nombre2, usuarios.apellido1, usuarios.apellido2, clase_empleado.clase, area_region.area_region FROM usuarios INNER JOIN clase_empleado ON usuarios.clase = clase_empleado.id_clase INNER JOIN area_region ON usuarios.area = area_region.id_area WHERE usuarios.cedula = ?");
   
    $stmt->bind_param('i', $cedula);
    $stmt->execute();

    $stmt->bind_result($cedula, $nombre1, $nombre2, $apellido1, $apellido2, $clase, $area);
    $stmt->fetch();

    // Sacamos los datos del usuario
    if($cedula){
        $respuesta = array(
            'respuesta' => 'correcto',
            'usuario' => $cedula,
            'nombre' => $nombre1 . ' ' . $nombre2 . ' ' . $apellido1 . ' ' . $apellido2,  
            'clase' => $clase,
            'area' => $area
        );

        $stmt->close();

    // Sacamos los registros del usuario

        $stmt = $conn->prepare("SELECT reg_fecha, actividad FROM registros WHERE reg_cedula = ? AND mes = ? AND anio = ? ORDER BY reg_fecha ");
        $stmt->bind_param('iii', $cedula, $mes, $anio);
        $stmt->execute();
        $stmt->bind_result($fecha, $Reg_actividad);

        while($stmt->fetch()) {                
            array_push($respuesta, array('fecha'=>$fecha,'registro'=> $Reg_actividad));
        }
            
        $stmt->close();

    }else {
        $respuesta = array(
            'error' => 'Usuario no existe'
        );
    }
    $conn->close();

} catch(Exception $e) {
    // En caso de un error, tomar la exepcion
    $respuesta = array(
        'error' => $e->getMessage()
    );
}

echo json_encode($respuesta);