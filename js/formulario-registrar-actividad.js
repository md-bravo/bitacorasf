
eventListeners();

function eventListeners() {
    document.querySelector('#formulario-registro-act').addEventListener('submit', verificacionVacaciones);   
}

const actAlmuerzo = 22; //id de la actividad de almuerzo
const actVacaciones = 24; // id de la actividad de vacaciones

// Verifica si la actividad a registrar es de vacaciones y determina las acciones
function verificacionVacaciones(e){
    e.preventDefault();

    let actividad = Number(document.querySelector('#actividad').value);
    let catAlmuerzoEncontrada = false;
    let catVacacionesEncontrada = false;
    let diaVacaciones = false;
    if(document.getElementById('dia-completo')){
        let diaCompleto = document.getElementById('dia-completo').checked;
    }
    if(document.getElementById('feriado')){
        let feriado = document.getElementById('feriado').checked;
    }
    let regTabla = document.querySelectorAll('#registro');
    let horas = document.querySelector('#horas').value

    
    if (horas === '') {
        // la validación falló
        swal({
            type: 'error',
            title: 'Error!',
            text: 'Debe indicar la cantidad de horas!'
          })
    }else if(horas > 24){
        swal({
            type: 'error',
            title: 'Error!',
            text: 'No puede superar las 24 horas!'
          })
    } else if(horas < 0){
        swal({
            type: 'error',
            title: 'Error!',
            text: 'Número de horas incorrecto!'
          })
    }
    else {

        if(actividad === actAlmuerzo && regTabla.length > 0){
            var hayAlmuerzo = false;
            var hayVacaciones = false;

            for(let x=0; x<regTabla.length; x++){

                if(regTabla[x].children[2].innerText == actAlmuerzo){
                    hayAlmuerzo = true;                    
                    swal({                                                                                     
                        title: 'Error',            
                        text: 'La actividad almuerzo ya se encuentra registrada',
                        type: 'error'
                    });
                }
                
                if(regTabla[x].children[2].innerText == actVacaciones){
                    hayVacaciones = true;                    
                    swal({                                                                                     
                        title: 'Error',            
                        text: 'No se puede registrar almuerzo si tiene registrado vacaciones o feriado',
                        type: 'error'
                    });
                }         
            }            
            if(hayAlmuerzo == false && hayVacaciones == false){                
                validarRegistro();
            }   
        }

        // Si la actividad es vacaciones hace la verificación
        else if(actividad === actVacaciones) {

            // Si es el día completo, verifica que no hayan registros en ese día
            if((diaCompleto == true || feriado == true) && regTabla.length > 0){
                for(let x=0; x<regTabla.length; x++){
                    // Verifica si la actividad vacaciones completo está agregada
                    if(regTabla[x].children[4].innerText == "Día de Vacaciones" || regTabla[x].children[4].innerText == "Feriado"){
                        swal({                                                                                     
                            title: 'Error',            
                            text: 'La actividad vacaciones o feriado ya se encuentra registrada',
                            type: 'error'
                        });
                    } else {
                        swal({                                                                                     
                            title: 'Error',            
                            text: 'Debe de eliminar todos los registros para poder registrar ésta actividad',
                            type: 'error'
                        });
                    }
                }
            // Si se desea registrar el día completo de vacaciones y no hay registros en la tabla
            } else if((diaCompleto == true || feriado == true) && regTabla.length == 0) {
                    validarRegistro();
            } else{            

                // Recorre los registros para saber si está la actividad de almuerzo
                for(let x=0; x<regTabla.length; x++){
                    // Verifica si la actividad almuerzo está agregada
                    if(Number(regTabla[x].children[2].innerText) === actAlmuerzo){
                        catAlmuerzoEncontrada = true;
                        swal({                                                                                     
                            title: 'Error',            
                            text: 'Debe de eliminar el registro de almuerzo para poder registrar vacaciones',
                            type: 'error'
                        });
                    }
                    // Verifica si la actividad vacaciones está agregada      
                    if(Number(regTabla[x].children[2].innerText) === actVacaciones){
                        catVacacionesEncontrada = true;
                        swal({                                                                                     
                            title: 'Error',            
                            text: 'Ya hay un registro de vacaciones o feriado',
                            type: 'error'
                        });
                    }  
                }
                // Si la actividad de almuerzo no está registrada, procede con el registro
                if(catAlmuerzoEncontrada == false && catVacacionesEncontrada == false){
                    validarRegistro();
                }    
            }
        // Si es una actividad diferente a vacaciones
        } else{
            if(regTabla.length > 0){
                for(let x=0; x<regTabla.length; x++){
                    // Verifica si la actividad vacaciones completo está agregada
                    if(regTabla[x].children[4].innerText === "Día de Vacaciones" || regTabla[x].children[4].innerText === "Feriado"){   
                        diaVacaciones = true;             
                        swal({                                                                                     
                            title: 'Error',            
                            text: 'No se puede registrar actividades si tiene todo el día de vacaciones o es feriado',
                            type: 'error'
                        });
                    }
                }
                if(diaVacaciones == false){
                    validarRegistro();
                }
            }else {
                validarRegistro();
            }
        }
    }
}


