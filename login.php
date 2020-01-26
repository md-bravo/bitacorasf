<?php
    session_start();
    include 'inc/funciones/funciones.php';
    include 'inc/templates/header.php';
    
    if(isset($_GET['cerrar_session'])) {
        $_SESSION = array();
    }
       
?>

    <div class="container container-login h-100">
        <div class="row h-100 align-items-center justify-content-center">
            <div class="col-md-7">
            <div class="contenido p-5 bg-light">
                <h2 class="titulo-login text-center bg-secondary text-light py-2 text-uppercase">Bitácora<span> Sistemas Fijos</span></h2>                       

                <form id="formulario-login" method="post">
                        <div class="form-group">                                  
                            <input type="text" class="form-control text-center mt-5 form-control-lg" name="usuario" id="usuario" placeholder="Ingrese su Cédula" maxlength="9" autofocus>
                        </div> 
                        
                        <div class="form-group mb-0">
                        <div class="d-flex mt-5">
                        <input type="hidden" id="tipo" value="login">
                        <div class="flex-grow-1 bd-highlight text-center">
                            <input type="submit" name="sub" id="sub" class="btn btn-primary" value="Iniciar Sesión">
                        </div>
                        <div class="bd-highlight align-self-center">
                            <a href="loginBackend.php"><i class="fas fa-user-cog fa-lg"></i></a>
                        </div>
                        </div>
                        </div>                            
            
                    </form>

            </div><!--.contenido-->
            </div><!--.col-md-7-->

            <!-- Spinner -->
            <div id="spinner" class="fondo-atenuado">                
                <div class="row mr-0 justify-content-center align-items-center minh-100">
                    <div class="d-flex justify-content-center">
                        <div class="spinner-border text-white spinner-size" role="status">
                            <span class="sr-only">Loading...</span>                                
                        </div>
                    </div>     
                </div>                                   
            </div>
        </div><!--.row-->
    </div><!--.container-->

<?php
    include 'inc/templates/footer.php';
?>