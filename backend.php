<?php
    include 'inc/funciones/sesiones.php';
    include 'inc/funciones/funciones.php';
    include 'inc/templates/header.php';
?>

<div class="barra">

    <nav class="navbar px-0 px-md-3 bd-navbar navbar-expand-lg navbar-light bg-dark">
          <div class="col-md-6 text-center text-md-left">
               <a href="#" class="navbar-brand text-light">Bitácora de Trabajo - Sistemas Fijos</a>
          </div>        
       
          <div class="pr-0 pr-md-3 d-flex nav nav-pills nav-tap col-md-6 align-items-center justify-content-md-end justify-content-center">
               <label class="mb-0 text-white-50">Usuario: <?php echo $_SESSION['nombre'] ?></label>

               <a href="login.php?cerrar_session=true" class="ml-2 btn btn-primary">Cerrar Sesión</a>
               
          </div>   
     </nav>
</div>

<div class="container-fluid bg-light">
    <div class="container">
        <div class="row pt-5">
            <div class="col-2">
                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                <a class="nav-link active" id="v-pills-reportes-tab" data-toggle="pill" href="#v-pills-reportes" role="tab" aria-controls="v-pills-reportes" aria-selected="true">Reportes</a>
                <a class="nav-link" id="v-pills-usuarios-tab" data-toggle="pill" href="#v-pills-usuarios" role="tab" aria-controls="v-pills-usuarios" aria-selected="false">Usuarios</a>
                <a class="nav-link" id="v-pills-cat-tab" data-toggle="pill" href="#v-pills-cat" role="tab" aria-controls="v-pills-cat" aria-selected="false">Categorias</a>
                <a class="nav-link" id="v-pills-act-tab" data-toggle="pill" href="#v-pills-act" role="tab" aria-controls="v-pills-act" aria-selected="false">Actividades</a>
                </div>
            </div>
            <div class="col-10">
                <div class="tab-content" id="v-pills-tabContent">
                <div class="tab-pane fade show active" id="v-pills-reportes" role="tabpanel" aria-labelledby="v-pills-reportes-tab">
                    <h2>Generar Reportes</h2>
                    <label class="pt-3" for="inlineFormCustomSelectPref">Reporte por Área-Región</label>
                    <form action="inc/modelos/modelo-exportar.php" method="post" class="form-inline pb-3 border-bottom">         
                        
                        <?php                                
                            $areasRegiones = obtenerAreaRegion();
                            if($areasRegiones) { ?>                                                    
                            <select name="areaRegion" class="custom-select my-1 mr-sm-2" id="inlineFormCustomSelectPref">
                                <?php
                                    foreach($areasRegiones as $areaRegion) { ?>                   
                                    <option value="<?php echo $areaRegion['id_area'] ?>"><?php echo $areaRegion['area_region'] ?></option>
                                    <?php } ?>  
                            </select>
                        <?php } ?>      
                        <select name="mes" class="custom-select mr-2" id="mes">
                            <option value="1">Enero</option>
                            <option value="2">Febrero</option>
                            <option value="3">Marzo</option>
                            <option value="4">Abril</option>
                            <option value="5">Mayo</option>
                            <option value="6">Junio</option>
                            <option value="7">Julio</option>
                            <option value="8">Agosto</option>
                            <option value="9">Septiembre</option>
                            <option value="10">Octubre</option>
                            <option value="11">Noviembre</option>
                            <option value="12">Diciembre</option>
                        </select> <!--Muestra solo el año actual y el anterior-->
                        <select name="anio" class="custom-select mr-2" id="anio">
                            <option value="<?php echo date('Y')-1; ?>"><?php echo date('Y')-1; ?></option>               
                            <option value="<?php echo date('Y'); ?>"><?php echo date('Y'); ?></option>               
                        </select>
                        
                        <input type="hidden" name="tipo" id="tipo" value="areaRegion">
                        <button type="submit" class="btn btn-primary my-1">Generar</button>
                    </form>

                    
                    <form action="inc/modelos/modelo-exportar.php" method="post" class="mt-3 border-bottom">         
                        <label for="inlineFormCustomSelectPref">Reporte de Todo el Personal</label>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <select name="mes" class="custom-select" id="mes">
                                    <option value="1">Enero</option>
                                    <option value="2">Febrero</option>
                                    <option value="3">Marzo</option>
                                    <option value="4">Abril</option>
                                    <option value="5">Mayo</option>
                                    <option value="6">Junio</option>
                                    <option value="7">Julio</option>
                                    <option value="8">Agosto</option>
                                    <option value="9">Septiembre</option>
                                    <option value="10">Octubre</option>
                                    <option value="11">Noviembre</option>
                                    <option value="12">Diciembre</option>
                                </select> <!--Muestra solo el año actual y el anterior-->
                            </div>
                            <div class="form-group col-md-2">
                                <select name="anio" class="custom-select" id="anio">
                                    <option value="<?php echo date('Y')-1; ?>"><?php echo date('Y')-1; ?></option>
                                    <option value="<?php echo date('Y'); ?>"><?php echo date('Y'); ?></option>               
                                </select>
                            </div>
                            <div class="form-group col-md-5">
                                
                                <?php         
                                $datosUsuarios = listaUsuarios();

                                if($datosUsuarios) { ?>    
                                    <select name="excepto[]" class="custom-select" id="dropDownExcepto" multiple="multiple">
                                    <?php 
                                    foreach($datosUsuarios as $datoUsuario) {                                         
                                        ?>                                        
                                        <option value="<?php echo($datoUsuario['cedula']); ?>"><?php echo($datoUsuario['apellido1'].' '.$datoUsuario['apellido2'].' '.$datoUsuario['nombre1'].' '.$datoUsuario['nombre2']); ?></option>
                                    <?php } ?>
                                    </select>                                 
                                <?php } ?>
                    
                        </div>
                        <div class="form-group col-md-2">
                            <input type="hidden" name="tipo" id="tipo" value="todos">                            
                            <button type="submit" class="btn btn-primary">Generar</button>
                        </div>
                        </div><!--.form-row-->
                                     
                    </form>


                    <form action="inc/modelos/modelo-exportar.php" method="post" class="form-inline pb-3 mt-3 border-bottom">         
                    <label class="my-1 mr-4" for="inlineFormCustomSelectPref">Totales del Mes - Todo el Personal</label>
                        <select name="mes" class="custom-select mr-2" id="mes">
                            <option value="1">Enero</option>
                            <option value="2">Febrero</option>
                            <option value="3">Marzo</option>
                            <option value="4">Abril</option>
                            <option value="5">Mayo</option>
                            <option value="6">Junio</option>
                            <option value="7">Julio</option>
                            <option value="8">Agosto</option>
                            <option value="9">Septiembre</option>
                            <option value="10">Octubre</option>
                            <option value="11">Noviembre</option>
                            <option value="12">Diciembre</option>
                        </select> <!--Muestra solo el año actual y el anterior-->
                        <select name="anio" class="custom-select mr-2" id="anio">
                            <option value="<?php echo date('Y')-1; ?>"><?php echo date('Y')-1; ?></option>               
                            <option value="<?php echo date('Y'); ?>"><?php echo date('Y'); ?></option>               
                        </select>
                        
                        <input type="hidden" name="tipo" id="tipo" value="totalesTodos">
                        <button type="submit" class="btn btn-primary my-1">Generar</button>
                    </form>
                </div>
                <div class="tab-pane fade" id="v-pills-usuarios" role="tabpanel" aria-labelledby="v-pills-usuarios-tab">
                    <h2>Lista de Usuarios</h2>

                    <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Cédula</th>
                                <th scope="col">Nombre1</th>                        
                                <th scope="col">Nombre2</th>                        
                                <th scope="col">Apellido1</th>                        
                                <th scope="col">Apellido2</th>                        
                                <th scope="col">Clase</th>                        
                                <th scope="col">Area-Región</th>   
                                <th scope="col">Estado</th>                                             
                            </tr>
                        </thead>
                        <tbody>
                            <?php      
                            $contador = 0;   
                            $usuarios = obtenerUsuarios();
                            if($usuarios) { 
                                foreach($usuarios as $usuario) { 
                                    if($usuario['estado']==1){
                                        $estado = "Activo";
                                    } else {
                                        $estado = "Inactivo";
                                    }
                                    ?>
                                    <tr>
                                        <th scope="row"><?php echo $usuario['cedula'] ?></th>
                                        <td><?php echo $usuario['nombre1'] ?></td>
                                        <td><?php echo $usuario['nombre2'] ?></td>
                                        <td><?php echo $usuario['apellido1'] ?></td>
                                        <td><?php echo $usuario['apellido2'] ?></td>
                                        <td><?php echo $usuario['clase'] ?></td>
                                        <td><?php echo $usuario['area_region'] ?></td>
                                        <td class="pl-0 pr-1">
                                        <div class="onoffswitch">
                                        <?php
                                        if($usuario['estado']==1){ ?>
                                            <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="switchEstado<?php echo $contador;?>" checked> <?php
                                        } else { ?>
                                            <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="switchEstado<?php echo $contador;?>" unchecked> <?php
                                        }
                                        ?>
                                            <label class="onoffswitch-label" for="switchEstado<?php echo $contador;?>">
                                                <span class="onoffswitch-inner"></span>
                                                <span class="onoffswitch-switch"></span>
                                            </label>
                                        </div>
                                        </td>
                                    </tr>
                                <?php $contador++; }                                 
                            } ?>
                        </tbody>
                    </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="v-pills-cat" role="tabpanel" aria-labelledby="v-pills-cat-tab">
                    <h2>Lista de Categorias</h2>
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Nombre</th>                        
                            </tr>
                        </thead>
                        <tbody>
                            <?php            
                            $categorias = obtenerCategorias();
                            if($categorias) { 
                                foreach($categorias as $categoria) { ?>
                                    <tr>
                                        <th scope="row"><?php echo $categoria['id_cat'] ?></th>
                                        <td><?php echo $categoria['nombre_cat'] ?></td>
                                    </tr>
                                <?php } 
                            } ?>
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="v-pills-act" role="tabpanel" aria-labelledby="v-pills-act-tab">
                    <h2>Lista de Actividades</h2>
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Nombre</th>                        
                            </tr>
                        </thead>
                        <tbody>
                            <?php            
                            $actividades = obtenerListaActividades();
                            if($actividades) { 
                                foreach($actividades as $actividad) { ?>
                                    <tr>
                                        <th scope="row"><?php echo $actividad['id_act'] ?></th>
                                        <td><?php echo $actividad['nombre_act'] ?></td>
                                    </tr>
                                <?php } 
                            } ?>
                        </tbody>
                    </table>
                </div>
                </div>
            
            </div><!--.col-10-->
        </div><!--.row-->
        
    </div>
</div><!--.container-fluid-->

<!-- Spinner -->
<div id="spinner" class=" fondo-atenuado">                
    <div class="row mr-0 justify-content-center align-items-center h-100">
        <div class="d-flex justify-content-center">
            <div class="spinner-border text-white spinner-size" role="status">
                <span class="sr-only">Loading...</span>                                
            </div>
        </div>     
        <div class="spinner-text d-flex justify-content-center"><i class="far fa-frown fa-lg"></i></div>
    </div>                                   
</div>



<?php
    include 'inc/templates/footer.php';
?>