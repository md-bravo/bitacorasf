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
                    <div class="contenido p-5 bg-light rounded">
                        <h2 class="text-center bg-secondary text-light py-2 text-uppercase">
                          Bitácora - <span>Backend</span>
                        </h2>
      
                        <form id="formulario-login-backend" method="post">
                              <div class="form-group">
                                  <label for="usuario">Usuario</label>
                                  <input type="text" class="form-control" name="usuario" id="usuario" placeholder="Usuario" autofocus>
                              </div>
                              <div class="form-group">
                                  <label for="password">Password</label>
                                  <input type="password" class="form-control" name="password" id="password" placeholder="Password">
                              </div>
                              <div class="form-group text-center mb-0">
                                  <input type="hidden" id="tipo" value="login-backend">
                                  <input type="submit" class="mt-4 btn btn-primary" value="Iniciar Sesión">
                                  <a href="index.php" class="mt-4 btn btn-secondary">Volver</a>
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