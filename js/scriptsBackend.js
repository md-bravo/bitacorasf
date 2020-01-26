
eventListener();

function eventListener(){
    // Document Ready
    document.addEventListener('DOMContentLoaded', function(){
        establecerFechaDefault();
        document.querySelectorAll('.onoffswitch-checkbox').forEach(checkbox => {
            checkbox.addEventListener('click', cambiarEstado);
        });
    });
}

// Cambiar el estado de un usuario utilizando fetch AJAX
function cambiarEstado(e){

    const usuario = e.target.parentNode.parentNode.parentNode.children[0].innerHTML;
    const estadoActual = e.target.checked;
    const accion = 'cambiarEstado';
    let estado;

    // Se establece el estado activo como 1, inactivo como 0
    if(estadoActual === true){
        estado = 1;
    } else if(estadoActual === false){
        estado = 0
    }

    // Se definen los datos que se van a enviar al fetch
    const data = new FormData();
    data.append('usuario', usuario);
    data.append('estado', estado);
    data.append('accion', accion);

    // Conexión del fetch al archivo php
    fetch('inc/modelos/modelo-backend.php', {
    method: 'POST',
    body: data
    })
    .then(respuestaExitosa) // Respuesta exitosa llama la función
    .catch(mostrarError); // Respuesta negativa llama la función

    // Si la ejecución del AJAX es correcta se verifica la respuesta
    function respuestaExitosa(response){
        if(response.ok) {   // Si la respuesta en ok se llama la función para mostrar los resultados
            response.json().then(mostrarResultado);
        } else {    // Si la respuesta no es ok se muestra el error
            mostrarError('status code: ' + response.status);
        }
    }

    // Se muestran los resultados devueltos en el JSON
    function mostrarResultado(respuesta){
        // Si la respuesta es correcta
        if(respuesta.respuesta === 'correcto') {      
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000
            })
            
            Toast.fire({
            type: 'success',
            title: 'Estado Actualizado'
            })         
            
        } else  if(respuesta.respuesta === 'error') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000
            })
            
            Toast.fire({
            type: 'error',
            title: 'Algo falló al actualizar el estado'
            })    
        }else {
            // Hubo un error
            if(respuesta.error) {
                swal({
                    title: 'Error',
                    text: 'Algo falló al actualizar el estado',
                    type: 'error'
                });    
            }
        }
    }

    // Muestra el error si el AJAX no se ejecuta o la respuesta no es ok
    function mostrarError(err){
        console.log('Error', err);
    }
 
}

// Acciones sobre el Select de Excepciones
$(document).ready(function() {

    $('#dropDownExcepto').multiselect({
        columns: 1,
        search: true,
        maxWidth: 300,
        maxPlaceholderOpts: 1,
        selectAll: true,
        texts    : {
            placeholder: 'Excluir los siguientes usuarios...',
            search     : 'Buscar usuario',
            selectedOptions : ' Seleccionado'
        }
    });
});


// Establecer mes y año actual al modal de reporte excel
function establecerFechaDefault(){

    let fecha = new Date();
    let mesActual = fecha.getMonth()+1;
    let anioActual = fecha.getFullYear();

    let ddMeses = document.querySelectorAll('#mes');

    for(let i=0; i<ddMeses.length; i++){
        let mes = document.querySelectorAll('#mes')[i].children;
        for(let x=0; x<mes.length; x++){
            if(mes[x].value == mesActual){            
                mes[x].selected = 'selected';
            }
        }
    }

    let ddAnios = document.querySelectorAll('#anio');

    for(let i=0; i<ddAnios.length; i++){
        let anios = document.querySelectorAll('#anio')[i].children;
        for(let x=0; x<anios.length; x++){
            if(anios[x].value == anioActual){  
                anios[x].selected = 'selected';                      
            }
        }
    }

}



