<?php
/*
  $Id: articles_categories.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

<?php echo osc_image(DIR_WS_IMAGES . $osC_Template->getPageImage(), $osC_Template->getPageTitle(), HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT, 'id="pageIcon"'); ?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<?php
  while ($Qarticles->next()) {
?>
  <div class="moduleBox">

    <div style="float: right; margin-top: 5px;"><?php echo osC_DateTime::getLong($Qarticles->value('articles_date_added')); ?></div>

    <h6><?php echo osc_link_object(osc_href_link(FILENAME_INFO, 'articles&articles_id=' . $Qarticles->valueInt('articles_id')), $Qarticles->value('articles_name')); ?></h6>

    <div class="content">

  <?php
      if (!osc_empty($Qarticles->value('articles_image'))) {
        echo osc_link_object(osc_href_link(FILENAME_INFO, 'articles&articles_id=' . $Qarticles->valueInt('articles_id')), $osC_Image->show($Qarticles->value('articles_image'), $Qarticles->value('articles_name'), 'style="float: left;"', '', 'articles'));
      }
  ?>

      <p style="padding-left: 100px;"><?php echo substr($Qarticles->value('articles_description'), 0, 300) . ((strlen($Qarticles->valueProtected('articles_description')) >= 100) ? '..' : ''); ?></p>

      <div style="clear: both;"></div>
    </div>

  </div>
<?php
  }
?>

<div class="listingPageLinks">
  <span style="float: right;"><?php echo $Qarticles->getBatchPageLinks('page', 'articles_categories&articles_categories_id=' . $_GET['articles_categories_id']); ?></span>

  <?php echo $Qarticles->getBatchTotalPages($osC_Language->get('result_set_number_of_articles')); ?>
</div>
