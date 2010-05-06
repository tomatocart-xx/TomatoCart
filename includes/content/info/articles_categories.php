<?php
/*
  $Id: articles_categories.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require_once('includes/classes/articles.php');

  class osC_Info_Articles_categories extends osC_Template {

/* Private variables */

    var $_module = 'articles_categories',
        $_group = 'info',
        $_page_title,
        $_page_contents = 'articles_categories.php',
        $_page_image = 'table_background_reviews_new.gif';

/* Class constructor */

    function osC_Info_Articles_categories() {
      global $osC_Language;

      if ( isset($_GET['articles_categories_id']) && !empty($_GET['articles_categories_id']) ) {
        $this->_page_title = toC_Articles::getArticleCategoriesName($_GET['articles_categories_id']);
      } else {
        $this->_page_title = $osC_Language->get('info_article_categories_heading');
      }
    }
  }
?>
