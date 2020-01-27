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
$GA = 1; 
$GEC = 3;
$GEDT = 4;
$GEDA = 7;
$GETR = 9;
$GRP = 10;
$GRM = 11;
$GSPCT = 13;
$GET = 15;
$COPD = 17;
$PER = 19;

// Actividades según BD
$MP = 2;
$MC = 1;
$EO = 3;
$TE = 4;
$GAAct = 5;
// $SF = 6;
$SI = 7;
$CR = 8;
$RT = 9;
$PM = 10;
$EC = 16;
$DCORS = 17;
$DGEAS = 18;
$ST = 19;
$SE = 20;
$AS = 21;
$ICM = 22;
$PAICEG = 23;
$EDC = 24;
$VAC = 25;
$DPAlm = 26;

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
    $spreadsheet = $reader->load('../../inc/templates/templateNew.xlsx');
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

            $sheet->getStyle('M'.$filaActual)->applyFromArray($fondoAmarillo);
            $sheet->getStyle('N'.$filaActual.':Q'.$filaActual)->applyFromArray($fondoCeleste);
            $sheet->getStyle('V'.$filaActual.':Y'.$filaActual)->applyFromArray($fondoCeleste);
            $sheet->getStyle('AD'.$filaActual.':AG'.$filaActual)->applyFromArray($fondoCeleste);
            $sheet->getStyle('AL'.$filaActual.':AO'.$filaActual)->applyFromArray($fondoCeleste);
            $sheet->getStyle('AT'.$filaActual.':BC'.$filaActual)->applyFromArray($fondoCeleste);
            $sheet->getStyle('CC'.$filaActual.':CG'.$filaActual)->applyFromArray($fondoVerde);
            $sheet->getStyle('A'.$filaActual.':CI'.$filaActual)->getBorders()->getAllBorders()->applyFromArray( array( 'borderStyle' => Border::BORDER_THIN) );

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
                            case $GA:
                                $sheet->setCellValue('M'.$filaActual, $totalHoras1 += $horas);                        
                                break;
                            case $GEC:
                                switch ($act) {
                                    case $MC:
                                        $sheet->setCellValue('N'.$filaActual, $totalHoras2 += $horas);  
                                        break;                            
                                    case $MP :
                                        $sheet->setCellValue('O'.$filaActual, $totalHoras3 += $horas);  
                                        break;
                                    case $EO :
                                        $sheet->setCellValue('P'.$filaActual, $totalHoras4 += $horas);  
                                        break;
                                    case $TE :
                                        $sheet->setCellValue('Q'.$filaActual, $totalHoras5 += $horas);  
                                        break;                                                                                        
                                    }
                                break;
                            case $GEDT:
                                switch ($act) {
                                    case $MC:
                                        $sheet->setCellValue('R'.$filaActual, $totalHoras6 += $horas);  
                                        break;                            
                                    case $MP :
                                        $sheet->setCellValue('S'.$filaActual, $totalHoras7 += $horas);  
                                        break;
                                    case $EO :
                                        $sheet->setCellValue('T'.$filaActual, $totalHoras8 += $horas);  
                                        break;
                                    case $TE :
                                        $sheet->setCellValue('U'.$filaActual, $totalHoras9 += $horas);  
                                        break;                                                                                        
                                    }
                                break;
                            case $GEDA :
                                switch ($act) {
                                    case $MC:
                                        $sheet->setCellValue('V'.$filaActual, $totalHoras10 += $horas);  
                                        break;                            
                                    case $MP :
                                        $sheet->setCellValue('W'.$filaActual, $totalHoras11 += $horas);  
                                        break;
                                    case $EO :
                                        $sheet->setCellValue('X'.$filaActual, $totalHoras12 += $horas);  
                                        break;
                                    case $TE :
                                        $sheet->setCellValue('Y'.$filaActual, $totalHoras13 += $horas);  
                                        break;                                                                                        
                                    }
                                break;
                            case $GETR :
                                switch ($act) {
                                    case $MC:
                                        $sheet->setCellValue('Z'.$filaActual, $totalHoras14 += $horas);  
                                        break;                            
                                    case $MP :
                                        $sheet->setCellValue('AA'.$filaActual, $totalHoras15 += $horas);  
                                        break;
                                    case $EO :
                                        $sheet->setCellValue('AB'.$filaActual, $totalHoras16 += $horas);  
                                        break;
                                    case $TE :
                                        $sheet->setCellValue('AC'.$filaActual, $totalHoras17 += $horas);  
                                        break;                                                                                        
                                    }
                                break;
                            case $GRP :
                                switch ($act) {
                                    case $MC:
                                        $sheet->setCellValue('AD'.$filaActual, $totalHoras18 += $horas);  
                                        break;                            
                                    case $MP :
                                        $sheet->setCellValue('AE'.$filaActual, $totalHoras19 += $horas);  
                                        break;
                                    case $EO :
                                        $sheet->setCellValue('AF'.$filaActual, $totalHoras20 += $horas);  
                                        break;
                                    case $TE :
                                        $sheet->setCellValue('AG'.$filaActual, $totalHoras21 += $horas);  
                                        break;                                                                                        
                                    }
                                break;
                            case $GRM :
                                switch ($act) {
                                    case $MC:
                                        $sheet->setCellValue('AH'.$filaActual, $totalHoras22 += $horas);  
                                        break;                            
                                    case $MP :
                                        $sheet->setCellValue('AI'.$filaActual, $totalHoras23 += $horas);  
                                        break;
                                    case $EO :
                                        $sheet->setCellValue('AJ'.$filaActual, $totalHoras24 += $horas);  
                                        break;
                                    case $TE :
                                        $sheet->setCellValue('AK'.$filaActual, $totalHoras25 += $horas);  
                                        break;                                                                                        
                                    }
                                break;
                            case $GSPCT :
                                switch ($act) {
                                    case $MC:
                                        $sheet->setCellValue('AL'.$filaActual, $totalHoras26 += $horas);  
                                        break;                            
                                    case $MP :
                                        $sheet->setCellValue('AM'.$filaActual, $totalHoras27 += $horas);  
                                        break;
                                    case $EO :
                                        $sheet->setCellValue('AN'.$filaActual, $totalHoras28 += $horas);  
                                        break;
                                    case $TE :
                                        $sheet->setCellValue('AO'.$filaActual, $totalHoras29 += $horas);  
                                        break;                                                                                        
                                    }
                                break;
                            case $GET :
                                switch ($act) {
                                    case $MC:
                                        $sheet->setCellValue('AP'.$filaActual, $totalHoras30 += $horas);  
                                        break;                            
                                    case $MP :
                                        $sheet->setCellValue('AQ'.$filaActual, $totalHoras31 += $horas);  
                                        break;
                                    case $EO :
                                        $sheet->setCellValue('AR'.$filaActual, $totalHoras32 += $horas);  
                                        break;
                                    case $TE :
                                        $sheet->setCellValue('AS'.$filaActual, $totalHoras33 += $horas);  
                                        break;                                                                                        
                                    }
                                break;
                            case $COPD  :
                                switch ($act) {
                                    // case $SF :
                                    //     $sheet->setCellValue('AT'.$filaActual, $totalHoras34 += $horas);  
                                    //     break;                            
                                    case $SI :
                                        $sheet->setCellValue('AT'.$filaActual, $totalHoras35 += $horas);  
                                        break;
                                    case $CR :
                                        $sheet->setCellValue('AU'.$filaActual, $totalHoras36 += $horas);  
                                        break;
                                    case $RT :
                                        $sheet->setCellValue('AV'.$filaActual, $totalHoras37 += $horas);  
                                        break;  
                                    case $PM :
                                        $sheet->setCellValue('AW'.$filaActual, $totalHoras38 += $horas);  
                                        break;  
                                    case $EC :
                                        $sheet->setCellValue('AX'.$filaActual, $totalHoras39 += $horas);  
                                        break;  
                                    case $DCORS :
                                        $sheet->setCellValue('AY'.$filaActual, $totalHoras40 += $horas);  
                                        break;  
                                    case $DGEAS :
                                        $sheet->setCellValue('AZ'.$filaActual, $totalHoras41 += $horas);  
                                        break; 
                                    case $ST :
                                        $sheet->setCellValue('BA'.$filaActual, $totalHoras42 += $horas);  
                                        break;  
                                    case $SE :
                                        $sheet->setCellValue('BB'.$filaActual, $totalHoras43 += $horas);  
                                        break;
                                    case $AS :
                                        $sheet->setCellValue('BC'.$filaActual, $totalHoras44 += $horas);  
                                        break;                                                                                           
                                    }
                                break;
                            case $PER  :
                                switch ($act) {
                                    case $ICM :
                                        $sheet->setCellValue('CC'.$filaActual, $totalHoras45 += $horas);  
                                        break;                            
                                    case $PAICEG :
                                        $sheet->setCellValue('CD'.$filaActual, $totalHoras46 += $horas);  
                                        break;
                                    case $EDC :
                                        $sheet->setCellValue('CE'.$filaActual, $totalHoras47 += $horas);  
                                        break;
                                    case $VAC :
                                        $sheet->setCellValue('CF'.$filaActual, $totalHoras48 += $horas);  
                                        break;                                                                                        
                                    case $DPAlm :
                                        $sheet->setCellValue('CG'.$filaActual, $totalHoras49 += $horas);  
                                        break; 
                                    }
                                break;
                        }            
                        $sheet->setCellValue('CH'.$filaActual, '=SUM(M'.$filaActual.':CG'.$filaActual.')');
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

            $sheet->getStyle('M'.$filaActual)->applyFromArray($fondoAmarillo);
            $sheet->getStyle('N'.$filaActual.':Q'.$filaActual)->applyFromArray($fondoCeleste);
            $sheet->getStyle('V'.$filaActual.':Y'.$filaActual)->applyFromArray($fondoCeleste);
            $sheet->getStyle('AD'.$filaActual.':AG'.$filaActual)->applyFromArray($fondoCeleste);
            $sheet->getStyle('AL'.$filaActual.':AO'.$filaActual)->applyFromArray($fondoCeleste);
            $sheet->getStyle('AT'.$filaActual.':BC'.$filaActual)->applyFromArray($fondoCeleste);
            $sheet->getStyle('CC'.$filaActual.':CG'.$filaActual)->applyFromArray($fondoVerde);
            $sheet->getStyle('A'.$filaActual.':CI'.$filaActual)->getBorders()->getAllBorders()->applyFromArray( array( 'borderStyle' => Border::BORDER_THIN) );

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
                            case $GA:
                                $sheet->setCellValue('M'.$filaActual, $totalHoras1 += $horas);                        
                                break;
                            case $GEC:
                                switch ($act) {
                                    case $MC:
                                        $sheet->setCellValue('N'.$filaActual, $totalHoras2 += $horas);  
                                        break;                            
                                    case $MP :
                                        $sheet->setCellValue('O'.$filaActual, $totalHoras3 += $horas);  
                                        break;
                                    case $EO :
                                        $sheet->setCellValue('P'.$filaActual, $totalHoras4 += $horas);  
                                        break;
                                    case $TE :
                                        $sheet->setCellValue('Q'.$filaActual, $totalHoras5 += $horas);  
                                        break;                                                                                        
                                    }
                                break;
                            case $GEDT:
                                switch ($act) {
                                    case $MC:
                                        $sheet->setCellValue('R'.$filaActual, $totalHoras6 += $horas);  
                                        break;                            
                                    case $MP :
                                        $sheet->setCellValue('S'.$filaActual, $totalHoras7 += $horas);  
                                        break;
                                    case $EO :
                                        $sheet->setCellValue('T'.$filaActual, $totalHoras8 += $horas);  
                                        break;
                                    case $TE :
                                        $sheet->setCellValue('U'.$filaActual, $totalHoras9 += $horas);  
                                        break;                                                                                        
                                    }
                                break;
                            case $GEDA :
                                switch ($act) {
                                    case $MC:
                                        $sheet->setCellValue('V'.$filaActual, $totalHoras10 += $horas);  
                                        break;                            
                                    case $MP :
                                        $sheet->setCellValue('W'.$filaActual, $totalHoras11 += $horas);  
                                        break;
                                    case $EO :
                                        $sheet->setCellValue('X'.$filaActual, $totalHoras12 += $horas);  
                                        break;
                                    case $TE :
                                        $sheet->setCellValue('Y'.$filaActual, $totalHoras13 += $horas);  
                                        break;                                                                                        
                                    }
                                break;
                            case $GETR :
                                switch ($act) {
                                    case $MC:
                                        $sheet->setCellValue('Z'.$filaActual, $totalHoras14 += $horas);  
                                        break;                            
                                    case $MP :
                                        $sheet->setCellValue('AA'.$filaActual, $totalHoras15 += $horas);  
                                        break;
                                    case $EO :
                                        $sheet->setCellValue('AB'.$filaActual, $totalHoras16 += $horas);  
                                        break;
                                    case $TE :
                                        $sheet->setCellValue('AC'.$filaActual, $totalHoras17 += $horas);  
                                        break;                                                                                        
                                    }
                                break;
                            case $GRP :
                                switch ($act) {
                                    case $MC:
                                        $sheet->setCellValue('AD'.$filaActual, $totalHoras18 += $horas);  
                                        break;                            
                                    case $MP :
                                        $sheet->setCellValue('AE'.$filaActual, $totalHoras19 += $horas);  
                                        break;
                                    case $EO :
                                        $sheet->setCellValue('AF'.$filaActual, $totalHoras20 += $horas);  
                                        break;
                                    case $TE :
                                        $sheet->setCellValue('AG'.$filaActual, $totalHoras21 += $horas);  
                                        break;                                                                                        
                                    }
                                break;
                            case $GRM :
                                switch ($act) {
                                    case $MC:
                                        $sheet->setCellValue('AH'.$filaActual, $totalHoras22 += $horas);  
                                        break;                            
                                    case $MP :
                                        $sheet->setCellValue('AI'.$filaActual, $totalHoras23 += $horas);  
                                        break;
                                    case $EO :
                                        $sheet->setCellValue('AJ'.$filaActual, $totalHoras24 += $horas);  
                                        break;
                                    case $TE :
                                        $sheet->setCellValue('AK'.$filaActual, $totalHoras25 += $horas);  
                                        break;                                                                                        
                                    }
                                break;
                            case $GSPCT :
                                switch ($act) {
                                    case $MC:
                                        $sheet->setCellValue('AL'.$filaActual, $totalHoras26 += $horas);  
                                        break;                            
                                    case $MP :
                                        $sheet->setCellValue('AM'.$filaActual, $totalHoras27 += $horas);  
                                        break;
                                    case $EO :
                                        $sheet->setCellValue('AN'.$filaActual, $totalHoras28 += $horas);  
                                        break;
                                    case $TE :
                                        $sheet->setCellValue('AO'.$filaActual, $totalHoras29 += $horas);  
                                        break;                                                                                        
                                    }
                                break;
                            case $GET :
                                switch ($act) {
                                    case $MC:
                                        $sheet->setCellValue('AP'.$filaActual, $totalHoras30 += $horas);  
                                        break;                            
                                    case $MP :
                                        $sheet->setCellValue('AQ'.$filaActual, $totalHoras31 += $horas);  
                                        break;
                                    case $EO :
                                        $sheet->setCellValue('AR'.$filaActual, $totalHoras32 += $horas);  
                                        break;
                                    case $TE :
                                        $sheet->setCellValue('AS'.$filaActual, $totalHoras33 += $horas);  
                                        break;                                                                                        
                                    }
                                break;
                            case $COPD  :
                                switch ($act) {
                                    // case $SF :
                                    //     $sheet->setCellValue('AT'.$filaActual, $totalHoras34 += $horas);  
                                    //     break;                            
                                    case $SI :
                                        $sheet->setCellValue('AT'.$filaActual, $totalHoras35 += $horas);  
                                        break;
                                    case $CR :
                                        $sheet->setCellValue('AU'.$filaActual, $totalHoras36 += $horas);  
                                        break;
                                    case $RT :
                                        $sheet->setCellValue('AV'.$filaActual, $totalHoras37 += $horas);  
                                        break;  
                                    case $PM :
                                        $sheet->setCellValue('AW'.$filaActual, $totalHoras38 += $horas);  
                                        break;  
                                    case $EC :
                                        $sheet->setCellValue('AX'.$filaActual, $totalHoras39 += $horas);  
                                        break;  
                                    case $DCORS :
                                        $sheet->setCellValue('AY'.$filaActual, $totalHoras40 += $horas);  
                                        break;  
                                    case $DGEAS :
                                        $sheet->setCellValue('AZ'.$filaActual, $totalHoras41 += $horas);  
                                        break; 
                                    case $ST :
                                        $sheet->setCellValue('BA'.$filaActual, $totalHoras42 += $horas);  
                                        break;  
                                    case $SE :
                                        $sheet->setCellValue('BB'.$filaActual, $totalHoras43 += $horas);  
                                        break;
                                    case $AS :
                                        $sheet->setCellValue('BC'.$filaActual, $totalHoras44 += $horas);  
                                        break;                                                                                           
                                    }
                                break;
                            case $PER  :
                                switch ($act) {
                                    case $ICM :
                                        $sheet->setCellValue('CC'.$filaActual, $totalHoras45 += $horas);  
                                        break;                            
                                    case $PAICEG :
                                        $sheet->setCellValue('CD'.$filaActual, $totalHoras46 += $horas);  
                                        break;
                                    case $EDC :
                                        $sheet->setCellValue('CE'.$filaActual, $totalHoras47 += $horas);  
                                        break;
                                    case $VAC :
                                        $sheet->setCellValue('CF'.$filaActual, $totalHoras48 += $horas);  
                                        break;                                                                                        
                                    case $DPAlm :
                                        $sheet->setCellValue('CG'.$filaActual, $totalHoras49 += $horas);  
                                        break; 
                                    }
                                break;
                        }            
                        $sheet->setCellValue('CH'.$filaActual, '=SUM(M'.$filaActual.':CG'.$filaActual.')');
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


