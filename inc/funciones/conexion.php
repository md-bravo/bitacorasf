<?php

    // Conexión a BD en Hosting
    // $conn = new mysqli('localhost', 'id10451637_root', 'Brxvx', 'id10451637_bitacora'); 

    $conn = new mysqli('localhost', 'root', 'root', 'bitacora');

    //$conn = new mysqli('localhost', 'admin', 'admin', 'bitacora');

    if($conn->connect_error){
        echo $conn->connect_error;
    }
    $conn->set_charset('utf8');


