<?php
/*
  $Id: manufacturers.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Boxes_manufacturers extends osC_Modules {
    var $_title,
        $_code = 'manufacturers',
        $_author_name = 'osCommerce',
        $_author_www = 'http://www.oscommerce.com',
        $_group = 'boxes';

    function osC_Boxes_manufacturers() {
      global $osC_Language;

      $this->_title = $osC_Language->get('box_manufacturers_heading');
    }

    function initialize() {
      global $osC_Database, $osC_Language;

      $Qmanufacturers = $osC_Database->query('select manufacturers_id as id, manufacturers_name as text, manufacturers_image as image from :table_manufacturers order by manufacturers_name');
      $Qmanufacturers->bindTable(':table_manufacturers', TABLE_MANUFACTURERS);
      $Qmanufacturers->setCache('manufacturers');
      $Qmanufacturers->execute();
      
      if (BOX_MANUFACTURERS_LIST_TYPE == 'ComboBox') {
        $manufacturers_array = array(array('id' => '', 'text' => $osC_Language->get('pull_down_default')));
  
        while ($Qmanufacturers->next()) {
          $manufacturers_array[] = $Qmanufacturers->toArray();
        }
  
        $this->_content = '<form name="manufacturers" action="' . osc_href_link(FILENAME_DEFAULT, null, 'NONSSL', false) . '" method="get">' .
                          osc_draw_pull_down_menu('manufacturers', $manufacturers_array, null, 'onchange="this.form.submit();" size="' . BOX_MANUFACTURERS_LIST_SIZE . '" style="width: 99%"') . osc_draw_hidden_session_id_field() .
                          '</form>';
      } else {
        $this->_content = '<ul>';
        
        while ($Qmanufacturers->next()) {
          $this->_content .= '<li>' . osc_link_object(osc_href_link(FILENAME_DEFAULT, 'manufacturers=' . $Qmanufacturers->valueInt('id')), osc_image("images/manufacturers/" . $Qmanufacturers->value('image'), $Qmanufacturers->value('text'))) . '</li>';
        }
        
        $this->_content .= '</ul>';
      }
      
      $Qmanufacturers->freeResult();
    }

    function install() {
      global $osC_Database;

      parent::install();

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) values ('Manufacturers List Type', 'BOX_MANUFACTURERS_LIST_TYPE', 'Image List', 'The type of the manufacturers list(ComboBox, Image List).', '6', '0', 'osc_cfg_set_boolean_value(array(\'ComboBox\', \'Image List\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Manufacturers List Size', 'BOX_MANUFACTURERS_LIST_SIZE', '1', 'The size of the manufacturers pull down menu listing.', '6', '0', now())");
      
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('BOX_MANUFACTURERS_LIST_TYPE', 'BOX_MANUFACTURERS_LIST_SIZE');
      }

      return $this->_keys;
    }
  }
?>
