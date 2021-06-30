

eventListener();

// Variables globales
const almuerzo = 22,    // id actividad almuerzo
    incapacidad = 23,   // id actividad incapacidad
    vacaciones = 24,    // id actividad vacaciones
    horasJornada = 9.6, //Horas disponibles basado en 9.6 horas
    diaVacaciones = 9.6,    
    diaFeriado = 9.6,
    diaIncapacidad = 9.6,
    medioDiaVacaciones = 4.8;


function eventListener() {

    // Document Ready
    document.addEventListener('DOMContentLoaded', function(){

        // Inicializar la variable en el session storage para medir el tiempo de inactividad
        var timeStamp = new Date();
        sessionStorage.setItem("lastTimeStamp",timeStamp);
        
        actualizarActividades();
        establecerFecha();
        fechaSeleccionada();
        tamanioVentana();
    });

    // Cuando se cambia de fecha
    document.querySelector('input[type="date"]').addEventListener('change', fechaSeleccionada);

    // Nuevo metodo
    document.querySelector('#categoria').addEventListener('change', actualizarActividades);

    // Cuando se selecciona la actividad de Descanso Profiláctico (Almuerzo) ó Vacaciones
    document.querySelector('#actividad').addEventListener('change', actividadPredefinida);
 
    // Contar caracteres ingresados en detalle
    document.querySelector('#detalle').addEventListener('keyup', contarDetalle);

    // Establece fecha actual en modal reporte excel
    document.getElementById('btnGenerarExcelBarra').addEventListener('click', establecerFechaDefault);

    // Generar archivo de Excel, según el mes y año elegido
    document.getElementById('btnGenerarExcelModalNuevo').addEventListener('click', generarExcel);

    document.getElementById('btnVerReporteModal').addEventListener('click', generarReporte);

    document.getElementById('btnCalendario').addEventListener('click', calendario);

    document.getElementById('btnPDF').addEventListener('click', generarPDF);
    
}


// Función para medir el tiempo de inactividad y cerrar sesión.
$(function()
{

    // Cada vez que se mueve el mouse se almacena el tiempo actual en el session storage
    if(typeof(Storage) !== "undefined") 
    {
        $(document).mousemove(function()
        {
            var timeStamp = new Date();
            sessionStorage.setItem("lastTimeStamp",timeStamp);
        });

        timeChecker();
    }  

    // Cada 3 segundos lee el último tiempo almacenado en session storage
    function timeChecker()
    {
        setInterval(function()
        {
            var storedTimeStamp = sessionStorage.getItem("lastTimeStamp");  
            timeCompare(storedTimeStamp);
        },3000);
    }

    /** Calcula el tiempo transcurrido desde la última vez que se movió el mouse
       se convierte ese resultado a minutos. Si la cantidad optenida es mayor o
       igual a la cantidad de minutos máxima establecida, se elimina la sessión. */
    function timeCompare(timeString)
    {
        var maxMinutes  = 5;  //GREATER THEN 1 MIN.
        var currentTime = new Date();
        var pastTime    = new Date(timeString);
        var timeDiff    = currentTime - pastTime;
        var minPast     = Math.floor( (timeDiff/60000) ); 

        if( minPast >= maxMinutes)
        {
            sessionStorage.removeItem("lastTimeStamp");
            window.location = "./inc/funciones/session_killer.php";
            return false;
        } 
        // }else
        // {
        //     //JUST ADDED AS A VISUAL CONFIRMATION
        //     console.log(currentTime +" - "+ pastTime+" - "+minPast+" min past");
        // }
    }

});//END JQUERY



// Detectar cambios en el tamaño de la ventana
window.onresize = function() {
    this.tamanioVentana();
}

// Calcula el alto que debe tener el div de la tabla
function tamanioVentana() {
    const windowSize = window.innerHeight-210;
    document.querySelector('.datosTabla').style.maxHeight = `${windowSize}px`;
}

function generarPDF(e){
    e.preventDefault();

    let spanTitulo = document.querySelector('#tituloReporte span').innerText;

    document.querySelector('.modal-footer').hidden = true;
    var DocumentContainer = document.querySelector('#pdf');
    var html = '<html><head>'+'<title>Reporte Actividades '+spanTitulo+'</title>'+
                   '<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css" />'+
                   '</head><body style="background:#ffffff;">'+
                   DocumentContainer.innerHTML+
                   '</body></html>';
   
        var WindowObject = window.open("", "Imprimir", "");
        WindowObject.document.writeln(html);
        WindowObject.document.close();
        document.querySelector('.modal-footer').hidden = false;
        WindowObject.focus();
    setTimeout(() => {
        WindowObject.print();    
        WindowObject.close();
    }, 25); 
      
    
}
    
