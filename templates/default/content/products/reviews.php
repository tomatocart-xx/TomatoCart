<?php
/*
  $Id: reviews.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  $Qreviews = osC_Reviews::getListing();
?>

<?php echo osc_image(DIR_WS_IMAGES . $osC_Template->getPageImage(), $osC_Template->getPageTitle(), HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT, 'id="pageIcon"'); ?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<?php
  while ($Qreviews->next()) {
?>

<div class="moduleBox">
  <div style="float: right; margin-top: 5px;"><?php echo sprintf($osC_Language->get('review_date_added'), osC_DateTime::getLong($Qreviews->value('date_added'))); ?></div>

  <h6><?php echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, 'reviews=' . $Qreviews->valueInt('reviews_id')), $Qreviews->value('products_name')); ?> (<?php echo sprintf($osC_Language->get('reviewed_by'), $Qreviews->valueProtected('customers_name')); ?>)</h6>

  <div class="content">

<?php
    if (!osc_empty($Qreviews->value('image'))) {
      echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, 'reviews=' . $Qreviews->valueInt('reviews_id') . '&' . $Qreviews->value('products_name')), $osC_Image->show($Qreviews->value('image'), $Qreviews->value('products_name'), 'style="float: left;"'));
    }
?>

    <p style="padding-left: 100px;"><?php echo wordwrap($Qreviews->valueProtected('reviews_text'), 60, '&shy;') . ((strlen($Qreviews->valueProtected('reviews_text')) >= 100) ? '..' : '') . '<br /><br /><i>' . sprintf($osC_Language->get('review_rating'), osc_image(DIR_WS_IMAGES . 'stars_' . $Qreviews->valueInt('reviews_rating') . '.png', sprintf($osC_Language->get('rating_of_5_stars'), $Qreviews->valueInt('reviews_rating'))), sprintf($osC_Language->get('rating_of_5_stars'), $Qreviews->valueInt('reviews_rating'))) . '</i>'; ?></p>

    <div style="clear: both;"></div>
  </div>
</div>

<?php
  }
?>

<div class="listingPageLinks">
  <span style="float: right;"><?php echo $Qreviews->getBatchPageLinks('page', 'reviews'); ?></span>

  <?php echo $Qreviews->getBatchTotalPages($osC_Language->get('result_set_number_of_reviews')); ?>
</div>
