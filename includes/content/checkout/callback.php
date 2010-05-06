<?php
/*
  $Id: callback.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Checkout_Callback extends osC_Template {

/* Private variables */

    var $_module = 'callback';

/* Class constructor */

    function osC_Checkout_Callback() {
      if (isset($_GET['module']) && (empty($_GET['module']) === false)) {
        if (file_exists('includes/modules/payment/' . $_GET['module'] . '.php')) {
          include('includes/classes/order.php');

          include('includes/classes/payment.php');
          include('includes/modules/payment/' . $_GET['module'] . '.php');

          $module = 'osC_Payment_' . $_GET['module'];
          $module = new $module();
          $module->callback();
        }
      }

      exit;
    }
  }
?>
