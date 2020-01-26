
eventListeners();

function eventListeners() {
    document.querySelector('#formulario-crear-cuenta').addEventListener('submit', validarRegistro);

    // Validar cedula ingresada en la creación de cuenta
    document.querySelector('#usuario').addEventListener('blur', validarCedula);
}

function validarCedula(){
    
    let cedula = document.getElementById('usuario').value;

    let tamanio = cedula.length;

    cedula = Number(cedula);

    // || tamanio < 9
    if(Number.isInteger(cedula) === false){
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
          });
          
          Toast.fire({
            type: 'error',
            title: 'La cédula ingresada no cumple con el formato'
          })
        document.getElementById('usuario').focus();
    } 
}


function validarRegistro(e) {
    e.preventDefault();

    var usuario = document.querySelector('#usuario').value,
        nombre1 = document.querySelector('#nombre1').value,
        nombre2 = document.querySelector('#nombre2').value,
        apellido1 = document.querySelector('#apellido1').value,
        apellido2 = document.querySelector('#apellido2').value,
        password = document.querySelector('#password').value,
        tipo = document.querySelector('#tipo').value;
        clase = document.querySelector('#clase').value;
        area = document.querySelector('#area').value;


    if (usuario === '' || password === '' || nombre1 === '' || apellido1 === '' || apellido2 === '') {
        // la validación falló
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
          });
          
          Toast.fire({
            type: 'error',
            title: 'Todos los campos son obligatorios!'
          })

    }else {
        // Todos los campos son correctos, mandar ejecutar Ajax

        // datos que se envian al servidor
        var datos = new FormData();
        datos.append('usuario', usuario);
        datos.append('nombre1', nombre1);
        datos.append('nombre2', nombre2);
        datos.append('apellido1', apellido1);
        datos.append('apellido2', apellido2);
        datos.append('password', password);
        datos.append('accion', tipo);
        datos.append('clase', clase);
        datos.append('area', area);

        activarSpinner();

        // crear el llamado a Ajax
        var xhr = new XMLHttpRequest();

        // abrir la conexión
        xhr.open('POST', 'inc/modelos/modelos-admin.php', true);

        // retorno de datos
        xhr.onload = function(){
            if(this.status === 200) {
                var respuesta = JSON.parse(xhr.responseText);

                desactivarSpinner();

                // Si la respuesta es correcta
                if(respuesta.respuesta === 'correcto') {
                    swal({
                        showConfirmButton: false,
                        timer:2000,
                        title: 'Usuario Creado',
                        text: 'El usuario se creó correctamente',
                        type: 'success'
                    });
                    setTimeout(() => {
                        window.location.href = "login.php";    
                    }, 2000);
                }else if(respuesta.respuesta === 'existe'){
                    swal({
                        title: 'Error',
                        text: 'La cédula ingresada, ya se encuentra registrada',
                        type: 'error'
                    });                        
                }else {
                    // Hubo un error
                    if(respuesta.error) {
                        swal({
                            title: 'Error',
                            text: 'Algo falló al intentar crear el usuario',
                            type: 'error'
                        });    
                    }
                }
            }
        }

        // enviar la petición
        xhr.send(datos);

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