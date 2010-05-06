<?php
/*
  $Id: authorizenet_echeck.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Payment_authorizenet_echeck extends osC_Payment {
    var $_title,
        $_code = 'authorizenet_echeck',
        $_status = false,
        $_sort_order,
        $_order_id,
        $_transaction_response;

    function osC_Payment_authorizenet_echeck() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;

      $this->_title = $osC_Language->get('payment_authorizenet_echeck_title');
      $this->_method_title = $osC_Language->get('payment_authorizenet_echeck_method_title');
      $this->_status = (MODULE_PAYMENT_AUTHORIZENET_ECHECK_STATUS == '1') ? true : false;
      $this->_sort_order = MODULE_PAYMENT_AUTHORIZENET_ECHECK_SORT_ORDER;

      switch (MODULE_PAYMENT_AUTHORIZENET_ECHECK_TRANSACTION_SERVER) {
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

      if ($this->_status === true) {
        if ((int)MODULE_PAYMENT_AUTHORIZENET_ECHECK_ORDER_STATUS_ID > 0) {
          $this->order_status = MODULE_PAYMENT_AUTHORIZENET_ECHECK_ORDER_STATUS_ID;
        }

        if ((int)MODULE_PAYMENT_AUTHORIZENET_ECHECK_ZONE > 0) {
          $check_flag = false;

          $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
          $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
          $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_AUTHORIZENET_ECHECK_ZONE);
          $Qcheck->bindInt(':zone_country_id', $osC_ShoppingCart->getBillingAddress('country_id'));
          $Qcheck->execute();

          while ($Qcheck->next()) {
            if ($Qcheck->valueInt('zone_id') < 1) {
              $check_flag = true;
              break;
              } elseif ($Qcheck->valueInt('zone_id') == $osC_ShoppingCart->getBillingAddress('zone_id')) {
              $check_flag = true;
              break;
            }
          }

          if ($check_flag == false) {
            $this->_status = false;
          }
        }
      }
    }

    function getJavascriptBlock() {
      global $osC_Language;

      $js = '  if (payment_value == "' . $this->_code . '") {' . "\n" .
            '    var authorizenet_echeck_owner = document.checkout_payment.authorizenet_echeck_owner.value;' . "\n" .
            '    authorizenet_echeck_owner = authorizenet_echeck_owner.replace(/^\s*|\s*$/g, "");' . "\n" .
            '    var authorizenet_echeck_account_number = document.checkout_payment.authorizenet_echeck_account_number.value;' . "\n" .
            '    authorizenet_echeck_account_number = authorizenet_echeck_account_number.replace(/^\s*|\s*$/g, "");' . "\n" .
            '    var authorizenet_echeck_bank_name = document.checkout_payment.authorizenet_echeck_bank_name.value;' . "\n" .
            '    authorizenet_echeck_bank_name = authorizenet_echeck_bank_name.replace(/^\s*|\s*$/g, "");' . "\n" .
            '    var authorizenet_echeck_routing_code = document.checkout_payment.authorizenet_echeck_routing_code.value;' . "\n" .
            '    authorizenet_echeck_routing_code = authorizenet_echeck_routing_code.replace(/[^\d]/gi, "");' . "\n";

      if (MODULE_PAYMENT_AUTHORIZENET_ECHECK_VERIFY_WITH_WF_SS == '1') {
        $js .= '    var authorizenet_echeck_tax_id = document.checkout_payment.authorizenet_echeck_tax_id.value;' . "\n" .
               '    authorizenet_echeck_tax_id = authorizenet_echeck_tax_id.replace(/[^\d]/gi, "");' . "\n";
      }

      $js .= '    if (authorizenet_echeck_owner == "") {' . "\n" .
             '      error_message = error_message + "' . $osC_Language->get('payment_authorizenet_echeck_js_owner') . '\n";' . "\n" .
             '      error = 1;' . "\n" .
             '    }' . "\n" .
             '    if (authorizenet_echeck_account_number == "") {' . "\n" .
             '      error_message = error_message + "' . $osC_Language->get('payment_authorizenet_echeck_js_account_number') . '\n";' . "\n" .
             '      error = 1;' . "\n" .
             '    }' . "\n" .
             '    if (authorizenet_echeck_bank_name == "") {' . "\n" .
             '      error_message = error_message + "' . $osC_Language->get('payment_authorizenet_echeck_js_bank_name') . '\n";' . "\n" .
             '      error = 1;' . "\n" .
             '    }' . "\n" .
             '    if (authorizenet_echeck_routing_code.length != 9) {' . "\n" .
             '      error_message = error_message + "' . sprintf($osC_Language->get('payment_authorizenet_echeck_js_routing_code'), 9) . '\n";' . "\n" .
             '      error = 1;' . "\n" .
             '    }' . "\n";

      if (MODULE_PAYMENT_AUTHORIZENET_ECHECK_VERIFY_WITH_WF_SS == '1') {
        $js .= '    if (authorizenet_echeck_tax_id.length != 9) {' . "\n" .
               '      error_message = error_message + "' . sprintf($osC_Language->get('payment_authorizenet_echeck_js_tax_id'), 9) . '\n";' . "\n" .
               '      error = 1;' . "\n" .
               '    }' . "\n";
      }

      $js .= '  }' . "\n";

      return $js;
    }

    function selection() {
      global $osC_Language, $osC_ShoppingCart;

      $account_types_array = array();
      foreach ($this->_getAccountTypes() as $key => $type) {
        $account_types_array[] = array('id' => $key,
                                       'text' => $type);
      }

      $selection = array('id' => $this->_code,
                         'module' => $this->_method_title,
                         'fields' => array(array('title' => $osC_Language->get('payment_authorizenet_echeck_owner'),
                                                 'field' => osc_draw_input_field('authorizenet_echeck_owner', $osC_ShoppingCart->getBillingAddress('firstname') . ' ' . $osC_ShoppingCart->getBillingAddress('lastname'))),
                                           array('title' => $osC_Language->get('payment_authorizenet_echeck_account_type'),
                                                 'field' => osc_draw_pull_down_menu('authorizenet_echeck_account_type', $account_types_array)),
                                           array('title' => $osC_Language->get('payment_authorizenet_echeck_account_number'),
                                                 'field' => osc_draw_input_field('authorizenet_echeck_account_number')),
                                           array('title' => $osC_Language->get('payment_authorizenet_echeck_bank_name'),
                                                 'field' => osc_draw_input_field('authorizenet_echeck_bank_name')),
                                           array('title' => $osC_Language->get('payment_authorizenet_echeck_routing_code'),
                                                 'field' => osc_draw_input_field('authorizenet_echeck_routing_code'))));

      if (MODULE_PAYMENT_AUTHORIZENET_ECHECK_VERIFY_WITH_WF_SS == '1') {
        $org_types_array = array();
        foreach ($this->_getOrganizationTypes() as $key => $type) {
          $org_types_array[] = array('id' => $key,
                                     'text' => $type);
        }

        $selection['fields'][] = array('title' => $osC_Language->get('payment_authorizenet_echeck_tax_id'),
                                       'field' => osc_draw_input_field('authorizenet_echeck_tax_id'));

        $selection['fields'][] = array('title' => $osC_Language->get('payment_authorizenet_echeck_org_type'),
                                       'field' => osc_draw_pull_down_menu('authorizenet_echeck_org_type', $org_types_array));
      }

      return $selection;
    }

    function pre_confirmation_check() {
      $this->_verifyData();
    }

    function confirmation() {
      global $osC_Language, $osC_Currencies, $osC_ShoppingCart;

      $confirmation = array('title' => $this->_method_title,
                            'fields' => array(array('title' => $osC_Language->get('payment_authorizenet_echeck_owner'),
                                                    'field' => $_POST['authorizenet_echeck_owner']),
                                              array('title' => $osC_Language->get('payment_authorizenet_echeck_account_type'),
                                                    'field' => $this->_getAccountTypes($_POST['authorizenet_echeck_account_type'])),
                                              array('title' => $osC_Language->get('payment_authorizenet_echeck_account_number'),
                                                    'field' => $_POST['authorizenet_echeck_account_number']),
                                              array('title' => $osC_Language->get('payment_authorizenet_echeck_bank_name'),
                                                    'field' => $_POST['authorizenet_echeck_bank_name']),
                                              array('title' => $osC_Language->get('payment_authorizenet_echeck_routing_code'),
                                                    'field' => $_POST['authorizenet_echeck_routing_code'])),
                            'text' => sprintf($osC_Language->get('payment_authorizenet_echeck_confirmation_message'), STORE_NAME, strtolower($this->_getAccountTypes($_POST['authorizenet_echeck_account_type'])), date('dS \of F Y'), $osC_Currencies->format($osC_ShoppingCart->getTotal())));

      if (MODULE_PAYMENT_AUTHORIZENET_ECHECK_VERIFY_WITH_WF_SS == '1') {
        $confirmation['fields'][] = array('title' => $osC_Language->get('payment_authorizenet_echeck_tax_id'),
                                          'field' => str_repeat('X', 5) . substr($_POST['authorizenet_echeck_tax_id'], -4));

        $confirmation['fields'][] = array('title' => $osC_Language->get('payment_authorizenet_echeck_org_type'),
                                          'field' => $this->_getOrganizationTypes($_POST['authorizenet_echeck_org_type']));
      }

      return $confirmation;
    }

    function process_button() {
      $fields = osc_draw_hidden_field('authorizenet_echeck_owner', $_POST['authorizenet_echeck_owner']) .
                osc_draw_hidden_field('authorizenet_echeck_account_type', $_POST['authorizenet_echeck_account_type']) .
                osc_draw_hidden_field('authorizenet_echeck_account_number', $_POST['authorizenet_echeck_account_number']) .
                osc_draw_hidden_field('authorizenet_echeck_bank_name', $_POST['authorizenet_echeck_bank_name']) .
                osc_draw_hidden_field('authorizenet_echeck_routing_code', $_POST['authorizenet_echeck_routing_code']);

      if (MODULE_PAYMENT_AUTHORIZENET_ECHECK_VERIFY_WITH_WF_SS == '1') {
        $fields .= osc_draw_hidden_field('authorizenet_echeck_tax_id', $_POST['authorizenet_echeck_tax_id']) .
                   osc_draw_hidden_field('authorizenet_echeck_org_type', $_POST['authorizenet_echeck_org_type']);
      }

      return $fields;
    }

    function process() {
      global $osC_Database, $messageStack, $osC_Customer, $osC_Language, $osC_Currencies, $osC_ShoppingCart;

      $this->_verifyData();

      $this->_order_id = osC_Order::insert();

      $params = array('x_version' => '3.1',
                      'x_delim_data' => 'TRUE',
                      'x_delim_char' => ',',
                      'x_encap_char' => '"',
                      'x_relay_response' => 'FALSE',
                      'x_login' => MODULE_PAYMENT_AUTHORIZENET_ECHECK_LOGIN_ID,
                      'x_tran_key' => MODULE_PAYMENT_AUTHORIZENET_ECHECK_TRANSACTION_KEY,
                      'x_amount' => $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal(), $osC_Currencies->getCode()),
                      'x_currency_code' => $osC_Currencies->getCode(),
                      'x_method' => 'ECHECK',
                      'x_bank_aba_code' => $_POST['authorizenet_echeck_routing_code'],
                      'x_bank_acct_num' => $_POST['authorizenet_echeck_account_number'],
                      'x_bank_acct_type' => $_POST['authorizenet_echeck_account_type'],
                      'x_bank_name' => $_POST['authorizenet_echeck_bank_name'],
                      'x_bank_acct_name' => $_POST['authorizenet_echeck_owner'],
                      'x_echeck_type' => 'WEB',
                      'x_type' => 'AUTH_ONLY',
                      'x_first_name' => $osC_ShoppingCart->getBillingAddress('firstname'),
                      'x_last_name' => $osC_ShoppingCart->getBillingAddress('lastname'),
                      'x_company' => $osC_ShoppingCart->getBillingAddress('company'),
                      'x_address' => $osC_ShoppingCart->getBillingAddress('street_address'),
                      'x_city' => $osC_ShoppingCart->getBillingAddress('city'),
                      'x_state' => $osC_ShoppingCart->getBillingAddress('state'),
                      'x_zip' => $osC_ShoppingCart->getBillingAddress('postcode'),
                      'x_country' => $osC_ShoppingCart->getBillingAddress('country_iso_code_2'),
                      'x_cust_id' => $osC_Customer->getID(),
                      'x_customer_ip' => osc_get_ip_address(),
                      'x_invoice_num' => $this->_order_id,
                      'x_email' => $osC_Customer->getEmailAddress(),
                      'x_email_customer' => 'FALSE',
                      'x_ship_to_first_name' => $osC_ShoppingCart->getShippingAddress('firstname'),
                      'x_ship_to_last_name' => $osC_ShoppingCart->getShippingAddress('lastname'),
                      'x_ship_to_company' => $osC_ShoppingCart->getShippingAddress('company'),
                      'x_ship_to_address' => $osC_ShoppingCart->getShippingAddress('street_address'),
                      'x_ship_to_city' => $osC_ShoppingCart->getShippingAddress('city'),
                      'x_ship_to_state' => $osC_ShoppingCart->getShippingAddress('state'),
                      'x_ship_to_zip' => $osC_ShoppingCart->getShippingAddress('postcode'),
                      'x_ship_to_country' => $osC_ShoppingCart->getShippingAddress('country_iso_code_2'));

      if (ACCOUNT_TELEPHONE > -1) {
        $params['x_phone'] = $osC_ShoppingCart->getBillingAddress('telephone_number');
      }

      if (MODULE_PAYMENT_AUTHORIZENET_ECHECK_TRANSACTION_TEST_MODE == '1') {
        $params['x_test_request'] = 'TRUE';
      }

      if (MODULE_PAYMENT_AUTHORIZENET_ECHECK_VERIFY_WITH_WF_SS == '1') {
        $params['x_customer_organization_type'] = $_POST['authorizenet_echeck_org_type'];
        $params['x_customer_tax_id'] = $_POST['authorizenet_echeck_tax_id'];
      }

      $post_string = '';

      foreach ($params as $key => $value) {
        $post_string .= $key . '=' . urlencode(trim($value)) . '&';
      }

      $post_string = substr($post_string, 0, -1);

      $this->_transaction_response = $this->sendTransactionToGateway($this->_gateway_url, $post_string);

      if (empty($this->_transaction_response) === false) {
        $regs = preg_split("/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/", $this->_transaction_response);
        foreach ($regs as $key => $value) {
          $regs[$key] = substr($value, 1, -1); // remove double quotes
        }
      } else {
        $regs = array('-1', '-1', '-1');
      }

      $error = false;

      if ($regs[0] == '1') {
        if (!osc_empty(MODULE_PAYMENT_AUTHORIZENET_ECHECK_MD5_HASH)) {
          if (strtoupper($regs[37]) != strtoupper(md5(MODULE_PAYMENT_AUTHORIZENET_ECHECK_MD5_HASH . MODULE_PAYMENT_AUTHORIZENET_ECHECK_LOGIN_ID . $regs[6] . $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal(), $osC_Currencies->getCode())))) {
            $error = $osC_Language->get('payment_authorizenet_echeck_error_general');
          }
        }
      } else {
        switch ($regs[2]) {
          case '9':
            $error = $osC_Language->get('payment_authorizenet_echeck_error_invalid_routing_code');
            break;

          case '10':
            $error = $osC_Language->get('payment_authorizenet_echeck_error_invalid_account');
            break;

          case '77':
            $error = $osC_Language->get('payment_authorizenet_echeck_error_invalid_tax_id');
            break;

          default:
            $error = $osC_Language->get('payment_authorizenet_echeck_error_general');
            break;
        }
      }

      if ($error === false) {
        osC_Order::process($this->_order_id, $this->order_status);

        $Qtransaction = $osC_Database->query('insert into :table_orders_transactions_history (orders_id, transaction_code, transaction_return_value, transaction_return_status, date_added) values (:orders_id, :transaction_code, :transaction_return_value, :transaction_return_status, now())');
        $Qtransaction->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
        $Qtransaction->bindInt(':orders_id', $this->_order_id);
        $Qtransaction->bindInt(':transaction_code', 1);
        $Qtransaction->bindValue(':transaction_return_value', $this->_transaction_response);
        $Qtransaction->bindInt(':transaction_return_status', 1);
        $Qtransaction->execute();
      } else {
        osC_Order::remove($this->_order_id);

        $messageStack->add_session('checkout_payment', $error, 'error');

        osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'payment&authorizenet_echeck_owner=' . $_POST['authorizenet_echeck_owner'] . '&authorizenet_echeck_account_type=' . $_POST['authorizenet_echeck_account_type'] . '&authorizenet_echeck_bank_name=' . $_POST['authorizenet_echeck_bank_name'] . '&authorizenet_echeck_routing_code=' . $_POST['authorizenet_echeck_routing_code'], 'SSL'));
      }
    }

    function _verifyData() {
      global $osC_Language, $messageStack;

      $error = false;

      $_POST['authorizenet_echeck_owner'] = (isset($_POST['authorizenet_echeck_owner']) ? trim($_POST['authorizenet_echeck_owner']) : '');
      $_POST['authorizenet_echeck_account_number'] = (isset($_POST['authorizenet_echeck_account_number']) ? trim($_POST['authorizenet_echeck_account_number']) : '');
      $_POST['authorizenet_echeck_bank_name'] = (isset($_POST['authorizenet_echeck_bank_name']) ? trim($_POST['authorizenet_echeck_bank_name']) : '');
      $_POST['authorizenet_echeck_routing_code'] = (isset($_POST['authorizenet_echeck_routing_code']) ? ereg_replace('[^0-9]', '', $_POST['authorizenet_echeck_routing_code']) : '');

      if (empty($_POST['authorizenet_echeck_owner']) || empty($_POST['authorizenet_echeck_account_number']) || empty($_POST['authorizenet_echeck_bank_name']) || (in_array($_POST['authorizenet_echeck_account_type'], array('CHECKING', 'BUSINESSCHECKING', 'SAVINGS')) === false)) {
        $error = true;

        $messageStack->add_session('checkout_payment', $osC_Language->get('payment_authorizenet_echeck_error_general'), 'error');
      } elseif (strlen($_POST['authorizenet_echeck_routing_code']) !== 9) {
        $error = true;

        $messageStack->add_session('checkout_payment', sprintf($osC_Language->get('payment_authorizenet_echeck_error_routing_code'), 9), 'error');
      }

      if (($error === false) && (MODULE_PAYMENT_AUTHORIZENET_ECHECK_VERIFY_WITH_WF_SS == '1')) {
        $_POST['authorizenet_echeck_tax_id'] = (isset($_POST['authorizenet_echeck_tax_id']) ? ereg_replace('[^0-9]', '', $_POST['authorizenet_echeck_tax_id']) : '');

        if (in_array($_POST['authorizenet_echeck_org_type'], array('I', 'B')) === false) {
          $error = true;

          $messageStack->add_session('checkout_payment', $osC_Language->get('payment_authorizenet_echeck_error_general'), 'error');
        } elseif (strlen($_POST['authorizenet_echeck_tax_id']) !== 9) {
          $error = true;

          $messageStack->add_session('checkout_payment', sprintf($osC_Language->get('payment_authorizenet_echeck_error_tax_id'), 9), 'error');
        }
      }

      if ($error !== false) {
//comment out for one page checkout
        //osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'payment&authorizenet_echeck_owner=' . $_POST['authorizenet_echeck_owner'] . '&authorizenet_echeck_account_type=' . $_POST['authorizenet_echeck_account_type'] . '&authorizenet_echeck_bank_name=' . $_POST['authorizenet_echeck_bank_name'] . '&authorizenet_echeck_routing_code=' . $_POST['authorizenet_echeck_routing_code'], 'SSL'));
      }
    }

    function _getAccountTypes($key = '') {
      global $osC_Language;

      $types = array('CHECKING' => $osC_Language->get('payment_authorizenet_echeck_account_type_checking'),
                     'BUSINESSCHECKING' => $osC_Language->get('payment_authorizenet_echeck_account_type_business_checking'),
                     'SAVINGS' => $osC_Language->get('payment_authorizenet_echeck_account_type_savings'));

      if ( (empty($key) === false) && isset($types[$key]) ) {
        return $types[$key];
      }

      return $types;
    }

    function _getOrganizationTypes($key = '') {
      global $osC_Language;

      $types = array('I' => $osC_Language->get('payment_authorizenet_echeck_org_type_individual'),
                     'B' => $osC_Language->get('payment_authorizenet_echeck_org_type_business'));

      if ( (empty($key) === false) && isset($types[$key]) ) {
        return $types[$key];
      }

      return $types;
    }
  }
?>