// Genera reporte de todos los trabajadores de una área o región en específico
if($tipo === 'areaRegion'){

    try {
        $activo = 1; //Estado activo en la base de datos
        // Extrae la lista de personas de un área en específico
        $stmt = $conn->prepare("SELECT usuarios.cedula, usuarios.nombre1, usuarios.nombre2, usuarios.apellido1, usuarios.apellido2, clase_empleado.clase, area_region.area_region FROM usuarios INNER JOIN clase_empleado ON usuarios.clase = clase_empleado.id_clase INNER JOIN area_region ON usuarios.area = area_region.id_area WHERE usuarios.area = ? AND usuarios.estado = ? ORDER BY usuarios.apellido1");
        $stmt->bind_param('ii', $areRegion, $activo);
        $stmt->execute();
        $stmt->bind_result($cedula, $nombre1, $nombre2, $apellido1, $apellido2, $clase, $area);
        
        $datosUsuarios = array();
        
        // Almacena cada usuario en un array
        while($stmt->fetch()) {                
            array_push($datosUsuarios, array('cedula'=>$cedula, 'nombre1'=>$nombre1, 'nombre2'=>$nombre2, 'apellido1'=>$apellido1, 'apellido2'=>$apellido2, 'clase'=>$clase, 'area'=>$area));
        }

        $stmt->close();

        $filaActual = 3;
        //Recorre el array y por cada usuario consulta sus actividades, según el mes y año elegido y lo agrega al excel
        foreach ($datosUsuarios as $llave => $usuario) {        
    
            // Recupera de la base de datos los registro del usuario del mes y año indicado.
            $stmt = $conn->prepare("SELECT reg_fecha, actividad FROM registros WHERE reg_cedula = ? AND mes = ? AND anio = ? ORDER BY reg_fecha ");
            $stmt->bind_param('iii', $usuario['cedula'], $mes, $anio);
            $stmt->execute();
            $stmt->bind_result($fecha, $Reg_actividad);

            $respuesta = array();
            
            while($stmt->fetch()) {                
                array_push($respuesta, array('fecha'=>$fecha,'registro'=> $Reg_actividad));
            }         

            $stmt->close();

                $dia=1;
                // Repite la cantidad de días que tenga el mes seleccionado
                for($x = 0; $x<$cantidad_Dias; $x++){
                    $totalHoras1=$totalHoras2=$totalHoras3=$totalHoras4=$totalHoras5=$totalHoras6=$totalHoras7=$totalHoras8=$totalHoras9=$totalHoras10=$totalHoras11=$totalHoras12=$totalHoras13=$totalHoras14=$totalHoras15=$totalHoras16=$totalHoras17=$totalHoras18=$totalHoras19=$totalHoras20=$totalHoras21=$totalHoras22=$totalHoras23=$totalHoras24=$totalHoras25=$totalHoras26=$totalHoras27=$totalHoras28=$totalHoras29=$totalHoras30=$totalHoras31=$totalHoras32=$totalHoras33=$totalHoras34=$totalHoras35=$totalHoras36=$totalHoras37=$totalHoras38=$totalHoras39=$totalHoras40=$totalHoras41=$totalHoras42=$totalHoras43=$totalHoras44=$totalHoras45=$totalHoras46=$totalHoras47=$totalHoras48=$totalHoras49=0;

                    $sheet->setCellValue('A'.$filaActual, $usuario['cedula']);
                    $sheet->setCellValue('B'.$filaActual, $usuario['apellido1']);
                    $sheet->setCellValue('C'.$filaActual, $usuario['apellido2']);
                    $sheet->setCellValue('D'.$filaActual, $usuario['nombre1']);
                    $sheet->setCellValue('E'.$filaActual, $usuario['nombre2']);
                    $sheet->setCellValue('F'.$filaActual, $usuario['clase']);
                    $sheet->setCellValue('G'.$filaActual, $usuario['area']);
                    
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

                    $sheet->getStyle('M'.$filaActual)->applyFromArray($fondoAmarillo);
                    $sheet->getStyle('N'.$filaActual.':Q'.$filaActual)->applyFromArray($fondoCeleste);
                    $sheet->getStyle('V'.$filaActual.':Y'.$filaActual)->applyFromArray($fondoCeleste);
                    $sheet->getStyle('AD'.$filaActual.':AG'.$filaActual)->applyFromArray($fondoCeleste);
                    $sheet->getStyle('AL'.$filaActual.':AO'.$filaActual)->applyFromArray($fondoCeleste);
                    $sheet->getStyle('AT'.$filaActual.':BD'.$filaActual)->applyFromArray($fondoCeleste);
                    $sheet->getStyle('BE'.$filaActual.':BI'.$filaActual)->applyFromArray($fondoVerde);
                    $sheet->getStyle('A'.$filaActual.':BK'.$filaActual)->getBorders()->getAllBorders()->applyFromArray( array( 'borderStyle' => Border::BORDER_THIN) );
            
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
                                    case $GA:
                                        $sheet->setCellValue('M'.$filaActual, $totalHoras1 += $horas);                        
                                        break;
                                    case $GEC:
                                        switch ($act) {
                                            case $MC:
                                                $sheet->setCellValue('N'.$filaActual, $totalHoras2 += $horas);  
                                                break;                            
                                            case $MP :
                                                $sheet->setCellValue('O'.$filaActual, $totalHoras3 += $horas);  
                                                break;
                                            case $EO :
                                                $sheet->setCellValue('P'.$filaActual, $totalHoras4 += $horas);  
                                                break;
                                            case $TE :
                                                $sheet->setCellValue('Q'.$filaActual, $totalHoras5 += $horas);  
                                                break;                                                                                        
                                            }
                                        break;
                                    case $GEDT:
                                        switch ($act) {
                                            case $MC:
                                                $sheet->setCellValue('R'.$filaActual, $totalHoras6 += $horas);  
                                                break;                            
                                            case $MP :
                                                $sheet->setCellValue('S'.$filaActual, $totalHoras7 += $horas);  
                                                break;
                                            case $EO :
                                                $sheet->setCellValue('T'.$filaActual, $totalHoras8 += $horas);  
                                                break;
                                            case $TE :
                                                $sheet->setCellValue('U'.$filaActual, $totalHoras9 += $horas);  
                                                break;                                                                                        
                                            }
                                        break;
                                    case $GEDA :
                                        switch ($act) {
                                            case $MC:
                                                $sheet->setCellValue('V'.$filaActual, $totalHoras10 += $horas);  
                                                break;                            
                                            case $MP :
                                                $sheet->setCellValue('W'.$filaActual, $totalHoras11 += $horas);  
                                                break;
                                            case $EO :
                                                $sheet->setCellValue('X'.$filaActual, $totalHoras12 += $horas);  
                                                break;
                                            case $TE :
                                                $sheet->setCellValue('Y'.$filaActual, $totalHoras13 += $horas);  
                                                break;                                                                                        
                                            }
                                        break;
                                    case $GETR :
                                        switch ($act) {
                                            case $MC:
                                                $sheet->setCellValue('Z'.$filaActual, $totalHoras14 += $horas);  
                                                break;                            
                                            case $MP :
                                                $sheet->setCellValue('AA'.$filaActual, $totalHoras15 += $horas);  
                                                break;
                                            case $EO :
                                                $sheet->setCellValue('AB'.$filaActual, $totalHoras16 += $horas);  
                                                break;
                                            case $TE :
                                                $sheet->setCellValue('AC'.$filaActual, $totalHoras17 += $horas);  
                                                break;                                                                                        
                                            }
                                        break;
                                    case $GRP :
                                        switch ($act) {
                                            case $MC:
                                                $sheet->setCellValue('AD'.$filaActual, $totalHoras18 += $horas);  
                                                break;                            
                                            case $MP :
                                                $sheet->setCellValue('AE'.$filaActual, $totalHoras19 += $horas);  
                                                break;
                                            case $EO :
                                                $sheet->setCellValue('AF'.$filaActual, $totalHoras20 += $horas);  
                                                break;
                                            case $TE :
                                                $sheet->setCellValue('AG'.$filaActual, $totalHoras21 += $horas);  
                                                break;                                                                                        
                                            }
                                        break;
                                    case $GRM :
                                        switch ($act) {
                                            case $MC:
                                                $sheet->setCellValue('AH'.$filaActual, $totalHoras22 += $horas);  
                                                break;                            
                                            case $MP :
                                                $sheet->setCellValue('AI'.$filaActual, $totalHoras23 += $horas);  
                                                break;
                                            case $EO :
                                                $sheet->setCellValue('AJ'.$filaActual, $totalHoras24 += $horas);  
                                                break;
                                            case $TE :
                                                $sheet->setCellValue('AK'.$filaActual, $totalHoras25 += $horas);  
                                                break;                                                                                        
                                            }
                                        break;
                                    case $GSPCT :
                                        switch ($act) {
                                            case $MC:
                                                $sheet->setCellValue('AL'.$filaActual, $totalHoras26 += $horas);  
                                                break;                            
                                            case $MP :
                                                $sheet->setCellValue('AM'.$filaActual, $totalHoras27 += $horas);  
                                                break;
                                            case $EO :
                                                $sheet->setCellValue('AN'.$filaActual, $totalHoras28 += $horas);  
                                                break;
                                            case $TE :
                                                $sheet->setCellValue('AO'.$filaActual, $totalHoras29 += $horas);  
                                                break;                                                                                        
                                            }
                                        break;
                                    case $GET :
                                        switch ($act) {
                                            case $MC:
                                                $sheet->setCellValue('AP'.$filaActual, $totalHoras30 += $horas);  
                                                break;                            
                                            case $MP :
                                                $sheet->setCellValue('AQ'.$filaActual, $totalHoras31 += $horas);  
                                                break;
                                            case $EO :
                                                $sheet->setCellValue('AR'.$filaActual, $totalHoras32 += $horas);  
                                                break;
                                            case $TE :
                                                $sheet->setCellValue('AS'.$filaActual, $totalHoras33 += $horas);  
                                                break;                                                                                        
                                            }
                                        break;
                                    case $COPD  :
                                        switch ($act) {
                                            // case $SF :
                                            //     $sheet->setCellValue('AT'.$filaActual, $totalHoras34 += $horas);  
                                            //     break;                            
                                            case $SI :
                                                $sheet->setCellValue('AT'.$filaActual, $totalHoras35 += $horas);  
                                                break;
                                            case $CR :
                                                $sheet->setCellValue('AU'.$filaActual, $totalHoras36 += $horas);  
                                                break;
                                            case $RT :
                                                $sheet->setCellValue('AV'.$filaActual, $totalHoras37 += $horas);  
                                                break;  
                                            case $PM :
                                                $sheet->setCellValue('AW'.$filaActual, $totalHoras38 += $horas);  
                                                break;  
                                            case $EC :
                                                $sheet->setCellValue('AX'.$filaActual, $totalHoras39 += $horas);  
                                                break;  
                                            case $DCORS :
                                                $sheet->setCellValue('AY'.$filaActual, $totalHoras40 += $horas);  
                                                break;  
                                            case $DGEAS :
                                                $sheet->setCellValue('AZ'.$filaActual, $totalHoras41 += $horas);  
                                                break; 
                                            case $ST :
                                                $sheet->setCellValue('BA'.$filaActual, $totalHoras42 += $horas);  
                                                break;  
                                            case $SE :
                                                $sheet->setCellValue('BB'.$filaActual, $totalHoras43 += $horas);  
                                                break;
                                            case $AS :
                                                $sheet->setCellValue('BC'.$filaActual, $totalHoras44 += $horas);  
                                                break;                                                                                           
                                            }
                                        break;
                                    case $PER  :
                                        switch ($act) {
                                            case $ICM :
                                                $sheet->setCellValue('CC'.$filaActual, $totalHoras45 += $horas);  
                                                break;                            
                                            case $PAICEG :
                                                $sheet->setCellValue('CD'.$filaActual, $totalHoras46 += $horas);  
                                                break;
                                            case $EDC :
                                                $sheet->setCellValue('CE'.$filaActual, $totalHoras47 += $horas);  
                                                break;
                                            case $VAC :
                                                $sheet->setCellValue('CF'.$filaActual, $totalHoras48 += $horas);  
                                                break;                                                                                        
                                            case $DPAlm :
                                                $sheet->setCellValue('CG'.$filaActual, $totalHoras49 += $horas);  
                                                break; 
                                            }
                                        break;
                                }            
                                $sheet->setCellValue('CH'.$filaActual, '=SUM(M'.$filaActual.':CG'.$filaActual.')');
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

        } //End foreach
        unset($respuesta);
        $conn->close();

    } catch(Exception $e) {
        // En caso de un error, tomar la exepcion
        $respuesta = array(
            'error' => $e->getMessage()
        );
    }

    //set the header first, so the result will be treated as an xlsx file.
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    //make it an attachment so we can define filename
    header('Content-Disposition: attachment;filename="Informe Actividades '.$nombreMes.' '.$usuario['area'].'.xlsx"');
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


// Genera reporte de todos los trabajadores de una área o región en específico
if($tipo === 'todos'){
    ini_set("max_execution_time", '300');
    try {
        $activo = 1; //Estado activo en la base de datos
        // Extrae la lista de personas de un área en específico
        $stmt = $conn->prepare("SELECT usuarios.cedula, usuarios.nombre1, usuarios.nombre2, usuarios.apellido1, usuarios.apellido2, clase_empleado.clase, area_region.area_region FROM usuarios INNER JOIN clase_empleado ON usuarios.clase = clase_empleado.id_clase INNER JOIN area_region ON usuarios.area = area_region.id_area WHERE usuarios.estado = ? ORDER BY area_region.area_region DESC ");
        $stmt->bind_param('i', $activo);
        $stmt->execute();
        $stmt->bind_result($cedula, $nombre1, $nombre2, $apellido1, $apellido2, $clase, $area);
        
        $datosUsuarios = array();
        
        // Almacena cada usuario en un array
        while($stmt->fetch()) {                
            array_push($datosUsuarios, array('cedula'=>$cedula, 'nombre1'=>$nombre1, 'nombre2'=>$nombre2, 'apellido1'=>$apellido1, 'apellido2'=>$apellido2, 'clase'=>$clase, 'area'=>$area));
        }

        $stmt->close();

        $filaActual = 3;
        //Recorre el array y por cada usuario consulta sus actividades, según el mes y año elegido y lo agrega al excel
        foreach ($datosUsuarios as $llave => $usuario) {    
            
            // Verifica si el usuario está en la lista de excluidos
            $excluido = false;            
            foreach($excepciones as $usuarioExcluido) {                
                if($usuario['cedula'] === (int)$usuarioExcluido){
                    $excluido = true;                                        
                }                
            }
           
            // Si el usuario no está excluido lo agrega al reporte
            if($excluido === false){
                // Recupera de la base de datos los registro del usuario del mes y año indicado.
                $stmt = $conn->prepare("SELECT reg_fecha, actividad FROM registros WHERE reg_cedula = ? AND mes = ? AND anio = ? ORDER BY reg_fecha ");
                $stmt->bind_param('iii', $usuario['cedula'], $mes, $anio);
                $stmt->execute();
                $stmt->bind_result($fecha, $Reg_actividad);

                $respuesta = array();
                
                while($stmt->fetch()) {                
                    array_push($respuesta, array('fecha'=>$fecha,'registro'=> $Reg_actividad));
                }         
        
                $stmt->close();
        

                $dia=1;
                //Repite la cantidad de días que tenga el mes seleccionado
                for($x = 0; $x<$cantidad_Dias; $x++){
                    $totalHoras1=$totalHoras2=$totalHoras3=$totalHoras4=$totalHoras5=$totalHoras6=$totalHoras7=$totalHoras8=$totalHoras9=$totalHoras10=$totalHoras11=$totalHoras12=$totalHoras13=$totalHoras14=$totalHoras15=$totalHoras16=$totalHoras17=$totalHoras18=$totalHoras19=$totalHoras20=$totalHoras21=$totalHoras22=$totalHoras23=$totalHoras24=$totalHoras25=$totalHoras26=$totalHoras27=$totalHoras28=$totalHoras29=$totalHoras30=$totalHoras31=$totalHoras32=$totalHoras33=$totalHoras34=$totalHoras35=$totalHoras36=$totalHoras37=$totalHoras38=$totalHoras39=$totalHoras40=$totalHoras41=$totalHoras42=$totalHoras43=$totalHoras44=$totalHoras45=$totalHoras46=$totalHoras47=$totalHoras48=$totalHoras49='';

                    $sheet->setCellValue('A'.$filaActual, $usuario['cedula']);
                    $sheet->setCellValue('B'.$filaActual, $usuario['apellido1']);
                    $sheet->setCellValue('C'.$filaActual, $usuario['apellido2']);
                    $sheet->setCellValue('D'.$filaActual, $usuario['nombre1']);
                    $sheet->setCellValue('E'.$filaActual, $usuario['nombre2']);
                    $sheet->setCellValue('F'.$filaActual, $usuario['clase']);
                    $sheet->setCellValue('G'.$filaActual, $usuario['area']);
                    
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

                    $sheet->getStyle('M'.$filaActual)->applyFromArray($fondoAmarillo);
                    $sheet->getStyle('N'.$filaActual.':Q'.$filaActual)->applyFromArray($fondoCeleste);
                    $sheet->getStyle('V'.$filaActual.':Y'.$filaActual)->applyFromArray($fondoCeleste);
                    $sheet->getStyle('AD'.$filaActual.':AG'.$filaActual)->applyFromArray($fondoCeleste);
                    $sheet->getStyle('AL'.$filaActual.':AO'.$filaActual)->applyFromArray($fondoCeleste);
                    $sheet->getStyle('AT'.$filaActual.':BD'.$filaActual)->applyFromArray($fondoCeleste);
                    $sheet->getStyle('BE'.$filaActual.':BI'.$filaActual)->applyFromArray($fondoVerde);
                    $sheet->getStyle('A'.$filaActual.':BK'.$filaActual)->getBorders()->getAllBorders()->applyFromArray( array( 'borderStyle' => Border::BORDER_THIN) );
            
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
                                    case $GA:
                                        $sheet->setCellValue('M'.$filaActual, $totalHoras1 += $horas);                        
                                        break;
                                    case $GEC:
                                        switch ($act) {
                                            case $MC:
                                                $sheet->setCellValue('N'.$filaActual, $totalHoras2 += $horas);  
                                                break;                            
                                            case $MP :
                                                $sheet->setCellValue('O'.$filaActual, $totalHoras3 += $horas);  
                                                break;
                                            case $EO :
                                                $sheet->setCellValue('P'.$filaActual, $totalHoras4 += $horas);  
                                                break;
                                            case $TE :
                                                $sheet->setCellValue('Q'.$filaActual, $totalHoras5 += $horas);  
                                                break;
                                            }
                                        break;
                                    case $GEDT:
                                        switch ($act) {
                                            case $MC:
                                                $sheet->setCellValue('R'.$filaActual, $totalHoras6 += $horas);  
                                                break;                            
                                            case $MP :
                                                $sheet->setCellValue('S'.$filaActual, $totalHoras7 += $horas);  
                                                break;
                                            case $EO :
                                                $sheet->setCellValue('T'.$filaActual, $totalHoras8 += $horas);  
                                                break;
                                            case $TE :
                                                $sheet->setCellValue('U'.$filaActual, $totalHoras9 += $horas);  
                                                break;                                                                   
                                            }
                                        break;
                                    case $GEDA :
                                        switch ($act) {
                                            case $MC:
                                                $sheet->setCellValue('V'.$filaActual, $totalHoras10 += $horas);  
                                                break;                            
                                            case $MP :
                                                $sheet->setCellValue('W'.$filaActual, $totalHoras11 += $horas);  
                                                break;
                                            case $EO :
                                                $sheet->setCellValue('X'.$filaActual, $totalHoras12 += $horas);  
                                                break;
                                            case $TE :
                                                $sheet->setCellValue('Y'.$filaActual, $totalHoras13 += $horas);  
                                                break;                                                               
                                            }
                                        break;
                                    case $GETR :
                                        switch ($act) {
                                            case $MC:
                                                $sheet->setCellValue('Z'.$filaActual, $totalHoras14 += $horas);  
                                                break;                            
                                            case $MP :
                                                $sheet->setCellValue('AA'.$filaActual, $totalHoras15 += $horas);  
                                                break;
                                            case $EO :
                                                $sheet->setCellValue('AB'.$filaActual, $totalHoras16 += $horas);  
                                                break;
                                            case $TE :
                                                $sheet->setCellValue('AC'.$filaActual, $totalHoras17 += $horas);  
                                                break;                                                                
                                            }
                                        break;
                                    case $GRP :
                                        switch ($act) {
                                            case $MC:
                                                $sheet->setCellValue('AD'.$filaActual, $totalHoras18 += $horas);  
                                                break;                            
                                            case $MP :
                                                $sheet->setCellValue('AE'.$filaActual, $totalHoras19 += $horas);  
                                                break;
                                            case $EO :
                                                $sheet->setCellValue('AF'.$filaActual, $totalHoras20 += $horas);  
                                                break;
                                            case $TE :
                                                $sheet->setCellValue('AG'.$filaActual, $totalHoras21 += $horas);  
                                                break;                                                              
                                            }
                                        break;
                                    case $GRM :
                                        switch ($act) {
                                            case $MC:
                                                $sheet->setCellValue('AH'.$filaActual, $totalHoras22 += $horas);  
                                                break;                            
                                            case $MP :
                                                $sheet->setCellValue('AI'.$filaActual, $totalHoras23 += $horas);  
                                                break;
                                            case $EO :
                                                $sheet->setCellValue('AJ'.$filaActual, $totalHoras24 += $horas);  
                                                break;
                                            case $TE :
                                                $sheet->setCellValue('AK'.$filaActual, $totalHoras25 += $horas);  
                                                break;                                                               
                                            }
                                        break;
                                    case $GSPCT :
                                        switch ($act) {
                                            case $MC:
                                                $sheet->setCellValue('AL'.$filaActual, $totalHoras26 += $horas);  
                                                break;                            
                                            case $MP :
                                                $sheet->setCellValue('AM'.$filaActual, $totalHoras27 += $horas);  
                                                break;
                                            case $EO :
                                                $sheet->setCellValue('AN'.$filaActual, $totalHoras28 += $horas);  
                                                break;
                                            case $TE :
                                                $sheet->setCellValue('AO'.$filaActual, $totalHoras29 += $horas);  
                                                break;                                                                        
                                            }
                                        break;
                                    case $GET :
                                        switch ($act) {
                                            case $MC:
                                                $sheet->setCellValue('AP'.$filaActual, $totalHoras30 += $horas);  
                                                break;                            
                                            case $MP :
                                                $sheet->setCellValue('AQ'.$filaActual, $totalHoras31 += $horas);  
                                                break;
                                            case $EO :
                                                $sheet->setCellValue('AR'.$filaActual, $totalHoras32 += $horas);  
                                                break;
                                            case $TE :
                                                $sheet->setCellValue('AS'.$filaActual, $totalHoras33 += $horas);  
                                                break;                                                                        
                                            }
                                        break;
                                    case $COPD  :
                                        switch ($act) {
                                            // case $SF :
                                            //     $sheet->setCellValue('AT'.$filaActual, $totalHoras34 += $horas);  
                                            //     break;                            
                                            case $SI :
                                                $sheet->setCellValue('AT'.$filaActual, $totalHoras35 += $horas);  
                                                break;
                                            case $CR :
                                                $sheet->setCellValue('AU'.$filaActual, $totalHoras36 += $horas);  
                                                break;
                                            case $RT :
                                                $sheet->setCellValue('AV'.$filaActual, $totalHoras37 += $horas);  
                                                break;  
                                            case $PM :
                                                $sheet->setCellValue('AW'.$filaActual, $totalHoras38 += $horas);  
                                                break;  
                                            case $EC :
                                                $sheet->setCellValue('AX'.$filaActual, $totalHoras39 += $horas);  
                                                break;  
                                            case $DCORS :
                                                $sheet->setCellValue('AY'.$filaActual, $totalHoras40 += $horas);  
                                                break;  
                                            case $DGEAS :
                                                $sheet->setCellValue('AZ'.$filaActual, $totalHoras41 += $horas);  
                                                break; 
                                            case $ST :
                                                $sheet->setCellValue('BA'.$filaActual, $totalHoras42 += $horas);  
                                                break;  
                                            case $SE :
                                                $sheet->setCellValue('BB'.$filaActual, $totalHoras43 += $horas);  
                                                break;
                                            case $AS :
                                                $sheet->setCellValue('BC'.$filaActual, $totalHoras44 += $horas);  
                                                break;                                                                                           
                                            }
                                        break;
                                    case $PER  :
                                        switch ($act) {
                                            case $ICM :
                                                $sheet->setCellValue('CC'.$filaActual, $totalHoras45 += $horas);  
                                                break;                            
                                            case $PAICEG :
                                                $sheet->setCellValue('CD'.$filaActual, $totalHoras46 += $horas);  
                                                break;
                                            case $EDC :
                                                $sheet->setCellValue('CE'.$filaActual, $totalHoras47 += $horas);  
                                                break;
                                            case $VAC :
                                                $sheet->setCellValue('CF'.$filaActual, $totalHoras48 += $horas);  
                                                break;                                                                                        
                                            case $DPAlm :
                                                $sheet->setCellValue('CG'.$filaActual, $totalHoras49 += $horas);  
                                                break; 
                                            }
                                        break;
                                }            
                                $sheet->setCellValue('CH'.$filaActual, '=SUM(M'.$filaActual.':CG'.$filaActual.')');
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
            }
        } //End foreach
        unset($respuesta);
        $conn->close();
        
    } catch(Exception $e) {
        // En caso de un error, tomar la exepcion
        $respuesta = array(
            'error' => $e->getMessage()
        );
    }

    //set the header first, so the result will be treated as an xlsx file.
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    //make it an attachment so we can define filename
    header('Content-Disposition: attachment;filename="Informe Actividades '.$nombreMes.' '.Todos.'.xlsx"');
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

// Genera reporte de los totales del mes de todos los trabajadores
if($tipo === 'totalesTodos'){
    ini_set("max_execution_time", '300');
    try {
        $activo = 1; //Estado activo en la base de datos
        // Extrae la lista de personas de un área en específico
        $stmt = $conn->prepare("SELECT usuarios.cedula, usuarios.nombre1, usuarios.nombre2, usuarios.apellido1, usuarios.apellido2, clase_empleado.clase, area_region.area_region FROM usuarios INNER JOIN clase_empleado ON usuarios.clase = clase_empleado.id_clase INNER JOIN area_region ON usuarios.area = area_region.id_area WHERE usuarios.estado = ? ORDER BY area_region.area_region DESC ");
        $stmt->bind_param('i', $activo);
        $stmt->execute();
        $stmt->bind_result($cedula, $nombre1, $nombre2, $apellido1, $apellido2, $clase, $area);
        
        $datosUsuarios = array();
        
        // Almacena cada usuario en un array
        while($stmt->fetch()) {                
            array_push($datosUsuarios, array('cedula'=>$cedula, 'nombre1'=>$nombre1, 'nombre2'=>$nombre2, 'apellido1'=>$apellido1, 'apellido2'=>$apellido2, 'clase'=>$clase, 'area'=>$area));
        }

        $stmt->close();
      
        $filaActual = 3;
        //Recorre el array y por cada usuario consulta sus actividades, según el mes y año elegido y lo agrega al excel
        foreach ($datosUsuarios as $llave => $usuario) {        
    
            // Recupera de la base de datos los registro del usuario del mes y año indicado.
            $stmt = $conn->prepare("SELECT reg_fecha, actividad FROM registros WHERE reg_cedula = ? AND mes = ? AND anio = ? ORDER BY reg_fecha ");
            $stmt->bind_param('iii', $usuario['cedula'], $mes, $anio);
            $stmt->execute();
            $stmt->bind_result($fecha, $Reg_actividad);

            $respuesta = array();
            
            while($stmt->fetch()) {                
                array_push($respuesta, array('fecha'=>$fecha,'registro'=> $Reg_actividad));
            }         
    
            $stmt->close();
     
            $sheet->setCellValue('A'.$filaActual, $usuario['cedula']);
            $sheet->setCellValue('B'.$filaActual, $usuario['apellido1']);
            $sheet->setCellValue('C'.$filaActual, $usuario['apellido2']);
            $sheet->setCellValue('D'.$filaActual, $usuario['nombre1']);
            $sheet->setCellValue('E'.$filaActual, $usuario['nombre2']);
            $sheet->setCellValue('F'.$filaActual, $usuario['clase']);
            $sheet->setCellValue('G'.$filaActual, $usuario['area']);
            $sheet->setCellValue('H'.$filaActual, $nombreMes);
            $sheet->setCellValue('I'.$filaActual, $anio);   

            $sheet->getStyle('J'.$filaActual)->applyFromArray($fondoAmarillo);
            $sheet->getStyle('K'.$filaActual.':N'.$filaActual)->applyFromArray($fondoCeleste);
            $sheet->getStyle('S'.$filaActual.':V'.$filaActual)->applyFromArray($fondoCeleste);
            $sheet->getStyle('AA'.$filaActual.':AD'.$filaActual)->applyFromArray($fondoCeleste);
            $sheet->getStyle('AI'.$filaActual.':AL'.$filaActual)->applyFromArray($fondoCeleste);
            $sheet->getStyle('AQ'.$filaActual.':BA'.$filaActual)->applyFromArray($fondoCeleste);
            $sheet->getStyle('BB'.$filaActual.':BF'.$filaActual)->applyFromArray($fondoVerde);
            $sheet->getStyle('A'.$filaActual.':BG'.$filaActual)->getBorders()->getAllBorders()->applyFromArray( array( 'borderStyle' => Border::BORDER_THIN) );

            
            $totalHoras1=$totalHoras2=$totalHoras3=$totalHoras4=$totalHoras5=$totalHoras6=$totalHoras7=$totalHoras8=$totalHoras9=$totalHoras10=$totalHoras11=$totalHoras12=$totalHoras13=$totalHoras14=$totalHoras15=$totalHoras16=$totalHoras17=$totalHoras18=$totalHoras19=$totalHoras20=$totalHoras21=$totalHoras22=$totalHoras23=$totalHoras24=$totalHoras25=$totalHoras26=$totalHoras27=$totalHoras28=$totalHoras29=$totalHoras30=$totalHoras31=$totalHoras32=$totalHoras33=$totalHoras34=$totalHoras35=$totalHoras36=$totalHoras37=$totalHoras38=$totalHoras39=$totalHoras40=$totalHoras41=$totalHoras42=$totalHoras43=$totalHoras44=$totalHoras45=$totalHoras46=$totalHoras47=$totalHoras48=$totalHoras49='';


            // Si hay valores los carga en la tabla
            foreach($respuesta as $registro){
                    
                $NumRegistros = sizeof(json_decode($registro['registro']));    //cantidad de registros en el array
                $regAct = json_decode($registro['registro']);

                //llenado de los datos en la tabla según condiciones
                for($i=0; $i < $NumRegistros; $i++){               
                    $cat = $regAct[$i]->categoria;
                    $act = $regAct[$i]->actividad;
                    $horas = floatval($regAct[$i]->horas);

                    switch ($cat) {
                        case $GA:
                            $totalHoras1 += $horas;                        
                            break;
                        case $GEC:
                            switch ($act) {
                                case $MC:
                                    $totalHoras2 += $horas;  
                                    break;                            
                                case $MP :
                                    $totalHoras3 += $horas;  
                                    break;
                                case $EO :
                                    $totalHoras4 += $horas;  
                                    break;
                                case $TE :
                                    $totalHoras5 += $horas;  
                                    break;
                                }
                            break;
                        case $GEDT:
                            switch ($act) {
                                case $MC:
                                    $totalHoras6 += $horas;  
                                    break;                            
                                case $MP :
                                    $totalHoras7 += $horas;  
                                    break;
                                case $EO :
                                    $totalHoras8 += $horas;  
                                    break;
                                case $TE :
                                    $totalHoras9 += $horas;  
                                    break;                                                                   
                                }
                            break;
                        case $GEDA :
                            switch ($act) {
                                case $MC:
                                    $totalHoras10 += $horas;  
                                    break;                            
                                case $MP :
                                    $totalHoras11 += $horas;  
                                    break;
                                case $EO :
                                    $totalHoras12 += $horas;  
                                    break;
                                case $TE :
                                    $totalHoras13 += $horas;  
                                    break;                                                               
                                }
                            break;
                        case $GETR :
                            switch ($act) {
                                case $MC:
                                    $totalHoras14 += $horas;  
                                    break;                            
                                case $MP :
                                    $totalHoras15 += $horas;  
                                    break;
                                case $EO :
                                    $totalHoras16 += $horas;  
                                    break;
                                case $TE :
                                    $totalHoras17 += $horas;  
                                    break;                                                                
                                }
                            break;
                        case $GRP :
                            switch ($act) {
                                case $MC:
                                    $totalHoras18 += $horas;  
                                    break;                            
                                case $MP :
                                    $totalHoras19 += $horas;  
                                    break;
                                case $EO :
                                    $totalHoras20 += $horas;  
                                    break;
                                case $TE :
                                    $totalHoras21 += $horas;  
                                    break;                                                              
                                }
                            break;
                        case $GRM :
                            switch ($act) {
                                case $MC:
                                    $totalHoras22 += $horas;  
                                    break;                            
                                case $MP :
                                    $totalHoras23 += $horas;  
                                    break;
                                case $EO :
                                    $totalHoras24 += $horas;  
                                    break;
                                case $TE :
                                    $totalHoras25 += $horas;  
                                    break;                                                               
                                }
                            break;
                        case $GSPCT :
                            switch ($act) {
                                case $MC:
                                    $totalHoras26 += $horas;  
                                    break;                            
                                case $MP :
                                    $totalHoras27 += $horas;  
                                    break;
                                case $EO :
                                    $totalHoras28 += $horas;  
                                    break;
                                case $TE :
                                    $totalHoras29 += $horas;  
                                    break;                                                                        
                                }
                            break;
                        case $GET :
                            switch ($act) {
                                case $MC:
                                    $totalHoras30 += $horas;  
                                    break;                            
                                case $MP :
                                    $totalHoras31 += $horas;  
                                    break;
                                case $EO :
                                    $totalHoras32 += $horas;  
                                    break;
                                case $TE :
                                    $totalHoras33 += $horas;  
                                    break;                                                                        
                                }
                            break;
                        case $COPD  :
                            switch ($act) {
                                // case $SF :
                                //     $totalHoras34 += $horas;  
                                //     break;                            
                                case $SI :
                                    $totalHoras35 += $horas;  
                                    break;
                                case $CR :
                                    $totalHoras36 += $horas;  
                                    break;
                                case $RT :
                                    $totalHoras37 += $horas;  
                                    break;  
                                case $PM :
                                    $totalHoras38 += $horas;  
                                    break;  
                                case $EC :
                                    $totalHoras39 += $horas;  
                                    break;  
                                case $DCORS :
                                    $totalHoras40 += $horas;  
                                    break;  
                                case $DGEAS :
                                    $totalHoras41 += $horas;  
                                    break; 
                                case $ST :
                                    $totalHoras42 += $horas;  
                                    break;  
                                case $SE :
                                    $totalHoras43 += $horas;  
                                    break;
                                case $AS :
                                    $totalHoras44 += $horas;  
                                    break;                                                                        
                                }
                            break;
                        case $PER  :
                            switch ($act) {
                                case $ICM :
                                    $totalHoras45 += $horas;  
                                    break;                            
                                case $PAICEG :
                                    $totalHoras46 += $horas;  
                                    break;
                                case $EDC :
                                    $totalHoras47 += $horas;  
                                    break;
                                case $VAC :
                                    $totalHoras48 += $horas;  
                                    break;                                                                                        
                                case $DPAlm :
                                    $totalHoras49 += $horas;  
                                    break; 
                                }
                            break;
                    }    
                }        

            } // End ForEach

            $sheet->setCellValue('J'.$filaActual, $totalHoras1);
            $sheet->setCellValue('K'.$filaActual, $totalHoras2);
            $sheet->setCellValue('L'.$filaActual, $totalHoras3);
            $sheet->setCellValue('M'.$filaActual, $totalHoras4);
            $sheet->setCellValue('N'.$filaActual, $totalHoras5);
            $sheet->setCellValue('O'.$filaActual, $totalHoras6);
            $sheet->setCellValue('P'.$filaActual, $totalHoras7);
            $sheet->setCellValue('Q'.$filaActual, $totalHoras8);
            $sheet->setCellValue('R'.$filaActual, $totalHoras9);
            $sheet->setCellValue('S'.$filaActual, $totalHoras10);
            $sheet->setCellValue('T'.$filaActual, $totalHoras11);
            $sheet->setCellValue('U'.$filaActual, $totalHoras12);
            $sheet->setCellValue('V'.$filaActual, $totalHoras13);
            $sheet->setCellValue('W'.$filaActual, $totalHoras14);
            $sheet->setCellValue('X'.$filaActual, $totalHoras15);
            $sheet->setCellValue('Y'.$filaActual, $totalHoras16);
            $sheet->setCellValue('Z'.$filaActual, $totalHoras17);
            $sheet->setCellValue('AA'.$filaActual, $totalHoras18);
            $sheet->setCellValue('AB'.$filaActual, $totalHoras19);
            $sheet->setCellValue('AC'.$filaActual, $totalHoras20);
            $sheet->setCellValue('AD'.$filaActual, $totalHoras21);
            $sheet->setCellValue('AE'.$filaActual, $totalHoras22);
            $sheet->setCellValue('AF'.$filaActual, $totalHoras23);
            $sheet->setCellValue('AG'.$filaActual, $totalHoras24);
            $sheet->setCellValue('AH'.$filaActual, $totalHoras25);
            $sheet->setCellValue('AI'.$filaActual, $totalHoras26);
            $sheet->setCellValue('AJ'.$filaActual, $totalHoras27);
            $sheet->setCellValue('AK'.$filaActual, $totalHoras28);
            $sheet->setCellValue('AL'.$filaActual, $totalHoras29);
            $sheet->setCellValue('AM'.$filaActual, $totalHoras30);
            $sheet->setCellValue('AN'.$filaActual, $totalHoras31);
            $sheet->setCellValue('AO'.$filaActual, $totalHoras32);
            $sheet->setCellValue('AP'.$filaActual, $totalHoras33);
            $sheet->setCellValue('AQ'.$filaActual, $totalHoras34);
            $sheet->setCellValue('AR'.$filaActual, $totalHoras35);
            $sheet->setCellValue('AS'.$filaActual, $totalHoras36);
            $sheet->setCellValue('AT'.$filaActual, $totalHoras37);
            $sheet->setCellValue('AU'.$filaActual, $totalHoras38);
            $sheet->setCellValue('AV'.$filaActual, $totalHoras39);
            $sheet->setCellValue('AW'.$filaActual, $totalHoras40);
            $sheet->setCellValue('AX'.$filaActual, $totalHoras41);
            $sheet->setCellValue('AY'.$filaActual, $totalHoras42);
            $sheet->setCellValue('AZ'.$filaActual, $totalHoras43);
            $sheet->setCellValue('BA'.$filaActual, $totalHoras44);
            $sheet->setCellValue('BB'.$filaActual, $totalHoras45);
            $sheet->setCellValue('BC'.$filaActual, $totalHoras46);
            $sheet->setCellValue('BD'.$filaActual, $totalHoras47);
            $sheet->setCellValue('BE'.$filaActual, $totalHoras48);
            $sheet->setCellValue('BF'.$filaActual, $totalHoras49);
            

            $sheet->setCellValue('BG'.$filaActual, '=SUM(J'.$filaActual.':BF'.$filaActual.')');

            $cat = '';
            $act = '';
            $horas = '';
            $filaActual++;

        } //End foreach
        unset($respuesta);
        $conn->close();

    } catch(Exception $e) {
        // En caso de un error, tomar la exepcion
        $respuesta = array(
            'error' => $e->getMessage()
        );
    }

    //set the header first, so the result will be treated as an xlsx file.
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    //make it an attachment so we can define filename
    header('Content-Disposition: attachment;filename="Informe Actividades '.$nombreMes.' '.Totales.'.xlsx"');
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
