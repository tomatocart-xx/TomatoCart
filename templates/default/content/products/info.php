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

<div class="moduleBox">

  <div class="content">
    <div style="float: left;">
      <link href="templates/<?php echo $osC_Template->getCode(); ?>/javascript/milkbox/milkbox.css" rel="stylesheet" type="text/css" />
      <script type="text/javascript" src="templates/<?php echo $osC_Template->getCode(); ?>/javascript/milkbox/milkbox.js"></script>
      
      <div id="productImages" style="width:200px">
      <?php
        echo osc_link_object($osC_Image->getImageUrl($osC_Product->getImage(), 'originals'), $osC_Image->show($osC_Product->getImage(), $osC_Product->getTitle(), 'hspace="5" vspace="5"', 'product_info'),'id="defaultProductImage"');

        $images = $osC_Product->getImages();
        foreach ($images as $image){
          echo osc_link_object($osC_Image->getImageUrl($image['image'], 'originals'), $osC_Image->show($image['image'], $osC_Product->getTitle(), 'hspace="5" vspace="5"', 'mini'), 'rel="milkbox:group_products" style="float:left"') . "\n";
        }
      ?>
      </div>
    </div>

    <form id="cart_quantity" name="cart_quantity" action="<?php echo osc_href_link(FILENAME_PRODUCTS, osc_get_all_get_params(array('action')) . '&action=cart_add'); ?>" method="post">
    <table class="productInfo" border="0" cellspacing="0" cellpadding="2">
    
      <tr>
        <td colspan="2" class="price"><?php echo $osC_Product->getPriceFormated(true) . '&nbsp;' . ( (DISPLAY_PRICE_WITH_TAX == '1') ? $osC_Language->get('including_tax') : ''); ?></td>
      </tr>
      
  <?php
    if (!$osC_Product->hasVariants()) {
  ?>
      <tr>
        <td class="label"><?php echo $osC_Language->get('field_sku'); ?></td>
        <td><?php echo $osC_Product->getModel(); ?>&nbsp;</td>
      </tr>
  <?php
    }
  ?>
      <tr>
        <td class="label"><?php echo $osC_Language->get('field_availability'); ?></td>
        <td><?php echo $osC_Product->getQuantity() > 0 ? $osC_Language->get('in_stock') : $osC_Language->get('out_of_stock'); ?></td>
      </tr>
      
  <?php
    if (PRODUCT_INFO_QUANTITY == '1') {
  ?>
      <tr>
        <td class="label"><?php echo $osC_Language->get('field_quantity'); ?></td>
        <td><?php echo $osC_Product->getQuantity() . ' ' . $osC_Product->getUnitClass(); ?></td>
      </tr>
  <?php
    }

    if (PRODUCT_INFO_MOQ == '1') {
  ?>
      <tr>
        <td class="label"><?php echo $osC_Language->get('field_moq'); ?></td>
        <td><?php echo $osC_Product->getMOQ() . ' ' . $osC_Product->getUnitClass(); ?></td>
      </tr>
  <?php
    }

    if (PRODUCT_INFO_ORDER_INCREMENT == '1') {
  ?>
      <tr>
        <td class="label"><?php echo $osC_Language->get('field_order_increment'); ?></td>
        <td><?php echo $osC_Product->getOrderIncrement() . ' ' . $osC_Product->getUnitClass(); ?></td>
      </tr>
  <?php
    }
    
    if ($osC_Product->isDownloadable() && $osC_Product->hasSampleFile()) {
  ?>
      <tr>  
        <td class="label"><?php echo $osC_Language->get('field_sample_url'); ?></td>
        <td><?php echo osc_link_object(osc_href_link(FILENAME_DOWNLOAD, 'type=sample&id=' . $osC_Product->getID()), $osC_Product->getSampleFile()); ?></td>
      </tr>     
  <?php
    }

    if ($osC_Product->hasURL()) {
  ?>
      <tr>
        <td colspan="2"><?php echo sprintf($osC_Language->get('go_to_external_products_webpage'), osc_href_link(FILENAME_REDIRECT, 'action=url&goto=' . urlencode($osC_Product->getURL()), 'NONSSL', null, false)); ?></td>
      </tr>
      
  <?php
    }
  
    if ($osC_Product->getDateAvailable() > osC_DateTime::getNow()) {
  ?>
      <tr>  
        <td colspan="2" align="center"><?php echo sprintf($osC_Language->get('date_availability'), osC_DateTime::getLong($osC_Product->getDateAvailable())); ?></td>
      </tr>
  <?php
    }
  ?>
      
      
  <?php
    if ($osC_Product->isGiftCertificate()) {
      if ($osC_Product->isOpenAmountGiftCertificate()) {
  ?>
      <tr>      
        <td class="label"><?php echo $osC_Language->get('field_gift_certificate_amount'); ?></td>
        <td><?php echo osc_draw_input_field('gift_certificate_amount', $osC_Product->getOpenAmountMinValue(), 'size="18"'); ?></td>
      </tr>
  <?php
    }
  ?>
      <tr>      
        <td class="label"><?php echo $osC_Language->get('field_senders_name'); ?></td>
        <td><?php echo osc_draw_input_field('senders_name', null, 'size="18"'); ?></td>
      </tr>
  <?php
    if ($osC_Product->isEmailGiftCertificate()) {
  ?>
      <tr>
        <td class="label"><?php echo $osC_Language->get('field_senders_email'); ?></td>
        <td><?php echo osc_draw_input_field('senders_email', null, 'size="18"'); ?></td>
      </tr>
  <?php
    }
  ?>        
      <tr>
        <td class="label"><?php echo $osC_Language->get('field_recipients_name'); ?></td>
        <td><?php echo osc_draw_input_field('recipients_name', null, 'size="18"'); ?></td>
      </tr>
  <?php
    if ($osC_Product->isEmailGiftCertificate()) {
  ?>  
      <tr>      
        <td class="label"><?php echo $osC_Language->get('field_recipients_email'); ?></td>
        <td><?php echo osc_draw_input_field('recipients_email', null, 'size="18"'); ?></td>
      </tr>
  <?php
    }
  ?>
      <tr>          
        <td class="label"><?php echo $osC_Language->get('fields_gift_certificate_message'); ?></td>
        <td><?php echo osc_draw_textarea_field('message', null, 15, 2); ?></td>
      </tr>
  <?php
  }
  ?>
      <tr>
        <td colspan="2" align="center">
          <div class="submitFormButtons">
          
          <?php
            if (!$osC_Product->hasVariants()) {
              echo '<p>' . 'Quantity:&nbsp;' . osc_draw_input_field('quantity', $osC_Product->getMOQ(), 'size="3"') . '&nbsp;' . osc_draw_image_submit_button('button_in_cart.gif', $osC_Language->get('button_add_to_cart'), 'id="addToShoppingCart" product_id ="' . $osC_Product->getID() . '"') . '</p>';
            }
            
            echo '<p>' . osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), $osC_Product->getID() . '&action=wishlist_add'), $osC_Language->get('add_to_wishlist')) . '</p>';
          ?>
          </div>
        </td>
      </tr>
    </table>
    </form>
    
    <div style="clear: both;"></div>
  </div>
  
