<?php
/*
  $Id: reports_web.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  include('includes/classes/piwik.php');  
  include('includes/classes/flash_line.php');
  include('includes/classes/flash_pie.php'); 
  include("external/sparkling/Drawing.class.php");
  include("external/sparkling/Sparkling.class.php");
  
  class toC_Json_Reports_Web {
  
// Visits Summary
    function loadVisitsSummaryPanel() {
      global $toC_Json, $osC_Language;

      $start_date = ( isset($_REQUEST['start_date']) && !empty($_REQUEST['start_date']) ) ? $_REQUEST['start_date'] : null;
      $end_date = ( isset($_REQUEST['end_date']) && !empty($_REQUEST['end_date']) ) ? $_REQUEST['end_date'] : null;
      $period = ( isset($_REQUEST['period']) && !empty($_REQUEST['period']) ) ? $_REQUEST['period'] : 'day';

      $toC_Piwik = new toC_Piwik();
      $sparkling = new Sparkling();
      
      //Visits
      $visits_data = $toC_Piwik->getData('visits', $start_date, $end_date, $period);
      $sum_visits = array_sum(array_values($visits_data));
      
      //sparkline
      $value[0] = array_values($visits_data);
      $sparkling->lineChart($value, 100, 15, array('blue'), array('line'));
      $file = DIR_FS_CACHE_ADMIN . '/sparkline_' . $_SESSION['admin']['id'] . '_visits.png';
      $sparkling->output($file);
  
      //Unique visitors
      $unique_visitors_data = $toC_Piwik->getData('unique_visitors', $start_date, $end_date, $period);
      $num_unique_visitors = array_sum(array_values($unique_visitors_data));
      
      //sparkline
      $value[0] = array_values($unique_visitors_data);
      $sparkling->lineChart($value, 100, 15, array('blue'), array('line'));
      $file = DIR_FS_CACHE_ADMIN . '/sparkline_' . $_SESSION['admin']['id'] . '_unique_visitors.png';
      $sparkling->output($file);
      
      //Actions
      $actions_data = $toC_Piwik->getData('actions', $start_date, $end_date, $period);
      $num_actions = array_sum(array_values($actions_data));
      
      //sparkline
      $value[0] = array_values($actions_data);
      $sparkling->lineChart($value, 100, 15, array('blue'), array('line'));
      $file = DIR_FS_CACHE_ADMIN . '/sparkline_' . $_SESSION['admin']['id'] . '_actions.png';
      $sparkling->output($file);
      
      //Visits Length
      $visits_length_data = $toC_Piwik->getData('visits_length_min', $start_date, $end_date, $period);
      $num_visits_length = floor(array_sum(array_values($visits_length_data)) / 60);
      
      //sparkline
      $value[0] = array_values($visits_length_data);
      $sparkling->lineChart($value, 100, 15, array('blue'), array('line'));
      $file = DIR_FS_CACHE_ADMIN . '/sparkline_' . $_SESSION['admin']['id'] . '_visits_length_min.png';
      $sparkling->output($file);
      
      //Max Actions
      $max_actions_data = $toC_Piwik->getData('max_actions', $start_date, $end_date, $period);
      $num_max_actions = max(array_values($max_actions_data));
      
      //sparkline
      $value[0] = array_values($max_actions_data);
      $sparkling->lineChart($value, 100, 15, array('blue'), array('line'));
      $file = DIR_FS_CACHE_ADMIN . '/sparkline_' . $_SESSION['admin']['id'] . '_max_actions.png';
      $sparkling->output($file);
      
      //Bounce Rate
      $bounce_rate_data = $toC_Piwik->getData('bounce_rate', $start_date, $end_date, $period);
      $count = 0;
      $sum_rate = 0;
      foreach ($bounce_rate_data as $date => $rate) {
        if ($rate > 0) {
          $sum_rate += $rate;
          $count++;
        }
      }
      
      $avg_bounce_rate = 0;
      if ($count > 0) {
        $avg_bounce_rate = floor($sum_rate / $count);
      }
      
      //sparkline
      $value[0] = array_values($max_actions_data);
      $sparkling->lineChart($value, 100, 15, array('blue'), array('line'));
      $file = DIR_FS_CACHE_ADMIN . '/sparkline_' . $_SESSION['admin']['id'] . '_bounce_rate.png';
      $sparkling->output($file);
            
      $src = 'json.php?module=reports_web&action=draw_visits_summary_sparkline&start_date=' . $start_date . '&end_date=' . $end_date . '&period=' . $period;
      
      $data['visits_panel'] = '<p class="sparkline"><img src="' . $src . '&type=visits" />&nbsp;&nbsp;<span>' . $sum_visits . '</span>&nbsp;' . $osC_Language->get('sparkline_label_visits') . '</p>';
      $data['unique_visits_panel'] = '<p class="sparkline"><img  src="' . $src . '&type=unique_visitors"/>&nbsp;&nbsp;<span>' . $num_unique_visitors . '</span>&nbsp;' . $osC_Language->get('sparkline_label_unique_visitors') . '</p>';
      $data['actions_panel'] = '<p class="sparkline"><img  src="' . $src . '&type=actions"/>&nbsp;&nbsp;<span>' . $num_actions . '</span>&nbsp;' . $osC_Language->get('sparkline_label_actions') . '</p>';
      $data['visits_length_panel'] = '<p class="sparkline"><img  src="' . $src . '&type=visits_length_min"/>&nbsp;&nbsp;<span>' . $num_visits_length . '</span>&nbsp;' . $osC_Language->get('sparkline_label_visits_length_min') . '</p>';
      $data['max_actions_panel'] = '<p class="sparkline"><img  src="' . $src . '&type=max_actions"/>&nbsp;&nbsp;<span>' . $num_max_actions . '</span>&nbsp;' . $osC_Language->get('sparkline_label_max_actions') . '</p>';
      $data['bounce_rate_panel'] = '<p class="sparkline"><img  src="' . $src . '&type=bounce_rate"/>&nbsp;&nbsp;<span>' . $avg_bounce_rate . '%</span>&nbsp;' . $osC_Language->get('sparkline_label_bounce_rate') . '</p>';
      
      $response = array('success' => true, 'data' => $data);
     
      echo $toC_Json->encode($response);   
    }
    
    function drawVisitsSummarySparkline() {
      $start_date = ( isset($_REQUEST['start_date']) && !empty($_REQUEST['start_date']) ) ? $_REQUEST['start_date'] : null;
      $end_date = ( isset($_REQUEST['end_date']) && !empty($_REQUEST['end_date']) ) ? $_REQUEST['end_date'] : null;
      $period = ( isset($_REQUEST['period']) && !empty($_REQUEST['period']) ) ? $_REQUEST['period'] : 'day';
      $type = ( isset($_REQUEST['type']) && !empty($_REQUEST['type']) ) ? $_REQUEST['type'] : 'visits';
      
      $file = DIR_FS_CACHE_ADMIN . '/sparkline_' . $_SESSION['admin']['id'] . '_' . $type . '.png';
      
      header("Content-type: image/png");
      header('Content-Length: ' . filesize($file));
      header('Pragma: public');
      header('Expires: 0');
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      readfile($file);
    }

    function _getFlashChartToolTip($type) {
      global $osC_Language;

      $tooltips = array(
        'visits' => '#x_label# <br> #val# ' . $osC_Language->get('tooltip_label_visits'),
        'unique_visitors' => '#x_label# <br> #val# ' . $osC_Language->get('tooltip_label_unique_visitors'),
        'actions' => '#x_label# <br> #val# ' . $osC_Language->get('tooltip_label_actions'),
        'visits_length_min' => '#x_label# <br> #val# ' . $osC_Language->get('tooltip_label_visits_length_min'),
        'max_actions' => '#x_label# <br> #val# ' . $osC_Language->get('tooltip_label_max_actions'),
        'bounce_rate' => '#x_label# <br> #val# ' . $osC_Language->get('tooltip_label_bounce_rate'),
        'referer_type_direct' => '#x_label# <br> #val# ' . $osC_Language->get('tooltip_label_referer_type_direct'),
        'referer_type_websites' => '#x_label# <br> #val# ' . $osC_Language->get('tooltip_label_referer_type_websites'),
        'referer_type_search_engines' => '#x_label# <br> #val# ' . $osC_Language->get('tooltip_label_referer_type_search_engines')
      );
      
      if (isset($tooltips[$type])) {
        return $tooltips[$type];
      }
      
      return '#x_label# <br> #val#';
    }    
    
    function renderVisitsSummaryFlashData() {
      global $osC_Language;
      
      $start_date = ( isset($_REQUEST['start_date']) && !empty($_REQUEST['start_date']) ) ? $_REQUEST['start_date'] : null;
      $end_date = ( isset($_REQUEST['end_date']) && !empty($_REQUEST['end_date']) ) ? $_REQUEST['end_date'] : null;
      $period = ( isset($_REQUEST['period']) && !empty($_REQUEST['period']) ) ? $_REQUEST['period'] : 'day';
      $type = ( isset($_REQUEST['type']) && !empty($_REQUEST['type']) ) ? $_REQUEST['type'] : 'visits';
      
      $toC_Piwik = new toC_Piwik();
      $visitsData = $toC_Piwik->getData($type, $start_date, $end_date, $period);

      $chart = new toC_Flash_Last_Visits_Chart($osC_Language->get('flash_chart_heading_' . $type));
      $chart->setData($visitsData);
      $chart->setToolTip(self::_getFlashChartToolTip($type));
      $chart->render();
    }
    
  // Traffic Source Summary    
    function loadTrafficSourceSummaryPanel() {
      global $toC_Json, $osC_Language;

      $start_date = ( isset($_REQUEST['start_date']) && !empty($_REQUEST['start_date']) ) ? $_REQUEST['start_date'] : null;
      $end_date = ( isset($_REQUEST['end_date']) && !empty($_REQUEST['end_date']) ) ? $_REQUEST['end_date'] : null;
      $period = ( isset($_REQUEST['period']) && !empty($_REQUEST['period']) ) ? $_REQUEST['period'] : 'day';

      $toC_Piwik = new toC_Piwik();
      $sparkling = new Sparkling();
      
      //Direct Entries
      $referer_direct_data = $toC_Piwik->getData('referer_type_direct', $start_date, $end_date, $period);
      $num_direct = array_sum(array_values($referer_direct_data));
      
      //sparkline
      $value[0] = array_values($referer_direct_data);
      $sparkling->lineChart($value, 100, 15, array('blue'), array('line'));
      $file = DIR_FS_CACHE_ADMIN . '/sparkline_' . $_SESSION['admin']['id'] . '_referer_type_direct.png';
      $sparkling->output($file);
  
      //From Websites
      $referer_websites_data = $toC_Piwik->getData('referer_type_websites', $start_date, $end_date, $period);
      $num_websites = array_sum(array_values($referer_websites_data));
      
      //sparkline
      $value[0] = array_values($referer_websites_data);
      $sparkling->lineChart($value, 100, 15, array('blue'), array('line'));
      $file = DIR_FS_CACHE_ADMIN . '/sparkline_' . $_SESSION['admin']['id'] . '_referer_type_websites.png';
      $sparkling->output($file);
      
      //From Search
      $referer_search_data = $toC_Piwik->getData('referer_type_search_engines', $start_date, $end_date, $period);
      $num_search_engine = array_sum(array_values($referer_search_data));
      
      //sparkline
      $value[0] = array_values($referer_search_data);
      $sparkling->lineChart($value, 100, 15, array('blue'), array('line'));
      $file = DIR_FS_CACHE_ADMIN . '/sparkline_' . $_SESSION['admin']['id'] . '_referer_type_search_engines.png';
      $sparkling->output($file);
      
      $src = 'json.php?module=reports_web&action=draw_traffic_source_summary_sparkline&start_date=' . $start_date . '&end_date=' . $end_date . '&period=' . $period;
      
      $data['referer_type_direct_panel'] = '<p class="sparkline"><img src="' . $src . '&type=referer_type_direct" />&nbsp;&nbsp;<span>' . $num_direct . '</span>&nbsp;' . $osC_Language->get('sparkline_label_referer_type_direct') . '</p>';
      $data['referer_type_websites_panel'] = '<p class="sparkline"><img  src="' . $src . '&type=referer_type_websites"/>&nbsp;&nbsp;<span>' . $num_websites . '</span>&nbsp;' . $osC_Language->get('sparkline_label_referer_type_websites') . '</p>';
      $data['referer_type_search_engines_panel'] = '<p class="sparkline"><img  src="' . $src . '&type=referer_type_search_engines"/>&nbsp;&nbsp;<span>' . $num_search_engine . '</span>&nbsp;' . $osC_Language->get('sparkline_label_referer_type_search_engines') . '</p>';

      $response = array('success' => true, 'data' => $data);
     
      echo $toC_Json->encode($response);   
    }
    
    function drawTrafficSourceSummarySparkline() {
    
      $start_date = ( isset($_REQUEST['start_date']) && !empty($_REQUEST['start_date']) ) ? $_REQUEST['start_date'] : null;
      $end_date = ( isset($_REQUEST['end_date']) && !empty($_REQUEST['end_date']) ) ? $_REQUEST['end_date'] : null;
      $period = ( isset($_REQUEST['period']) && !empty($_REQUEST['period']) ) ? $_REQUEST['period'] : 'day';
      $type = ( isset($_REQUEST['type']) && !empty($_REQUEST['type']) ) ? $_REQUEST['type'] : 'referer_type_direct';
      
      $file = DIR_FS_CACHE_ADMIN . '/sparkline_' . $_SESSION['admin']['id'] . '_' . $type . '.png';
      
      header("Content-type: image/png");
      header('Content-Length: ' . filesize($file));
      header('Pragma: public');
      header('Expires: 0');
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      readfile($file);
    }
    
    function renderTrafficSourceSummaryFlashData() {
      global $osC_Language;

      $start_date = ( isset($_REQUEST['start_date']) && !empty($_REQUEST['start_date']) ) ? $_REQUEST['start_date'] : null;
      $end_date = ( isset($_REQUEST['end_date']) && !empty($_REQUEST['end_date']) ) ? $_REQUEST['end_date'] : null;
      $period = ( isset($_REQUEST['period']) && !empty($_REQUEST['period']) ) ? $_REQUEST['period'] : 'day';
      $type = ( isset($_REQUEST['type']) && !empty($_REQUEST['type']) ) ? $_REQUEST['type'] : 'referer_type_direct';

      $toC_Piwik = new toC_Piwik();
      $visitsData = $toC_Piwik->getData($type, $start_date, $end_date, $period);
      
      $chart = new toC_Flash_Last_Visits_Chart($osC_Language->get('flash_chart_heading_' . $type));
      $chart->setData($visitsData);
      $chart->setToolTip(self::_getFlashChartToolTip($type));
      $chart->render();
    }  

    function renderTrafficSourceSummaryPieChartData() {
      global $osC_Language;

      $start_date = ( isset($_REQUEST['start_date']) && !empty($_REQUEST['start_date']) ) ? $_REQUEST['start_date'] : null;
      $end_date = ( isset($_REQUEST['end_date']) && !empty($_REQUEST['end_date']) ) ? $_REQUEST['end_date'] : null;
      $period = ( isset($_REQUEST['period']) && !empty($_REQUEST['period']) ) ? $_REQUEST['period'] : 'day';

      $toC_Piwik = new toC_Piwik();
            
      //Direct Entries
      $referer_direct_data = $toC_Piwik->getData('referer_type_direct', $start_date, $end_date, $period);
      $num_direct = array_sum(array_values($referer_direct_data));
  
      //From Websites
      $referer_websites_data = $toC_Piwik->getData('referer_type_websites', $start_date, $end_date, $period);
      $num_websites = array_sum(array_values($referer_websites_data));
      
      //From Search
      $referer_search_data = $toC_Piwik->getData('referer_type_search_engines', $start_date, $end_date, $period);
      $num_search_engine = array_sum(array_values($referer_search_data));
      
      $data = array();
      
      if ($num_direct > 0) {
        $data[$osC_Language->get('flash_pie_chart_direct_access')] = $num_direct;
      }
      
      if ($num_websites > 0) {
        $data[$osC_Language->get('flash_pie_chart_websites')] = $num_websites;
      }
      
      if ($num_search_engine > 0) {
        $data[$osC_Language->get('flash_pie_chart_search_engines')] = $num_search_engine;
      }
      
      $chart = new toC_Flash_Pie();
      $chart->setData($data);
      $chart->render();
    }

//  Visit Settings   
    function renderBrowserFamiliesPieChartData() {
      $start_date = ( isset($_REQUEST['start_date']) && !empty($_REQUEST['start_date']) ) ? $_REQUEST['start_date'] : null;
      $end_date = ( isset($_REQUEST['end_date']) && !empty($_REQUEST['end_date']) ) ? $_REQUEST['end_date'] : null;
      $period = ( isset($_REQUEST['period']) && !empty($_REQUEST['period']) ) ? $_REQUEST['period'] : 'week';

      $toC_Piwik = new toC_Piwik();
      $data = $toC_Piwik->getData('browser', $start_date, $end_date, $period);   
      
      $records = array();
      if (sizeof($data) > 0) {
        foreach ($data as $type => $count) {
          $result = array();
          preg_match("/[\/\sa-z(]*/i", $type, $results);
          $browser = rtrim($results[0]);
          
          if (isset($records[$browser])) {
            $records[$browser] = $records[$browser] + $count;
          } else {
            $records[$browser] = $count;
          }
        }
      }

      $chart = new toC_Flash_Pie();
      $chart->setData($records);
      $chart->render();
    }
    
    function getBrowsersData() {
	    global $toC_Json;
      
	    $start_date = ( isset($_REQUEST['start_date']) && !empty($_REQUEST['start_date']) ) ? $_REQUEST['start_date'] : null;
      $end_date = ( isset($_REQUEST['end_date']) && !empty($_REQUEST['end_date']) ) ? $_REQUEST['end_date'] : null;
      $period = ( isset($_REQUEST['period']) && !empty($_REQUEST['period']) ) ? $_REQUEST['period'] : 'week';
      	      
	    $toC_Piwik = new toC_Piwik();
	    $browsers = $toC_Piwik->getData('browser', $start_date, $end_date, $period);
	       
	    $records = array();
	    foreach ( $browsers as $browser => $visitors ) {
	      $records[] = array('browser' => $browser, 'visitors' => $visitors);
	    }
	    
	    $response = array(EXT_JSON_READER_TOTAL => sizeof($records),
	                      EXT_JSON_READER_ROOT => $records);

	    echo $toC_Json->encode($response);
    }    
    
    function getConfigurationsData() {
      global $toC_Json;
      
      $start_date = ( isset($_REQUEST['start_date']) && !empty($_REQUEST['start_date']) ) ? $_REQUEST['start_date'] : null;
      $end_date = ( isset($_REQUEST['end_date']) && !empty($_REQUEST['end_date']) ) ? $_REQUEST['end_date'] : null;
      $period = ( isset($_REQUEST['period']) && !empty($_REQUEST['period']) ) ? $_REQUEST['period'] : 'week';
              
	    $toC_Piwik = new toC_Piwik();
	    
	    $configurations = $toC_Piwik->getData('configurations', $start_date, $end_date, $period);
	    
	    $records = array();
	    foreach ( $configurations as $configuration => $visitors ) {
	      $records[] = array('configurations' => $configuration, 'visitors' => $visitors);
	    }
	    
	    $response = array(EXT_JSON_READER_TOTAL => sizeof($records),
	                      EXT_JSON_READER_ROOT => $records);
	                    
	    echo $toC_Json->encode($response);
    }

    function getResolutionData() {
      global $toC_Json;
      
      $start_date = ( isset($_REQUEST['start_date']) && !empty($_REQUEST['start_date']) ) ? $_REQUEST['start_date'] : null;
      $end_date = ( isset($_REQUEST['end_date']) && !empty($_REQUEST['end_date']) ) ? $_REQUEST['end_date'] : null;
      $period = ( isset($_REQUEST['period']) && !empty($_REQUEST['period']) ) ? $_REQUEST['period'] : 'week';  
              
	    $toC_Piwik = new toC_Piwik();
	    $resolutions =$toC_Piwik->getData('resolutions', $start_date, $end_date, $period);
	    
	    $records = array();
	    foreach ( $resolutions as $resolution => $visitors ) {
	      $records[] = array('resolutions' => $resolution, 'visitors' => $visitors);
	    }
	    
	    $response = array(EXT_JSON_READER_TOTAL => sizeof($records),
	                      EXT_JSON_READER_ROOT => $records);
	                    
	    echo $toC_Json->encode($response);
    } 

