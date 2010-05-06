<?php
/*
  $Id: usps.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Shipping_usps extends osC_Shipping_Admin {
    var $icon;

    var $_title,
        $_code = 'usps',
        $_author_name = 'osCommerce',
        $_author_www = 'http://www.oscommerce.com',
        $_status = false,
        $_sort_order;

// class constructor
    function osC_Shipping_usps() {
      global $osC_Language;

      $this->icon = DIR_WS_IMAGES . 'icons/shipping_usps.gif';

      $this->_title = $osC_Language->get('shipping_usps_title');
      $this->_description = $osC_Language->get('shipping_usps_description');
      $this->_status = (defined('MODULE_SHIPPING_USPS_STATUS') && (MODULE_SHIPPING_USPS_STATUS == 'True') ? true : false);
      $this->_sort_order = (defined('MODULE_SHIPPING_USPS_SORT_ORDER') ? MODULE_SHIPPING_USPS_SORT_ORDER : null);
    }

// class methods
    function isInstalled() {
      return (bool)defined('MODULE_SHIPPING_USPS_STATUS');
    }

    function install() {
      global $osC_Database;

      parent::install();

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable USPS Shipping', 'MODULE_SHIPPING_USPS_STATUS', 'True', 'Do you want to offer USPS shipping?', '6', '0', 'osc_cfg_set_boolean_value(array(\'True\', \'False\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Enter the USPS User ID', 'MODULE_SHIPPING_USPS_USERID', 'NONE', 'Enter the USPS USERID assigned to you.', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Enter the USPS Password', 'MODULE_SHIPPING_USPS_PASSWORD', 'NONE', 'See USERID, above.', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Which server to use', 'MODULE_SHIPPING_USPS_SERVER', 'production', 'An account at USPS is needed to use the Production server', '6', '0', 'osc_cfg_set_boolean_value(array(\'test\', \'production\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Handling Fee', 'MODULE_SHIPPING_USPS_HANDLING', '0', 'Handling fee for this shipping method.', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_SHIPPING_USPS_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'osc_cfg_use_get_tax_class_title', 'osc_cfg_set_tax_classes_pull_down_menu', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Shipping Zone', 'MODULE_SHIPPING_USPS_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '0', 'osc_cfg_use_get_zone_class_title', 'osc_cfg_set_zone_classes_pull_down_menu', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SHIPPING_USPS_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('MODULE_SHIPPING_USPS_STATUS',
                             'MODULE_SHIPPING_USPS_USERID',
                             'MODULE_SHIPPING_USPS_PASSWORD',
                             'MODULE_SHIPPING_USPS_SERVER',
                             'MODULE_SHIPPING_USPS_HANDLING',
                             'MODULE_SHIPPING_USPS_TAX_CLASS',
                             'MODULE_SHIPPING_USPS_ZONE',
                             'MODULE_SHIPPING_USPS_SORT_ORDER');
      }

      return $this->_keys;
    }
  }
?>
