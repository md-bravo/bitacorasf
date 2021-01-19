<div class="barra">

    <nav class="navbar px-0 px-md-3 bd-navbar navbar-expand-lg navbar-light color-fondo">
          <div class="col-md-6 text-center text-md-left">
               <a href="#" class="navbar-brand text-light">Bitácora de Trabajo - Sistemas Fijos</a>
          </div>        
       
          <div class="pr-0 pr-md-3 d-flex nav nav-pills nav-tap col-md-6 align-items-center justify-content-md-end justify-content-center">
               <label class="mb-0 text-white-50">Usuario: <?php echo $_SESSION['nombre'] ?></label>

               <li class="ml-2 nav-item dropdown">
               <a class="nav-link active dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false"><i class="fas fa-bars"></i></a>
               <div class="dropdown-menu dropdown-menu-right">                         
                    <button type="button" class="dropdown-item" id="btnGenerarExcelBarra" data-toggle="modal" data-target="#modalMesAnio">Reportes</button>       
                    <button type="button" class="dropdown-item" data-toggle="modal" data-target="#info">Info</button>
                    <a href="login.php?cerrar_session=true" class="dropdown-item">Cerrar Sesión</a>                      
               </div>
               </li>
          </div>   
     </nav>
</div>