// Visit Location    
    function renderContinentPieChartData() {

      $start_date = ( isset($_REQUEST['start_date']) && !empty($_REQUEST['start_date']) ) ? $_REQUEST['start_date'] : null;
      $end_date = ( isset($_REQUEST['end_date']) && !empty($_REQUEST['end_date']) ) ? $_REQUEST['end_date'] : null;
      $period = ( isset($_REQUEST['period']) && !empty($_REQUEST['period']) ) ? $_REQUEST['period'] : 'week';   
      
      $toC_Piwik = new toC_Piwik();
      $continents = $toC_Piwik->getData('continent', $start_date, $end_date, $period);   
      
      $sum = 0;
      foreach ( $continents as $key => $continent ) {
        $sum += $continent['nb_visits'];
      }

      $others = 0;
      $data = array();
      foreach ( $continents as $key => $continent ) {
        if (($continent['nb_visits'] * 100 / $sum) <= 5) {
          $others = $others + $continent['nb_visits'];
        } else {
          $data = $data + array($continent['nb_label'] => $continent['nb_visits']);
        }
      }

      if ($others > 0) {
        $data = $data + array('Others' => $others);
      }
        
      arsort($data);      
      
      $chart = new toC_Flash_Pie();
      $chart->setData($data);
      $chart->render();
    }    

    function getCountryData() { 
	    global $toC_Json, $osC_Database;

      $start_date = ( isset($_REQUEST['start_date']) && !empty($_REQUEST['start_date']) ) ? $_REQUEST['start_date'] : null;
      $end_date = ( isset($_REQUEST['end_date']) && !empty($_REQUEST['end_date']) ) ? $_REQUEST['end_date'] : null;
	    $period = ( isset($_REQUEST['period']) && !empty($_REQUEST['period']) ) ? $_REQUEST['period'] : 'week';   
	    
	    $toC_Piwik = new toC_Piwik();
	    $country_data = $toC_Piwik->getData('user_country', $start_date, $end_date, $period);
      
	    $records = array();
	    foreach ( $country_data as $key => $value ) {
	      $nb_visits[]  = $value['nb_visits']; 
	        
	      $records[] = array('country' => osc_image('../images/worldflags/' . strtolower($value['code']) . '.png') . '&nbsp;&nbsp;' . $value['nb_label'],
	                         'visitors' => $value['nb_visits']);
	    }

	    array_multisort($nb_visits, SORT_DESC, $records);
   
	    $response = array(EXT_JSON_READER_TOTAL => sizeof($records),
	                      EXT_JSON_READER_ROOT => $records);
                
	    echo $toC_Json->encode($response);
    }    
    
 // Search Engines
    function getKeywordsData() {
      global $toC_Json;

      $start_date = ( isset($_REQUEST['start_date']) && !empty($_REQUEST['start_date']) ) ? $_REQUEST['start_date'] : null;
      $end_date = ( isset($_REQUEST['end_date']) && !empty($_REQUEST['end_date']) ) ? $_REQUEST['end_date'] : null;
      $period = ( isset($_REQUEST['period']) && !empty($_REQUEST['period']) ) ? $_REQUEST['period'] : 'day';            
      
      $toC_Piwik = new toC_Piwik();
      $keywords = $toC_Piwik->getData('referers_keywords', $start_date, $end_date, $period);
      
      $records = array();
      foreach ( $keywords as $keyword => $visitors ) {
        $records[] = array('keywords' => $keyword, 'visitors' => $visitors);
      }
      
      $response = array(EXT_JSON_READER_TOTAL => sizeof($records),
                        EXT_JSON_READER_ROOT => $records);
                      
      echo $toC_Json->encode($response);
    }
    
    function getSearchEnginesData() {
	    global $toC_Json;
	    
	    $start_date = ( isset($_REQUEST['start_date']) && !empty($_REQUEST['start_date']) ) ? $_REQUEST['start_date'] : null;
      $end_date = ( isset($_REQUEST['end_date']) && !empty($_REQUEST['end_date']) ) ? $_REQUEST['end_date'] : null;    
	    $period = ( isset($_REQUEST['period']) && !empty($_REQUEST['period']) ) ? $_REQUEST['period'] : 'day';           
	    
	    $toC_Piwik = new toC_Piwik();
	    $search_engines = $toC_Piwik->getData('referers_search_engines', $start_date, $end_date, $period);
	    
	    $records = array();
	    foreach ( $search_engines as $search_engine => $visitors ) {
	      $records[] = array('search_engines' => $search_engine, 'visitors' => $visitors);
	    }
	    
	    $response = array(EXT_JSON_READER_TOTAL => sizeof($records),
	                      EXT_JSON_READER_ROOT => $records);
	                    
	    echo $toC_Json->encode($response);
    }

//Referer Websites
    function getRefererWebsites() {
	    global $toC_Json;
	    
      $start_date = ( isset($_REQUEST['start_date']) && !empty($_REQUEST['start_date']) ) ? $_REQUEST['start_date'] : null;
      $end_date = ( isset($_REQUEST['end_date']) && !empty($_REQUEST['end_date']) ) ? $_REQUEST['end_date'] : null;
      $period = ( isset($_REQUEST['period']) && !empty($_REQUEST['period']) ) ? $_REQUEST['period'] : 'day'; 
    
	    $toC_Piwik = new toC_Piwik();
	    $websites = $toC_Piwik->getData('referers_websites', $start_date, $end_date, $period);

	    $records = array();
	    foreach ( $websites as $website => $visitors ) {
	      $records[] = array('websites' => $website, 'visitors' => $visitors);
	    }
	    
	    $response = array(EXT_JSON_READER_TOTAL => sizeof($records),
	                      EXT_JSON_READER_ROOT => $records);
	                    
	    echo $toC_Json->encode($response);
    }
  }      
?>