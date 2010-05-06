<?php
/*
  $Id: reviews.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Reviews_Admin {
    function getData($id) {
      global $osC_Database;

      $Qreview = $osC_Database->query('select r.*, pd.products_name from :table_reviews r left join :table_products_description pd on (r.products_id = pd.products_id and r.languages_id = pd.language_id) where r.reviews_id = :reviews_id');
      $Qreview->bindTable(':table_reviews', TABLE_REVIEWS);
      $Qreview->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qreview->bindInt(':reviews_id', $id);
      $Qreview->execute();

      $data = $Qreview->toArray();

      $Qaverage = $osC_Database->query('select (avg(reviews_rating) / 5 * 100) as average_rating from :table_reviews where products_id = :products_id');
      $Qaverage->bindTable(':table_reviews', TABLE_REVIEWS);
      $Qaverage->bindInt(':products_id', $Qreview->valueInt('products_id'));
      $Qaverage->execute();

      $data['average_rating'] = $Qaverage->value('average_rating');

      $Qaverage->freeResult();
      $Qreview->freeResult();

      return $data;
    }

    function save($id, $data) {
      global $osC_Database;

      $Qreview = $osC_Database->query('update :table_reviews set reviews_text = :reviews_text, reviews_rating = :reviews_rating, last_modified = now() where reviews_id = :reviews_id');
      $Qreview->bindTable(':table_reviews', TABLE_REVIEWS);
      $Qreview->bindValue(':reviews_text', $data['review']);
      $Qreview->bindInt(':reviews_rating', $data['rating']);
      $Qreview->bindInt(':reviews_id', $id);
      $Qreview->setLogging($_SESSION['module'], $id);
      $Qreview->execute();

      if ( !$osC_Database->isError() ) {
        osC_Cache::clear('reviews');
        return true;
      }

      return false;
    }

    function delete($id) {
      global $osC_Database;

      $Qreview = $osC_Database->query('delete from :table_reviews where reviews_id = :reviews_id');
      $Qreview->bindTable(':table_reviews', TABLE_REVIEWS);
      $Qreview->bindInt(':reviews_id', $id);
      $Qreview->setLogging($_SESSION['module'], $id);
      $Qreview->execute();

      if ( !$osC_Database->isError() ) {
        return true;
      }

      return false;
    }
  }
?>
