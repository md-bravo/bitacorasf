<?php
// Obtener el ID de la URL
if (isset($_GET['cedula'])) {
    $cedula = filter_var($_GET['cedula'], FILTER_SANITIZE_STRING);
    }


// Conexión a la base de datos
include '../funciones/conexion.php';

try {
    // Consulta registros del usuario
    $stmt = $conn->prepare("SELECT reg_fecha, actividad FROM registros WHERE reg_cedula = ? ORDER BY reg_fecha");   
    $stmt->bind_param('i', $cedula);
    $stmt->execute();
    $stmt->bind_result($reg_fecha, $reg_actividad);

    // Almacena los registros en un array
    $lista_registros = array();

    while($stmt->fetch()) {                
        array_push($lista_registros, array('fecha'=>$reg_fecha,'registro'=> $reg_actividad));
    }

    //Recorre los registros, extra la fecha y cada una de las actividades
    foreach($lista_registros as $registro){

        $sumaHoras = 0;
        $jornada = 9.6;
        $fecha = $registro["fecha"];
        $registro_act = json_decode($registro["registro"]);

        // Recorre las actividades de una fecha y suma las horas
        foreach ($registro_act as $act_especifica) {
            $horas = $act_especifica->horas;
            $detalle = $act_especifica->detalle;
            $sumaHoras += $horas;
        }        

        //Determina el mensaje para el calendario, si es Feriado o Vacaciones

        if(round($sumaHoras, 2) >= round($jornada,2)){
            if($detalle == "Feriado"){
                $titulo = 'Feriado';
                $color = 'gray';
            } else if($detalle == "Día de Vacaciones"){
                $titulo = 'Vacaciones';
                $color = 'gray';
            } else {
                $titulo = $sumaHoras.' Horas';
                $color = 'green';
            }            
        } elseif(round($sumaHoras, 2) > 0 && round($sumaHoras, 2) < round($jornada,2)){
            $titulo = $sumaHoras.' Horas';
            $color = 'orange';
        }    

        // Crea y llena el arreglo con la información para el calendario
        $respuesta[] = [
            'title' => $titulo,
            'start' => $fecha,
            'color' => $color
        ];
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