<?php
/*
  $Id: popular_search_terms_tag_cloud.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  
  class osC_Boxes_popular_search_terms_tag_cloud extends osC_Modules {
    var $_title,
        $_code = 'popular_search_terms_tag_cloud',
        $_author_name = 'TomatoCart',
        $_author_www = 'http://www.tomatocart.com',
        $_group = 'boxes';

    function osC_Boxes_popular_search_terms_tag_cloud() {
      global $osC_Language;

      $this->_title = $osC_Language->get('box_popular_search_terms_tag_cloud_heading');
    }

    function initialize() {
      global $osC_Database;
      
      $Qterms = $osC_Database->query('select search_terms_id, text, search_count from :table_search_terms where show_in_terms = 1');
      $Qterms->bindTable(':table_search_terms', TABLE_SEARCH_TERMS);
      $Qterms->execute();
      
      $search_terms = array();
      while($Qterms->next()) {
        $search_terms[] = array(
          'tag' => $Qterms->value('text'), 
          'url' => osc_href_link(FILENAME_SEARCH, 'keywords=' . $Qterms->value('text')), 
          'count' => $Qterms->valueInt('search_count'));
      }
      
      require('includes/classes/tag_cloud.php');
      $cloud = new toC_Tag_Cloud($search_terms);
      
      $this->_content = $cloud->generateTagCloud();
    }
  }
?>
