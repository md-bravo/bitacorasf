<script src="js/jquery-3.4.1.slim.min.js"></script>
<script src="js/popper.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/sweetalert2.all.min.js"></script> 
<script src="js/fullcalendar/core/main.min.js"></script> 
<script src="js/fullcalendar/daygrid/main.min.js"></script> 
<script src="js/fullcalendar/timegrid/main.min.js"></script> 
<script src="js/fullcalendar/interaction/main.min.js"></script> 
<script src="js/fullcalendar/core/locales-all.min.js"></script> 
<script src="js/fullcalendar/bootstrap/main.min.js"></script>
<script src="js/jquery.multiselect.js"></script>
<script src="js/pdf/jspdf.min.js"></script>

<?php 
    $actual = obtenerPaginaActual();
    if($actual === 'crear-cuenta'){
        echo '<script src="js/formulario-crear-cuenta.js"></script>';
    } elseif($actual === 'login') {
        echo '<script src="js/formulario-login.js"></script>';        
    } elseif($actual === 'loginBackend') {
        echo '<script src="js/formulario-login.js"></script>';     
    }elseif($actual === 'backend') {
        echo '<script src="js/scriptsBackend.js"></script>';
    }else {
        echo '<script src="js/formulario-registrar-actividad.js"></script>';
        echo '<script src="js/scripts.js?v=2"></script>';
    }
?>

</body>
</html>