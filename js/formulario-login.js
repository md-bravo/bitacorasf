eventListeners();

function eventListeners() {
    if(document.querySelector('#formulario-login')){
        document.querySelector('#formulario-login').addEventListener('submit', validarRegistro);
    }
    
    if(document.querySelector('#formulario-login-backend')){
        document.querySelector('#formulario-login-backend').addEventListener('submit', validarLoginBackEnd);
    }    
}

function validarRegistro(e) {
    e.preventDefault();

    var usuario = Number(document.querySelector('#usuario').value),       
        tipo = document.querySelector('#tipo').value;

    if (usuario === 0) {
        // la validación falló
        swal({
            type: 'error',
            title: 'Error!',
            text: 'Debe ingresar un valor!'
          })
    } else if(usuario === 999999999){
        swal({
            type: 'error',
            title: 'Error!',
            text: 'Usuario no existe!'
          })
    }else {
        // Ambos campos son correctos, mandar ejecutar Ajax

        // datos que se envian al servidor
        var datos = new FormData();
        datos.append('usuario', usuario);        
        datos.append('accion', tipo);

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

                    Swal.fire({
                        title: 'Es usted...',
                        text: respuesta.nombre+" ?",
                        type: 'warning',
                        showCancelButton: true,
                        cancelButtonText: 'No',
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Si'                        
                      }).then((result) => {                          
                        if (result.value) {
                            window.location.href = 'index.php';           
                        }
                        if (result.dismiss == "cancel") {
                            document.getElementById('usuario').value = "";
                        }
                      })

                } else if(respuesta.inactivo){                
                    swal({
                        title: 'Error',
                        text: 'Usuario se encuentra Inactivo',
                        type: 'error'
                    });  
                }
                else {
                    // Hubo un error
                    if(respuesta.error) {
                        swal({
                            title: 'Error',
                            text: 'Usuario no existe',
                            type: 'error'
                        });    
                    }else if(respuesta.resultado) {
                        swal({
                            title: 'Error',
                            text: 'Password Incorrecto',
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

function validarLoginBackEnd(e){
    e.preventDefault();

    var usuario = document.querySelector('#usuario').value,
        password = document.querySelector('#password').value,
        tipo = document.querySelector('#tipo').value;

    if (usuario === '' || password === '') {
        // la validación falló
        swal({
            type: 'error',
            title: 'Error!',
            text: 'Ambos campos son obligatorios!'
          })
    }else {
        // Ambos campos son correctos, mandar ejecutar Ajax

        // datos que se envian al servidor
        var datos = new FormData();
        datos.append('usuario', usuario);
        datos.append('password', password);
        datos.append('accion', tipo);

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
                        timer:1500,
                        title: 'Login Correcto',                        
                        type: 'success'
                    });
                    setTimeout(() => {           
                        window.location.href = 'backend.php';                
                    }, 1500);
                          
                }else {
                    // Hubo un error
                    if(respuesta.error) {
                        swal({
                            title: 'Error',
                            text: 'Usuario no existe',
                            type: 'error'
                        });    
                    }else if(respuesta.resultado) {
                        swal({
                            title: 'Error',
                            text: 'Password Incorrecto',
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