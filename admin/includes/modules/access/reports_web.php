<?php
/*
  $Id: reports_web.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Reports_Web extends osC_Access {
    var $_module = 'reports_web',
        $_group = 'reports',
        $_icon = 'world.png',
        $_title,
        $_sort_order = 300;

    function osC_Access_Reports_Web() {
      global $osC_Language;

      $this->_title = $osC_Language->get('access_reports_web_title');

      $this->_subgroups = array(array('iconCls' => 'icon-reports-web-win',
                                      'shortcutIconCls' => 'icon-reports-web-shortcut',
                                      'title' => $osC_Language->get('access_visits_summary_title'),
                                      'identifier' => 'reports_web-visits-summary-win',
                                      'params' => array('report' => 'visits-summary')),
                                array('iconCls' => 'icon-reports-web-win',
                                      'shortcutIconCls' => 'icon-reports-web-shortcut',
                                      'title' => $osC_Language->get('access_traffic_source_summary_title'),
                                      'identifier' => 'reports_web-traffic_source_summary-win',
                                      'params' => array('report' => 'traffic_source_summary')),
                                array('iconCls' => 'icon-reports-web-win',
                                      'shortcutIconCls' => 'icon-reports-web-shortcut',
                                      'title' => $osC_Language->get('access_visit_settings_title'),
                                      'identifier' => 'reports_web-visit_settings-win',
                                      'params' => array('report' => 'visit_settings')),
                                array('iconCls' => 'icon-reports-web-win',
                                      'shortcutIconCls' => 'icon-reports-web-shortcut',
                                      'title' => $osC_Language->get('access_visit_location_title'),
                                      'identifier' => 'reports_web-visit_location-win',
                                      'params' => array('report' => 'visit_location')),
                                array('iconCls' => 'icon-reports-web-win',
                                      'shortcutIconCls' => 'icon-reports-web-shortcut',
                                      'title' => $osC_Language->get('access_search_engines_title'),
                                      'identifier' => 'reports_web-search_engines-win',
                                      'params' => array('report' => 'search_engines')),
                                array('iconCls' => 'icon-reports-web-win',
                                      'shortcutIconCls' => 'icon-reports-web-shortcut',
                                      'title' => $osC_Language->get('access_referer_websites_title'),
                                      'identifier' => 'reports_web-referer_websites-win',
                                      'params' => array('report' => 'referer_websites')));
    }
  }
?>
