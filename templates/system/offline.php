<?php
/*
  $Id: offline.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo $osC_Language->getTextDirection(); ?>" xml:lang="<?php echo $osC_Language->getCode(); ?>" lang="<?php echo $osC_Language->getCode(); ?>">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $osC_Language->getCharacterSet(); ?>" />

    <title><?php echo STORE_NAME; ?></title>

    <meta name="Generator" content="TomatoCart" />  

    <link rel="shortcut icon" href="templates/system/images/tomatocart.ico" type="image/x-icon" />

    <link rel="stylesheet" href="templates/system/stylesheet.css" type="text/css" />
  </head>
  <body id="offline">
    <div id="pageContent">
      
      <div class="content">
        <img src= "images/login_logo.png"/> 
        <h1><?php echo STORE_NAME; ?></h1>
        
        <p><?php echo $osC_Language->get('introduction_maintenance_mode_text'); ?></p>
        
        <?php
          if ($messageStack->size('maintenance') > 0) {
            echo $messageStack->output('maintenance');
          }
        ?>
        
        <form name="login" action="<?php echo osc_href_link(FILENAME_DEFAULT, 'maintenance=login', 'SSL'); ?>" method="post">
        
          <ol>
            <li><?php echo osc_draw_label($osC_Language->get('field_username'), 'user_name') . osc_draw_input_field('user_name'); ?></li>
            <li><?php echo osc_draw_label($osC_Language->get('field_password'), 'user_password') . osc_draw_password_field('user_password'); ?></li>
          </ol>
          
          <p align="center"><?php echo osc_draw_image_submit_button('button_login.gif', $osC_Language->get('button_sign_in')); ?></p>
          
        </form>
      </div>
    </div>
  </body>
</html>