<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

if (isset($_POST['usuario'])) {
    $cedula = filter_var($_POST['usuario'], FILTER_SANITIZE_STRING);
} 
if (isset($_POST['areaRegion'])) {
    $areRegion = filter_var($_POST['areaRegion'], FILTER_SANITIZE_STRING);
} 
if (isset($_POST['mes'])) {
    $mes = filter_var($_POST['mes'], FILTER_SANITIZE_STRING);
} 
if (isset($_POST['anio'])) {
    $anio = filter_var($_POST['anio'], FILTER_SANITIZE_STRING);
} 
if (isset($_POST['tipo'])) {
    $tipo = filter_var($_POST['tipo'], FILTER_SANITIZE_STRING);
} 
if (isset($_POST['excepto'])) {
    $excepciones = $_POST['excepto'];
} 


$diaAnterior = 26;      // Dia de inicio del mes anterior
$dia = 1;               // Día de inicio del mes actual

// Se define el número de mes anterior
if($mes == 1 ) {
    $mesAnterior = 12;
} else {
    $mesAnterior = $mes - 1;
}

// Se define la cantidad de días del mes anterior y cuantos días se incluiran en el reporte.
$totalDiasMesAnterior = cal_days_in_month(CAL_GREGORIAN, $mesAnterior, $anio);
$cantidadDiasMesAnterior = $totalDiasMesAnterior - 25;

$cantidadDiasMesActual = 25;    // cantidad de días del mes actual que se incluiran
$cantidad_Dias = $cantidadDiasMesAnterior + $cantidadDiasMesActual;

// Se define el año anterior
if($mesAnterior === 12){
    $anioAnterior = $anio - 1;
} else {
    $anioAnterior = $anio;
}

function saber_dia($fecha_ingresada) {
    $dias = array('', 'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado', 'Domingo');
    $nombreDia = $dias[date('N', strtotime($fecha_ingresada))];
    return $nombreDia;
}

$jornadaLaboral = 9.6; //Horas
// $cantidad_Dias = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);

// Meses
$meses = array(
    '1' => 'Enero',
    '2' => 'Febrero',
    '3' => 'Marzo',
    '4' => 'Abril',
    '5' => 'Mayo',
    '6' => 'Junio',
    '7' => 'Julio',
    '8' => 'Agosto',
    '9' => 'Septiembre',
    '10' => 'Octubre',
    '11' => 'Noviembre',
    '12' => 'Diciembre',
);

// Se establece el nombre del mes según el elegido
foreach ($meses as $numero => $mesNombre){
    if($numero == $mes){
        //$nombreMes se inserta en el archivo excel
        $nombreMes = $mesNombre;
    }
}

// Se establece el nombre del mes anterior según el elegido
foreach ($meses as $numero => $mesNombre){
    if($numero == $mesAnterior){
        //$nombreMes se inserta en el archivo excel
        $nombreMesAnterior = $mesNombre;
    }
}

// Categorias según BD
$MC = 1;
$MP = 2;
$PA = 3;
$COP = 4;
$PI = 5;
$PII = 6;

// Actividades según BD
$GEC = 1;
$GEDT = 2;
$GEDA = 3;
$GETR = 4;
$GRP = 5;
$GRM = 6;
$GSPCT = 7;
$GET = 8;
$HE = 9;
$SI = 10;
$CR = 11;
$RT = 12;
$PM = 13;
$EC = 14;
$DCORS = 15;
$DGEAS = 16;
$ST = 17;
$SE = 18;
$AS = 19;
$PAICEG = 20;
$EDC = 21;
$ALM = 22;
$ICM = 23;
$VF = 24;


// // Conexión a la base de datos
include '../funciones/conexion.php';

//call the autoload
require '../../vendor/autoload.php';

//load phpspreadsheet class using namespaces
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
//call iofactory instead of xlsx writer
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

//----------------------------------------------------------------------------------------
// Cargar el template
$reader = IOFactory::createReader('Xlsx');

if($tipo === 'totalesTodos'){
    $spreadsheet = $reader->load('../../inc/templates/templateTotales.xlsx');
}else{
    $spreadsheet = $reader->load('../../inc/templates/template.xlsx');
}

$sheet = $spreadsheet->getActiveSheet();

// Propiedades del archivo
$spreadsheet->getProperties()->setCreator('MDBB')
                     ->setTitle('Informe de Actividades')
                     ->setLastModifiedBy('MDBB');