function calendario(e){
    e.preventDefault();    

    if(document.querySelector('#modalCalendario #calendar').children.length > 0){        
        var calendario = document.querySelector('#modalCalendario #calendar');
        while (calendario.firstChild) {
            calendario.removeChild(calendario.firstChild);
        }
    }

    $('#modalCalendario').modal('show');
    

    let cedula = document.querySelector('#cedula').value;
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        locale:'es',
        plugins: [ 'interaction', 'dayGrid', 'timeGrid', 'bootstrap' ],
        themeSystem: 'bootstrap',
        selectable: true,
        events : `inc/modelos/lista_tareas_calendario.php?cedula=${cedula}`,
        header: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek'
        },
        dateClick: function(info) {
        //alert('clicked ' + info.dateStr);        
            document.querySelector('input[type="date"]').value = info.dateStr;
            fechaSeleccionada();
            $('#modalCalendario').modal('hide');
        }
    });

    calendar.render();

    setTimeout(() => {
        document.querySelector('.fc-dayGridMonth-button').click();
    }, 200);

}

// Nuevo metodo para establecer actividades
function actualizarActividades(){
    let id_cat = document.getElementById('categoria').value;

    let actRelacionadas = document.getElementById('relacionCatAct');
    let actividades = document.getElementById('actividades');

    let dropdownActividades = document.getElementById('actividad');
    dropdownActividades.options.length=0;

    for(let x=0; x<actRelacionadas.length; x++){
        if(actRelacionadas[x].value === id_cat){
            for(i=0; i<actividades.length; i++){
                if(actividades[i].value === actRelacionadas[x].innerHTML){                          
                                                
                    var nuevaActividad = document.createElement('option');
                    nuevaActividad.value = actividades[i].value;
                    nuevaActividad.innerHTML = `${actividades[i].value} - ${actividades[i].innerHTML}`;  
                    dropdownActividades.appendChild(nuevaActividad);
                    
                }
            }
        }        
    }
    act_campos();
    eliminar_radio();
    actividadPredefinida();
}


// Establecer mes y año actual al modal de reporte excel
function establecerFechaDefault(e){
    e.preventDefault();

    let fecha = new Date();
    let mesActual = fecha.getMonth()+1;
    let anioActual = fecha.getFullYear();

    let meses = document.getElementById('mes').children;

    for(let x=0; x<meses.length; x++){
        if(meses[x].value == mesActual){            
            meses[x].selected = 'selected';
        }
    }

    let anios = document.getElementById('anio').children;

    for(let x=0; x<anios.length; x++){
        if(anios[x].value == anioActual){  
            anios[x].selected = 'selected';                      
        }
    }

}

// Contar caracteres ingresados en detalle
function contarDetalle(){
    var cuenta = document.querySelector('#detalle').value.length;
        actual = document.getElementById('actual'),
     
        actual.innerText = cuenta;
}

// Generar archivo de Excel, según el mes elegido
function generarExcel(e){
    e.preventDefault();

        document.forms['reporteMesDia'].action='inc/modelos/modelo-exportar.php';    
        document.forms['reporteMesDia'].method='post';        
        document.forms['reporteMesDia'].submit();

        $('#modalMesAnio').modal('hide');

        activarSpinner();

        setTimeout(() => {
            swal({                                                                     
                showConfirmButton: false,                        
                timer:2000,
                title: 'Archivo Generado',                
                text: 'El archivo se descargará en seguida...',
                type: 'success'
            });
            desactivarSpinner();
        }, 3000);

return true;
}


