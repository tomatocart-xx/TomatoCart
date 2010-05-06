<?php
/*
  $Id: administrators_log.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_HomeMeta_Admin {
      
    function saveData($keywords, $prefix) {
      global $osC_Database;
      
      foreach($keywords as $key => $value) {
        $Qconfiguration = $osC_Database->query("update :table_configuration set configuration_value = :configuration_value where configuration_key = :configuration_key");
        $Qconfiguration->bindValue(":configuration_key", $prefix . $key);
        $Qconfiguration->bindValue(":configuration_value", $value);
        $Qconfiguration->bindTable(":table_configuration", TABLE_CONFIGURATION);
        $Qconfiguration->execute();
        
        if(!$osC_Database->isError()) {
          define($prefix . $key, $value);
          osC_Cache::clear("configuration");
        } else {
          return false;
        }
      }
      return true;
    }
  }
?>
