<?php
  define('MENU_STANDARD', 1);  
  define('MENU_ORTE', 2);  
  define('MENU_SERVICE', 3);  
  define('MENU_FOOTER', 4);  
  define('START_PAGE_ID', 1);  
  define('BLOCK_CONTENT', 1);  
  define('BLOCK_LEFT', 2);  
  define('BLOCK_CENTER', 3);  
  define('BLOCK_RIGHT', 4);  
  define('ERROR_404', 17);  
  define('ERROR_403', 16);  
  define('ERROR_410', 18);  
  global $database;  
  $SQL = "SELECT `menu` FROM `".TABLE_PREFIX."pages` WHERE `page_id`='".PAGE_ID."'";  
  $menu_id = $database->get_one($SQL);    
?>
<html lang="en">  
  <head>    
    <title><?php page_title('', '[PAGE_TITLE]'); ?></title>    
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>    
    <meta name="robots" content="index,follow" />    
    <meta name="description" content="<?php page_description(); ?>" />    
    <meta name="keywords" content="<?php page_keywords(); ?>" />    
    <meta name="content-language" content="<?php echo strtolower(LANGUAGE); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <!--[if lt IE 9]>      
      <script type="text/javascript" src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>      
      <script type="text/javascript" src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>    
    <![endif]-->    
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo TEMPLATE_DIR; ?>/favicon.ico" />    
    <script src="<?php echo TEMPLATE_DIR; ?>/jquery/jquery/1.10.2/jquery.min.js"></script>    
    <script src="<?php echo TEMPLATE_DIR; ?>/bootstrap/3.0.3/js/bootstrap.min.js"></script>        
    <link rel="stylesheet" type="text/css" href="<?php echo TEMPLATE_DIR; ?>/bootstrap/3.0.3/css/bootstrap.min.css" />    
    <link rel="stylesheet" type="text/css" href="<?php echo TEMPLATE_DIR; ?>/font-awesome/4.0.3/css/font-awesome.min.css" />    
    <link rel="stylesheet" type="text/css" href="<?php echo TEMPLATE_DIR; ?>/screen.css" />    
    <link rel="stylesheet" type="text/css" href="<?php echo TEMPLATE_DIR; ?>/flexcontent.css" />      
  </head>  
  <body>    
    <div class="container">      
      <nav class="navbar navbar-default" role="navigation">        
        <!-- Brand and toggle get grouped for better mobile display -->        
        <div class="navbar-header">          
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-navbar-collapse-1">            
            <span class="sr-only">Toggle navigation</span>            
            <span class="icon-bar"></span>            
            <span class="icon-bar"></span>            
            <span class="icon-bar"></span>          
          </button>          
          <a class="navbar-brand" href="<?php echo WB_URL; ?>" title="Start: Freie Ferienwohnungen an der Ostsee"><span class="glyphicon glyphicon-home"></span></a>        
        </div>        
        <div class="collapse navbar-collapse" id="bs-navbar-collapse-1">          
          <!-- Orte -->          
          <ul class="nav navbar-nav">            
            <?php show_menu2(MENU_ORTE, SM2_ROOT+1, SM2_ALL, SM2_ALL, '[li][a][menu_title]</a>', '</li>', '', ''); ?>          
          </ul>          
          <?php if ((PAGE_ID != START_PAGE_ID) && (PAGE_ID != ERROR_403) && (PAGE_ID != ERROR_404) && (PAGE_ID != ERROR_410)) { ?>            
            <!-- Search -->            
            <div class="navbar-right">              
              <form action="<?php echo WB_URL; ?>/search/index.php" name="search" class="navbar-form " role="form" method="get">                
                <div class="input-group nav-search">                  
                  <input id="search_string" name="string" type="text" class="form-control input-sm" placeholder="Suche ...">                  
                  <span class="input-group-btn">                    
                    <button class="btn btn-default input-sm" type="submit"><span class="glyphicon glyphicon-search"></span></button>                  
                  </span>                
                </div>                        
              </form>            
            </div>          
          <?php } ?>          
          <!-- Service -->          
          <ul class="nav navbar-nav navbar-right">            
            <li class="dropdown">              
              <a href="#" class="dropdown-toggle <?php if ($menu_id == MENU_SERVICE) { echo 'dropdown-active'; } ?>" data-toggle="dropdown">Service <b class="caret"></b></a>              
              <ul class="dropdown-menu">                
                <?php show_menu2(MENU_SERVICE, SM2_ROOT+1, SM2_ALL, SM2_ALL, '[li][a][menu_title]</a>', '</li>', '', ''); ?>              
              </ul>            
            </li>                      
          </ul>        
        </div>        
      </nav>      
      <div class="content">        
        <?php           
          ob_start();          
          page_content(BLOCK_CONTENT);          
          $content_block = ob_get_clean();           
          $is_content = (is_string($content_block) && !empty($content_block));          
          ob_start();          
          page_content(BLOCK_LEFT);          
          $left_block = ob_get_clean();          
          $is_left = (is_string($left_block) && !empty($left_block));          
          ob_start();          
          page_content(BLOCK_CENTER);          
          $center_block = ob_get_clean();          
          $is_center = (is_string($center_block) && !empty($center_block));          
          ob_start();          
          page_content(BLOCK_RIGHT);          
          $right_block = ob_get_clean();          
          $is_right = (is_string($right_block) && !empty($right_block));          
          if ((PAGE_ID == START_PAGE_ID) || (PAGE_ID == ERROR_403) || (PAGE_ID == ERROR_404) || (PAGE_ID == ERROR_410)) { ?>            
            <h1 class="text-center"><?php echo WEBSITE_TITLE; ?></h1>            
            <!-- Search -->            
            <div class="row">              
              <div class="col-md-3 hidden-xs">&nbsp;</div>              
              <div class="col-md-6">                
                <div class="search-exposed">                  
                  <form action="<?php echo WB_URL; ?>/search/index.php" name="search" class="form " role="form" method="get">                    
                    <div class="input-group">                      
                      <input id="search_string" name="string" type="text" class="form-control input-lg" placeholder="Welche Kriterien soll Ihre Unterkunft erfüllen?">                      
                      <span class="input-group-btn">                        
                        <button class="btn btn-default input-lg" type="submit"><span class="glyphicon glyphicon-search"></span></button>                      
                      </span>                        
                    </div>
                    <p class="help-block">z.B.: Am Ostseestand, Hundefreundlich, Kinder willkommen, gewünschter Ort ...</p>
                  </form>                
                </div>              
              </div>              
              <div class="col-md-3 hidden-xs">&nbsp;</div>            
            </div>                      
          <?php }          
            if ($is_left && $is_center && $is_right) {             
              if ($is_content) {              
                echo '<div class="content_block">'.$content_block.'</div>';                           
              }            
          ?>              
          <div class="row">                
            <div class="col-lg-4 col-md-4">                  
              <div class="left_block"><?php echo $left_block; ?></div>                
            </div>                  
            <div class="col-lg-4 col-md-4">                  
              <div class="center_block"><?php echo $center_block; ?></div>                
            </div>                
            <div class="col-lg-4 col-md-4">                  
              <div class="right_block"><?php echo $right_block; ?></div>                
            </div>              
          </div>            
          <?php          
            }          
            elseif ($is_content && $is_left && $is_right) {            
          ?>              
            <div class="row">                
              <div class="col-lg-3 col-md-3">                  
                <div class="left_block"><?php echo $left_block; ?></div>                
              </div>                  
              <div class="col-lg-6 col-md-6">                  
                <div class="center_block"><?php echo $content_block; ?></div>                
              </div>                
              <div class="col-lg-3 col-md-3">                  
                <div class="right_block"><?php echo $right_block; ?></div>                
              </div>              
            </div>            
          <?php          
            }          
            elseif ($is_left && $is_right) {            
          ?>              
          <div class="row">                
            <div class="col-lg-6 col-md-6 col-sm-6">                  
              <div class="left_block"><?php echo $left_block; ?></div>                
            </div>                  
            <div class="col-lg-6 col-md-6 col-sm-6">                  
              <div class="right_block"><?php echo $right_block; ?></div>                
            </div>              
          </div>            
          <?php }          
            elseif ($is_content && $is_left) {            
          ?>              
          <div class="row">                
            <div class="col-lg-4 col-md-4 col-sm-4">                  
              <div class="left_block"><?php echo $left_block; ?></div>                
            </div>                  
            <div class="col-lg-8 col-md-8 col-sm-8">                  
              <div class="right_block"><?php echo $content_block; ?></div>                
            </div>              
          </div>            
          <?php }          
            elseif ($is_content && $is_right) {            
          ?>              
          <div class="row">                
            <div class="col-lg-8 col-md-8 col-sm-8">                  
              <div class="left_block"><?php echo $content_block; ?></div>                
            </div>                  
            <div class="col-lg-4 col-md-4 col-sm-4">                  
              <div class="right_block"><?php echo $right_block; ?></div>                
            </div>              
          </div>            
          <?php }          
            elseif ($is_content) {            
              echo '<div class="content_block">'.$content_block.'</div>';          
            }          
            else {            
              echo '<div class="alert alert-danger">FEHLER: Unklare Verwendung der Abschnitte, kann die Seite nicht darstellen!</div>';          
            }        
          ?>      
          </div>      
      <div class="footer">        
        <ul class="nav nav-pills pull-right">          
          <?php show_menu2(MENU_FOOTER, SM2_ROOT+1, SM2_ALL, SM2_ALL, '[li][a][menu_title]</a>', '</li>', '', ''); ?>        
        </ul>        
        <div class="clearfix"></div>      
      </div>    
    </div>    
    <!-- footer -->          
  </body>
</html>