function generarReporte(e){
    e.preventDefault();

    let fecha = document.getElementById('anio').value+"-"+document.getElementById('mes').value+"-"+"1";
    let cedula = document.querySelector('#cedula').value;

    // Si la tabla tiene contenido, se limpia para su uso
    if(document.querySelector('#tablaModalReporte #tablaModalContenido').children.length > 0 ){
        var tabla = document.querySelector('#tablaModalReporte #tablaModalContenido');
        while (tabla.firstChild) {
        tabla.removeChild(tabla.firstChild);
        }
    }

    if(document.getElementById('divMensajeReporte')){
        document.getElementById('divMensajeReporte').remove();
    }

    //Crear llamada AJAX
    var xhr = new XMLHttpRequest();

    // Información
    // Si hay registros en la tabla
        var datos = new FormData();
        datos.append('fecha', fecha);
        datos.append('cedula', cedula);

    activarSpinner();

    // Abrir la conexión
    xhr.open('POST', 'inc/modelos/modelo-reporte.php', true);

    // On load
    xhr.onload = function(){
        if(this.status === 200) {
            let respuesta = JSON.parse(xhr.responseText);           

            desactivarSpinner();            

            // Si la respuesta es correcta
            if(respuesta.respuesta === 'correcto') {
               
               $('#modalReporte').modal('toggle');
               
               // Muestra en el título del reporte el mes y año
               let mes = document.getElementById('mes');
               document.querySelector('#tituloReporte span').innerHTML = mes.options[mes.selectedIndex].text + ' ' + document.getElementById('anio').value;


               let tablaDatosUsuario = document.querySelector('#tablaModalReporteInfo #tableDatosUsario');

               tablaDatosUsuario.children[0].innerHTML = respuesta.usuario;
               tablaDatosUsuario.children[1].innerHTML = respuesta.nombre;
               tablaDatosUsuario.children[2].innerHTML = respuesta.clase;
               tablaDatosUsuario.children[3].innerHTML = respuesta.area;

               let tablaReporteContendio = document.querySelector('#tablaModalReporte #tablaModalContenido');
               let registros = Object.values(respuesta).length-5;
               let listaCategorias = document.getElementById('categoria');
               let listaActividades = document.getElementById('actividades');
               let nombreCategoria = "";
               let nombreActividad = "";

               if(registros === 0){
                   let divMensaje = document.createElement('div');
                   divMensaje.id = "divMensajeReporte";
                   divMensaje.innerHTML = `<h4 class="text-center">No hay registros en éste mes</h4>`
                   document.getElementById('modal-body-reporte').appendChild(divMensaje);
               }
               // Se recorre la cantidad de registros que tiene el mes 
                for(let x=0; x<registros; x++){
                    let arrayRegistro = JSON.parse(Object.values(respuesta)[x].registro);
                    let totalRegistros = arrayRegistro.length;                 

                    let sumaHoras = 0;
                    // Se recorre la cantidad de registros que tiene el día 
                    for(let i=0; i<totalRegistros; i++){

                        for(let j=0; j<listaCategorias.length; j++){
                            if(arrayRegistro[i].categoria == listaCategorias.children[j].value){
                                nombreCategoria = listaCategorias.children[j].innerHTML.slice(4);
                            }
                        }

                        for(let k=0; k<listaActividades.length; k++){
                            if(arrayRegistro[i].actividad == listaActividades.children[k].value){
                                nombreActividad = listaActividades.children[k].innerHTML;
                            }
                        }

                        // Se suma las horas de las actividades de un día
                        sumaHoras += Number(arrayRegistro[i].horas);

                        // Se establece la fecha personalizada de día de la semana y día del mes
                        let dias = {0:"Domingo", 1:"Lunes", 2:"Martes", 3:"Miércoles", 4:"Jueves", 5:"Viernes", 6:"Sábado"};
                        
                        // la función Date maneja el mes de 0 a 11
                        // Entonces se separa la fecha, se le resta 1 al mes y se vuelve a conformar la fecha.
                        let fechaPartes =Object.values(respuesta)[x].fecha.split('-');
                        let fecha = new Date(fechaPartes[0], fechaPartes[1] - 1, fechaPartes[2]);                     

                        for(let i in dias){
                            if(fecha.getDay() == i){
                                var fechaPersonalizada = dias[i] + ' ' + fecha.getDate();                                
                            }                    
                        }
                        

                        let nuevaFila = document.createElement('tr');
                        if(i===0){
                            nuevaFila.innerHTML = `<th scope="row" class="thFechaReporte" rowspan="${totalRegistros}">${fechaPersonalizada}</th>                        
                            <td>${nombreCategoria}</td>
                            <td>${nombreActividad}</td>
                            <td>${arrayRegistro[i].horas}</td>
                            <td>${arrayRegistro[i].detalle}</td>`;   
                        }else{
                            nuevaFila.innerHTML = `                        
                            <td>${nombreCategoria}</td>
                            <td>${nombreActividad}</td>
                            <td>${arrayRegistro[i].horas}</td>
                            <td>${arrayRegistro[i].detalle}</td>`;   
                        }                                                
                
                        tablaReporteContendio.appendChild(nuevaFila);
                    }      
                    
                    let filaSuma = document.createElement('tr');
                    filaSuma.classList.add('fondo-suma-repo');
                    filaSuma.innerHTML = `<th scope="row"></th>                        
                        <td></td>
                        <td>Total Horas:</td>
                        <td class="font-weight-bold">${sumaHoras.toFixed(2)}</td>
                        <td></td>`;                                            
                    tablaReporteContendio.appendChild(filaSuma);
                }                

            }else {
                // Hubo un error
                if(respuesta.error) {
                    swal({
                        title: 'Error',
                        text: 'Algo falló al generar el reporte',
                        type: 'error'
                    });    
                }
            }
            
        }
    }

    // Enviar la petición
    xhr.send(datos);
    
}


