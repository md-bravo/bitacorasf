<?php

// Obtiene la página actual que se ejecuta
function obtenerPaginaActual() {
    $archivo = basename($_SERVER['PHP_SELF']);
    $pagina = str_replace(".php", "", $archivo);
    return $pagina;
}

/* Consultas **/

function obtenerCategorias() {
    include 'conexion.php';
    try {
        return $conn->query('SELECT id_cat, nombre_cat FROM categorias');
    } catch(Exception $e) {
        echo "Error! : " . $e->getMessage();
        return false;
    }
}

function obtenerRelacionCatAct() {
    include 'conexion.php';
    try {
        return $conn->query('SELECT id_cat, id_act FROM relacion_cat_act');
    } catch(Exception $e) {
        echo "Error! : " . $e->getMessage();
        return false;
    }
}

function obtenerListaActividades(){
    include 'conexion.php';
    try {
        return $conn->query('SELECT id_act, nombre_act FROM actividades');
    } catch(Exception $e) {
        echo "Error! : " . $e->getMessage();
        return false;
    }
}

function obtenerActividades($id_cat) {
    include 'conexion.php';
    
    try {
        return $conn->query("SELECT id_act, nombre_act FROM actividades WHERE id_act in (SELECT id_act FROM relacion_cat_act WHERE id_cat = {$id_cat})");
    } catch(Exception $e) {
        echo "Error! : " . $e->getMessage();
        return false;
    }
}


function obtenerClasificacion() {
    include 'conexion.php';
    try {
        return $conn->query('SELECT id_clase, clase FROM clase_empleado');
    } catch(Exception $e) {
        echo "Error! : " . $e->getMessage();
        return false;
    }
}

function obtenerAreaRegion() {
    include 'conexion.php';
    try {
        return $conn->query('SELECT id_area, area_region FROM area_region');
    } catch(Exception $e) {
        echo "Error! : " . $e->getMessage();
        return false;
    }
}

function obtenerUsuarios() {
    include 'conexion.php';
    try {
        return $conn->query('SELECT cedula, nombre1, nombre2, apellido1, apellido2, clase_empleado.clase, area_region.area_region, estado FROM `usuarios` INNER JOIN area_region, clase_empleado WHERE usuarios.area = area_region.id_area && usuarios.clase = clase_empleado.id_clase && usuarios.rol = "user" ORDER BY area_region DESC');
    } catch(Exception $e) {
        echo "Error! : " . $e->getMessage();
        return false;
    }
}

function listaUsuarios() {
    include 'conexion.php';
    $activo = 1;
    try {
        return $conn->query("SELECT cedula, nombre1, nombre2, apellido1, apellido2 FROM usuarios WHERE rol = 'user' && estado = {$activo} ORDER BY apellido1");
    } catch(Exception $e) {
        echo "Error! : " . $e->getMessage();
        return false;
    }
}
