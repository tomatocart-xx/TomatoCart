<?php
/*
  $Id: checkout.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('includes/classes/address_book.php');

  class osC_Checkout_Checkout extends osC_Template {

/* Private variables */

    var $_module = 'checkout',
        $_group = 'checkout',
        $_page_title,
        $_page_contents = 'checkout.php',
        $_page_image = 'table_background_delivery.gif';

/* Class constructor */

    function osC_Checkout_Checkout() {
      global $osC_ShoppingCart;

      if ($osC_ShoppingCart->hasContents() === false) {
        osc_redirect(osc_href_link(FILENAME_CHECKOUT, null, 'SSL'));
      }                
      
      $this->addJavascriptFilename('includes/javascript/checkout.js');
    } 
  }
?>