// Establecer la fecha actual en el campo fecha
function establecerFecha(){
    var dateControl = document.querySelector('input[type="date"]');

    fecha =  new Date();
    //Año
    anio = fecha.getFullYear();
    //Mes
    mes = fecha.getMonth() + 1;
    //Día
    dia = fecha.getDate();
    
    if(dia<10)
    dia='0'+dia; //agrega cero si el menor de 10
    if(mes<10)
    mes='0'+mes //agrega cero si el menor de 10

    dateControl.value = anio + "-" + mes + "-" + dia;
}


// Si la actividad seleccionada es vacaciones o almuerzo, se establecen valores predefinidos
function actividadPredefinida(){
    let idAct = Number(document.getElementById('actividad').value);      

    if(idAct === almuerzo){
        document.getElementById('horas').value = "0.75";
        document.getElementById('detalle').value = "Almuerzo";
        des_campos();
        eliminar_radio();
        
    } else if(idAct === vacaciones) {

        eliminar_radio();

        let formRegistro = document.getElementById('formulario-registro-act');
        
        let radioBtn = document.createElement('div');
        radioBtn.classList.add("input-group", "mt-3", "mb-3");
        radioBtn.id = "div-radio-vacaciones";
        radioBtn.innerHTML = `                            
                            <div class="input-group-prepend">                                
                                <span class="input-group-text">Opciones:</span>
                            </div>
                            <div class="form-control border border-success radioVacaciones">
                            <div class="form-check form-check-inline pl-3">
                            <input class="form-check-input" type="radio" name="inlineRadioOptions" id="medio-dia" value="medio-dia" onchange="establecerVacaciones()">
                            <label class="form-check-label" for="medio-dia">1/2 día</label>
                            </div>
                            <div class="form-check form-check-inline pl-3">
                            <input class="form-check-input" type="radio" name="inlineRadioOptions" id="dia-completo" value="dia-completo" onchange="establecerVacaciones()">
                            <label class="form-check-label" for="dia-completo">Completo</label>
                            </div>
                            <div class="form-check form-check-inline pl-3">
                            <input class="form-check-input" type="radio" name="inlineRadioOptions" id="feriado" value="feriado" onchange="establecerVacaciones()">
                            <label class="form-check-label" for="feriado">Feriado</label>
                            </div>
                            </div>                        
        `;
        formRegistro.insertBefore(radioBtn,formRegistro.childNodes[11]);
        document.getElementById('horas').value = "";
        document.getElementById('detalle').value = "";
        des_campos();

    } else if(idAct === incapacidad) {

        eliminar_radio();

        let formRegistro = document.getElementById('formulario-registro-act');
        
        let radioBtn = document.createElement('div');
        radioBtn.classList.add("input-group", "mt-3", "mb-3");
        radioBtn.id = "div-radio-incapacidad";
        radioBtn.innerHTML = `                            
                            <div class="input-group-prepend">                                
                                <span class="input-group-text">Opciones:</span>
                            </div>
                            <div class="form-control border border-success radioIncapacidad">
                                <div class="form-check form-check-inline pl-3">
                                    <input class="form-check-input" type="radio" name="inlineRadioOptions" id="incapacidad" value="incapacidad" onchange="establecerIncapacidad()">
                                    <label class="form-check-label" for="incapacidad">Incapacidad</label>
                                </div>
                                <div class="form-check form-check-inline pl-3">
                                    <input class="form-check-input" type="radio" name="inlineRadioOptions" id="cita-med" value="cita-med" onchange="establecerIncapacidad()">
                                    <label class="form-check-label" for="cita-med">Cita Médica</label>
                                </div>                            
                            </div>                        
        `;
        formRegistro.insertBefore(radioBtn,formRegistro.childNodes[11]);
        document.getElementById('horas').value = "";
        document.getElementById('detalle').value = "";
        des_campos();
    
    } else {
        act_campos();
        eliminar_radio();
    }
}

