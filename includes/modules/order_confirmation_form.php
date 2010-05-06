<?php
/*
  $Id: order_confirmation_form.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

<div class="moduleBox">
  <div class="content">
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="50%" valign="top">

<?php
  if ($osC_ShoppingCart->hasShippingAddress()) {
?>
          <p><?php echo '<b>' . $osC_Language->get('order_delivery_address_title') . '</b> '; ?></p>
          <p><?php echo osC_Address::format($osC_ShoppingCart->getShippingAddress(), '<br />'); ?></p>

<?php
    if ($osC_ShoppingCart->hasShippingMethod()) {
?>

          <p><?php echo '<b>' . $osC_Language->get('order_shipping_method_title') . '</b> '; ?></p>
          <p><?php echo $osC_ShoppingCart->getShippingMethod('title'); ?></p>

<?php
    }
  }
?>
        </td>
        <td valign="top">
          <p><?php echo '<b>' . $osC_Language->get('order_billing_address_title') . '</b> '; ?></p>
          <p><?php echo osC_Address::format($osC_ShoppingCart->getBillingAddress(), '<br />'); ?></p>

          <p><?php echo '<b>' . $osC_Language->get('order_payment_method_title') . '</b> '; ?></p>
          <p><?php echo implode(', ', $osC_ShoppingCart->getCartBillingMethods()); ?></p>
        </td>
      </tr>
      <tr>
        <td width="100%" colspan="2" valign="top">
          <div style="border: 1px; border-style: solid; border-color: #CCCCCC; background-color: #FBFBFB; padding: 5px;">
            <table border="0" width="100%" cellspacing="0" cellpadding="2">

<?php
  if ($osC_ShoppingCart->numberOfTaxGroups() > 1) {
?>

              <tr>
                <td colspan="2"><?php echo '<b>' . $osC_Language->get('order_products_title') . '</b> '; ?></td>
                <td align="right"><b><?php echo $osC_Language->get('order_tax_title'); ?></b></td>
                <td align="right"><b><?php echo $osC_Language->get('order_total_title'); ?></b></td>
              </tr>

<?php
  } else {
?>

              <tr>
                <td colspan="3"><?php echo '<b>' . $osC_Language->get('order_products_title') . '</b> '; ?></td>
              </tr>

<?php
  }

  foreach ($osC_ShoppingCart->getProducts() as $products) {
    echo '              <tr>' . "\n" .
         '                <td align="right" valign="top" width="30">' . $products['quantity'] . '&nbsp;x&nbsp;</td>' . "\n" .
         '                <td valign="top">' . $products['name'];

    if ( (STOCK_CHECK == '1') && !$osC_ShoppingCart->isInStock($products['id']) ) {
      echo '<span class="markProductOutOfStock">' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . '</span>';
    }
    
    if ($products['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) {
      echo '<br /><nobr><small>&nbsp;<i> - ' . $osC_Language->get('senders_name') . ': ' . $products['gc_data']['recipients_name'] . '</i></small></nobr>';
      
      if ($products['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
        echo '<br /><nobr><small>&nbsp;<i> - ' . $osC_Language->get('senders_email')  . ': ' . $products['gc_data']['recipients_email'] . '</i></small></nobr>';
      }
      
      echo '<br /><nobr><small>&nbsp;<i> - ' . $osC_Language->get('recipients_name') . ': ' . $products['gc_data']['recipients_name'] . '</i></small></nobr>';
      
      if ($products['gc_data']['type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
        echo '<br /><nobr><small>&nbsp;<i> - ' . $osC_Language->get('recipients_email')  . ': ' . $products['gc_data']['recipients_email'] . '</i></small></nobr>';
      }
      
      echo '<br /><nobr><small>&nbsp;<i> - ' . $osC_Language->get('message')  . ': ' . $products['gc_data']['message'] . '</i></small></nobr>';
    }
    
    if ( (isset($products['variants'])) && (sizeof($products['variants']) > 0) ) {
      foreach ($products['variants'] as $variants) {
        echo '<br /><nobr><small>&nbsp;<i> - ' . $variants['groups_name'] . ': ' . $variants['values_name'] . '</i></small></nobr>';
      }
    }

    echo '</td>' . "\n";

    if ($osC_ShoppingCart->numberOfTaxGroups() > 1) {
      //echo '                <td valign="top" align="right">' . osC_Tax::displayTaxRateValue($products['tax']) . '</td>' . "\n";
    }

    echo '                <td align="right" valign="top">' . $osC_Currencies->displayPrice($products['final_price'], $products['tax_class_id'], $products['quantity']) . '</td>' . "\n" .
         '              </tr>' . "\n";
  }
?>

            </table>

            <table border="0" width="100%" cellspacing="0" cellpadding="2">

<?php
// HPDL
//  if ($osC_OrderTotal->hasActive()) {
//    foreach ($osC_OrderTotal->getResult() as $module) {
    foreach ($osC_ShoppingCart->getOrderTotals() as $module) {
      echo '              <tr>' . "\n" .
           '                <td align="right">' . $module['title'] . '</td>' . "\n" .
           '                <td align="right">' . $module['text'] . '</td>' . "\n" .
           '              </tr>';
    }
//  }
?>

            </table>
          </div>
        </td>      
      </tr>
    </table>
  </div>
</div>

<?php
  if ($osC_Payment->hasActive()) {
    if ($confirmation = $osC_Payment->confirmation()) {
?>

<div class="moduleBox">
  <h6><?php echo $osC_Language->get('order_payment_information_title'); ?></h6>

  <div class="content">
    <p><?php echo $confirmation['title']; ?></p>

<?php
      if (isset($confirmation['fields'])) {
?>

    <table border="0" cellspacing="0" cellpadding="2">

<?php
        for ($i=0, $n=sizeof($confirmation['fields']); $i<$n; $i++) {
?>

      <tr>
        <td width="10">&nbsp;</td>
        <td><?php echo $confirmation['fields'][$i]['title']; ?></td>
        <td width="10">&nbsp;</td>
        <td><?php echo $confirmation['fields'][$i]['field']; ?></td>
      </tr>

<?php
        }
?>

    </table>

<?php
      }

      if (isset($confirmation['text'])) {
?>

    <p><?php echo $confirmation['text']; ?></p>

<?php
      }
?>

  </div>
</div>

<?php
    }
  }

  if (isset($_SESSION['comments']) && !empty($_SESSION['comments'])) {
?>

<div class="moduleBox">
  <h6><?php echo '<b>' . $osC_Language->get('order_comments_title') . '</b> '; ?></h6>

  <div class="content">
    <?php echo nl2br(osc_output_string_protected($_SESSION['comments'])) . osc_draw_hidden_field('comments', $_SESSION['comments']); ?>
  </div>
</div>
<?php
  }
?>

<?php
  global $osC_OrderTotal_coupon;
  if(isset($osC_OrderTotal_coupon) && is_object($osC_OrderTotal_coupon) && $osC_OrderTotal_coupon->isEnabled()){
?>
<div class="moduleBox">
  <h6><?php echo '<b>' . $osC_Language->get('coupons_redeem_heading') . '</b>'; ?></h6>
  <div class="content" id="couponRedeem">
<?php
    if(!$osC_ShoppingCart->hasCoupon()){
?>
<?php echo '<b>' . $osC_Language->get('coupons_redeem_information_title') . '</b>'; ?><br/>
    <div>
      <br/>
      <?php echo '<b>' . $osC_Language->get('fields_coupons_redeem_code') . '</b>'; ?>
      <?php echo osc_draw_input_field('coupon_redeem_code'); ?>&nbsp;&nbsp;
      <?php echo osc_draw_image_submit_button('button_redeem.gif', $osC_Language->get('button_coupon_redeem'), 'id="btnRedeemCoupon" style="vertical-align: middle"'); ?>
    </div>
<?php
    }else{
?>
    <?php echo '<b>' . $osC_Language->get('coupons_redeem_information_title') . '</b>'; ?><br/>
    <div>
      <br/>
      <?php echo '<b>' . $osC_Language->get('fields_coupons_redeem_code') . '</b>'; ?>
      <?php echo $osC_ShoppingCart->getCouponCode(); ?>&nbsp;&nbsp;
      <?php echo osc_draw_image_submit_button('small_delete.gif', $osC_Language->get('button_delete'), 'id="btnDeleteCoupon" style="vertical-align: middle"'); ?>
    </div>
<?php
    }
?>
  </div>
</div>
<?php
  }
?>

<?php
  global $osC_OrderTotal_gift_certificate;
  if(isset($osC_OrderTotal_gift_certificate) && is_object($osC_OrderTotal_gift_certificate) && $osC_OrderTotal_gift_certificate->isEnabled()){
?>
<div class="moduleBox">
  <h6><?php echo '<b>' . $osC_Language->get('gift_certificates_redeem_heading') . '</b>'; ?></h6>
  <div class="content">
<?php echo '<b>' . $osC_Language->get('gift_certificates_redeem_information_title') . '</b>'; ?><br/>
<?php
    if  ($osC_ShoppingCart->hasGiftCertificate()){
      foreach ($osC_ShoppingCart->getGiftCertificateCodes() as $gift_certificate) {
        echo '<p id="' . $gift_certificate . '">' . $gift_certificate . '&nbsp;[' . $osC_Currencies->format($osC_ShoppingCart->getGiftCertificateRedeemAmount($gift_certificate)) . ']' . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . osc_draw_image_submit_button('small_delete.gif', $osC_Language->get('button_delete'), 'class="btnDeleteGiftCertificate" style="vertical-align: middle"') . '</p>';
      }
    }
?>
    <div>
      <br/>
      <?php echo '<b>' . $osC_Language->get('fields_gift_certificates_redeem_code') . '</b>'; ?>
      <?php echo osc_draw_input_field('gift_certificate_redeem_code', null, 'id="gift_certificate_code"'); ?>&nbsp;&nbsp;
      <?php echo osc_draw_image_submit_button('button_redeem.gif', $osC_Language->get('button_gift_certificate_redeem'), 'id="btnRedeemGiftCertificate" style="vertical-align: middle"'); ?>
    </div>
  </div>
</div>
<?php
  }
?>
<div class="submitFormButtons" style="text-align: right;">

<?php
  if ($osC_Payment->hasActionURL()) {
    $form_action_url = $osC_Payment->getActionURL();
  } else {
    $form_action_url = osc_href_link(FILENAME_CHECKOUT, 'process', 'SSL');
  }

  echo '<form name="checkout_confirmation" action="' . $form_action_url . '" method="post">';

  if ($osC_Payment->hasActive()) {
    echo $osC_Payment->process_button();
  }

  echo osc_draw_image_submit_button('button_confirm_order.gif', $osC_Language->get('button_confirm_order')) . '</form>';
?>

</div>