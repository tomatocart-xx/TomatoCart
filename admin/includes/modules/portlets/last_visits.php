<?php
/*
  $Id: last_visits.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

class osC_Portlet_Last_Visits extends osC_Portlet {

  var $_title,
      $_code = 'last_visits';

  function osC_Portlet_Last_Visits() {
    global $osC_Language;
    
    $this->_title = $osC_Language->get('portlet_last_visits_title');
  }
  
  function renderView() {
    $config = array(
      'title' => '"' . $this->_title . '"', 
      'code' => '"' . $this->_code . '"',
      'height' => 200,
      'layout' => '"fit"',
      'swf' => '"' . HTTP_SERVER . DIR_WS_HTTP_CATALOG . DIR_FS_ADMIN. 'external/open-flash-chart/open-flash-chart.swf"', 
      'flashvars' => array('data' => '"' . HTTP_SERVER . DIR_WS_HTTP_CATALOG . DIR_FS_ADMIN. 'json.php?module=dashboard&action=render_data&portlet=' . $this->_code . '"'),
      'plugins' => 'new Ext.ux.PortletFlashPlugin()');
    
    $response = array('success' => true, 'view' => $config);
    return $this->encodeArray($response);
  }
  
  function renderData() {
    global $osC_Database, $osC_Language;

    require_once('includes/classes/piwik.php');
    require_once('includes/classes/flash_line.php');
          
    $toC_Piwik = new toC_Piwik();
    
    $end_date = date("Y-m-d");
    $start_date = date("Y-m-d", strtotime('-2 weeks'));
    
    $data = $toC_Piwik->getVisits($start_date, $end_date);
    
    $chart = new toC_Flash_Last_Visits_Chart(' ');
    $chart->setData($data);
    
    $chart->render();
  }
}
?>