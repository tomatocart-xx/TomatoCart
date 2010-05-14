<?php
/*
  $Id: images.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Products_Images extends osC_Template {

/* Private variables */

    var $_module = 'images',
        $_group = 'products',
        $_page_title,
        $_page_contents = 'images.php',
        $_page_image = 'table_background_list.gif',
        $_has_header = false,
        $_has_footer = false,
        $_has_box_modules = false,
        $_has_content_modules = false,
        $_show_debug_messages = false;

/* Class constructor */

    function osC_Products_Images() {
      global $osC_Session, $osC_Language, $osC_Product;

      if (empty($_GET) === false) {
        $id = false;

        $counter = 0;
        foreach ($_GET as $key => $value) {
          $counter++;

          if ($counter < 2) {
            continue;
          }

          if ( (ereg('^[0-9]+(#?([0-9]+:?[0-9]+)+(;?([0-9]+:?[0-9]+)+)*)*$', $key) || ereg('^[a-zA-Z0-9 -_]*$', $key)) && ($key != $osC_Session->getName()) ) {
            $id = $key;
          }

          break;
        }

        if (($id !== false) && osC_Product::checkEntry($id)) {
          $osC_Product = new osC_Product($id);

          $this->addPageTags('keywords', $osC_Product->getTitle());
          $this->addPageTags('keywords', $osC_Product->getSKU());

          if ($osC_Product->hasTags()) {
            $this->addPageTags('keywords', $osC_Product->getTags());
          }

          $this->addJavascriptFilename('ext/prototype/prototype.js');
          $this->addJavascriptFilename('ext/scriptaculous/scriptaculous.js');

          $this->_page_title = $osC_Product->getTitle();
        } else {
          $this->_page_title = $osC_Language->get('product_not_found_heading');
          $this->_page_contents = 'info_not_found.php';
        }
      } else {
        $this->_page_title = $osC_Language->get('product_not_found_heading');
        $this->_page_contents = 'info_not_found.php';
      }
    }
  }
?>