</div>

<?php

  if ($osC_Product->hasAttributes()) {
    echo '<h6>' . $osC_Language->get('section_heading_attributes') . '</h6>';
    
    $attributes = $osC_Product->getAttributes();
    echo '<ul>';
    foreach($attributes as $attribute) {
      echo '<li>' . osc_draw_label($attribute['name'], null) . ': ' . $attribute['value'] . '</li>';
    }
    echo '</ul>';
  }
?>

<?php

  if ($osC_Product->hasVariants()) {
    echo '<h6>' . $osC_Language->get('section_heading_variants') . '</h6>';
    echo $osC_Product->renderVariantsTable();
  }
?>

<?php
  if ($osC_Product->hasQuantityDiscount()) {
    echo '<h6>' . $osC_Language->get('section_heading_quantity_discount') . '</h6>';
    echo $osC_Product->renderQuantityDiscountTable();
  }
?>

<h6><?php echo $osC_Language->get('section_heading_products_description'); ?></h6>

<div><?php echo $osC_Product->getDescription(); ?></div>

<div style="clear: both;"></div>

<div class="submitFormButtons" style="text-align: right;">

<?php
  if ($osC_Services->isStarted('reviews')) {
    echo '<span style="float: left;">' . osc_link_object(osc_href_link(FILENAME_PRODUCTS, 'reviews&' . osc_get_all_get_params()), osc_draw_image_button('button_reviews.gif', $osC_Language->get('button_reviews'))) . '</span>';
  }
?>

</div>

<?php
  if ($osC_Services->isStarted('reviews') && osC_Reviews::exists(osc_get_product_id($osC_Product->getID()))) {
?>

<p><?php echo $osC_Language->get('number_of_product_reviews') . ' ' . osC_Reviews::getTotal(osc_get_product_id($osC_Product->getID())); ?></p>

<?php
  }
?>

<script type=text/javascript>

window.addEvent('domready', function(){
  $('defaultProductImage').addEvent('click',function(e){
    e.preventDefault();
    
    Milkbox.openMilkbox(Milkbox.galleries[0], 0);
  });
    
  <?php 
  if ($osC_Product->isGiftCertificate()) {
  ?>
    $('addToShoppingCart').addEvent('click', function(e){
      e.preventDefault();
      
      var errors = [];
      
    <?php 
    if ($osC_Product->isOpenAmountGiftCertificate()) {
      $min = $osC_Product->getOpenAmountMinValue();
      $max = $osC_Product->getOpenAmountMaxValue();
    ?>
      var amount = $('gift_certificate_amount').value;
      
      if (amount < <?php echo $min; ?> || amount > <?php echo $max; ?>) {
        errors.push('<?php echo sprintf($osC_Language->get('error_message_open_gift_certificate_amount'), $osC_Currencies->format($min), $osC_Currencies->format($max)); ?>');
      }
    <?php 
    } 
    ?>
    
    <?php 
    if ($osC_Product->isEmailGiftCertificate()) {
    ?>
    
      if ($('senders_name').value == '') {
        errors.push('<?php echo $osC_Language->get('error_sender_name_empty'); ?>');
      }
      
      if ($('senders_email').value == '') {
        errors.push('<?php echo $osC_Language->get('error_sender_email_empty'); ?>');
      }
      
      if ($('recipients_name').value == '') {
        errors.push('<?php echo $osC_Language->get('error_recipient_name_empty'); ?>');
      }
      
      if ($('recipients_email').value == '') {
        errors.push('<?php echo $osC_Language->get('error_recipient_email_empty'); ?>');
      }
      
      if ($('message').value == '') {
        errors.push('<?php echo $osC_Language->get('error_message_empty'); ?>');
      }
      
    <?php 
    } 
    ?>
      
      if (errors.length > 0) {
        alert(errors.join('\n'));
      } else {
        $('cart_quantity').submit();
      }
    });
  <?php 
  } 
  ?>
});

</script>
