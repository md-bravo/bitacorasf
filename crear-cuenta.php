<?php
    include 'inc/funciones/funciones.php';
    include 'inc/templates/header.php';
?>

<body class="crear-cuenta">

            <div class="container">
              <div class="row mt-3 mb-3 align-items-center justify-content-center">
                  <div class="col-md-7 p-0">
                    <div class="contenido px-5 pt-4 pb-2 bg-light rounded">
                        <h2 class="text-center bg-secondary text-light py-2 text-uppercase">
                          Bitácora - <span>Crear Cuenta</span>
                        </h2> 
                       
                         <form id="formulario-crear-cuenta" method="post">
                              <div class="form-group">
                                   <label for="usuario">Cédula: </label>
                                   <input type="text" class="form-control" name="usuario" id="usuario" placeholder="Cédula con 9 dígitos" maxlength="9" autofocus>
                              </div>
                              <div class="form-row">
                                   <div class="form-group col-md-6">
                                        <label for="nombre">Primer Nombre: </label>
                                        <input type="text" class="form-control" name="nombre1" id="nombre1" placeholder="Primer Nombre">      
                                   </div>
                                   <div class="form-group col-md-6">
                                        <label for="nombre">Segundo Nombre: </label>
                                        <input type="text" class="form-control" name="nombre2" id="nombre2" placeholder="Segundo Nombre">
                               </div>
                              </div>
                              <div class="form-row">
                                   <div class="form-group col-md-6">
                                        <label for="apellidos">Primer Apellido: </label>
                                        <input type="text" class="form-control" name="apellido1" id="apellido1" placeholder="Primer Apellido">
                                   </div>
                                   <div class="form-group col-md-6">
                                        <label for="apellidos">Segundo Apellido: </label>
                                        <input type="text" class="form-control" name="apellido2" id="apellido2" placeholder="Segundo Apellido">     
                                   </div>
                              </div>                              
                              <div class="form-group">
                                   <label for="password">Password: </label>
                                   <input type="password" class="form-control" name="password" id="password" placeholder="Password">
                              </div>

                              <div class="form-group">
                                   <?php
                                   $clasificacion = obtenerClasificacion();
                                   if($clasificacion) { ?>
                                   <label for="clasificacion">Clasificación</label>
                                        <select class="form-control" name="clase" id="clase">
                                        <?php
                                        foreach($clasificacion as $clase) { ?>                   
                                        <option value="<?php echo $clase['id_clase'] ?>"><?php echo  $clase['clase'] ?></option>

                                        <?php } ?>  
                                        </select>
                                   <?php } ?>
                              </div>

                              <div class="form-group">
                                   <?php
                                   $areas = obtenerAreaRegion();
                                   if($areas) { ?>
                                   <label for="area">Área / Región</label>
                                        <select class="form-control" name="area" id="area">
                                        <?php
                                        foreach($areas as $area) { ?>                   
                                        <option value = "<?php echo $area['id_area'] ?>"><?php echo $area['area_region'] ?></option>                                                                        
                                        <?php } ?>  
                                        </select>
                                   <?php } ?>
                              </div>

                              <div class="form-group" >
                                   <input type="hidden" id="tipo" value="crear">
                                   <input type="submit" id="boton_crear" class="mt-4 btn btn-primary" value="Crear cuenta" >
                              </div>
                              <div class="form-group d-flex justify-content-end">
                                   <a href="login.php">Inicia Sesión Aquí</a>
                              </div>
                         </form>

                    </div><!--.contenido-->    
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
                  </div><!--.col-md-7-->
            
              </div><!--.row-->
          </div><!--.container-->    
      
<?php
    include 'inc/templates/footer.php';
?>