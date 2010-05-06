<?php
/*
  $Id: compare_products.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Boxes_Compare_Products extends osC_Modules {
    var $_title,
        $_code = 'compare_products',
        $_author_name = 'TomatoCart',
        $_author_www = 'http://www.tomatocart.com',
        $_group = 'boxes';

    function osC_Boxes_Compare_Products() {
      global $osC_Language;

      $this->_title = $osC_Language->get('box_compare_products_heading');
    }

    function initialize() {
      global $osC_Language, $toC_Compare_Products;
      
      if ($toC_Compare_Products->hasContents()) {
        $this->_content = '<ul>';
        
        foreach ($toC_Compare_Products->getProducts() as $products_id) {
          $osC_Product = new osC_Product($products_id);
            
          $this->_content .= '<li>' . osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), $products_id . '&' . osc_get_all_get_params(array('action')) . '&action=compare_products_remove'), osc_draw_image_button('button_delete_icon.png', $osC_Language->get('button_delete')), 'style="float: right; margin: 0 3px 1px 3px"') . osc_link_object(osc_href_link(FILENAME_PRODUCTS, $products_id), $osC_Product->getTitle()) . '</li>';
	      }
	      
        $this->_content .= '</ul>';
        $this->_content .= 
          '<p>' .
            '<span style="float: right">' . osc_link_object('javascript:popupWindow(\'' . osc_href_link(FILENAME_PRODUCTS, 'compare_products') . '\', \'popupWindow\', \'scrollbars=yes\');', osc_draw_image_button('small_compare_now.png', $osC_Language->get('button_compare_now'))) . '</span>' .
            osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), osc_get_all_get_params(array('action')) . '&action=compare_products_clear'), osc_draw_image_button('small_clear.png', $osC_Language->get('button_clear'))) . '&nbsp;&nbsp;' .
          '</p>';
      }
    }
  }
?>