// Establece las horas y detalle según la elección en el radio button
function establecerVacaciones(){
    let diaCompleto = document.getElementById('dia-completo').checked;
    let feriado = document.getElementById('feriado').checked;

    if(diaCompleto === true){
        document.getElementById('horas').value = diaVacaciones;
        document.getElementById('detalle').value = "Día de Vacaciones";
    } else if(feriado === true){
        document.getElementById('horas').value = diaFeriado;
        document.getElementById('detalle').value = "Feriado";
    } else {        
        document.getElementById('horas').value = medioDiaVacaciones;
        document.getElementById('detalle').value = "Medio día vacaciones";
    }
}

// Establece las horas y detalle según la elección en el radio button
function establecerIncapacidad() {
    let incapacidad = document.getElementById('incapacidad').checked;

    if(incapacidad === true){
        des_campos();
        document.getElementById('horas').value = diaIncapacidad;
        document.getElementById('detalle').value = "Incapacidad";
    } else {
        act_campos();        
    }
}

// Activa y vacia los campos de hora y detalle
function act_campos(){
    let campos = document.querySelectorAll('#horas, #detalle');
    campos[0].value = "";
    campos[1].value = "";
    document.getElementById('actual').innerHTML = 0;
    campos[0].disabled = false;
    campos[1].disabled = false;
}

// Desactiva los campos de hora y detalle
function des_campos(){
    let campos = document.querySelectorAll('#horas, #detalle');
    campos[0].disabled = true;
    campos[1].disabled = true;
}

// Eliminar div de radio button
function eliminar_radio(){
    let divRadio = document.getElementById('div-radio-vacaciones');
    let divRadioInc = document.getElementById('div-radio-incapacidad');
    if(divRadio){
        divRadio.remove();
    }
    if(divRadioInc){
        divRadioInc.remove();
    }
}

// Restricts input for the given textbox to the given inputFilter.
function setInputFilter(textbox, inputFilter) {
    ["input", "keydown", "keyup", "mousedown", "mouseup", "select", "contextmenu", "drop"].forEach(function(event) {
      textbox.addEventListener(event, function() {
        if (inputFilter(this.value)) {
          this.oldValue = this.value;
          this.oldSelectionStart = this.selectionStart;
          this.oldSelectionEnd = this.selectionEnd;
        } else if (this.hasOwnProperty("oldValue")) {
          this.value = this.oldValue;
          this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
        }
      });
    });
  }

// Restringir la entrada de solo números con dos decimales
setInputFilter(document.getElementById("horas"), function(value) {
    return /^\d*[.]?\d{0,2}$/.test(value);
  });


