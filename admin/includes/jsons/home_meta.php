<?php
/*
  $Id: home_meta.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/home_data.php');

  class toC_Json_Home_meta {

    function saveMetaInfo() {
      global $toC_Json, $osC_Language;
      
      $error = false;
      $keywords = $_REQUEST['HOME_META_KEYWORD'];
      $description = $_REQUEST['HOME_META_DESCRIPTION'];
      
      if(count($keywords) > 0 &&  count($description) > 0) {
        if(osC_HomeMeta_Admin::saveData($keywords, 'HOME_META_KEYWORD_') && osC_HomeMeta_Admin::saveData($description, 'HOME_META_DESCRIPTION_')) {
          $error = false;
        } else {
          $error = true;
        }
      } else {
        $error = true;
      }

      if (!$error) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      echo $toC_Json->encode($response);
    }

    function loadMetaInfo() {
      global $toC_Json, $osC_Language, $osC_Database;
            
      foreach ($osC_Language->getAll() as $l) {
        $code = strtoupper($l['code']);
        osC_Cache::clear("configuration");
        
        if (!defined('HOME_META_KEYWORD_' . $code)) {
          $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Homepage Meta', 'HOME_META_KEYWORD_$code', '','the meta keywords in the front page', '6', '0', now())");
          define('HOME_META_KEYWORD_' . $code, '');  
        }
        
        if (!defined('HOME_META_DESCRIPTION_' . $code)) {
          $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Homepage Description', 'HOME_META_DESCRIPTION_$code', '','the meta description in the front page', '6', '0', now())");
          define('HOME_META_DESCRIPTION_' . $code, '');        
        }
        
        $data['HOME_META_KEYWORD[' .$code .']'] = constant('HOME_META_KEYWORD_' . $code);
        $data['HOME_META_DESCRIPTION[' .$code .']'] = constant('HOME_META_DESCRIPTION_' . $code);
      }
      
      $response = array('success' => true, 'data' => $data);
      echo $toC_Json->encode($response);
    }
  }
?>
