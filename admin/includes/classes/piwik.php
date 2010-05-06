<?php
/*
  $Id: piwik.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Piwik {

/* Private data */
  
    var $hooks = array('visits' => 'getVisits', 
                       'unique_visitors' => 'getUniqueVisitors',
                       'actions' => 'getActions',
                       'visits_length' => 'getSumVisitsLength',
                       'visits_length_min' => 'getSumVisitsLengthMinutes',
                       'max_actions' => 'getMaxActions',
                       'bounce_count' => 'getBounceCount',
                       'bounce_rate' => 'getBounceRate',
                       'referer_type_direct' => 'getRefererTypeDirect',
                       'referer_type_search_engines' => 'getRefererTypeSearchEngine',
                       'referer_type_websites' => 'getRefererTypeWebsites',
                       'browser_type' => 'getBrowserType',
                       'browser' => 'getBrowser', 
                       'configurations' => 'getConfiguration', 
                       'resolutions' => 'getResolution', 
                       'continent' => 'getContinent', 
                       'user_country' => 'getUserCountry', 
                       'referers_search_engines' => 'getReferersSearchEngines', 
                       'referers_keywords' => 'getReferersKeywords', 
                       'referers_websites' => 'getReferersWebsites');

/* Class constructor */
    
    function toC_Piwik() {
      define('PIWIK_USER_PATH', DIR_FS_CATALOG . 'ext/piwik');
      define('PIWIK_INCLUDE_PATH', DIR_FS_CATALOG . 'ext/piwik');
      define('PIWIK_ENABLE_DISPATCH', false);
      define('PIWIK_ENABLE_ERROR_HANDLER', false);
      define('PIWIK_ENABLE_SESSION_START', 0);
      define('PIWIK_DISPLAY_ERRORS', 0);
      
      require_once PIWIK_INCLUDE_PATH . "/index.php";
      require_once PIWIK_INCLUDE_PATH . "/core/API/Request.php";
      Piwik_FrontController::getInstance()->init();
    }

    function getData($type, $start_date = null, $end_date = null, $period = null) {
      $method = $this->hooks[$type];
      
      return $this->$method($start_date, $end_date, $period);
    }
    
    function getWebsiteDateCreated() {
      global $osC_Database;
      
      $Qdate = $osC_Database->query('select DATE(ts_created) as date_created from :table_piwik_site where idsite = 1');
      $Qdate->bindTable(':table_piwik_site', TABLE_PIWIK_SITE);
      $Qdate->execute();
      
      if ($Qdate->numberOfRows() > 0) {
        return $Qdate->value('date_created');
      }
      
      return false;
    }
    
/* Private methods */
    
    function _getToken() {
      global $osC_Database;
      
      $Qtoken = $osC_Database->query('select token_auth from :toc_piwik_user where login = :login');
      $Qtoken->bindTable(':toc_piwik_user', TABLE_PIWIK_USER);
      $Qtoken->bindValue(':login', 'toc_piwik_view');
      $Qtoken->execute();
      
      return $Qtoken->value('token_auth');
    }
    
    function _processRequest($request) {
      $token_auth = self::_getToken();
      $request = new Piwik_API_Request($request . "&format=php&token_auth=" . $token_auth);
      $result = $request->process();
      $content = unserialize($result);
      
      return $content;
    }
    
/* Visits Summary */
    
    function _getVisitsSummary($method, $start_date = null, $end_date = null, $period = null) {
      $content = '';

      if (empty($start_date) || empty($end_date)) {
        $end_date = date('Y-m-d');
        $start_date = date("Y-m-d", strtotime('-1 month'));
      }

      $request = "method=VisitsSummary." . $method . "&idSite=1&date=$start_date,$end_date&period=$period";
      $content = $this->_processRequest($request);

      return $content;
    }

    function getVisits($start_date = null, $end_date = null, $period = 'day') {
        return $this->_getVisitsSummary('getVisits', $start_date, $end_date, $period);
    }

    function getUniqueVisitors($start_date = null, $end_date = null, $period = 'day') {
      return $this->_getVisitsSummary('getUniqueVisitors', $start_date, $end_date, $period);
    }

    function getActions($start_date = null, $end_date = null, $period = 'day') {
      return $this->_getVisitsSummary('getActions', $start_date, $end_date, $period);
    }

    function getSumVisitsLengthMinutes($start_date = null, $end_date = null, $period = 'day') {
      $records = $this->_getVisitsSummary('getSumVisitsLength', $start_date, $end_date, $period);
      
      $data = array();
      foreach ($records as $key => $value) {
        $data[$key] = floor($value / 60);
      }
      
      return $data;
    }
    
    function getMaxActions($start_date = null, $end_date = null, $period = 'day') {
      return $this->_getVisitsSummary('getMaxActions', $start_date, $end_date, $period);
    }
    
    function getBounceCount($start_date = null, $end_date = null, $period = 'day') {
      return $this->_getVisitsSummary('getBounceCount', $start_date, $end_date, $period);
    }

    function getBounceRate($start_date = null, $end_date = null, $period = 'day') {
      $counts = $this->_getVisitsSummary('getBounceCount', $start_date, $end_date, $period);
      $visits = $this->getVisits($start_date, $end_date, $period); 
      
      $data = array();
      foreach ($counts as $key => $value) {
        if ($visits[$key] == 0) {
          $data[$key] = 0;
        } else {
          $data[$key] = floor($value * 100 / $visits[$key]);
        }
      }

      return $data;
    }
      