// Establecer la fecha seleccionada en la infomación de tareas
function fechaSeleccionada(){
    
    const spanFechaSeleccionada = document.getElementById('fecha-seleccionada');
    const fechaSeleccionada = document.querySelector('input[type="date"]').value;

    let anio = fechaSeleccionada.slice(0,4);
    let mes = fechaSeleccionada.slice(5,7);
    let dia = fechaSeleccionada.slice(8,10);

    spanFechaSeleccionada.innerText = dia + "-" + mes + "-" + anio;


    // Estable la primera categoria como default cuando se cambia de fecha
    let cat_Seleccionada = document.getElementById('categoria').selectedIndex
    if (cat_Seleccionada != 0){
        document.getElementById('categoria').selectedIndex = "0";
        actualizarActividades();
    }
    
    act_campos();
    
    let cedula = document.querySelector('#cedula').value,
        fecha = document.querySelector('#fecha').value;
    
    // Crear llamada AJAX
    var xhr = new XMLHttpRequest();

    // Información
    var datos = new FormData();
    datos.append('cedula', cedula);
    datos.append('fecha', fecha);
    datos.append('accion', 'consultar');

    activarSpinner();

    // Abrir la conexión
    xhr.open('POST', 'inc/modelos/modelo-registro.php', true);

    // On load
    xhr.onload = function(){
        if(this.status === 200) {
            respuesta = JSON.parse(xhr.responseText);

            desactivarSpinner();

            let tabla = document.getElementById('tabla-act');
            let div_tabla = document.getElementById('lista-act');
            let div_mensaje = document.getElementById('mensaje');            

            //Remueve el mensaje cuando hay registros
            if (div_mensaje != null) {
                div_tabla.removeChild(div_mensaje);
            }            

            //Remueve todos los registros de la tabla
            while (tabla.firstChild) {
              tabla.removeChild(tabla.firstChild);
            }

            if(document.getElementById('mostrarTotalHoras')){
                document.getElementById('mostrarTotalHoras').remove();
            }

            if (respuesta.respuesta === 'correcto') {

                let respuesta_act = JSON.parse(respuesta.reg_actividad);
                let idRegistro = respuesta.id_registro;
                
                let linea = 1,      //Contador para las lineas de la tabla
                    sumaHoras = 0;  //Sumar las horas de las actividades registradas                    

                // Recorre el array de actividades y por cada registro crea una fila nueva en la tabla y le asigna los valores
                for (x=0; x<respuesta_act.length; x++){                   
                    let nuevaFila = document.createElement('tr');
                    nuevaFila.id = "registro";
                    nuevaFila.innerHTML = `<th scope="row">${linea}</th><td>${respuesta_act[x].categoria}</td><td>${respuesta_act[x].actividad}</td><td>${respuesta_act[x].horas}</td><td>${respuesta_act[x].detalle}</td><td id="accion"><a href="#" id="btn-editar" class="btn-editar pr-3"><i id="btn-editar-${x}" class="fa-lg text-primary fas fa-edit"></i></a><a href="#" class="btn-borrar" id="btn-borrar"><i id="btn-borrar-${x}" class="text-danger fa-lg fas fa-trash-alt"></i></a></td>`;                    
                    tabla.appendChild(nuevaFila);
                    sumaHoras += parseFloat(respuesta_act[x].horas); //Sumar horas
                    linea++;

                    // Agrega el eventListener a cada botón de edición
                    let btnEditar = document.querySelectorAll('#btn-editar i');
                    btnEditar[x].addEventListener('click', editarRegistro);

                    // Agrega el eventListener a cada botón de borrar
                    let btnBorrar = document.querySelectorAll('#btn-borrar i');
                    btnBorrar[x].addEventListener('click', borrarRegistro);
                }
                //Crea una fila extra para mostrar el total de horas
                const divHorasTotal = document.createElement('div');
                divHorasTotal.id = "mostrarTotalHoras";
                divHorasTotal.classList.add("pb-2", "pt-3");
                divHorasTotal.innerHTML = `<h5 class="pl-2">Total Horas:<span id="suma" class="pl-2">${sumaHoras.toFixed(2)}</span></h5><input type="hidden" id="idRegistro" value=${idRegistro}>`;
                div_tabla.appendChild(divHorasTotal);


                // let filaHorasTotal = document.createElement('tr');
                // filaHorasTotal.innerHTML = `<td class="text-right pt-3" colspan="3"><h5>Total Horas: </h5></td><td class="pt-3" colspan="2"><h5 id="suma">${sumaHoras.toFixed(2)}</h5></td><td><input type="hidden" id="idRegistro" value=${idRegistro}></td>`;                
                // tabla.appendChild(filaHorasTotal);            

                let horasDisponibles = horasJornada-sumaHoras;
                horasDisponibles = horasDisponibles.toFixed(2);

                //document.getElementById('horas-disponibles').innerHTML = `Restantes: ${horasJornada}`;
                document.querySelector('#horas-disponibles span').innerHTML = `${horasDisponibles}`;                

            } else if(respuesta.respuesta === 'no-registro') { //Si no hay registros en el día seleccionado                
                noHayRegistros();
            }
        }
    }

    // Enviar la petición
    xhr.send(datos);
}


// Crear Mensaje no hay registros
function noHayRegistros(){
    let div_tabla = document.getElementById('lista-act');

    let mensaje = document.createElement('div');
    mensaje.id = "mensaje";
    mensaje.align = "center";
    mensaje.className= "pb-2";
    mensaje.innerHTML = `<h3>No hay registros en éste día</h3>`;
    div_tabla.appendChild(mensaje);
    document.querySelector('#horas-disponibles span').innerHTML = `${horasJornada}`;

}

function actualizarTabla(){

    let regTabla = document.querySelectorAll('#registro');

    if(regTabla.length === 0){
        noHayRegistros();
        //document.getElementById('suma').parentNode.parentNode.remove();
        document.getElementById('suma').parentNode.remove();
    }
    
    // Lee la tabla de registro
    let i=1;
    let suma = 0;
    for(let x=0; x<regTabla.length; x++){
        regTabla[x].children[0].innerText = i;
        suma += Number(regTabla[x].children[3].innerText);
        i++;
    }      

    if(document.getElementById('suma')){
        document.getElementById('suma').innerHTML = suma.toFixed(2);
    }
    
    horasDisponibles = horasJornada - suma;
    
    horasDisponibles = horasDisponibles.toFixed(2);

    document.querySelector('#horas-disponibles span').innerHTML = `${horasDisponibles}`;              

}

