<?php
/*
  $Id: wishlist_add.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Actions_wishlist_add {
    function execute() {
      global $osC_Session, $toC_Wishlist, $osC_Product;

      if (!isset($osC_Product)) {
        $id = false;

        foreach ($_GET as $key => $value) {
          if ( (ereg('^[0-9]+(#?([0-9]+:?[0-9]+)+(;?([0-9]+:?[0-9]+)+)*)*$', $key) || ereg('^[a-zA-Z0-9 -_]*$', $key)) && ($key != $osC_Session->getName()) ) {
            $id = $key;
          }

          break;
        }

        if (($id !== false) && osC_Product::checkEntry($id)) {
          $osC_Product = new osC_Product($id);
        }
      }

      if (isset($osC_Product)) {
        $toC_Wishlist->add($osC_Product->getID());      
      }

      osc_redirect(osc_href_link(FILENAME_ACCOUNT, 'wishlist'));
    }
  }
?>
