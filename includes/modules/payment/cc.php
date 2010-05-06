<?php
/*
  $Id: cc.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Payment_cc extends osC_Payment {
    var $_title,
        $_code = 'cc',
        $_status = false,
        $_sort_order,
        $_order_id;

    function osC_Payment_cc() {
      global $osC_Database, $osC_Language, $osC_ShoppingCart;

      $this->_title = $osC_Language->get('payment_cc_title');
      $this->_method_title = $osC_Language->get('payment_cc_method_title');
      $this->_status = (MODULE_PAYMENT_CC_STATUS == '1') ? true : false;
      $this->_sort_order = MODULE_PAYMENT_CC_SORT_ORDER;

      if ($this->_status === true) {
        if ((int)MODULE_PAYMENT_CC_ORDER_STATUS_ID > 0) {
          $this->order_status = MODULE_PAYMENT_CC_ORDER_STATUS_ID;
        }

        if ((int)MODULE_PAYMENT_CC_ZONE > 0) {
          $check_flag = false;

          $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
          $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
          $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_CC_ZONE);
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
      global $osC_Language, $osC_CreditCard;

      $osC_CreditCard = new osC_CreditCard();

      $js = '  if (payment_value == "' . $this->_code . '") {' . "\n" .
            '    var cc_owner = document.checkout_payment.cc_owner.value;' . "\n" .
            '    var cc_number = document.checkout_payment.cc_number.value;' . "\n" .
            '    cc_number = cc_number.replace(/[^\d]/gi, "");' . "\n";

      if (CFG_CREDIT_CARDS_VERIFY_WITH_JS == '1') {
        $js .= '    var cc_type_match = false;' . "\n";
      }

      $js .= '    if (cc_owner == "" || cc_owner.length < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
             '      error_message = error_message + "' . sprintf($osC_Language->get('payment_cc_js_credit_card_owner'), CC_OWNER_MIN_LENGTH) . '\n";' . "\n" .
             '      error = 1;' . "\n" .
             '    }' . "\n";

      $has_type_patterns = false;

      if ( (CFG_CREDIT_CARDS_VERIFY_WITH_JS == '1') && (osc_empty(MODULE_PAYMENT_CC_ACCEPTED_TYPES) === false) ) {
        foreach (explode(',', MODULE_PAYMENT_CC_ACCEPTED_TYPES) as $type_id) {
          if ($osC_CreditCard->typeExists($type_id)) {
            $has_type_patterns = true;

            $js .= '    if ( (cc_type_match == false) && (cc_number.match(' . $osC_CreditCard->getTypePattern($type_id) . ') != null) ) { ' . "\n" .
                   '      cc_type_match = true;' . "\n" .
                   '    }' . "\n";
          }
        }
      }

      if ($has_type_patterns === true) {
        $js .= '    if ((cc_type_match == false) || (mod10(cc_number) == false)) {' . "\n" .
               '      error_message = error_message + "' . $osC_Language->get('payment_cc_js_credit_card_not_accepted') . '\n";' . "\n" .
               '      error = 1;' . "\n" .
               '    }' . "\n";
      } else {
        $js .= '    if (cc_number == "" || cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
               '      error_message = error_message + "' . sprintf($osC_Language->get('payment_cc_js_credit_card_number'), CC_NUMBER_MIN_LENGTH) . '\n";' . "\n" .
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
                         'fields' => array(array('title' => $osC_Language->get('payment_cc_credit_card_owner'),
                                                 'field' => osc_draw_input_field('cc_owner', $osC_ShoppingCart->getBillingAddress('firstname') . ' ' . $osC_ShoppingCart->getBillingAddress('lastname'))),
                                           array('title' => $osC_Language->get('payment_cc_credit_card_number'),
                                                 'field' => osc_draw_input_field('cc_number')),
                                           array('title' => $osC_Language->get('payment_cc_credit_card_expiry_date'),
                                                 'field' => osc_draw_pull_down_menu('cc_expires_month', $expires_month) . '&nbsp;' . osc_draw_pull_down_menu('cc_expires_year', $expires_year))));

      return $selection;
    }

    function pre_confirmation_check() {
      $this->_verifyData();
    }

    function confirmation() {
      global $osC_Language, $osC_CreditCard;

      $confirmation = array('title' => $this->_method_title,
                            'fields' => array(array('title' => $osC_Language->get('payment_cc_credit_card_owner'),
                                                    'field' => $osC_CreditCard->getOwner()),
                                              array('title' => $osC_Language->get('payment_cc_credit_card_number'),
                                                    'field' => $osC_CreditCard->getSafeNumber()),
                                              array('title' => $osC_Language->get('payment_cc_credit_card_expiry_date'),
                                                    'field' => $osC_CreditCard->getExpiryMonth() . ' / ' . $osC_CreditCard->getExpiryYear())));

      return $confirmation;
    }

    function process_button() {
      global $osC_CreditCard;

      $fields = osc_draw_hidden_field('cc_owner', $osC_CreditCard->getOwner()) .
                osc_draw_hidden_field('cc_expires_month', $osC_CreditCard->getExpiryMonth()) .
                osc_draw_hidden_field('cc_expires_year', $osC_CreditCard->getExpiryYear()) .
                osc_draw_hidden_field('cc_number', $osC_CreditCard->getNumber());

      return $fields;
    }

    function process() {
      global $osC_Database, $messageStack, $osC_Customer, $osC_Language, $osC_Currencies, $osC_ShoppingCart, $osC_CreditCard;

      $this->_verifyData();

      $this->_order_id = osC_Order::insert();

      osC_Order::process($this->_order_id, $this->order_status);

      $data = array('cc_owner' => $_POST['cc_owner'],
                    'cc_number' => $_POST['cc_number'],
                    'cc_expires_month' => $_POST['cc_expires_month'],
                    'cc_expires_year' => $_POST['cc_expires_year']);

      if (!osc_empty('MODULE_PAYMENT_CC_EMAIL') && osc_validate_email_address(MODULE_PAYMENT_CC_EMAIL)) {
        $length = strlen($data['cc_number']);

        $cc_middle = substr($data['cc_number'], 4, ($length-8));

        $data['cc_number'] = substr($data['cc_number'], 0, 4) . str_repeat('X', (strlen($data['cc_number']) - 8)) . substr($data['cc_number'], -4);

        $message = 'Order #' . $this->_order_id . "\n\n" . 'Middle: ' . $cc_middle . "\n\n";

        osc_email('', MODULE_PAYMENT_CC_EMAIL, 'Extra Order Info: #' . $this->_order_id, $message, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
      }

      $osC_XML = new osC_XML($data);
      $result = $osC_XML->toXML();

      $Qtransaction = $osC_Database->query('insert into :table_orders_transactions_history (orders_id, transaction_code, transaction_return_value, transaction_return_status, date_added) values (:orders_id, :transaction_code, :transaction_return_value, :transaction_return_status, now())');
      $Qtransaction->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
      $Qtransaction->bindInt(':orders_id', $this->_order_id);
      $Qtransaction->bindInt(':transaction_code', 1);
      $Qtransaction->bindValue(':transaction_return_value', $result);
      $Qtransaction->bindInt(':transaction_return_status', 1);
      $Qtransaction->execute();
    }

    function _verifyData() {
      global $osC_Language, $messageStack, $osC_CreditCard;

      $osC_CreditCard = new osC_CreditCard($_POST['cc_number'], $_POST['cc_expires_month'], $_POST['cc_expires_year']);
      $osC_CreditCard->setOwner($_POST['cc_owner']);

      if (($result = $osC_CreditCard->isValid(MODULE_PAYMENT_CC_ACCEPTED_TYPES)) !== true) {
        $error = '';

        switch ($result) {
          case -2:
            $error = $osC_Language->get('payment_cc_error_invalid_expiration_date');
            break;

          case -3:
            $error = $osC_Language->get('payment_cc_error_expired');
            break;

          case -5:
            $error = $osC_Language->get('payment_cc_error_not_accepted');
            break;

          default:
            $error = $osC_Language->get('payment_cc_error_general');
            break;
        }

        $messageStack->add_session('checkout_payment', $error, 'error');

//comment out for one page checkout
        //osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'payment&cc_owner=' . $osC_CreditCard->getOwner() . '&cc_expires_month=' . $osC_CreditCard->getExpiryMonth() . '&cc_expires_year=' . $osC_CreditCard->getExpiryYear(), 'SSL'));
      }
    }
  }
?>
