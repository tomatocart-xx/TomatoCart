<?php
/*
  $Id: checkout_one_page.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

$step = 1;
?>

<h1><?php echo $osC_Language->get('checkout')?></h1>
  
<ul id="checkoutForm"> 
  <? if ($osC_Customer->isLoggedOn() === false) { ?>
    <li id="checkoutMethodForm">
      <h3 class="formHeader">
         <?php echo $step++ . '.&nbsp;&nbsp;' . $osC_Language->get('checkout_method') ?>  <span>+</span>
      </h3>
      <div class="formBody"></div>
    </li>
  <? } ?>
  
  <li id="billingInformationForm">
    <h3 class="formHeader">
       <?php echo $step++ . '.&nbsp;&nbsp;' . $osC_Language->get('checkout_billing_information'); ?> <span>+</span>
    </h3>
    <div class="formBody"></div>
  </li>  
  
  <li id="shippingInformationForm">
    <h3 class="formHeader">
       <?php echo $step++ . '.&nbsp;&nbsp;' . $osC_Language->get('checkout_shipping_information');?> 
       <span>+</span>
    </h3>
    <div class="formBody"></div>
  </li>
  
  <li id="shippingMethodForm">
    <h3 class="formHeader">
       <?php echo $step++ . '.&nbsp;&nbsp;' . $osC_Language->get('checkout_shipping_method'); ?> <span>+</span>
    </h3>
    <div class="formBody"></div>
  </li>
  
  <li id="paymentInformationForm">
    <h3 class="formHeader">
       <?php echo $step++ . '.&nbsp;&nbsp;' . $osC_Language->get('checkout_payment_information'); ?> <span>+</span>
    </h3>
    <div class="formBody"></div>
  </li>
  
  <li id="orderConfirmationForm">
    <h3 class="formHeader">
       <?php echo $step++ . '.&nbsp;&nbsp;' . $osC_Language->get('checkout_order_review'); ?> <span>+</span>
    </h3>
    <div class="formBody"></div>
  </li>
</ul>

<script type="text/javascript">
  window.addEvent('domready', function() {
    checkout = new Checkout({
      isLoggedOn: <?php echo ($osC_Customer->isLoggedOn() === true) ? 'true' : 'false';?>,
      sessionName: '<?php echo $osC_Session->getName(); ?>',
      sessionId: '<?php echo $osC_Session->getID(); ?>',
      isVirtualCart: <?php echo ($osC_ShoppingCart->isVirtualCart() ? 'true' : 'false'); ?>,
      isTotalZero: <?php echo ($osC_ShoppingCart->isTotalZero() ? 'true' : 'false'); ?>
    });
  });  
</script>