// Borra un registro del día seleccionado
function borrarRegistro(e){
    e.preventDefault();

    let elemento = e.target.parentNode.parentNode.parentNode; // Elemento al que se le hizo click

    let idAct = (Number(elemento.children[2].innerHTML));

    let regTablaAntes = document.querySelectorAll('#registro');

  
    elemento.remove();   

    let idRegistro = document.getElementById('idRegistro').value;
    let regTabla = document.querySelectorAll('#registro');

    // Lee la tabla de registro y crea un array con los existentes
    let arrayRegistros = Array();
    for(let x=0; x<regTabla.length; x++){
        let idCat = regTabla[x].children[1].innerText;
        let idAct = regTabla[x].children[2].innerText;
        let horas = regTabla[x].children[3].innerText;
        let detalle = regTabla[x].children[4].innerText;        
        
        let registro = { 'categoria': idCat , 'actividad' : idAct, 'horas': horas, 'detalle': detalle};

        arrayRegistros.push(registro);
    }      

    // Convierte el array en una cadena JSON
    let jsonRegistros = JSON.stringify(arrayRegistros);
    

    //Crear llamada AJAX
    var xhr = new XMLHttpRequest();

    // Información
    // Si hay registros en la tabla
    if(arrayRegistros.length > 0){
        var datos = new FormData();
        datos.append('idRegistro', idRegistro);
        datos.append('RegActividad', jsonRegistros);
        datos.append('accion', 'eliminar');
    } else {    // Si la tabla está vacia
        var datos = new FormData();
        datos.append('idRegistro', idRegistro);
        datos.append('RegActividad', '');
        datos.append('accion', 'eliminar');
    }

    activarSpinner();

    // Abrir la conexión
    xhr.open('POST', 'inc/modelos/modelo-registro.php', true);

    // On load
    xhr.onload = function(){
        if(this.status === 200) {
            let respuesta = JSON.parse(xhr.responseText);

            desactivarSpinner();

            // Si la respuesta es correcta
            if(respuesta.respuesta === 'correcto') {
                swal({                                                                     
                    showConfirmButton: false,                        
                    timer:1500,
                    title: 'Actividad Eliminada',                    
                    type: 'success'
                });
                //fechaSeleccionada(); // Recarga la lista de actividades del día
                actualizarTabla();

                // Si la actividad que se eliminó fue 1/2 día de vacaciones y aún quedan registros, se vuelve a registrar el amuerzo.
                setTimeout(() => { 
                    if(idAct == vacaciones && regTabla.length >= 1){
                        registrarAlmuerzo();
                    }    
                }, 1000);

            }else {
                // Hubo un error
                if(respuesta.error) {
                    swal({
                        title: 'Error',
                        text: 'Algo falló al eliminar la actividad',
                        type: 'error'
                    });    
                }
            }
            
        }
    }

    // Enviar la petición
    xhr.send(datos);
}

// Editar horas y detalle de un registro
function editarRegistro(e){
    e.preventDefault();

    let elemento = e.target.parentNode.parentNode.parentNode;

    let idCat = elemento.children[1].innerText;
    let idAct = elemento.children[2].innerText;
    let horas = elemento.children[3].innerText;
    let detalle = elemento.children[4].innerText;

    if(idAct == vacaciones){
        swal({
            title: 'Error',
            text: 'No se puede editar vacaciones',
            type: 'error'
        });
    } else if(idAct == almuerzo) {
        swal({
            title: 'Error',
            text: 'No se puede editar almuerzo',
            type: 'error'
        });
    } else {
        
        let btnAccion = document.querySelectorAll('#accion a');

        for(let x=0; x<btnAccion.length; x++){        
            btnAccion[x].classList.add("disabled");
        }
    

        document.getElementById('fecha').disabled = true;

        let categorias = document.getElementById('categoria').children;

        // Se establece la categoría según el registro a editar
        for(let x=0; x<categorias.length; x++){
            if(categorias[x].value == idCat){
                document.getElementById('categoria').selectedIndex = categoria[x].index;
                document.getElementById('categoria').disabled = true;
            }
        }
        
        actualizarActividades();

        // Se establece la actividad según el registro a actualizar
            let actividades = document.getElementById('actividad').children;

            for(let x=0; x<actividades.length; x++){
                if(actividades[x].value == idAct){
                    document.getElementById('actividad').selectedIndex = actividades[x].index;
                    document.getElementById('actividad').disabled = true;
                }
            }   

        document.getElementById('horas').value = horas;
        document.getElementById('detalle').value = detalle; 
        elemento.className = "fondo-editar"; //Se agrega un fondo al registro a editar

        // Se oculta el boton de guardar del formulario
        let btnGuardar = document.getElementById('btnGuardar');
        btnGuardar.hidden = true;
        btnGuardar.disabled = true;

        // Se crea el boton de editar
        let btnEditar = document.createElement('input');
        btnEditar.classList.add("btn-lg","btn-primary");
        btnEditar.type = "button";
        btnEditar.id = "btnEditar";
        btnEditar.value = "Guardar";
        btnEditar.addEventListener('click', guardarEdicion);
        btnGuardar.parentNode.appendChild(btnEditar);

        // Se crea el botón de cancelar
        let btnCancelar = document.createElement('input');
        btnCancelar.classList.add("ml-1","mt-0","btn-lg","btn-secondary");
        btnCancelar.type = "button";
        btnCancelar.id = "btnCancelar";
        btnCancelar.value = "Cancelar";
        btnCancelar.addEventListener('click', cancelarEdicion);
        btnGuardar.parentNode.appendChild(btnCancelar);
    }
}

