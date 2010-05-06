<?php
/*
  $Id: homepage_meta_info.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Homepage_Meta_Info_Admin {
  
    function getData() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      $data = array();
      $clear_cache = false;
      
      foreach ($osC_Language->getAll() as $l) {
        $name = $l['name'];
        $code = strtoupper($l['code']);
        
        //check meta keywords for language
        if (!defined('HOME_META_KEYWORD_' . $code)) {
          $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Homepage Meta Keywords For $name', 'HOME_META_KEYWORD_$code', '','the meta keywords for the front page', '6', '0', now())");

          define('HOME_META_KEYWORD_' . $code, '');
          
          $clear_cache = true;
        }
        
        //check meta description for language
        if (!defined('HOME_META_DESCRIPTION_' . $code)) {
          $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Homepage Meta Description For $name', 'HOME_META_DESCRIPTION_$code', '','the meta description for the front page', '6', '0', now())");
          
          define('HOME_META_DESCRIPTION_' . $code, '');
          
          $clear_cache = true;        
        }
        
        $data['HOME_META_KEYWORD[' . $code . ']'] = constant('HOME_META_KEYWORD_' . $code);
        $data['HOME_META_DESCRIPTION[' . $code . ']'] = constant('HOME_META_DESCRIPTION_' . $code);
      }
      
      if ($clear_cache == true) {
        osC_Cache::clear("configuration");
      }
      
      return $data;
    }
      
    function saveData($data) {
      global $osC_Database;
      
      $error = false;
      
      foreach($data['keywords'] as $key => $value) {
        $Qconfiguration = $osC_Database->query("update :table_configuration set configuration_value = :configuration_value where configuration_key = :configuration_key");
        $Qconfiguration->bindValue(":configuration_key", 'HOME_META_KEYWORD_' . $key);
        $Qconfiguration->bindValue(":configuration_value", $value);
        $Qconfiguration->bindTable(":table_configuration", TABLE_CONFIGURATION);
        $Qconfiguration->execute();
        
        if($osC_Database->isError()) {
          $error = true;
          break;
        }
      }
      
      foreach($data['descriptions'] as $key => $value) {
        $Qconfiguration = $osC_Database->query("update :table_configuration set configuration_value = :configuration_value where configuration_key = :configuration_key");
        $Qconfiguration->bindValue(":configuration_key", 'HOME_META_DESCRIPTION_' . $key);
        $Qconfiguration->bindValue(":configuration_value", $value);
        $Qconfiguration->bindTable(":table_configuration", TABLE_CONFIGURATION);
        $Qconfiguration->execute();
        
        if($osC_Database->isError()) {
          $error = true;
          break;
        }
      }
      
      if ($error === false) {
        osC_Cache::clear("configuration");
        return true;
      }
      
      return false;
    }
  }
?>
