<?php
/*
  $Id: authorizenet_cc.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

/**
 * The administration side of the Authorize.net Credit Card payment module
 */

  class osC_Payment_authorizenet_cc extends osC_Payment_Admin {

/**
 * The administrative title of the payment module
 *
 * @var string
 * @access private
 */

    var $_title;

/**
 * The code of the payment module
 *
 * @var string
 * @access private
 */

    var $_code = 'authorizenet_cc';

/**
 * The developers name
 *
 * @var string
 * @access private
 */

    var $_author_name = 'osCommerce';

/**
 * The developers address
 *
 * @var string
 * @access private
 */

  var $_author_www = 'http://www.oscommerce.com';

/**
 * The status of the module
 *
 * @var boolean
 * @access private
 */

    var $_status = false;

/**
 * Constructor
 */

    function osC_Payment_authorizenet_cc() {
      global $osC_Language;

      $this->_title = $osC_Language->get('payment_authorizenet_cc_title');
      $this->_description = $osC_Language->get('payment_authorizenet_cc_description');
      $this->_method_title = $osC_Language->get('payment_authorizenet_cc_method_title');
      $this->_status = (defined('MODULE_PAYMENT_AUTHORIZENET_CC_STATUS') && (MODULE_PAYMENT_AUTHORIZENET_CC_STATUS == '1') ? true : false);
      $this->_sort_order = (defined('MODULE_PAYMENT_AUTHORIZENET_CC_SORT_ORDER') ? MODULE_PAYMENT_AUTHORIZENET_CC_SORT_ORDER : null);

      if (defined('MODULE_PAYMENT_AUTHORIZENET_CC_TRANSACTION_SERVER')) {
        switch (MODULE_PAYMENT_AUTHORIZENET_CC_TRANSACTION_SERVER) {
          case 'production':
            $this->_gateway_url = 'https://secure.authorize.net:443/gateway/transact.dll';
            break;

          case 'certification':
            $this->_gateway_url = 'https://certification.authorize.net:443/gateway/transact.dll';
            break;

          default:
            $this->_gateway_url = 'https://test.authorize.net:443/gateway/transact.dll';
            break;
        }
      }
    }

/**
 * Checks to see if the module has been installed
 *
 * @access public
 * @return boolean
 */

    function isInstalled() {
      return (bool)defined('MODULE_PAYMENT_AUTHORIZENET_CC_STATUS');
    }

/**
 * Installs the module
 *
 * @access public
 * @see osC_Payment_Admin::install()
 */

    function install() {
      global $osC_Database;

      parent::install();

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Enable Authorize.net Credit Card Module', 'MODULE_PAYMENT_AUTHORIZENET_CC_STATUS', '-1', 'Do you want to accept Authorize.net credit card payments?', '6', '0', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Login ID', 'MODULE_PAYMENT_AUTHORIZENET_CC_LOGIN_ID', '', 'The Authorize.net account login ID.', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Transaction Key', 'MODULE_PAYMENT_AUTHORIZENET_CC_TRANSACTION_KEY', '', 'The transaction key obtained from the Merchant Interface.', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('MD5 Hash Signature', 'MODULE_PAYMENT_AUTHORIZENET_CC_MD5_HASH', '', 'The MD5 hash value to verify the results of a transaction.', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Credit Cards', 'MODULE_PAYMENT_AUTHORIZENET_CC_ACCEPTED_TYPES', '', 'Accept these credit card types for this payment method.', '6', '0', 'osc_cfg_set_credit_cards_checkbox_field', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Verify With CVC', 'MODULE_PAYMENT_AUTHORIZENET_CC_VERIFY_WITH_CVC', '1', 'Verify the credit card with the billing address with the Credit Card Verification Checknumber (CVC)?', '6', '0', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Server', 'MODULE_PAYMENT_AUTHORIZENET_CC_TRANSACTION_SERVER', 'test', 'Perform transactions on the production server or on the testing server.', '6', '0', 'osc_cfg_set_boolean_value(array(\'production\', \'certification\', \'test\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Transaction Mode', 'MODULE_PAYMENT_AUTHORIZENET_CC_TRANSACTION_TEST_MODE', '-1', 'Perform test transactions only.', '6', '0', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_AUTHORIZENET_CC_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '0', 'osc_cfg_use_get_zone_class_title', 'osc_cfg_set_zone_classes_pull_down_menu', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_AUTHORIZENET_CC_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_AUTHORIZENET_CC_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0' , now())");
    }

/**
 * Return the configuration parameter keys in an array
 *
 * @access public
 * @return array
 */

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('MODULE_PAYMENT_AUTHORIZENET_CC_STATUS',
                             'MODULE_PAYMENT_AUTHORIZENET_CC_LOGIN_ID',
                             'MODULE_PAYMENT_AUTHORIZENET_CC_TRANSACTION_KEY',
                             'MODULE_PAYMENT_AUTHORIZENET_CC_MD5_HASH',
                             'MODULE_PAYMENT_AUTHORIZENET_CC_ACCEPTED_TYPES',
                             'MODULE_PAYMENT_AUTHORIZENET_CC_VERIFY_WITH_CVC',
                             'MODULE_PAYMENT_AUTHORIZENET_CC_TRANSACTION_SERVER',
                             'MODULE_PAYMENT_AUTHORIZENET_CC_TRANSACTION_TEST_MODE',
                             'MODULE_PAYMENT_AUTHORIZENET_CC_ZONE',
                             'MODULE_PAYMENT_AUTHORIZENET_CC_ORDER_STATUS_ID',
                             'MODULE_PAYMENT_AUTHORIZENET_CC_SORT_ORDER');
      }

      return $this->_keys;
    }