// Manda a guardar los datos editados
function guardarEdicion(){

    let horaEditada = document.getElementsByClassName('fondo-editar')[0].children[3];
    let detalleEditado = document.getElementsByClassName('fondo-editar')[0].children[4];

    let nuevaHora = document.getElementById('horas').value;
    let nuevoDetalle = document.getElementById('detalle').value;

    horaEditada.innerText = nuevaHora;
    detalleEditado.innerHTML = nuevoDetalle;

    let idRegistro = document.getElementById('idRegistro').value;
    let regTabla = document.querySelectorAll('#registro');

    // Lee la tabla de registro y crea un array con los existentes
    let arrayRegistros = Array();
    for(let x=0; x<regTabla.length; x++){
        let idCat = regTabla[x].children[1].innerText;
        let idAct = regTabla[x].children[2].innerText;
        let horas = regTabla[x].children[3].innerText;
        let detalle = regTabla[x].children[4].innerText;        
        
        let registro = { 'categoria': idCat , 'actividad' : idAct, 'horas': horas, 'detalle': detalle};

        arrayRegistros.push(registro);
    }      

    // Convierte el array en una cadena JSON
    let jsonRegistros = JSON.stringify(arrayRegistros);

    //Crear llamada AJAX
    var xhr = new XMLHttpRequest();

    // Información
    // Si hay registros en la tabla
        var datos = new FormData();
        datos.append('idRegistro', idRegistro);
        datos.append('RegActividad', jsonRegistros);
        datos.append('accion', 'actualizar-registro');

    activarSpinner();
    
    // Abrir la conexión
    xhr.open('POST', 'inc/modelos/modelo-registro.php', true);

    // On load
    xhr.onload = function(){
        if(this.status === 200) {
            let respuesta = JSON.parse(xhr.responseText);

            desactivarSpinner();

            // Si la respuesta es correcta
            if(respuesta.respuesta === 'correcto') {
                swal({                                                                     
                    showConfirmButton: false,                        
                    timer:1500,
                    title: 'Actividad Actualizada',                    
                    type: 'success'
                });
                actualizarTabla();
                cancelarEdicion();
            }else {
                // Hubo un error
                if(respuesta.error) {
                    swal({
                        title: 'Error',
                        text: 'Algo falló al actualizar la actividad',
                        type: 'error'
                    });    
                }
            }
            
        }
    }

    // Enviar la petición
    xhr.send(datos);
}

// Cancela la edición de un registro y restablece campos
function cancelarEdicion(){

    document.getElementById('fecha').disabled = false;
    document.getElementById('categoria').disabled = false;
    document.getElementById('actividad').disabled = false;
    act_campos();
    document.getElementById('btnGuardar').hidden = false;
    document.getElementById('btnGuardar').disabled = false;
    document.getElementById('btnEditar').remove();
    document.getElementById('btnCancelar').remove();
    document.getElementsByClassName('fondo-editar')[0].classList.remove("fondo-editar");

    let btnAccion = document.querySelectorAll('#accion a');

    for(let x=0; x<btnAccion.length; x++){        
        btnAccion[x].classList.remove("disabled");
    }

    // Estable la primera categoria como default cuando se cambia de fecha
    let cat_Seleccionada = document.getElementById('categoria').selectedIndex
    if (cat_Seleccionada != 0){
        document.getElementById('categoria').selectedIndex = "0";
        actualizarActividades();
    }
}


// Función para bloquer la app mientras espera una respuesta y mostrar spinner
function activarSpinner(){
    document.getElementById('spinner').style.display = 'block';    
}

// Función para ocultar el spinner
function desactivarSpinner(){
    document.getElementById('spinner').style.display = 'none';
}
