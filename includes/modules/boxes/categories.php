<?php
/*
  $Id: categories.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Boxes_categories extends osC_Modules {
    var $_title,
        $_code = 'categories',
        $_author_name = 'osCommerce',
        $_author_www = 'http://www.oscommerce.com',
        $_group = 'boxes';

    function osC_Boxes_categories() {
      global $osC_Language;

      $this->_title = $osC_Language->get('box_categories_heading');
    }

    function initialize() {
      global $osC_CategoryTree, $cPath;

      $osC_CategoryTree->reset();
      $osC_CategoryTree->setCategoryPath($cPath, '<b>', '</b>');
      $osC_CategoryTree->setParentGroupString('', '');
      $osC_CategoryTree->setParentString('', '&raquo;');
      $osC_CategoryTree->setChildString('<li>', '</li>');
      $osC_CategoryTree->setSpacerString('&nbsp;', 4);
      $osC_CategoryTree->setShowCategoryProductCount((BOX_CATEGORIES_SHOW_PRODUCT_COUNT == '1') ? true : false);

      $this->_content = $osC_CategoryTree->buildTree();
    }

    function install() {
      global $osC_Database;

      parent::install();

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Show Product Count', 'BOX_CATEGORIES_SHOW_PRODUCT_COUNT', '1', 'Show the amount of products each category has', '6', '0', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('BOX_CATEGORIES_SHOW_PRODUCT_COUNT');
      }

      return $this->_keys;
    }
  }
?>
