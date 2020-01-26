<?php
session_start();

if (isset($_POST['accion'])) {
    $accion = filter_var($_POST['accion'], FILTER_SANITIZE_STRING);
}
if (isset($_POST['usuario'])) {
    $usuario = filter_var($_POST['usuario'], FILTER_SANITIZE_STRING);
}
if (isset($_POST['nombre1'])) {
    $nombre1 = filter_var($_POST['nombre1'], FILTER_SANITIZE_STRING);
}
if (isset($_POST['nombre2'])) {
    $nombre2 = filter_var($_POST['nombre2'], FILTER_SANITIZE_STRING);
}
if (isset($_POST['apellido1'])) {
    $apellido1 = filter_var($_POST['apellido1'], FILTER_SANITIZE_STRING);
}
if (isset($_POST['apellido2'])) {
    $apellido2 = filter_var($_POST['apellido2'], FILTER_SANITIZE_STRING);
}
if (isset($_POST['password'])) {
    $password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);
}
if (isset($_POST['clase'])) {
    $clase = $_POST['clase'];
}
if (isset($_POST['area'])) {
    $area = $_POST['area'];
}

if($accion === 'crear') {
    // Código para crear usuarios
    
    // hashear passwords
    $opciones = array(
        'cost' => 12
    );
    $hash_password = password_hash($password, PASSWORD_BCRYPT, $opciones);
    // importar la conexion
    include '../funciones/conexion.php';
    
    try {

        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE cedula = ?");
        $stmt->bind_param('s', $usuario);
        $stmt->execute();

        $stmt->bind_result($id_usuario);
        $stmt->fetch();

        if($id_usuario){
            $respuesta = array(
                'respuesta' => 'existe'
            );

            $stmt->close();
            $conn->close();
            
        } else {
            $estado = 1;
            $rol = 'user';
            // Realizar la consulta a la base de datos
            $stmt = $conn->prepare("INSERT INTO usuarios (cedula, nombre1, nombre2, apellido1, apellido2, password, clase, area, estado, rol) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ");
            $stmt->bind_param('ssssssssis', $usuario, $nombre1, $nombre2, $apellido1, $apellido2, $hash_password, $clase, $area, $estado, $rol);
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

if($accion === 'login') {
    // Código para loguear los usuarios

    include '../funciones/conexion.php';

    
    try {
        // Seleccionar el administrador de la base de datos
        $stmt = $conn->prepare("SELECT id, cedula, nombre1, nombre2, apellido1, apellido2, estado FROM usuarios WHERE cedula = ?");
        $stmt->bind_param('s', $usuario);
        $stmt->execute();
        // Loguear el usuario
        $stmt->bind_result($id_usuario, $cedula, $nombre1, $nombre2, $apellido1, $apellido2, $estado);
        $stmt->fetch();

        // Si el usuario existe
        if($cedula){
            if($estado === 0){
                $respuesta = array(
                    'inactivo' => 'inactivo'
                );
            }else{
                // Iniciar la sesion                
                $_SESSION['usuario'] = $cedula;
                $_SESSION['nombre'] = $nombre1 . ' ' . $nombre2 . ' ' . $apellido1 . ' ' . $apellido2;                
                $_SESSION['login'] = true;
                // $_SESSION['last_login_timestamp'] = time();  
                
                //Login correcto
                $respuesta = array(
                    'respuesta' => 'correcto',
                    'usuario' => $cedula,
                    'nombre' => $nombre1 . ' ' . $nombre2 . ' ' . $apellido1 . ' ' . $apellido2,
                    'tipo' => $accion
                );
            }
           
        } else {
            $respuesta = array(
                'error' => 'Usuario no existe'
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

if($accion === 'login-backend') {
    // Código para loguear los usuarios

    include '../funciones/conexion.php';

        
    try {
        // Seleccionar el administrador de la base de datos
        $stmt = $conn->prepare("SELECT id, cedula, nombre1, password FROM usuarios WHERE nombre1 = ?");
        $stmt->bind_param('s', $usuario);
        $stmt->execute();
        // Loguear el usuario
        $stmt->bind_result($id_usuario, $cedula, $nombre1, $pass_usuario);
        $stmt->fetch();

        if($cedula){
            // El usuario existe, verificar el password
            if(password_verify($password, $pass_usuario)){
 
                // El usuario existe
                // Iniciar la sesion                
                $_SESSION['usuario'] = $cedula;
                $_SESSION['nombre'] = $nombre1;                
                $_SESSION['login'] = true;
                
                //Login correcto
                $respuesta = array(
                    'respuesta' => 'correcto',
                    'usuario' => $cedula,
                    'nombre' => $nombre1,
                    'tipo' => $accion
                );
                                
            } else{
                // Login incorrecto,enviar error
                $respuesta = array (
                    'resultado' => 'Password Incorrecto'
                );
            }

        } else {
            $respuesta = array(
                'error' => 'Usuario no existe'
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