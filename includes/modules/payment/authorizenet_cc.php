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

  class osC_Payment_authorizenet_cc extends osC_Payment {
    var $_title,
        $_code = 'authorizenet_cc',
        $_status = false,
        $_sort_order,
        $_order_id,
        $_transaction_response;

    function osC_Payment_authorizenet_cc() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;

      $this->_title = $osC_Language->get('payment_authorizenet_cc_title');
      $this->_method_title = $osC_Language->get('payment_authorizenet_cc_method_title');
      $this->_status = (MODULE_PAYMENT_AUTHORIZENET_CC_STATUS == '1') ? true : false;
      $this->_sort_order = MODULE_PAYMENT_AUTHORIZENET_CC_SORT_ORDER;

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

      if ($this->_status === true) {
        if ((int)MODULE_PAYMENT_AUTHORIZENET_CC_ORDER_STATUS_ID > 0) {
          $this->order_status = MODULE_PAYMENT_AUTHORIZENET_CC_ORDER_STATUS_ID;
        }

        if ((int)MODULE_PAYMENT_AUTHORIZENET_CC_ZONE > 0) {
          $check_flag = false;

          $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
          $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
          $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_AUTHORIZENET_CC_ZONE);
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

          if ($check_flag === false) {
            $this->_status = false;
          }
        }
      }
    }

    function getJavascriptBlock() {
      global $osC_Language, $osC_CreditCard;

      $osC_CreditCard = new osC_CreditCard();

      $js = '  if (payment_value == "' . $this->_code . '") {' . "\n" .
            '    var authorizenet_cc_owner = document.checkout_payment.authorizenet_cc_owner.value;' . "\n" .
            '    var authorizenet_cc_number = document.checkout_payment.authorizenet_cc_number.value;' . "\n" .
            '    authorizenet_cc_number = authorizenet_cc_number.replace(/[^\d]/gi, "");' . "\n";

      if (MODULE_PAYMENT_AUTHORIZENET_CC_VERIFY_WITH_CVC == '1') {
        $js .= '    var authorizenet_cc_cvc = document.checkout_payment.authorizenet_cc_cvc.value;' . "\n";
      }

      if (CFG_CREDIT_CARDS_VERIFY_WITH_JS == '1') {
        $js .= '    var authorizenet_cc_type_match = false;' . "\n";
      }

      $js .= '    if (authorizenet_cc_owner == "" || authorizenet_cc_owner.length < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
             '      error_message = error_message + "' . sprintf($osC_Language->get('payment_authorizenet_cc_js_credit_card_owner'), CC_OWNER_MIN_LENGTH) . '\n";' . "\n" .
             '      error = 1;' . "\n" .
             '    }' . "\n";

      $has_type_patterns = false;

      if ( (CFG_CREDIT_CARDS_VERIFY_WITH_JS == '1') && (osc_empty(MODULE_PAYMENT_AUTHORIZENET_CC_ACCEPTED_TYPES) === false) ) {
        foreach (explode(',', MODULE_PAYMENT_AUTHORIZENET_CC_ACCEPTED_TYPES) as $type_id) {
          if ($osC_CreditCard->typeExists($type_id)) {
            $has_type_patterns = true;

            $js .= '    if ( (authorizenet_cc_type_match == false) && (authorizenet_cc_number.match(' . $osC_CreditCard->getTypePattern($type_id) . ') != null) ) { ' . "\n" .
                   '      authorizenet_cc_type_match = true;' . "\n" .
                   '    }' . "\n";
          }
        }
      }

      if ($has_type_patterns === true) {
        $js .= '    if ((authorizenet_cc_type_match == false) || (mod10(authorizenet_cc_number) == false)) {' . "\n" .
               '      error_message = error_message + "' . $osC_Language->get('payment_authorizenet_cc_js_credit_card_not_accepted') . '\n";' . "\n" .
               '      error = 1;' . "\n" .
               '    }' . "\n";
      } else {
        $js .= '    if (authorizenet_cc_number == "" || authorizenet_cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
               '      error_message = error_message + "' . sprintf($osC_Language->get('payment_authorizenet_cc_js_credit_card_number'), CC_NUMBER_MIN_LENGTH) . '\n";' . "\n" .
               '      error = 1;' . "\n" .
               '    }' . "\n";
      }

      if (MODULE_PAYMENT_AUTHORIZENET_CC_VERIFY_WITH_CVC == '1') {
        $js .= '    if (authorizenet_cc_cvc == "" || authorizenet_cc_cvc.length < 3) {' . "\n" .
               '      error_message = error_message + "' . sprintf($osC_Language->get('payment_authorizenet_cc_js_credit_card_cvc'), 3) . '\n";' . "\n" .
               '      error = 1;' . "\n" .
               '    }' . "\n";
      }

      $js .= '  }' . "\n";

      return $js;
    }

    function selection() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;

      for ($i=1; $i<13; $i++) {
        $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B',mktime(0,0,0,$i,1)));
      }

      $year = date('Y');
      for ($i=$year; $i < $year+10; $i++) {
        $expires_year[] = array('id' => $i, 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
      }

      $selection = array('id' => $this->_code,
                         'module' => $this->_method_title,
                         'fields' => array(array('title' => $osC_Language->get('payment_authorizenet_cc_credit_card_owner'),
                                                 'field' => osc_draw_input_field('authorizenet_cc_owner', $osC_ShoppingCart->getBillingAddress('firstname') . ' ' . $osC_ShoppingCart->getBillingAddress('lastname'))),
                                           array('title' => $osC_Language->get('payment_authorizenet_cc_credit_card_number'),
                                                 'field' => osc_draw_input_field('authorizenet_cc_number')),
                                           array('title' => $osC_Language->get('payment_authorizenet_cc_credit_card_expiry_date'),
                                                 'field' => osc_draw_pull_down_menu('authorizenet_cc_expires_month', $expires_month) . '&nbsp;' . osc_draw_pull_down_menu('authorizenet_cc_expires_year', $expires_year))));

     if (MODULE_PAYMENT_AUTHORIZENET_CC_VERIFY_WITH_CVC == '1') {
       $selection['fields'][] = array('title' => $osC_Language->get('payment_authorizenet_cc_credit_card_cvc'),
                                      'field' => osc_draw_input_field('authorizenet_cc_cvc', null, 'size="5" maxlength="4"'));
     }

      return $selection;
    }

    function pre_confirmation_check() {
      $this->_verifyData();
    }

    function confirmation() {
      global $osC_Language, $osC_CreditCard;

      $confirmation = array('title' => $this->_method_title,
                            'fields' => array(array('title' => $osC_Language->get('payment_authorizenet_cc_credit_card_owner'),
                                                    'field' => $osC_CreditCard->getOwner()),
                                              array('title' => $osC_Language->get('payment_authorizenet_cc_credit_card_number'),
                                                    'field' => $osC_CreditCard->getSafeNumber()),
                                              array('title' => $osC_Language->get('payment_authorizenet_cc_credit_card_expiry_date'),
                                                    'field' => $osC_CreditCard->getExpiryMonth() . ' / ' . $osC_CreditCard->getExpiryYear())));

      if (MODULE_PAYMENT_AUTHORIZENET_CC_VERIFY_WITH_CVC == '1') {
        $confirmation['fields'][] = array('title' => $osC_Language->get('payment_authorizenet_cc_credit_card_cvc'),
                                          'field' => $osC_CreditCard->getCVC());
      }

      return $confirmation;
    }

    function process_button() {
      global $osC_CreditCard;

      $fields = osc_draw_hidden_field('authorizenet_cc_owner', $osC_CreditCard->getOwner()) .
                osc_draw_hidden_field('authorizenet_cc_expires_month', $osC_CreditCard->getExpiryMonth()) .
                osc_draw_hidden_field('authorizenet_cc_expires_year', $osC_CreditCard->getExpiryYear()) .
                osc_draw_hidden_field('authorizenet_cc_number', $osC_CreditCard->getNumber());

      if (MODULE_PAYMENT_AUTHORIZENET_CC_VERIFY_WITH_CVC == '1') {
        $fields .= osc_draw_hidden_field('authorizenet_cc_cvc', $osC_CreditCard->getCVC());
      }

      return $fields;
    }

    function process() {
      global $osC_Database, $messageStack, $osC_Customer, $osC_Language, $osC_Currencies, $osC_ShoppingCart, $osC_CreditCard;

      $this->_verifyData();

      $this->_order_id = osC_Order::insert();

      $params = array('x_version' => '3.1',
                      'x_delim_data' => 'TRUE',
                      'x_delim_char' => ',',
                      'x_encap_char' => '"',
                      'x_relay_response' => 'FALSE',
                      'x_login' => MODULE_PAYMENT_AUTHORIZENET_CC_LOGIN_ID,
                      'x_tran_key' => MODULE_PAYMENT_AUTHORIZENET_CC_TRANSACTION_KEY,
                      'x_amount' => $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal(), $osC_Currencies->getCode()),
                      'x_currency_code' => $osC_Currencies->getCode(),
                      'x_method' => 'CC',
                      'x_card_num' => $osC_CreditCard->getNumber(),
                      'x_exp_date' => $osC_CreditCard->getExpiryMonth() . $osC_CreditCard->getExpiryYear(),
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

      if (MODULE_PAYMENT_AUTHORIZENET_CC_VERIFY_WITH_CVC == '1') {
        $params['x_card_code'] = $osC_CreditCard->getCVC();
      }

      if (MODULE_PAYMENT_AUTHORIZENET_CC_TRANSACTION_TEST_MODE == '1') {
        $params['x_test_request'] = 'TRUE';
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
        if (!osc_empty(MODULE_PAYMENT_AUTHORIZENET_CC_MD5_HASH)) {
          if (strtoupper($regs[37]) != strtoupper(md5(MODULE_PAYMENT_AUTHORIZENET_CC_MD5_HASH . MODULE_PAYMENT_AUTHORIZENET_CC_LOGIN_ID . $regs[6] . $osC_Currencies->formatRaw($osC_ShoppingCart->getTotal(), $osC_Currencies->getCode())))) {
            $error = $osC_Language->get('payment_authorizenet_cc_error_general');
          }
        }
      } else {
        switch ($regs[2]) {
          case '7':
            $error = $osC_Language->get('payment_authorizenet_cc_error_invalid_expiration_date');
            break;

          case '8':
            $error = $osC_Language->get('payment_authorizenet_cc_error_expired');
            break;

          case '17':
          case '28':
            $error = $osC_Language->get('payment_authorizenet_cc_error_unknown_card');
            break;

          default:
            $error = $osC_Language->get('payment_authorizenet_cc_error_general');
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

        osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'payment&authorizenet_cc_owner=' . $osC_CreditCard->getOwner() . '&authorizenet_cc_expires_month=' . $osC_CreditCard->getExpiryMonth() . '&authorizenet_cc_expires_year=' . $osC_CreditCard->getExpiryYear() . (MODULE_PAYMENT_AUTHORIZENET_CC_VERIFY_WITH_CVC == '1' ? '&authorizenet_cc_cvc=' . $osC_CreditCard->getCVC() : ''), 'SSL'));
      }
    }

    function _verifyData() {
      global $osC_Language, $messageStack, $osC_CreditCard;

      $osC_CreditCard = new osC_CreditCard($_POST['authorizenet_cc_number'], $_POST['authorizenet_cc_expires_month'], $_POST['authorizenet_cc_expires_year']);
      $osC_CreditCard->setOwner($_POST['authorizenet_cc_owner']);

      if (MODULE_PAYMENT_AUTHORIZENET_CC_VERIFY_WITH_CVC == '1') {
        $osC_CreditCard->setCVC($_POST['authorizenet_cc_cvc']);
      }

      if (($result = $osC_CreditCard->isValid(MODULE_PAYMENT_AUTHORIZENET_CC_ACCEPTED_TYPES)) !== true) {
        $error = '';

        switch ($result) {
          case -2:
            $error = $osC_Language->get('payment_authorizenet_cc_error_invalid_expiration_date');
            break;

          case -3:
            $error = $osC_Language->get('payment_authorizenet_cc_error_expired');
            break;

          case -5:
            $error = $osC_Language->get('payment_authorizenet_cc_error_not_accepted');
            break;

          default:
            $error = $osC_Language->get('payment_authorizenet_cc_error_general');
            break;
        }

        $messageStack->add_session('checkout_payment', $error, 'error');

//comment out for one page checkout
        //osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'payment&authorizenet_cc_owner=' . $osC_CreditCard->getOwner() . '&authorizenet_cc_expires_month=' . $osC_CreditCard->getExpiryMonth() . '&authorizenet_cc_expires_year=' . $osC_CreditCard->getExpiryYear() . (MODULE_PAYMENT_AUTHORIZENET_CC_VERIFY_WITH_CVC == '1' ? '&authorizenet_cc_cvc=' . $osC_CreditCard->getCVC() : ''), 'SSL'));
      }
    }
  }
?>
