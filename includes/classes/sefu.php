<?php
/*
  $Id: sefu.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2004 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

class osC_Sefu{

  var $_process_methods = array(),
      $_anchors = array(),
      $_products_cache = array(),
      $_products_reviews_cache = array(),
      $_articles_cache = array(),
      $_article_categories_cache = array(),
      $_manufacturers_cache = array(),
      $_faqs_cache = array();

  function osC_Sefu(){
    $this->_reg_anchors = array('products_id' => '-p-',
                                'cPath' => '-c-',
                                'articles_id' => '-a-',
                                'faqs_id' => '-f-',
                                'articles_categories_id' => '-ac-',
                                'reviews' => '-r-',
                                'reviews_new' => '-rn-',
                                'products_reviews' => '-pr-',
                                'manufacturers' => '-m-',
                                'tell_a_friend' => '-pt-');

    $this->initialize();
  }

  function initialize(){
    $this->_iniProductsCache();
    $this->_iniArticlesCache();
    $this->_iniProductsReviewsCache();
    $this->_iniArticleCategoriesCache();
    $this->_iniFaqsCache();
    $this->_iniManufacturersCache();
  }

  function generateURL($link, $page){
    if (SERVICES_KEYWORD_RICH_URLS == '0') {
      $link = str_replace(array('?', '&', '='), array('/', '/', ','), $link);
    } else if(SERVICES_KEYWORD_RICH_URLS == '1'){
      $link = $this->generateRichKeywordURL($link, $page);
    } else{
      if (strpos($link, '&') !== false) {
        $link = str_replace('&', '&amp;', $link);
      }
    }

    return $link;
  }

  function generateRichKeywordURL($link, $page){
    if ( preg_match("/index.php\?cPath=([0-9_]+)(.*)/", $link, $matches) > 0 ) {
      $link = $this->makeUrl($page, $this->getCategoryName($matches[1]), 'cPath', $matches[1]);
      if( !empty($matches[2]) ){
        $link .= '?' . substr($matches[2], 1);
      }
    } else if ( preg_match("/index.php\?manufacturers=([0-9]+)(.*)/", $link, $matches) > 0 ) {
      $link = $this->makeUrl($page, $this->getManufacturersName($matches[1]), 'manufacturers', $matches[1]);
      if ( !empty($matches[2]) ) {
        $link .= '?' . substr($matches[2], 1);
      }
    } else if ( preg_match("/index.php\?([0-9]+)(.*)/", $link, $matches) > 0 ) {
      $link = $this->makeUrl($page, $this->getProductsName($matches[1]), 'products_id', $matches[1]);
      if ( !empty($matches[2]) ) {
        $link .= '?' . substr($matches[2], 1);
      }
    } else if ( preg_match("/products.php\?([0-9]+)(.*)/", $link, $matches) > 0 ) {
      $link = $this->makeUrl($page, $this->getProductsName($matches[1]), 'products_id', $matches[1]);
      if ( !empty($matches[2]) ) {
        $link .= '?' . substr($matches[2], 1);
      }
    } else if ( preg_match("/products.php\?tell_a_friend\&([0-9]+)(.*)/", $link, $matches) > 0 ) {
      $link = $this->makeUrl($page, $this->getProductsName($matches[1]), 'tell_a_friend', $matches[1]);
      if ( !empty($matches[2]) ) {
        $link .= '?' . substr($matches[2], 1);
      }
    } else if ( preg_match("/\?(.*)?reviews=([0-9]+)(.*)/", $link, $matches) > 0 ) {
      $link = $this->makeUrl($page, $this->getProductsNameViaReviews($matches[2]), 'reviews', $matches[2]);
      if ( !empty($matches[3]) ) {
        $link .= '?' . substr($matches[3], 1);
      }
    } else if ( preg_match("/products.php\?reviews\&([0-9]+)(.*)/", $link, $matches) > 0 ) {
      $link = $this->makeUrl($page, $this->getProductsName($matches[1]), 'products_reviews', $matches[1]);
      if ( !empty($matches[2]) ) {
        $link .= '?' . substr($matches[2], 1);
      }
    } else if ( preg_match("/products.php\?reviews=new\&([0-9]+)(.*)/", $link, $matches) > 0 ) {
      $link = $this->makeUrl($page, $this->getProductsName($matches[1]), 'reviews_new', $matches[1]);
      if ( !empty($matches[2]) ) {
        $link .= '?' . substr($matches[2], 1);
      }
    } else if ( preg_match("/\?(.*)articles_id=([0-9]+)(.*)/", $link, $matches) > 0 ) {
      $link = $this->makeUrl($page, $this->getArticleName($matches[2]), 'articles_id', $matches[2]);
      if ( !empty($matches[3]) ) {
        $link .= '?' . substr($matches[3], 1);
      }
    } else if ( preg_match("/\?(.*)articles_categories_id=([0-9]+)(.*)/", $link, $matches) > 0 ) {
      $link = $this->makeUrl($page, $this->getArticleCategoriesName($matches[2]), 'articles_categories_id', $matches[2]);
      if ( !empty($matches[3]) ) {
        $link .= '?' . substr($matches[3], 1);
      }
    } else if ( preg_match("/faqs_id=([0-9]+)(.*)/", $link, $matches) > 0 ) {
      $link = $this->makeUrl($page, $this->getFaqQuestion($matches[1]), 'faqs_id', $matches[1]);
      if ( !empty($matches[2]) ) {
        $link .= '?' . substr($matches[2], 1);
      }
    }

    return $link;
  }

  function makeUrl($page, $string, $anchor_type, $id, $extension = '.html' ) {
    $string = $this->stripString($string);

    return $string . $this->_reg_anchors[$anchor_type] . $id . $extension;
  }

  function stripString($string) {
    $pattern = "([[:punct:]])+";
    $anchor = ereg_replace($pattern, '', strtolower($string));

    $pattern = "([[:space:]]|[[:blank:]])+";
    $anchor = ereg_replace($pattern, '-', $anchor);

    return $anchor;
  }

  function getProductsName($products_id) {
    return $this->_products_cache[$products_id];
  }

  function getProductsNameViaReviews($reviews_id) {
    return $this->getProductsName($this->_products_reviews_cache[$reviews_id]);
  }

  function getArticleCategoriesName($article_categories_id) {
    return $this->_article_categories_cache[$article_categories_id];
  }

  function getFaqQuestion($faqs_id) {
    return $this->_faqs_cache[$faqs_id];
  }

  function getArticleName($article_id) {
    return $this->_articles_cache[$article_id];
  }

  function getManufacturersName($manufacturers_id) {
    return $this->_manufacturers_cache[$manufacturers_id];
  }

  function getCategoryName($cPath) {
    global $osC_CategoryTree;
    return $osC_CategoryTree->getCategoryName($cPath);
  }

  function _iniProductsCache() {
    global $osC_Database, $osC_Language;

    $Qproducts = $osC_Database->query('select products_id, products_name from :table_products_description  where language_id=:language_id ');
    $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
    $Qproducts->bindInt(':language_id', $osC_Language->getID());
    $Qproducts->setCache('sefu-products-' . $osC_Language->getCode());
    $Qproducts->execute();

    while ($Qproducts->next()) {
      $this->_products_cache[$Qproducts->valueInt('products_id')] = $Qproducts->value('products_name');
    }
    $Qproducts->freeResult();
  }

  function _iniArticlesCache() {
    global $osC_Database, $osC_Language;

    $Qarticles = $osC_Database->query('select articles_id, articles_name from :table_articles_description where language_id=:language_id ');
    $Qarticles->bindTable(':table_articles_description', TABLE_ARTICLES_DESCRIPTION);
    $Qarticles->bindInt(':language_id', $osC_Language->getID());
    $Qarticles->setCache('sefu-articles-' . $osC_Language->getCode());
    $Qarticles->execute();

    while ($Qarticles->next()) {
      $this->_articles_cache[$Qarticles->valueInt('articles_id')] = $Qarticles->value('articles_name');
    }
    $Qarticles->freeResult();
  }

  function _iniProductsReviewsCache() {
    global $osC_Database, $osC_Language;

    $Qreviews = $osC_Database->query('select reviews_id, products_id from :table_reviews where languages_id=:language_id ');
    $Qreviews->bindTable(':table_reviews', TABLE_REVIEWS);
    $Qreviews->bindInt(':language_id', $osC_Language->getID());
    $Qreviews->setCache('sefu-products-reviews-' . $osC_Language->getCode());
    $Qreviews->execute();

    while ($Qreviews->next()) {
      $this->_products_reviews_cache[$Qreviews->valueInt('reviews_id')] = $Qreviews->value('products_id');
    }
    $Qreviews->freeResult();
  }

  function _iniArticleCategoriesCache() {
    global $osC_Database, $osC_Language;

    $Qcategories = $osC_Database->query('select articles_categories_id, articles_categories_name from :table_articles_categories_description where language_id=:language_id ');
    $Qcategories->bindTable(':table_articles_categories_description', TABLE_ARTICLES_CATEGORIES_DESCRIPTION);
    $Qcategories->bindInt(':language_id', $osC_Language->getID());
    $Qcategories->setCache('sefu-article-categories-' . $osC_Language->getCode());
    $Qcategories->execute();

    while ($Qcategories->next()) {
      $this->_article_categories_cache[$Qcategories->valueInt('articles_categories_id')] = $Qcategories->value('articles_categories_name');
    }
    $Qcategories->freeResult();
  }

  function _iniFaqsCache() {
    global $osC_Database, $osC_Language;

    $Qfaqs = $osC_Database->query('select faqs_id, faqs_question from :table_faqs_description where language_id=:language_id ');
    $Qfaqs->bindTable(':table_faqs_description', TABLE_FAQS_DESCRIPTION);
    $Qfaqs->bindInt(':language_id', $osC_Language->getID());
    $Qfaqs->setCache('sefu-faqs-' . $osC_Language->getCode());
    $Qfaqs->execute();

    while ($Qfaqs->next()) {
      $this->_faqs_cache[$Qfaqs->valueInt('faqs_id')] = $Qfaqs->value('faqs_question');
    }
    $Qfaqs->freeResult();
  }

  function _iniManufacturersCache() {
    global $osC_Database, $osC_Language;

    $Qmanufacturers = $osC_Database->query('select manufacturers_id , manufacturers_name from :table_manufacturers');
    $Qmanufacturers->bindTable(':table_manufacturers', TABLE_MANUFACTURERS);
    $Qmanufacturers->setCache('sefu-manufacturers-' . $osC_Language->getCode());
    $Qmanufacturers->execute();

    while ($Qmanufacturers->next()) {
      $this->_manufacturers_cache[$Qmanufacturers->valueInt('manufacturers_id')] = $Qmanufacturers->value('manufacturers_name');
    }
    $Qmanufacturers->freeResult();
  }
}
?>
