<?php
    include 'inc/funciones/sesiones.php';
    include 'inc/funciones/funciones.php';
    include 'inc/templates/header.php';
    include 'inc/templates/barra.php';

?>

<div class="container-fluid">
    <div class="row mt-2 justify-content-center">
        <div class="col-lg-6 pr-2 pr-lg-1 pl-2">
            <div class="p-3 mb-3 bg-light rounded">
                <h4 class="text-center bg-secondary text-light py-2 text-uppercase">
                        Registro de Actividades Diarias
                </h4>

                <div class="registro-actividad">
                    <form id="formulario-registro-act" class="registro-act" method="post">
                        <div class="input-group input-group-lg mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="inputGroup-sizing-lg">Fecha</span>
                            </div>
                            <input type="date" class="form-control" id="fecha" name="name">
                            <div class="input-group-append">
                                <label for=btnCalendario class="input-group-text"><a href="#" id="btnCalendario"><i class="far fa-calendar-alt fa-lg"></i></a></label>    
                            </div>     
                        </div>
                                            
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <?php
                                
                                $categorias = obtenerCategorias();
                                if($categorias) { ?>                    
                                <span class="input-group-text" id="categorias">Categorias</span>
                            </div>
                                <select class="form-control" name="categoria" id="categoria">
                                    <?php
                                        foreach($categorias as $categoria) { ?>                   
                                        <option value="<?php echo $categoria['id_cat'] ?>"><?php echo  $categoria['id_cat'] . ' - ' . $categoria['nombre_cat'] ?></option>
                                        <?php } ?>  
                                </select>
                                <?php } ?>                
                        </div>                        

                        <div class="input-group mb-3" hidden>                            
                            <?php                                
                                $relacionCatAct = obtenerRelacionCatAct();
                                if($relacionCatAct) { ?>                                                    
                            
                                <select class="form-control" name="relacionCatAct" id="relacionCatAct">
                                    <?php
                                        foreach($relacionCatAct as $relacion) { ?>                   
                                        <option value="<?php echo $relacion['id_cat'] ?>"><?php echo  $relacion['id_act']?></option>
                                        <?php } ?>  
                                </select>
                                <?php } ?>                
                        </div>

                        <div class="input-group mb-3" hidden >                            
                            <?php                                
                                $actividades = obtenerListaActividades();
                                if($actividades) { ?>                                                    
                            
                                <select class="form-control" name="actividades" id="actividades">
                                    <?php
                                        foreach($actividades as $actividad) { ?>                   
                                        <option value="<?php echo $actividad['id_act'] ?>"><?php echo  $actividad['nombre_act']?></option>
                                        <?php } ?>  
                                </select>
                                <?php } ?>                
                        </div>                                            

                        <div class="input-group mb-3" id="div-actividades">
                            <div class="input-group-prepend">                                
                                <span class="input-group-text">Actividades</span>
                            </div>
                                <select class="form-control" name="actividad" id="actividad">                                
                                        
                                </select>                                       
                        </div>

                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Horas</span>
                            </div>
                            <input type="text" class="form-control" name="horas" id="horas" placeholder="Digite aquí">    
                            <div class="input-group-append">
                                <label for="horas-disponibles" class="input-group-text text-info" id="horas-disponibles">Restantes:<span class="ml-1"></span></label>    
                            </div>                            
                        </div>

                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Detalle</span>
                            </div>
                            <input class="form-control" name="detalle" id="detalle" maxlength="30">                            
                            <div class="input-group-append">
                                <p class="input-group-text text-muted"><span class="pr-1" id="actual">0</span>/ 30</p>
                            </div>
                        </div>
           
                        <div class="form-group mb-0 d-flex justify-content-center">
                            <input type="hidden" for="cedula" id="cedula" value="<?php echo $_SESSION['usuario'] ?>">
                            <input type="hidden" id="accion" value="registrar">
                            <input type="submit" class="btn-lg btn-primary" id="btnGuardar" value="Guardar">
                        </div>
                    </form> 
                </div>      

            </div><!--.rounded-->
        </div><!--.col-md-6-->

        <div class="col-lg-6 pl-2 pl-lg-1 pr-2 mb-2 mb-lg-0">
            <div class="px-3 pt-3 mb-3 mb-lg-0 bg-light rounded">
                <h4 class="text-center bg-secondary text-light py-2 text-uppercase">
                    Actividades Registradas <span id="fecha-seleccionada"></span>
                </h4>
                <div id="lista-act" class="act-registradas">
                    <div class="table-responsive-sm datosTabla">
                        <table id="tabla" class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Cat.</th>
                                <th scope="col">Act.</th>
                                <th scope="col">Horas</th>
                                <th scope="col">Detalle</th>
                                <th scope="col"></th>                              
                            </tr>
                        </thead>
                            <tbody id="tabla-act">                        
                            </tbody>
                        </table>      
                    </div>
                </div>
                                                        
            </div><!--.rounded-->
        </div><!---col-md-6-->
    </div><!--.row-->

    <!-- Modal Calendario  -->
    <div class="modal fade" id="modalCalendario" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            
            <div class="modal-body" id="modal-body-calendario">
                <div id='calendar'></div>                                              
            </div>

        </div>
    </div>
    </div>

    <!-- Modal reporte  -->
    <div class="modal fade" id="modalReporte" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
        <div class="modal-content" id="pdf">
            <div class="modal-header d-block pb-0 imprimir">
                <div id="tituloReporte" class="text-center pb-1"><h5>Reporte de Actividades <span></span></h5></div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="tablaModalReporteInfo">
                        <thead>
                            <tr class="thead-dark">
                                <th scope="col">Cédula</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Clasificación</th>
                                <th scope="col">Area</th>
                            </tr>                 
                        </thead>
                        <tbody>
                            <tr id="tableDatosUsario">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>    
                        </tbody>
                    </table>
                </div><!--.table-responsive-->   
            </div>
            <div class="modal-body imprimir" id="modal-body-reporte">  
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover" id="tablaModalReporte">
                        <thead>
                            <tr class="table-secondary">
                                <th scope="col">Fecha</th>
                                <th scope="col">Categoría</th>
                                <th scope="col">Actividad</th>
                                <th scope="col">Horas</th>
                                <th scope="col">Detalle</th>
                            </tr>
                        </thead>
                        <tbody id="tablaModalContenido">
                            <!-- Contenido autogenerado                                               -->
                        </tbody>
                    </table>
                </div><!--.table-responsive-->
            </div>

            <div class="modal-footer">
                <button id="btnPDF" type="button" class="btn btn-primary">Imprimir - PDF</button>
                <button id="btnCerrar" type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
    </div>

    <!-- Modal para mostrar información del sitio-->
    <div class="modal fade" id="info" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Información</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <p>Bitácora de Trabajo Sistemas Fijos, permite registrar las actividades diárias que se realizan en el proceso.</p>
            <p>Versión 1.3</p>
            <p>Desarrollada por Mac Donald Bravo.</p>
            <div id="contacto">
                <a href="mailto:mbravob@hotmail.com?subject=Feedback Bitácora SF&body=Usuario:%20<?php echo $_SESSION['nombre']?>%0D%0A%0D%0A%0D%0A">Enviar Consulta o Comentario</a>
            </div>

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
        </div>
    </div>
    </div>

    <!-- Modal para elegir mes y año del reporte-->
    <div class="modal fade" id="modalMesAnio" tabindex="-1" role="dialog" aria-labelledby="reporteExcelTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reporteExcelTitle">Generar Reporte</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
            
            <form id="reporteMesDia">
                <select name="mes" class="ddl-reporte" id="mes">
                <option value="1">Enero</option>
                <option value="2">Febrero</option>
                <option value="3">Marzo</option>
                <option value="4">Abril</option>
                <option value="5">Mayo</option>
                <option value="6">Junio</option>
                <option value="7">Julio</option>
                <option value="8">Agosto</option>
                <option value="9">Setiembre</option>
                <option value="10">Octubre</option>
                <option value="11">Noviembre</option>
                <option value="12">Diciembre</option>
                </select> <!--Muestra solo el año actual y el anterior-->
                <select name="anio" class="ddl-reporte ml-2 ml-sm-3" id="anio">
                    <option value="<?php echo date('Y')-1; ?>"><?php echo date('Y')-1; ?></option>               
                    <option value="<?php echo date('Y'); ?>"><?php echo date('Y'); ?></option>               
                </select>
                <div class="pt-3 d-flex justify-content-end">
                    <input type="hidden" name="tipo" id="tipo" value="reportePersonal">
                    <input type="hidden" for="usuario" id="usuario" name="usuario" value="<?php echo $_SESSION['usuario'] ?>">
                    <button type="button" class="btn btn-primary mr-1" id="btnVerReporteModal" data-dismiss="modal">Ver Reporte</button>
                    <!-- <button type="button" class="btn btn-primary mr-1" id="btnGenerarExcelModal">Generar Excel</button> -->
                    <button type="button" class="btn btn-primary mr-1" id="btnGenerarExcelModalNuevo">Generar Excel</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>                
            </form>
            </div>            
        </div>
    </div>
    </div>

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

</div><!--.container-->

<div class="footer bg-dark">
    <div class="row justify-content-center">
        <div class="col col-4">
            <!-- <marquee direction="left" speed="normal" behavior="loop" >
                Bitácora Sistemas Fijos se encuentra en modo de prueba - Desarrollada por Mac Donald Bravo.
            </marquee> -->
        </div>        
    </div>
</div>


<?php
    include 'inc/templates/footer.php';
?>