// Realizar la solicitud de registro de la actividad
function validarRegistro() {
    
    var cedula = document.querySelector('#cedula').value,
        fecha = document.querySelector('#fecha').value,
        categoria = document.querySelector('#categoria').value,
        actividad = document.querySelector('#actividad').value,
        horas = document.querySelector('#horas').value,
        detalle = document.querySelector('#detalle').value;
        accion = document.querySelector('#accion').value;

        // Todos los campos son correctos, mandar ejecutar Ajax        
             
        // datos que se envian al servidor
        var datos = new FormData();
        datos.append('cedula', cedula);
        datos.append('fecha', fecha);
        datos.append('categoria', categoria);
        datos.append('actividad', actividad);
        datos.append('horas', horas);
        datos.append('detalle', detalle);
        datos.append('accion', accion);

        activarSpinner();

        // crear el llamado a Ajax
        var xhr = new XMLHttpRequest();

        // abrir la conexión
        xhr.open('POST', 'inc/modelos/modelo-registro.php', true);

        // retorno de datos
        xhr.onload = function(){

            if(this.status === 200) {
                var respuesta = JSON.parse(xhr.responseText);

                desactivarSpinner();

                // Si la respuesta es correcta
                if(respuesta.respuesta === 'correcto') {
                    swal({                                                                     
                        showConfirmButton: false,                        
                        timer:1500,
                        title: 'Actividad Guardada',                        
                        type: 'success'
                    });

                    document.getElementById("horas").value ="";
                    document.getElementById("detalle").value="";                                        
                    fechaSeleccionada();

                    // Agregar actividad de almuerzo si es la primera actividad
                    setTimeout(() => {
                        let regTabla = document.querySelectorAll('#registro');

                        if(regTabla.length == 1 && actividad != actVacaciones && actividad != actAlmuerzo){
                            registrarAlmuerzo();
                        }
                    }, 2000);

                }else {
                    // Hubo un error
                    if(respuesta.error) {
                        swal({
                            title: 'Error',
                            text: 'Algo falló al registrar la actividad',
                            type: 'error'
                        });    
                    }
                }
            }
        }

        // enviar la petición
        xhr.send(datos);
}

// Realizar la solicitud de registro de la actividad específica de almuerzo
function registrarAlmuerzo(){

    var cedula = document.querySelector('#cedula').value,
        fecha = document.querySelector('#fecha').value,
        accion = document.querySelector('#accion').value;

    let catPermisos =  5;
    let tiempoAlmuerzo = 0.75;

    // datos que se envian al servidor
    var datos = new FormData();
    datos.append('cedula', cedula);
    datos.append('fecha', fecha);
    datos.append('categoria', catPermisos);
    datos.append('actividad', actAlmuerzo);
    datos.append('horas', tiempoAlmuerzo);
    datos.append('detalle', "Almuerzo");
    datos.append('accion', accion);

    activarSpinner();

    // crear el llamado a Ajax
    var xhr = new XMLHttpRequest();

    // abrir la conexión
    xhr.open('POST', 'inc/modelos/modelo-registro.php', true);

    // retorno de datos
    xhr.onload = function(){

        if(this.status === 200) {
            var respuesta = JSON.parse(xhr.responseText);

            desactivarSpinner();

            // Si la respuesta es correcta
            if(respuesta.respuesta === 'correcto') {               
                fechaSeleccionada();
            }else {
                // Hubo un error
                if(respuesta.error) {
                    swal({
                        title: 'Error',
                        text: 'Algo falló al registrar la actividad',
                        type: 'error'
                    });    
                }
            }
        }
    }

    // enviar la petición
    xhr.send(datos);
}