$sheet->setTitle('Informe de Actividades');     

$fondoAmarillo = [
    'fill'=>[
        'fillType'=> Fill::FILL_SOLID,
        'startColor'=> [
            'rgb' => 'FFD54B'
        ]
    ]  
];

$fondoCeleste = [
    'fill'=>[
        'fillType'=> Fill::FILL_SOLID,
        'startColor'=> [
            'rgb' => 'b8cce4'
        ]
    ]
];

$fondoVerde = [
    'fill'=>[
        'fillType'=> Fill::FILL_SOLID,
        'startColor'=> [
            'rgb' => '92d050'
        ]
    ]
];

$fondoMorado = [
    'fill'=>[
        'fillType'=> Fill::FILL_SOLID,
        'startColor'=> [
            'rgb' => 'CE81F0'
        ]
    ]
];

// Genera el reporte de una persona en particular
if($tipo === 'reportePersonal'){
    
    try {
        // Consulta datos del usuario
        $stmt = $conn->prepare("SELECT usuarios.cedula, usuarios.nombre1, usuarios.nombre2, usuarios.apellido1, usuarios.apellido2, clase_empleado.clase, area_region.area_region FROM usuarios INNER JOIN clase_empleado ON usuarios.clase = clase_empleado.id_clase INNER JOIN area_region ON usuarios.area = area_region.id_area WHERE usuarios.cedula = ? ");
    
        $stmt->bind_param('i', $cedula);
        $stmt->execute();

        $stmt->bind_result($cedula, $nombre1, $nombre2, $apellido1, $apellido2, $clase, $area);
        $stmt->fetch();
        $stmt->close();


        // Recupera de la base de datos los registro del usuario del mes anterior y año indicado.
        $stmt = $conn->prepare("SELECT reg_fecha, actividad FROM registros WHERE reg_cedula = ? AND mes = ? AND anio = ? ORDER BY reg_fecha ");
        $stmt->bind_param('iii', $cedula, $mesAnterior, $anioAnterior);
        $stmt->execute();
        $stmt->bind_result($fecha, $Reg_actividad);

        $registrosMesAnterior = array();
        
        while($stmt->fetch()) {                
            array_push($registrosMesAnterior, array('fecha'=>$fecha,'registro'=> $Reg_actividad));
        }

        $stmt->close();

        // var_dump($registroMesAnterior);
        // echo die();

        // Recupera de la base de datos los registro del usuario del mes y año indicado.
        $stmt = $conn->prepare("SELECT reg_fecha, actividad FROM registros WHERE reg_cedula = ? AND mes = ? AND anio = ? ORDER BY reg_fecha ");
        $stmt->bind_param('iii', $cedula, $mes, $anio);
        $stmt->execute();
        $stmt->bind_result($fecha, $Reg_actividad);

        $respuesta = array();
        
        while($stmt->fetch()) {                
            array_push($respuesta, array('fecha'=>$fecha,'registro'=> $Reg_actividad));
        }

        $stmt->close();
        $conn->close();

        //Insertar datos en las celdas
        $filaInicio = 3;
        $filaActual = 3;


        // Repite la cantidad de días del mes anterior al seleccionado
        for($x = 0; $x<$cantidadDiasMesAnterior; $x++){
            $totalHoras1=$totalHoras2=$totalHoras3=$totalHoras4=$totalHoras5=$totalHoras6=$totalHoras7=$totalHoras8=$totalHoras9=$totalHoras10=$totalHoras11=$totalHoras12=$totalHoras13=$totalHoras14=$totalHoras15=$totalHoras16=$totalHoras17=$totalHoras18=$totalHoras19=$totalHoras20=$totalHoras21=$totalHoras22=$totalHoras23=$totalHoras24=$totalHoras25=$totalHoras26=$totalHoras27=$totalHoras28=$totalHoras29=$totalHoras30=$totalHoras31=$totalHoras32=$totalHoras33=$totalHoras34=$totalHoras35=$totalHoras36=$totalHoras37=$totalHoras38=$totalHoras39=$totalHoras40=$totalHoras41=$totalHoras42=$totalHoras43=$totalHoras44=$totalHoras45=$totalHoras46=$totalHoras47=$totalHoras48=$totalHoras49=0;

            $sheet->setCellValue('A'.$filaActual, $cedula);
            $sheet->setCellValue('B'.$filaActual, $apellido1);
            $sheet->setCellValue('C'.$filaActual, $apellido2);
            $sheet->setCellValue('D'.$filaActual, $nombre1);
            $sheet->setCellValue('E'.$filaActual, $nombre2);
            $sheet->setCellValue('F'.$filaActual, $clase);
            $sheet->setCellValue('G'.$filaActual, $area);
            
            // La fecha necesita los 0 adelante para poder compararse
            if($mesAnterior < 10 && $diaAnterior < 10){
                $fecha_establecida = $anioAnterior.'-0'.$mesAnterior.'-0'.$diaAnterior;    
            } else if($mesAnterior < 10){
                $fecha_establecida = $anioAnterior.'-0'.$mesAnterior.'-'.$diaAnterior;    
            } else if($diaAnterior < 10){
                $fecha_establecida = $anioAnterior.'-'.$mesAnterior.'-0'.$diaAnterior;    
            }else{
                $fecha_establecida = $anioAnterior.'-'.$mesAnterior.'-'.$diaAnterior;    
            }
            
            $nombreDia= saber_dia($fecha_establecida);
            $sheet->setCellValue('H'.$filaActual, $nombreDia);
            $sheet->setCellValue('I'.$filaActual, $diaAnterior);
            $sheet->setCellValue('J'.$filaActual, $nombreMesAnterior);
            $sheet->setCellValue('K'.$filaActual, $anioAnterior);        
            if($nombreDia != 'Domingo' AND $nombreDia != 'Sábado'){
                $sheet->setCellValue('L'.$filaActual, $jornadaLaboral);
            }

            $sheet->getStyle('M'.$filaActual.':U'.$filaActual)->applyFromArray($fondoCeleste);
            $sheet->getStyle('AE'.$filaActual.':AM'.$filaActual)->applyFromArray($fondoCeleste);
            $sheet->getStyle('AN'.$filaActual.':AX'.$filaActual)->applyFromArray($fondoVerde);
            $sheet->getStyle('AY'.$filaActual.':BA'.$filaActual)->applyFromArray($fondoAmarillo);
            $sheet->getStyle('BB'.$filaActual.':BC'.$filaActual)->applyFromArray($fondoMorado);
            $sheet->getStyle('A'.$filaActual.':BE'.$filaActual)->getBorders()->getAllBorders()->applyFromArray( array( 'borderStyle' => Border::BORDER_THIN) );

            // Si hay valores los carga en la tabla
            foreach($registrosMesAnterior as $registro){
                if($registro['fecha'] === $fecha_establecida){
                    
                    $NumRegistros = sizeof(json_decode($registro['registro']));    //cantidad de registros en el array
                    $regAct = json_decode($registro['registro']);

                    //llenado de los datos en la tabla según condiciones
                    $detalle = '';
                    for($i=0; $i < $NumRegistros; $i++){               
                        $cat = $regAct[$i]->categoria;
                        $act = $regAct[$i]->actividad;
                        $horas = floatval($regAct[$i]->horas);

                        switch ($cat) {
                            case $MC:
                                switch ($act) {
                                    case $GEC:
                                        $sheet->setCellValue('M'.$filaActual, $totalHoras1 += $horas);  
                                        break;                            
                                    case $GEDT :
                                        $sheet->setCellValue('N'.$filaActual, $totalHoras2 += $horas);  
                                        break;
                                    case $GEDA :
                                        $sheet->setCellValue('O'.$filaActual, $totalHoras3 += $horas);  
                                        break;
                                    case $GETR :
                                        $sheet->setCellValue('P'.$filaActual, $totalHoras4 += $horas);  
                                        break;
                                    case $GRP :
                                        $sheet->setCellValue('Q'.$filaActual, $totalHoras5 += $horas);  
                                        break;
                                    case $GRM :
                                        $sheet->setCellValue('R'.$filaActual, $totalHoras6 += $horas);  
                                        break;
                                    case $GSPCT :
                                        $sheet->setCellValue('S'.$filaActual, $totalHoras7 += $horas);  
                                        break;
                                    case $GET :
                                        $sheet->setCellValue('T'.$filaActual, $totalHoras8 += $horas);  
                                        break;
                                    case $HE :
                                        $sheet->setCellValue('U'.$filaActual, $totalHoras9 += $horas);  
                                        break;                                            
                                    }
                                break;
                            case $MP:
                                switch ($act) {
                                    case $GEC:
                                        $sheet->setCellValue('V'.$filaActual, $totalHoras10 += $horas);  
                                        break;                            
                                    case $GEDT :
                                        $sheet->setCellValue('W'.$filaActual, $totalHoras11 += $horas);  
                                        break;
                                    case $GEDA :
                                        $sheet->setCellValue('X'.$filaActual, $totalHoras12 += $horas);  
                                        break;
                                    case $GETR :
                                        $sheet->setCellValue('Y'.$filaActual, $totalHoras13 += $horas);  
                                        break;
                                    case $GRP :
                                        $sheet->setCellValue('Z'.$filaActual, $totalHoras14 += $horas);  
                                        break;
                                    case $GRM :
                                        $sheet->setCellValue('AA'.$filaActual, $totalHoras15 += $horas);  
                                        break;
                                    case $GSPCT :
                                        $sheet->setCellValue('AB'.$filaActual, $totalHoras16 += $horas);  
                                        break;
                                    case $GET :
                                        $sheet->setCellValue('AC'.$filaActual, $totalHoras17 += $horas);  
                                        break;
                                    case $HE :
                                        $sheet->setCellValue('AD'.$filaActual, $totalHoras18 += $horas);  
                                        break;                                            
                                    }
                                break;
                            case $PA:
                                switch ($act) {
                                    case $GEC:
                                        $sheet->setCellValue('AE'.$filaActual, $totalHoras19 += $horas);  
                                        break;                            
                                    case $GEDT :
                                        $sheet->setCellValue('AF'.$filaActual, $totalHoras20 += $horas);  
                                        break;
                                    case $GEDA :
                                        $sheet->setCellValue('AG'.$filaActual, $totalHoras21 += $horas);  
                                        break;
                                    case $GETR :
                                        $sheet->setCellValue('AH'.$filaActual, $totalHoras22 += $horas);  
                                        break;
                                    case $GRP :
                                        $sheet->setCellValue('AI'.$filaActual, $totalHoras23 += $horas);  
                                        break;
                                    case $GRM :
                                        $sheet->setCellValue('AJ'.$filaActual, $totalHoras24 += $horas);  
                                        break;
                                    case $GSPCT :
                                        $sheet->setCellValue('AK'.$filaActual, $totalHoras25 += $horas);  
                                        break;
                                    case $GET :
                                        $sheet->setCellValue('AL'.$filaActual, $totalHoras26 += $horas);  
                                        break;
                                    case $HE :
                                        $sheet->setCellValue('AM'.$filaActual, $totalHoras27 += $horas);  
                                        break;                                            
                                    }
                                break;
                            case $COP:
                                switch ($act) {
                                    case $SI:
                                        $sheet->setCellValue('AN'.$filaActual, $totalHoras28 += $horas);  
                                        break;                            
                                    case $CR :
                                        $sheet->setCellValue('AO'.$filaActual, $totalHoras29 += $horas);  
                                        break;
                                    case $RT :
                                        $sheet->setCellValue('AP'.$filaActual, $totalHoras30 += $horas);  
                                        break;
                                    case $PM :
                                        $sheet->setCellValue('AQ'.$filaActual, $totalHoras31 += $horas);  
                                        break;
                                    case $EC :
                                        $sheet->setCellValue('AR'.$filaActual, $totalHoras32 += $horas);  
                                        break;
                                    case $DCORS :
                                        $sheet->setCellValue('AS'.$filaActual, $totalHoras33 += $horas);  
                                        break;
                                    case $DGEAS :
                                        $sheet->setCellValue('AT'.$filaActual, $totalHoras34 += $horas);  
                                        break;
                                    case $ST :
                                        $sheet->setCellValue('AU'.$filaActual, $totalHoras35 += $horas);  
                                        break;
                                    case $SE :
                                        $sheet->setCellValue('AV'.$filaActual, $totalHoras36 += $horas);  
                                        break;                                           
                                    case $AS :
                                        $sheet->setCellValue('AW'.$filaActual, $totalHoras37 += $horas);  
                                        break;
                                    case $HE :
                                        $sheet->setCellValue('AX'.$filaActual, $totalHoras38 += $horas);  
                                        break; 
                                    }
                                break;
                            case $PI:
                                switch ($act) {
                                    case $PAICEG:
                                        $sheet->setCellValue('AY'.$filaActual, $totalHoras39 += $horas);  
                                        break;                            
                                    case $EDC :
                                        $sheet->setCellValue('AZ'.$filaActual, $totalHoras40 += $horas);  
                                        break;
                                    case $ALM :
                                        $sheet->setCellValue('BA'.$filaActual, $totalHoras41 += $horas);  
                                        break;                                     
                                    }
                                break;
                            case $PII :
                                switch ($act) {
                                    case $ICM:
                                        $sheet->setCellValue('BB'.$filaActual, $totalHoras42 += $horas);  
                                        break;                            
                                    case $VF :
                                        $sheet->setCellValue('BC'.$filaActual, $totalHoras43 += $horas);  
                                        break;                                            
                                    }
                                break;                            
                        }            
                        $sheet->setCellValue('BD'.$filaActual, '=SUM(M'.$filaActual.':BC'.$filaActual.')');
                        // $detalle = $detalle . $regAct[$i]->detalle . ' / ';
                        // $sheet->setCellValue('BK'.$filaActual, $detalle);
                    }        

                // Si no hay valores, deja los espacios en blanco
                } else {
                    $cat = '';
                    $act = '';
                    $horas = '';
                    $detalle = '';
                    }
            } // End ForEach

            $filaActual++;
            $diaAnterior++;
        } // End For

        // Repite la cantidad de días que tenga el mes seleccionado
        for($x = 0; $x<$cantidadDiasMesActual; $x++){
            $totalHoras1=$totalHoras2=$totalHoras3=$totalHoras4=$totalHoras5=$totalHoras6=$totalHoras7=$totalHoras8=$totalHoras9=$totalHoras10=$totalHoras11=$totalHoras12=$totalHoras13=$totalHoras14=$totalHoras15=$totalHoras16=$totalHoras17=$totalHoras18=$totalHoras19=$totalHoras20=$totalHoras21=$totalHoras22=$totalHoras23=$totalHoras24=$totalHoras25=$totalHoras26=$totalHoras27=$totalHoras28=$totalHoras29=$totalHoras30=$totalHoras31=$totalHoras32=$totalHoras33=$totalHoras34=$totalHoras35=$totalHoras36=$totalHoras37=$totalHoras38=$totalHoras39=$totalHoras40=$totalHoras41=$totalHoras42=$totalHoras43=$totalHoras44=$totalHoras45=$totalHoras46=$totalHoras47=$totalHoras48=$totalHoras49=0;

            $sheet->setCellValue('A'.$filaActual, $cedula);
            $sheet->setCellValue('B'.$filaActual, $apellido1);
            $sheet->setCellValue('C'.$filaActual, $apellido2);
            $sheet->setCellValue('D'.$filaActual, $nombre1);
            $sheet->setCellValue('E'.$filaActual, $nombre2);
            $sheet->setCellValue('F'.$filaActual, $clase);
            $sheet->setCellValue('G'.$filaActual, $area);
            
            // La fecha necesita los 0 adelante para poder compararse
            if($mes < 10 && $dia < 10){
                $fecha_establecida = $anio.'-0'.$mes.'-0'.$dia;    
            } else if($mes < 10){
                $fecha_establecida = $anio.'-0'.$mes.'-'.$dia;    
            } else if($dia < 10){
                $fecha_establecida = $anio.'-'.$mes.'-0'.$dia;    
            }else{
                $fecha_establecida = $anio.'-'.$mes.'-'.$dia;    
            }
            
            $nombreDia= saber_dia($fecha_establecida);
            $sheet->setCellValue('H'.$filaActual, $nombreDia);
            $sheet->setCellValue('I'.$filaActual, $dia);
            $sheet->setCellValue('J'.$filaActual, $nombreMes);
            $sheet->setCellValue('K'.$filaActual, $anio);        
            if($nombreDia != 'Domingo' AND $nombreDia != 'Sábado'){
                $sheet->setCellValue('L'.$filaActual, $jornadaLaboral);
            }

            $sheet->getStyle('M'.$filaActual.':U'.$filaActual)->applyFromArray($fondoCeleste);
            $sheet->getStyle('AE'.$filaActual.':AM'.$filaActual)->applyFromArray($fondoCeleste);
            $sheet->getStyle('AN'.$filaActual.':AX'.$filaActual)->applyFromArray($fondoVerde);
            $sheet->getStyle('AY'.$filaActual.':BA'.$filaActual)->applyFromArray($fondoAmarillo);
            $sheet->getStyle('BB'.$filaActual.':BC'.$filaActual)->applyFromArray($fondoMorado);
            $sheet->getStyle('A'.$filaActual.':BE'.$filaActual)->getBorders()->getAllBorders()->applyFromArray( array( 'borderStyle' => Border::BORDER_THIN) );

            // Si hay valores los carga en la tabla
            foreach($respuesta as $registro){
                if($registro['fecha'] === $fecha_establecida){
                    
                    $NumRegistros = sizeof(json_decode($registro['registro']));    //cantidad de registros en el array
                    $regAct = json_decode($registro['registro']);

                    //llenado de los datos en la tabla según condiciones
                    $detalle = '';
                    for($i=0; $i < $NumRegistros; $i++){               
                        $cat = $regAct[$i]->categoria;
                        $act = $regAct[$i]->actividad;
                        $horas = floatval($regAct[$i]->horas);

                        switch ($cat) {
                            case $MC:
                                switch ($act) {
                                    case $GEC:
                                        $sheet->setCellValue('M'.$filaActual, $totalHoras1 += $horas);  
                                        break;                            
                                    case $GEDT :
                                        $sheet->setCellValue('N'.$filaActual, $totalHoras2 += $horas);  
                                        break;
                                    case $GEDA :
                                        $sheet->setCellValue('O'.$filaActual, $totalHoras3 += $horas);  
                                        break;
                                    case $GETR :
                                        $sheet->setCellValue('P'.$filaActual, $totalHoras4 += $horas);  
                                        break;
                                    case $GRP :
                                        $sheet->setCellValue('Q'.$filaActual, $totalHoras5 += $horas);  
                                        break;
                                    case $GRM :
                                        $sheet->setCellValue('R'.$filaActual, $totalHoras6 += $horas);  
                                        break;
                                    case $GSPCT :
                                        $sheet->setCellValue('S'.$filaActual, $totalHoras7 += $horas);  
                                        break;
                                    case $GET :
                                        $sheet->setCellValue('T'.$filaActual, $totalHoras8 += $horas);  
                                        break;
                                    case $HE :
                                        $sheet->setCellValue('U'.$filaActual, $totalHoras9 += $horas);  
                                        break;                                            
                                    }
                                break;
                            case $MP:
                                switch ($act) {
                                    case $GEC:
                                        $sheet->setCellValue('V'.$filaActual, $totalHoras10 += $horas);  
                                        break;                            
                                    case $GEDT :
                                        $sheet->setCellValue('W'.$filaActual, $totalHoras11 += $horas);  
                                        break;
                                    case $GEDA :
                                        $sheet->setCellValue('X'.$filaActual, $totalHoras12 += $horas);  
                                        break;
                                    case $GETR :
                                        $sheet->setCellValue('Y'.$filaActual, $totalHoras13 += $horas);  
                                        break;
                                    case $GRP :
                                        $sheet->setCellValue('Z'.$filaActual, $totalHoras14 += $horas);  
                                        break;
                                    case $GRM :
                                        $sheet->setCellValue('AA'.$filaActual, $totalHoras15 += $horas);  
                                        break;
                                    case $GSPCT :
                                        $sheet->setCellValue('AB'.$filaActual, $totalHoras16 += $horas);  
                                        break;
                                    case $GET :
                                        $sheet->setCellValue('AC'.$filaActual, $totalHoras17 += $horas);  
                                        break;
                                    case $HE :
                                        $sheet->setCellValue('AD'.$filaActual, $totalHoras18 += $horas);  
                                        break;                                            
                                    }
                                break;
                            case $PA:
                                switch ($act) {
                                    case $GEC:
                                        $sheet->setCellValue('AE'.$filaActual, $totalHoras19 += $horas);  
                                        break;                            
                                    case $GEDT :
                                        $sheet->setCellValue('AF'.$filaActual, $totalHoras20 += $horas);  
                                        break;
                                    case $GEDA :
                                        $sheet->setCellValue('AG'.$filaActual, $totalHoras21 += $horas);  
                                        break;
                                    case $GETR :
                                        $sheet->setCellValue('AH'.$filaActual, $totalHoras22 += $horas);  
                                        break;
                                    case $GRP :
                                        $sheet->setCellValue('AI'.$filaActual, $totalHoras23 += $horas);  
                                        break;
                                    case $GRM :
                                        $sheet->setCellValue('AJ'.$filaActual, $totalHoras24 += $horas);  
                                        break;
                                    case $GSPCT :
                                        $sheet->setCellValue('AK'.$filaActual, $totalHoras25 += $horas);  
                                        break;
                                    case $GET :
                                        $sheet->setCellValue('AL'.$filaActual, $totalHoras26 += $horas);  
                                        break;
                                    case $HE :
                                        $sheet->setCellValue('AM'.$filaActual, $totalHoras27 += $horas);  
                                        break;                                            
                                    }
                                break;
                            case $COP:
                                switch ($act) {
                                    case $SI:
                                        $sheet->setCellValue('AN'.$filaActual, $totalHoras28 += $horas);  
                                        break;                            
                                    case $CR :
                                        $sheet->setCellValue('AO'.$filaActual, $totalHoras29 += $horas);  
                                        break;
                                    case $RT :
                                        $sheet->setCellValue('AP'.$filaActual, $totalHoras30 += $horas);  
                                        break;
                                    case $PM :
                                        $sheet->setCellValue('AQ'.$filaActual, $totalHoras31 += $horas);  
                                        break;
                                    case $EC :
                                        $sheet->setCellValue('AR'.$filaActual, $totalHoras32 += $horas);  
                                        break;
                                    case $DCORS :
                                        $sheet->setCellValue('AS'.$filaActual, $totalHoras33 += $horas);  
                                        break;
                                    case $DGEAS :
                                        $sheet->setCellValue('AT'.$filaActual, $totalHoras34 += $horas);  
                                        break;
                                    case $ST :
                                        $sheet->setCellValue('AU'.$filaActual, $totalHoras35 += $horas);  
                                        break;
                                    case $SE :
                                        $sheet->setCellValue('AV'.$filaActual, $totalHoras36 += $horas);  
                                        break;                                           
                                    case $AS :
                                        $sheet->setCellValue('AW'.$filaActual, $totalHoras37 += $horas);  
                                        break;
                                    case $HE :
                                        $sheet->setCellValue('AX'.$filaActual, $totalHoras38 += $horas);  
                                        break; 
                                    }
                                break;
                            case $PI:
                                switch ($act) {
                                    case $PAICEG:
                                        $sheet->setCellValue('AY'.$filaActual, $totalHoras39 += $horas);  
                                        break;                            
                                    case $EDC :
                                        $sheet->setCellValue('AZ'.$filaActual, $totalHoras40 += $horas);  
                                        break;
                                    case $ALM :
                                        $sheet->setCellValue('BA'.$filaActual, $totalHoras41 += $horas);  
                                        break;                                     
                                    }
                                break;
                            case $PII :
                                switch ($act) {
                                    case $ICM:
                                        $sheet->setCellValue('BB'.$filaActual, $totalHoras42 += $horas);  
                                        break;                            
                                    case $VF :
                                        $sheet->setCellValue('BC'.$filaActual, $totalHoras43 += $horas);  
                                        break;                                            
                                    }
                                break;                            
                        }            
                        $sheet->setCellValue('BD'.$filaActual, '=SUM(M'.$filaActual.':BC'.$filaActual.')');
                        // $detalle = $detalle . $regAct[$i]->detalle . ' / ';
                        // $sheet->setCellValue('BK'.$filaActual, $detalle);
                    }        

                // Si no hay valores, deja los espacios en blanco
                } else {
                    $cat = '';
                    $act = '';
                    $horas = '';
                    $detalle = '';
                    }
            } // End ForEach

            $filaActual++;
            $dia++;
        } // End For

        } catch(Exception $e) {
            // En caso de un error, tomar la exepcion
            $respuesta = array(
                'error' => $e->getMessage()
            );
        }


    //set the header first, so the result will be treated as an xlsx file.
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    //make it an attachment so we can define filename
    header('Content-Disposition: attachment;filename="Informe Actividades '.$nombreMes.'.xlsx"');
    header('Cache-Control: max-age-0');
    // If use IE 9
    header('Cache-Control: max-age-1');

     // If you're serving to IE over SSL, then the following may be needed
     header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
     header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
     //header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
     header('Pragma: public'); // HTTP/1.0

    //create IOFactory object
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    //save into php output
    $writer->save('php://output');
}