/**
 * Returns the available post transaction actions in an array
 *
 * @access public
 * @param $history An array of transaction actions already processed
 * @return array
 */

    function getPostTransactionActions($history) {
      $actions = array();

      if ( (in_array('3', $history) === false) && (in_array('2', $history) === false) ) {
        $actions[3] = 'approveTransaction';
      }

      if (in_array('2', $history) === false) {
        $actions[2] = 'cancelTransaction';
      }

      return $actions;
    }

/**
 * Approves the transaction at the gateway server
 *
 * @access public
 * @param $id The ID of the order
 */

    function approveTransaction($id) {
      global $osC_Database;

      $Qorder = $osC_Database->query('select transaction_return_value from :table_orders_transactions_history where orders_id = :orders_id and transaction_code = 1 order by date_added limit 1');
      $Qorder->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
      $Qorder->bindInt(':orders_id', $id);
      $Qorder->execute();

      if ($Qorder->numberOfRows() === 1) {
        $inquiry_regs = preg_split("/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/", $Qorder->value('transaction_return_value'));
        foreach ($inquiry_regs as $key => $value) {
          $inquiry_regs[$key] = substr($value, 1, -1); // remove double quotes
        }

        $params = array('x_version' => '3.1',
                        'x_delim_data' => 'TRUE',
                        'x_delim_char' => ',',
                        'x_encap_char' => '"',
                        'x_type' => 'PRIOR_AUTH_CAPTURE',
                        'x_login' => MODULE_PAYMENT_AUTHORIZENET_CC_LOGIN_ID,
                        'x_tran_key' => MODULE_PAYMENT_AUTHORIZENET_CC_TRANSACTION_KEY,
                        'x_trans_id' => $inquiry_regs[6],
                        'x_amount' => $inquiry_regs[9]);

        $post_string = '';

        foreach ($params as $key => $value) {
          $post_string .= $key . '=' . urlencode(trim($value)) . '&';
        }

        $post_string = substr($post_string, 0, -1);

        $result = osC_Payment::sendTransactionToGateway($this->_gateway_url, $post_string);

        if (empty($result) === false) {
          $regs = preg_split("/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/", $result);
          foreach ($regs as $key => $value) {
            $regs[$key] = substr($value, 1, -1); // remove double quotes
          }

          $transaction_return_status = $regs[0];

          if ($transaction_return_status == '1') {
            if (!osc_empty(MODULE_PAYMENT_AUTHORIZENET_CC_MD5_HASH)) {
              if ($regs[37] != strtoupper(md5(MODULE_PAYMENT_AUTHORIZENET_CC_MD5_HASH . MODULE_PAYMENT_AUTHORIZENET_CC_LOGIN_ID . $inquiry_regs[6] . $inquiry_regs[9]))) {
                $transaction_return_status = '0';
              }
            }
          } else {
            $transaction_return_status = '0';
          }

          $Qtransaction = $osC_Database->query('insert into :table_orders_transactions_history (orders_id, transaction_code, transaction_return_value, transaction_return_status, date_added) values (:orders_id, :transaction_code, :transaction_return_value, :transaction_return_status, now())');
          $Qtransaction->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
          $Qtransaction->bindInt(':orders_id', $id);
          $Qtransaction->bindInt(':transaction_code', 3);
          $Qtransaction->bindValue(':transaction_return_value', $result);
          $Qtransaction->bindInt(':transaction_return_status', $transaction_return_status);
          $Qtransaction->execute();
        }
      }
    }

