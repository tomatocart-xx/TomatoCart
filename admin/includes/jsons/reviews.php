  <?php
/*
  $Id: reviews.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/reviews.php');

  class toC_Json_Reviews {
  
    function listReviews() {
      global $toC_Json, $osC_Language, $osC_Database;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $Qreviews = $osC_Database->query('select r.reviews_id, r.products_id, r.date_added, r.last_modified, r.reviews_rating, r.reviews_status, pd.products_name, l.code as languages_code from :table_reviews r left join :table_products_description pd on (r.products_id = pd.products_id and r.languages_id = pd.language_id), :table_languages l where r.languages_id = l.languages_id order by r.date_added desc');
      $Qreviews->bindTable(':table_reviews', TABLE_REVIEWS);
      $Qreviews->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qreviews->bindTable(':table_languages', TABLE_LANGUAGES);
      $Qreviews->setExtBatchLimit($start, $limit);
      $Qreviews->execute();
  
      $record = array();
      while ( $Qreviews->next() ) {
        $record[] = array('reviews_id' => $Qreviews->value('reviews_id'),
                           'date_added' => osC_DateTime::getShort($Qreviews->value('date_added')),
                           'reviews_rating' => osc_image('../images/stars_' . $Qreviews->valueInt('reviews_rating') . '.png', sprintf($osC_Language->get('rating_from_5_stars'), $Qreviews->valueInt('reviews_rating'))),
                           'products_name' => $Qreviews->value('products_name'),
                           'code' => $osC_Language->showImage($Qreviews->value('languages_code')));         
      }
        
      $response = array(EXT_JSON_READER_TOTAL => $Qreviews->getBatchSize(),
                        EXT_JSON_READER_ROOT => $record); 
                          
      echo $toC_Json->encode($response);
    }
    
    function loadReviews() {
      global $toC_Json;
      
      $data = osC_Reviews_Admin::getData( $_REQUEST['reviews_id'] );  
      $data['date_added'] = osC_DateTime::getShort($data['date_added']);
      
      $response = array('success' => true, 'data' => $data); 
     
      echo $toC_Json->encode($response);  
    }
    
    function saveReviews() {
      global $toC_Json, $osC_Language;
      
      $data = array('review' => $_REQUEST['reviews_text'], 'rating' => $_REQUEST['reviews_rating']);
      
      if ( osC_Reviews_Admin::save( $_REQUEST['reviews_id'], $data )) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
      }
      
      echo $toC_Json->encode($response);
    }
    
    function deleteReview() {
      global $toC_Json, $osC_Language;
      
      if ( osC_Reviews_Admin::delete( $_REQUEST['reviews_id'] ) ) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));    
      }
    
      echo $toC_Json->encode($response);
    }
    
    function deleteReviews() {
      global $toC_Json, $osC_Language;
      
      $error = false;
      $ids = explode(',', $_REQUEST['batch']);
     
      foreach ($ids as $id) {
        if (!osC_Reviews_Admin::delete($id)) {
          $error = true;
          break;
        }
      }
     
      if ($error === false) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false ,'feedback' => $osC_Language->get('ms_error_action_not_performed'));               
      }
        
      echo $toC_Json->encode($response);
    }
  }
?>
  