/* Referers Type */
    
    function _getRefererType($start_date, $end_date, $period = 'day', $typeReferer = 1) {
      if (empty($start_date) || empty($end_date)) {
        $end_date = date('Y-m-d');
        $start_date = date("Y-m-d", strtotime('-1 month'));
      }
      
      $request = "method=Referers.getRefererType&idSite=1&date=$start_date,$end_date&period=$period&typeReferer=" . $typeReferer;
      $result = $this->_processRequest($request);
    
      $data = array();
      if (is_array($result)) {
        foreach ($result as $date => $values) {
          $nb_visits = 0;
          
          if ( is_array($values) && !empty($values) ) {
            $nb_visits = $values[0]['nb_visits'];
          }

          $data[$date] = $nb_visits;
        }
      }

      return $data;
    }
    
    function getRefererTypeDirect($start_date, $end_date, $period = 'day') {
      return $this->_getRefererType($start_date, $end_date, $period, 1);
    }
    
    function getRefererTypeSearchEngine($start_date, $end_date, $period = 'day') {
      return $this->_getRefererType($start_date, $end_date, $period, 2);
    }
    
    function getRefererTypeWebsites($start_date, $end_date, $period = 'day') {
      return $this->_getRefererType($start_date, $end_date, $period, 3);
    }
    
/* User Setting Summary */
    
    function _getUserSettingSummary($method, $start_date, $end_date, $period){
      if (empty($start_date) || empty($end_date)) {
        $end_date = date('Y-m-d');
        $start_date = date("Y-m-d", strtotime('-1 month'));
      }

      $request = "method=UserSettings.$method&idSite=1&date=$start_date,$end_date&period=$period";
      $result = $this->_processRequest($request);
                 
      $data = array();
      if (is_array($result)) {
        foreach ($result as $date => $values) {
          foreach ($values as $value) {
            if ( !isset($data[$value['label']]) || empty($data[$value['label']]) ) {
              $data[$value['label']] = $value['nb_visits'];
            } else {
               $data[$value['label']] += $value['nb_visits'];
            }
          }
        }
      }

      arsort($data);
      
      return $data;
    }

    function getBrowser($start_date, $end_date, $period){
      return $this->_getUserSettingSummary('getBrowser', $start_date, $end_date, $period);
    }
    
    function getBrowserType($start_date, $end_date, $period){
      return $this->_getUserSettingSummary('getBrowserType', $start_date, $end_date, $period);
    }

    function getOS($start_date, $end_date, $period){
      return $this->_getUserSettingSummary('getOS', $start_date, $end_date, $period);
    }

    function getResolution($start_date, $end_date, $period){
      return $this->_getUserSettingSummary('getResolution', $start_date, $end_date, $period);
    }
    
    function getConfiguration($start_date, $end_date, $period){
      return $this->_getUserSettingSummary('getConfiguration', $start_date, $end_date, $period);
    }
    
/* Country Summary */
    
    function _getCountrySummary($method, $start_date, $end_date, $period){
      if (empty($start_date) || empty($end_date)) {
        $end_date = date('Y-m-d');
        $start_date = date("Y-m-d", strtotime('-1 month'));
      }

      $request = "method=UserCountry.$method&idSite=1&date=$start_date,$end_date&period=$period";
      $result = $this->_processRequest($request);
      
      $data = array();
      if(is_array($result)){
        $isArray = false;
        
        foreach($result as $date => $values){
          if(!empty($values)) {
            foreach($values as $value){
              if(!isset($data[$value['label']]) || empty($data[$value['label']]) ){
                $data[$value['label']] = array('nb_label' => $value['label'], 'nb_visits' => $value['nb_visits'], 'code' => $value['code']);
              }else{
                 $data[$value['label']]['nb_visits'] += $value['nb_visits'];
              }
            }
            
            $isArray = true;
          }
        }
      }
      
      if($isArray === true) {
        arsort($data);
      } else {
        $data = array();
      }

      return $data;
    }

    function getUserCountry($start_date, $end_date, $period){
      return $this->_getCountrySummary('getCountry', $start_date, $end_date, $period);
    }

    function getContinent($start_date, $end_date, $period){
      return $this->_getCountrySummary('getContinent', $start_date, $end_date, $period);
    }
    
/* Referers Summary */
    
    function _getReferersSummary($method, $start_date, $end_date, $period){
      if (empty($start_date) || empty($end_date)) {
        $end_date = date('Y-m-d');
        $start_date = date("Y-m-d", strtotime('-1 month'));
      }
      
      $request = "method=Referers.$method&idSite=1&date=$start_date,$end_date&period=$period";
      $result = $this->_processRequest($request);
    
      $data = array();
      if (is_array($result)) {
        foreach ($result as $date => $values) {
          foreach ($values as $value) {
            if (!empty($value)) {
              if ( !isset($data[$value['label']]) || empty($data[$data[$value['label']]]) ) {
                $data[$value['label']] = $value['nb_visits'];
              } else {
                 $data[$value['label']] += $value['nb_visits'];
              }
            }
          }
        }
      }
	
      arsort($data);   
	      
      return $data;
    }

    function getReferersWebsites($start_date, $end_date, $period){
      return $this->_getReferersSummary('getWebsites', $start_date, $end_date, $period);
    }

    function getReferersKeywords($start_date, $end_date, $period){
      return $this->_getReferersSummary('getKeywords', $start_date, $end_date, $period);
    }

    function getReferersSearchEngines($start_date, $end_date, $period){
      return $this->_getReferersSummary('getSearchEngines', $start_date, $end_date, $period);
    }
  }
?>