/**
 * Cancels the transaction at the gateway server
 *
 * @access public
 * @param $id The ID of the order
 */

    function cancelTransaction($id) {
      global $osC_Database;

      $Qorder = $osC_Database->query('select transaction_return_value from :table_orders_transactions_history where orders_id = :orders_id and transaction_code = 1 order by date_added limit 1');
      $Qorder->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
      $Qorder->bindInt(':orders_id', $id);
      $Qorder->execute();

      if ($Qorder->numberOfRows() === 1) {
        $inquiry_regs = preg_split("/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/", $Qorder->value('transaction_return_value'));
        foreach ($inquiry_regs as $key => $value) {
          $inquiry_regs[$key] = substr($value, 1, -1); // remove double quotes
        }

        $params = array('x_version' => '3.1',
                        'x_delim_data' => 'TRUE',
                        'x_delim_char' => ',',
                        'x_encap_char' => '"',
                        'x_type' => 'VOID',
                        'x_login' => MODULE_PAYMENT_AUTHORIZENET_CC_LOGIN_ID,
                        'x_tran_key' => MODULE_PAYMENT_AUTHORIZENET_CC_TRANSACTION_KEY,
                        'x_trans_id' => $inquiry_regs[6],
                        'x_amount' => $inquiry_regs[9]);

        $post_string = '';

        foreach ($params as $key => $value) {
          $post_string .= $key . '=' . urlencode(trim($value)) . '&';
        }

        $post_string = substr($post_string, 0, -1);

        $result = osC_Payment::sendTransactionToGateway($this->_gateway_url, $post_string);

        if (empty($result) === false) {
          $regs = preg_split("/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/", $result);
          foreach ($regs as $key => $value) {
            $regs[$key] = substr($value, 1, -1); // remove double quotes
          }

          $transaction_return_status = $regs[0];

          if ($transaction_return_status == '1') {
            if (!osc_empty(MODULE_PAYMENT_AUTHORIZENET_CC_MD5_HASH)) {
              if ($regs[37] != strtoupper(md5(MODULE_PAYMENT_AUTHORIZENET_CC_MD5_HASH . MODULE_PAYMENT_AUTHORIZENET_CC_LOGIN_ID . $inquiry_regs[6] . $inquiry_regs[9]))) {
                $transaction_return_status = '0';
              }
            }
          } else {
            $transaction_return_status = '0';
          }

          $Qtransaction = $osC_Database->query('insert into :table_orders_transactions_history (orders_id, transaction_code, transaction_return_value, transaction_return_status, date_added) values (:orders_id, :transaction_code, :transaction_return_value, :transaction_return_status, now())');
          $Qtransaction->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
          $Qtransaction->bindInt(':orders_id', $id);
          $Qtransaction->bindInt(':transaction_code', 2);
          $Qtransaction->bindValue(':transaction_return_value', $result);
          $Qtransaction->bindInt(':transaction_return_status', $transaction_return_status);
          $Qtransaction->execute();
        }
      }
    }
  }
?>
