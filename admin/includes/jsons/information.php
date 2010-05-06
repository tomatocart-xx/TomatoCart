<?php
/*
  $Id: information.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/articles.php');
  require('includes/classes/articles_categories.php');
  require('includes/classes/image.php');
  
  class toC_Json_Information {
    
    function listArticles() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      $Qarticles = $osC_Database->query('select a.articles_id, a.articles_status, a.articles_order, ad.articles_name, acd.articles_categories_name from :table_articles a, :table_articles_description ad, :articles_categories_description acd where acd.articles_categories_id = a.articles_categories_id and acd.language_id = ad.language_id and a.articles_id = ad.articles_id and ad.language_id = :language_id');

      $Qarticles->appendQuery('order by a.articles_id ');
      $Qarticles->bindTable(':table_articles', TABLE_ARTICLES);
      $Qarticles->bindTable(':table_articles_description', TABLE_ARTICLES_DESCRIPTION);
      $Qarticles->bindTable(':articles_categories_description', TABLE_ARTICLES_CATEGORIES_DESCRIPTION);
      $Qarticles->bindInt(':language_id', $osC_Language->getID());
      $Qarticles->execute();
      
      $records = array();
      while ($Qarticles->next()) {
        $records[] = array('articles_id' => $Qarticles->ValueInt('articles_id'),
                           'articles_status' => $Qarticles->ValueInt('articles_status'),
                           'articles_order' => $Qarticles->Value('articles_order'),
                           'articles_categories_name' => $Qarticles->Value('articles_categories_name'),
                           'articles_name' => $Qarticles->Value('articles_name'));
      }
      
      $response = array(EXT_JSON_READER_TOTAL => sizeof($records),
                        EXT_JSON_READER_ROOT => $records);
                        
      echo $toC_Json->encode($response);
    }
    
    function loadArticle() {
      global $osC_Database, $toC_Json;
      
      $data = toC_Articles_Admin::getData($_REQUEST['articles_id']);
      
      $Qad = $osC_Database->query('select articles_name, articles_description,articles_head_desc_tag, articles_head_keywords_tag, language_id from :table_articles_description where articles_id = :articles_id');
      $Qad->bindTable(':table_articles_description', TABLE_ARTICLES_DESCRIPTION);
      $Qad->bindInt(':articles_id', $_REQUEST['articles_id']);
      $Qad->execute();
      
      while ($Qad->next()) {
        $data['articles_name[' . $Qad->valueInt('language_id') . ']'] = $Qad->value('articles_name');
        $data['articles_description[' . $Qad->valueInt('language_id') . ']'] = $Qad->value('articles_description');
        $data['articles_head_desc_tag[' . $Qad->valueInt('language_id') . ']'] = $Qad->value('articles_head_desc_tag');
        $data['articles_head_keywords_tag[' . $Qad->valueInt('language_id') . ']'] = $Qad->value('articles_head_keywords_tag');
      }
      
      $response = array('success' => true, 'data' => $data);
      
      echo $toC_Json->encode($response);
    }
    
    function saveArticle() {
      global $toC_Json, $osC_Language, $osC_Image;
      
      $osC_Image = new osC_Image_Admin();
      
      $data = array('articles_name' => $_REQUEST['articles_name'],
                    'articles_description' => $_REQUEST['articles_description'],
                    'articles_head_desc_tag' => $_REQUEST['articles_head_desc_tag'],
                    'articles_head_keywords_tag' => $_REQUEST['articles_head_keywords_tag'],
                    'articles_order' => $_REQUEST['articles_order'],
                    'articles_status' => $_REQUEST['articles_status'],
                    'articles_categories' => (isset($_REQUEST['articles_categories_id'])? $_REQUEST['articles_categories_id']:'1'));
                    
      if ( toC_Articles_Admin::save((isset($_REQUEST['articles_id']) && ($_REQUEST['articles_id'] != -1) ? $_REQUEST['articles_id'] : null), $data) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      header('Content-Type: text/html');
      
      echo $toC_Json->encode($response);
    }
    
    function setStatus() {
      global $toC_Json, $osC_Language;
      
      if ( isset($_REQUEST['aID']) && toC_Articles_Admin::setStatus($_REQUEST['aID'], (isset($_REQUEST['flag']) ? $_REQUEST['flag'] : null)) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function  deleteArticle() {
      global $toC_Json, $osC_Language, $osC_Image;
      
      $osC_Image = new osC_Image_Admin();
      
      if ( toC_Articles_Admin::delete($_REQUEST['articles_id']) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function  deleteArticles() {
      global $toC_Json, $osC_Language, $osC_Image;
      
      $osC_Image = new osC_Image_Admin();

      $error = false;

      $batch = explode(',', $_REQUEST['batch']);
      foreach ($batch as $id) {
        if ( !toC_Articles_Admin::delete($id) ) {
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
    
  }
?>