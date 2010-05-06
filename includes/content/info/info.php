<?php
/*
  $Id: info.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require_once('includes/classes/articles.php');

  class osC_Info_Info extends osC_Template {

/* Private variables */

    var $_module = 'info',
        $_group = 'info',
        $_page_title,
        $_page_contents = 'info.php',
        $_page_image = 'table_background_reviews_new.gif';

    function osC_Info_Info() {
      global $osC_Language, $osC_Database, $content, $breadcrumb, $osC_Services, $Qarticle;

      if (isset($_GET['articles_id']) && !empty($_GET['articles_id'])) {
        $Qarticle = osC_Articles::getEntry($_GET['articles_id']);

        if($Qarticle->numberofRows() > 0){
          $this->_page_title = $Qarticle->value('articles_name');

          if ($osC_Services->isStarted('breadcrumb')) {
            $breadcrumb->add($osC_Language->get($Qarticle->value('articles_name')), osc_href_link(FILENAME_INFO, 'articles&articles_id=' . $_GET['articles_id']));
          }
        } else {
          $this->_page_title = $osC_Language->get('info_not_found_heading');
          $this->_page_contents = 'info_not_found.php';
        }
      } else {
        $this->_page_title = $osC_Language->get('info_not_found_heading');
        $this->_page_contents = 'info_not_found.php';
      }
    }
  }
?>
