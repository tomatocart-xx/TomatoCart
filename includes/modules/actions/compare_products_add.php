<?php
/*
  $Id: compare_products_add.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Actions_compare_products_add {
    function execute() {
      global $osC_Session, $toC_Compare_Products;

      $id = false;
	
      foreach ($_GET as $key => $value) {
        if ( (ereg('^[0-9]+(#?([0-9]+:?[0-9]+)+(;?([0-9]+:?[0-9]+)+)*)*$', $key) || ereg('^[a-zA-Z0-9 -_]*$', $key)) && ($key != $osC_Session->getName()) ) {
          if(ereg('^[0-9]', $key)) {
	          $id = $key;
          }
        } 
      }
	
		  if (isset($id) && is_numeric($id)) {
		    $toC_Compare_Products->addProduct($id);
		  }
      
		  if (basename($_SERVER['SCRIPT_FILENAME']) == FILENAME_PRODUCTS ) {
	        osc_redirect(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), osc_get_all_get_params(array('action'))));
		  } else {
		    osc_redirect(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), osc_get_all_get_params(array($id, 'action'))));
		  }
    }
  }
?>