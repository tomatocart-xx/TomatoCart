<?php
/*
  $Id: manufacturers.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/manufacturers.php');

  class toC_Json_Manufacturers {
 
    function listManufacturers() {
      global $toC_Json, $osC_Database;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];     
      
      $Qmanufacturers = $osC_Database->query('select manufacturers_id, manufacturers_name, manufacturers_image, date_added, last_modified from :table_manufacturers order by manufacturers_name');
      $Qmanufacturers->bindTable(':table_manufacturers', TABLE_MANUFACTURERS);
      $Qmanufacturers->setExtBatchLimit($start, $limit);
      $Qmanufacturers->execute();
        
      $records = array();     
      while ( $Qmanufacturers->next() ) {
        $Qclicks = $osC_Database->query('select sum(url_clicked) as total from :table_manufacturers_info where manufacturers_id = :manufacturers_id');
        $Qclicks->bindTable(':table_manufacturers_info', TABLE_MANUFACTURERS_INFO);
        $Qclicks->bindInt(':manufacturers_id', $Qmanufacturers->valueInt('manufacturers_id')); 
        $Qclicks->execute();    
         
        $records[] = array(
          'manufacturers_id' => $Qmanufacturers->valueInt('manufacturers_id'),
          'manufacturers_name' => $Qmanufacturers->value('manufacturers_name'),
          'url_clicked' => $Qclicks->valueInt('total')
        );           
      }
      $Qmanufacturers->freeResult();         
       
      $response = array(EXT_JSON_READER_TOTAL => $Qmanufacturers->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
     
      echo $toC_Json->encode($response);
    }          
    
    function loadManufacturer() {
      global $toC_Json, $osC_Database;
     
      $data = osC_Manufacturers_Admin::getData($_REQUEST['manufacturers_id']);
      
      $Qmanufacturer = $osC_Database->query('select languages_id, manufacturers_url from :table_manufacturers_info where manufacturers_id = :manufacturers_id');
      $Qmanufacturer->bindTable(':table_manufacturers_info', TABLE_MANUFACTURERS_INFO);
      $Qmanufacturer->bindInt(':manufacturers_id', $_REQUEST['manufacturers_id']);
      $Qmanufacturer->execute();
      
      while ($Qmanufacturer->next()) {
        $data['manufacturers_url[' . $Qmanufacturer->ValueInt('languages_id') . ']'] = $Qmanufacturer->Value('manufacturers_url');
      }
      $Qmanufacturer->freeResult();
      
      $response = array('success' => true, 'data' => $data);
        
      echo $toC_Json->encode($response);   
    }
   
    function saveManufacturer() {
      global $toC_Json, $osC_Language;
      
      $data = array('name' => $_REQUEST['manufacturers_name'],
                    'url' => $_REQUEST['manufacturers_url']);
     
      if ( osC_Manufacturers_Admin::save(isset($_REQUEST['manufacturers_id']) ? $_REQUEST['manufacturers_id'] : null, $data) ) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));      
      }
     
      header('Content-Type: text/html');
      echo $toC_Json->encode($response);
    }
           
    function deleteManufacturers() {
      global $toC_Json, $osC_Language;
     
      $batch = explode(',', $_REQUEST['batch']);
      $error = false;
     
      foreach ($batch as $id) {
        if (!osC_Manufacturers_Admin::delete($id)) {
          $error = true;
          break;
        }
      }

      if ($error === false) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);               
    } 

    function deleteManufacturer() {
      global $toC_Json, $osC_Language;
      
      if ( osC_Manufacturers_Admin::delete($_REQUEST['manufacturers_id']) ) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
  }
?>
