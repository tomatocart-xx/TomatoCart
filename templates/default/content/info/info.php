<?php
/*
  $Id: info.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<link href="templates/<?php echo $osC_Template->getCode(); ?>/javascript/milkbox/milkbox.css" rel="stylesheet" type="text/css" />

<script type="text/javascript" src="templates/<?php echo $osC_Template->getCode(); ?>/javascript/milkbox/milkbox.js"></script>

<?php
    if (!osc_empty($Qarticle->value('articles_image'))) {
      echo '<p style="float: right; padding: 0px 5px 5px 5px">' . $osC_Image->show($Qarticle->value('articles_image'), $Qarticle->value('articles_name'), '', 'product_info', 'articles') . '</p>';
    }
?>

<p><?php echo $Qarticle->value('articles_description'); ?></p>

<div class="submitFormButtons" style="text-align: right;">
  <?php echo osc_link_object(osc_href_link(FILENAME_DEFAULT), osc_draw_image_button('button_continue.gif', $osC_Language->get('button_continue'))); ?>
</div>
