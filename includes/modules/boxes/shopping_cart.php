<?php
/*
  $Id: shopping_cart.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Boxes_shopping_cart extends osC_Modules {
    var $_title,
        $_code = 'shopping_cart',
        $_author_name = 'osCommerce',
        $_author_www = 'http://www.oscommerce.com',
        $_group = 'boxes';

    function osC_Boxes_shopping_cart() {
      global $osC_Language;

      $this->_title = $osC_Language->get('box_shopping_cart_heading');
    }

    function initialize() {
      global $osC_Language, $osC_ShoppingCart, $osC_Currencies;

      $this->_title_link = osc_href_link(FILENAME_CHECKOUT, null, 'SSL');

      if ($osC_ShoppingCart->hasContents()) {
        $this->_content = '<table border="0" width="100%" cellspacing="0" cellpadding="0">';

        foreach ($osC_ShoppingCart->getProducts() as $products) {
          $this->_content .= '  <tr>' .
                             '    <td align="left" width="20" valign="top">' . $products['quantity'] . '&nbsp;x&nbsp;</td>' .
                             '    <td valign="top">' . osc_link_object(osc_href_link(FILENAME_PRODUCTS, $products['id']), $products['name']) . '</td>' .
                             '  </tr>';
        }

        $this->_content .= '</table>' .
                           '<p style="text-align: right;padding-right:5px">' . $osC_Language->get('box_shopping_cart_subtotal') . ' ' . $osC_Currencies->format($osC_ShoppingCart->getSubTotal()) . '</p>';
      } else {
        $this->_content = $osC_Language->get('box_shopping_cart_empty');
      }
    }
  }
?>
