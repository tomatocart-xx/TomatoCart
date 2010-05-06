<?php
/*
  $Id: currencies.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require_once('../includes/classes/currencies.php');

  class osC_Currencies_Admin extends osC_Currencies {
    function getData($id = null) {
      if ( !empty($id) ) {
        $currency_code = $this->getCode($id);

        return array_merge($this->currencies[$currency_code], array('code' => $currency_code));
      }

      return $this->currencies;
    }

    function save($id = null, $data, $set_default = false) {
      global $osC_Database;

      $osC_Database->startTransaction();

      if ( is_numeric($id) ) {
        $Qcurrency = $osC_Database->query('update :table_currencies set title = :title, code = :code, symbol_left = :symbol_left, symbol_right = :symbol_right, decimal_places = :decimal_places, value = :value where currencies_id = :currencies_id');
        $Qcurrency->bindInt(':currencies_id', $id);
      } else {
        $Qcurrency = $osC_Database->query('insert into :table_currencies (title, code, symbol_left, symbol_right, decimal_places, value) values (:title, :code, :symbol_left, :symbol_right, :decimal_places, :value)');
      }

      $Qcurrency->bindTable(':table_currencies', TABLE_CURRENCIES);
      $Qcurrency->bindValue(':title', $data['title']);
      $Qcurrency->bindValue(':code', $data['code']);
      $Qcurrency->bindValue(':symbol_left', $data['symbol_left']);
      $Qcurrency->bindValue(':symbol_right', $data['symbol_right']);
      $Qcurrency->bindInt(':decimal_places', $data['decimal_places']);
      $Qcurrency->bindValue(':value', $data['value']);
      $Qcurrency->setLogging($_SESSION['module'], $id);
      $Qcurrency->execute();

      if ( !$osC_Database->isError() ) {
        if ( !is_numeric($id) ) {
          $id = $osC_Database->nextID();
        }

        if ( $set_default === true ) {
          $Qupdate = $osC_Database->query('update :table_configuration set configuration_value = :configuration_value where configuration_key = :configuration_key');
          $Qupdate->bindTable(':table_configuration', TABLE_CONFIGURATION);
          $Qupdate->bindValue(':configuration_value', $data['code']);
          $Qupdate->bindValue(':configuration_key', 'DEFAULT_CURRENCY');
          $Qupdate->setLogging($_SESSION['module'], $id);
          $Qupdate->execute();

          if ( $Qupdate->affectedRows() ) {
            osC_Cache::clear('configuration');
          }
        }

        osC_Cache::clear('currencies');

        return true;
      }

      return false;
    }

    function delete($id) {
      global $osC_Database;

      $Qcheck = $osC_Database->query('select code from :table_currencies where currencies_id = :currencies_id');
      $Qcheck->bindTable(':table_currencies', TABLE_CURRENCIES);
      $Qcheck->bindInt(':currencies_id', $id);
      $Qcheck->execute();

      if ( $Qcheck->value('code') != DEFAULT_CURRENCY ) {
        $Qdelete = $osC_Database->query('delete from :table_currencies where currencies_id = :currencies_id');
        $Qdelete->bindTable(':table_currencies', TABLE_CURRENCIES);
        $Qdelete->bindInt(':currencies_id', $id);
        $Qdelete->setLogging($_SESSION['module'], $id);
        $Qdelete->execute();

        if ( !$osC_Database->isError() ) {
          osC_Cache::clear('currencies');

          return true;
        }
      }

      return false;
    }

    function updateRates($service) {
      global $osC_Database;

      $updated = array('0' => array(), '1' => array());

      $Qcurrencies = $osC_Database->query('select currencies_id, code, title from :table_currencies');
      $Qcurrencies->bindTable(':table_currencies', TABLE_CURRENCIES);
      $Qcurrencies->execute();

      while ( $Qcurrencies->next() ) {
        $rate = call_user_func('quote_' . $service . '_currency', $Qcurrencies->value('code'));

        if ( !empty($rate) ) {
          $Qupdate = $osC_Database->query('update :table_currencies set value = :value, last_updated = now() where currencies_id = :currencies_id');
          $Qupdate->bindTable(':table_currencies', TABLE_CURRENCIES);
          $Qupdate->bindValue(':value', $rate);
          $Qupdate->bindInt(':currencies_id', $Qcurrencies->valueInt('currencies_id'));
          $Qupdate->setLogging($_SESSION['module'], $Qcurrencies->valueInt('currencies_id'));
          $Qupdate->execute();

          $updated[1][] = array('title' => $Qcurrencies->value('title'),
                                'code' => $Qcurrencies->value('code'));
        } else {
          $updated[0][] = array('title' => $Qcurrencies->value('title'),
                                'code' => $Qcurrencies->value('code'));
        }
      }

      osC_Cache::clear('currencies');

      return $updated;
    }
  }
?>
