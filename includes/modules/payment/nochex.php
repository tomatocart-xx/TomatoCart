<?php
/*
  $Id: nochex.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Payment_nochex extends osC_Payment {
    var $_title,
        $_code = 'nochex',
        $_author_name = 'osCommerce',
        $_status = false,
        $_sort_order;

    function osC_Payment_nochex() {
      global $osC_Language;

      $this->_title = $osC_Language->get('payment_nochex_title');
      $this->_description = $osC_Language->get('payment_nochex_description');
      $this->_status = (defined('MODULE_PAYMENT_NOCHEX_STATUS') && (MODULE_PAYMENT_NOCHEX_STATUS == 'True') ? true : false);
      $this->_sort_order = (defined('MODULE_PAYMENT_NOCHEX_SORT_ORDER') ? MODULE_PAYMENT_NOCHEX_SORT_ORDER : null);

      if (defined('MODULE_PAYMENT_NOCHEX_STATUS')) {
        $this->initialize();
      }
    }

    function initialize() {
      global $order;

      if ((int)MODULE_PAYMENT_NOCHEX_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_NOCHEX_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();

      $this->form_action_url = 'https://www.nochex.com/nochex.dll/checkout';
    }

    function update_status() {
      global $osC_Database, $order;

      if ( ($this->_status === true) && ((int)MODULE_PAYMENT_NOCHEX_ZONE > 0) ) {
        $check_flag = false;

        $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
        $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
        $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_NOCHEX_ZONE);
        $Qcheck->bindInt(':zone_country_id', $order->billing['country']['id']);
        $Qcheck->execute();

        while ($Qcheck->next()) {
          if ($Qcheck->valueInt('zone_id') < 1) {
            $check_flag = true;
            break;
          } elseif ($Qcheck->valueInt('zone_id') == $order->billing['zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->_status = false;
        }
      }
    }

    function selection() {
      return array('id' => $this->_code,
                   'module' => $this->_title);
    }

    function pre_confirmation_check() {
      return false;
    }

    function confirmation() {
      return false;
    }

    function process_button() {
      global $order, $osC_Currencies, $osC_Customer;

      $process_button_string = osc_draw_hidden_field('cmd', '_xclick') .
                               osc_draw_hidden_field('email', MODULE_PAYMENT_NOCHEX_ID) .
                               osc_draw_hidden_field('amount', number_format($order->info['total'] * $osC_Currencies->currencies['GBP']['value'], $osC_Currencies->currencies['GBP']['decimal_places'])) .
                               osc_draw_hidden_field('ordernumber', $osC_Customer->getID() . '-' . date('Ymdhis')) .
                               osc_draw_hidden_field('returnurl', osc_href_link(FILENAME_CHECKOUT, 'process', 'SSL')) .
                               osc_draw_hidden_field('cancel_return', osc_href_link(FILENAME_CHECKOUT, 'payment', 'SSL'));

      return $process_button_string;
    }

    function before_process() {
      return false;
    }

    function after_process() {
      return false;
    }

    function output_error() {
      return false;
    }

    function check() {
      if (!isset($this->_check)) {
        $this->_check = defined('MODULE_PAYMENT_NOCHEX_STATUS');
      }

      return $this->_check;
    }

    function install() {
      global $osC_Database;

      parent::install();

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable NOCHEX Module', 'MODULE_PAYMENT_NOCHEX_STATUS', 'True', 'Do you want to accept NOCHEX payments?', '6', '3', 'osc_cfg_set_boolean_value(array(\'True\', \'False\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('E-Mail Address', 'MODULE_PAYMENT_NOCHEX_ID', 'you@yourbuisness.com', 'The e-mail address to use for the NOCHEX service', '6', '4', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_NOCHEX_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_NOCHEX_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'osc_cfg_use_get_zone_class_title', 'osc_cfg_set_zone_classes_pull_down_menu', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_NOCHEX_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('MODULE_PAYMENT_NOCHEX_STATUS',
                             'MODULE_PAYMENT_NOCHEX_ID',
                             'MODULE_PAYMENT_NOCHEX_ZONE',
                             'MODULE_PAYMENT_NOCHEX_ORDER_STATUS_ID',
                             'MODULE_PAYMENT_NOCHEX_SORT_ORDER');
      }

      return $this->_keys;
    }
  }
?>
