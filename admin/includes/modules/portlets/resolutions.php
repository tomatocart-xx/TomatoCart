<?php
/*
  $Id: resolutions.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

class toC_Portlet_Resolutions extends toC_Portlet {

  var $_title,
      $_code = 'resolutions';

  function toC_Portlet_Resolutions() {
    global $osC_Language;
    
    $this->_title = $osC_Language->get('portlet_resolutions_title');
  }
  
  function renderView() {
    $config = array(
      'title' => '"' . $this->_title . '"',
      'code' => '"' . $this->_code . '"', 
      'height' => 200,
      'layout' => '"fit"',
      'swf' => '"' . osc_href_link_admin('external/open-flash-chart/open-flash-chart.swf') . '"', 
      'flashvars' => array('data' => '"' . osc_href_link_admin(FILENAME_JSON, 'module=dashboard&action=render_data&portlet=' . $this->_code) . '"'),
      'plugins' => 'new Ext.ux.PortletFlashPlugin()');
    
    $response = array('success' => true, 'view' => $config);
    return $this->encodeArray($response);
  }
  
  function renderData() {
    global $osC_Language;
    
   include('includes/classes/piwik.php');
    include('includes/classes/flash_pie.php');      
    
    $end_date = date("Y-m-d");
    $start_date = date("Y-m-d", strtotime('-2 weeks'));

    $toC_Piwik = new toC_Piwik();
    
    $resolutions_data =$toC_Piwik->getResolution($start_date, $end_date, 'day');
    
    $pie_chart = new toC_Flash_Pie('', '80', '');
    $pie_chart->setData($resolutions_data);
    
    $pie_chart->render();
  }
}
?>