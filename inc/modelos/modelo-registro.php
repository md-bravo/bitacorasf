<?php
if (isset($_POST['accion'])) {
    $accion = filter_var($_POST['accion'], FILTER_SANITIZE_STRING);
}
if (isset($_POST['id'])) {
    $id_Categoria = (int) $_POST['id'];
}
if (isset($_POST['cedula'])) {
    $cedula = (int) $_POST['cedula'];
}
if (isset($_POST['fecha'])) {
    $fecha = $_POST['fecha'];
    list($anio, $mes, $dia) = explode("-", $fecha);
}
if (isset($_POST['categoria'])) {
    $categoria = $_POST['categoria'];
}
if (isset($_POST['actividad'])) {
    $actividad = $_POST['actividad'];
}
if (isset($_POST['horas'])) {
    $horas = filter_var($_POST['horas'], FILTER_SANITIZE_STRING);
}
if (isset($_POST['detalle'])) {
    $detalle = filter_var($_POST['detalle'], FILTER_SANITIZE_STRING);
}
if (isset($_POST['idRegistro'])) {
    $idRegistro = $_POST['idRegistro'];
}
if (isset($_POST['RegActividad'])) {
    $RegActividad = $_POST['RegActividad'];
}

function console_log( $data ){
    echo '<script>';
    echo 'console.log('. json_encode( $data ) .')';
    echo '</script>';
  }

if($accion === 'actualizar-registro') {
    // importar la conexion
    include '../funciones/conexion.php';

    try {
        $stmt = $conn->prepare("UPDATE registros SET actividad = ? WHERE id_registros = ?");
        $stmt->bind_param('si', $RegActividad, $idRegistro);
        $stmt->execute();
        
        
        if($stmt->affected_rows > 0) {
            $respuesta = array(
                'respuesta' => 'correcto',                    
                'tipo' => $accion
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



if($accion === 'eliminar') {
    // importar la conexion
    include '../funciones/conexion.php';

    try {

        // Si no hay actividades, borra el registro de día y usuario seleccionado
        if ($RegActividad === ''){
            $stmt = $conn->prepare("DELETE FROM registros WHERE id_registros = ?");
            $stmt->bind_param('i', $idRegistro);
            $stmt->execute();
        }else { // Si hay actividades actualiza el registro
            $stmt = $conn->prepare("UPDATE registros SET actividad = ? WHERE id_registros = ?");
            $stmt->bind_param('si', $RegActividad, $idRegistro);
            $stmt->execute();
        }
        
        if($stmt->affected_rows > 0) {
            $respuesta = array(
                'respuesta' => 'correcto',                    
                'tipo' => $accion
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

if($accion === 'consultar') {
     // importar la conexion
     include '../funciones/conexion.php';
      
    try {
         
        $stmt = $conn->prepare("SELECT id_registros, actividad FROM registros WHERE reg_cedula = ? AND reg_fecha = ? ");
        $stmt->bind_param('is', $cedula, $fecha);
        $stmt->execute();
        $stmt->bind_result($id_registro, $Reg_actividad);
        $stmt->fetch();

        if($id_registro){
            $respuesta = array(
                'respuesta' => 'correcto',                    
                'id_registro' => $id_registro,
                'reg_actividad' => $Reg_actividad
            );
        } else {
            $respuesta = array(
                'respuesta' => 'no-registro'
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

if($accion === 'registrar') {


    $registro_act = array(array('categoria'=>$categoria,
                        'actividad'=>$actividad,
                        'horas'=>$horas,
                        'detalle'=>$detalle)
                    );

    include '../funciones/conexion.php';
    
    try {

        $stmt = $conn->prepare("SELECT id_registros, actividad FROM registros WHERE reg_cedula = ? AND reg_fecha = ? ");
        $stmt->bind_param('is', $cedula, $fecha);
        $stmt->execute();
        $stmt->bind_result($id_registro, $Reg_actividad);
        $stmt->fetch();
        $stmt->close();
      
        if($id_registro){

            $registro_recuperado = json_decode($Reg_actividad);

            array_push($registro_recuperado, array('categoria'=>$categoria, 'actividad'=>$actividad, 'horas'=>$horas, 'detalle'=>$detalle) );

            $registro_act_json = json_encode($registro_recuperado);

            $stmt = $conn->prepare("UPDATE registros SET actividad = ? WHERE id_registros = ?");
            $stmt->bind_param('si', $registro_act_json, $id_registro);
            $stmt->execute();

            if($stmt->affected_rows > 0) {
                $respuesta = array(
                    'respuesta' => 'correcto',                    
                    'tipo' => $accion
                );
            }  else {
                $respuesta = array(
                    'respuesta' => 'error'
                );
            }
            $stmt->close();
            $conn->close();


        } else {

            $registro_act_json = json_encode($registro_act);

            // Realizar la consulta a la base de datos
            $stmt = $conn->prepare("INSERT INTO registros (reg_cedula, reg_fecha, mes, anio, actividad) VALUES (?, ?, ?, ?, ?) ");
            $stmt->bind_param('isiis', $cedula, $fecha, $mes, $anio, $registro_act_json);
            $stmt->execute();
            if($stmt->affected_rows > 0) {
                $respuesta = array(
                    'respuesta' => 'correcto',
                    'id_insertado' => $stmt->insert_id,
                    'tipo' => $accion
                );
            }  else {
                $respuesta = array(
                    'respuesta' => 'error'
                );
            }
            $stmt->close();
            $conn->close();

        }
      
    } catch(Exception $e) {
        // En caso de un error, tomar la exepcion
        $respuesta = array(
            'error' => $e->getMessage()
        );
    }
    

   echo json_encode($respuesta);
}
