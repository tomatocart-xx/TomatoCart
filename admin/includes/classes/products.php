<?php
/*
  $Id: products.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  include('../includes/classes/products.php');

  class osC_Products_Admin extends osC_Products {

    function getData($id) {
      global $osC_Database, $osC_Language;

      $Qproducts = $osC_Database->query('select p.*, pd.*, ptoc.*  from :table_products p left join  :table_products_description pd on p.products_id = pd.products_id left join :table_products_to_categories ptoc on ptoc.products_id = p.products_id  where p.products_id = :products_id and pd.language_id = :language_id');
      $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qproducts->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
      $Qproducts->bindInt(':products_id', $id);
      $Qproducts->bindInt(':language_id', $osC_Language->getID());
      $Qproducts->execute();

      $data = $Qproducts->toArray();

      $Qproducts->freeResult();

      return $data;
    }

    function getAttributes($attributes_groups_id, $products_id = null) {
      global $osC_Database, $osC_Language;

      $Qattributes = $osC_Database->query('select * from :table_products_attributes_values where products_attributes_groups_id = :products_attributes_groups_id and language_id = :language_id and status = 1 order by sort_order');
      $Qattributes->bindTable(':table_products_attributes_values', TABLE_PRODUCTS_ATTRIBUTES_VALUES);
      $Qattributes->bindInt(':products_attributes_groups_id', $attributes_groups_id);
      $Qattributes->bindInt(':language_id', $osC_Language->getID());
      $Qattributes->execute();

      $attributes = array();
      while ($Qattributes->next()) {
        $attribute = $Qattributes->toArray();
        $attribute['choosed_value'] = '';

        if (is_numeric($products_id)) {
          $Qvalue = $osC_Database->query('select value from :table_products_attributes where products_id = :products_id and language_id = :language_id and products_attributes_values_id = :products_attributes_values_id ');
          $Qvalue->bindTable(':table_products_attributes', TABLE_PRODUCTS_ATTRIBUTES);
          $Qvalue->bindInt(':products_id', $products_id);
          $Qvalue->bindInt(':products_attributes_values_id', $attribute['products_attributes_values_id']);
          $Qvalue->bindInt(':language_id', $osC_Language->getID());
          $Qvalue->execute();

          if ($Qvalue->numberOfRows() > 0) {
            $attribute['choosed_value'] = $Qvalue->value('value');
          }
        }
        $attributes[] = $attribute;
      }

      for($i = 0; $i < sizeof($attributes); $i++) {
        if ($attributes[$i]['module'] == 'text_field') {
          $attributes[$i]['lang_values'] = array();
          foreach ($osC_Language->getAll() as $l) {
            $choosed_value = '';

            if (is_numeric($products_id)) {
              $Qvalue = $osC_Database->query('select value from :table_products_attributes where products_id = :products_id and language_id = :language_id and products_attributes_values_id =:products_attributes_values_id');
              $Qvalue->bindTable(':table_products_attributes', TABLE_PRODUCTS_ATTRIBUTES);
              $Qvalue->bindInt(':products_id', $products_id);
              $Qvalue->bindInt(':language_id', $l['id']);
              $Qvalue->bindInt(':products_attributes_values_id', $attributes[$i]['products_attributes_values_id']);
              $Qvalue->execute();

              if ($Qvalue->numberOfRows() > 0) {
                $choosed_value = $Qvalue->value('value');
              }
            }
              
            $attributes[$i]['lang_values'][$l['id']] = $choosed_value;
          }
        }
      }
      
      return $attributes;
    }


    function save($id = null, $data) {
      global $osC_Database, $osC_Language, $osC_Image;

      $error = false;

      $osC_Database->startTransaction();

          //products
      if (is_numeric($id)) {
        $Qproduct = $osC_Database->query('update :table_products set products_type = :products_type, products_sku = :products_sku, products_model = :products_model, products_price = :products_price, products_quantity = :products_quantity, products_moq = :products_moq, products_max_order_quantity = :products_max_order_quantity, order_increment = :order_increment, quantity_unit_class = :quantity_unit_class, products_date_available = :products_date_available, products_weight = :products_weight, products_weight_class = :products_weight_class, products_status = :products_status, products_tax_class_id = :products_tax_class_id, manufacturers_id = :manufacturers_id, quantity_discount_groups_id = :quantity_discount_groups_id, products_last_modified = now(), products_attributes_groups_id = :products_attributes_groups_id where products_id = :products_id');
        $Qproduct->bindInt(':products_id', $id);
      } else {
        $Qproduct = $osC_Database->query('insert into :table_products (products_type, products_sku, products_model, products_price, products_quantity, products_moq, products_max_order_quantity, order_increment, quantity_unit_class, products_date_available, products_weight, products_weight_class, products_status, products_tax_class_id, manufacturers_id, products_date_added, quantity_discount_groups_id, products_attributes_groups_id) values (:products_type, :products_sku, :products_model, :products_price, :products_quantity, :products_moq, :products_max_order_quantity, :order_increment, :quantity_unit_class, :products_date_available, :products_weight, :products_weight_class, :products_status, :products_tax_class_id, :manufacturers_id, :products_date_added, :quantity_discount_groups_id, :products_attributes_groups_id)');
        $Qproduct->bindRaw(':products_date_added', 'now()');
      }

      $Qproduct->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproduct->bindInt(':products_type', $data['products_type']);
      $Qproduct->bindValue(':products_sku', $data['products_sku']);
      $Qproduct->bindValue(':products_model', $data['products_model']);
      $Qproduct->bindValue(':products_price', $data['price']);
      $Qproduct->bindInt(':products_quantity', $data['quantity']);
      $Qproduct->bindInt(':products_moq', $data['products_moq']);
      $Qproduct->bindInt(':products_max_order_quantity', $data['products_max_order_quantity']);
      $Qproduct->bindInt(':order_increment', $data['order_increment']);
      $Qproduct->bindInt(':quantity_unit_class', $data['quantity_unit_class']);

      if (date('Y-m-d') < $data['date_available']) {
        $Qproduct->bindValue(':products_date_available', $data['date_available']);
      } else {
        $Qproduct->bindRaw(':products_date_available', 'null');
      }
      
      $Qproduct->bindValue(':products_weight', $data['weight']);
      $Qproduct->bindInt(':products_weight_class', $data['weight_class']);
      $Qproduct->bindInt(':products_status', $data['status']);
      $Qproduct->bindInt(':products_tax_class_id', $data['tax_class_id']);
      $Qproduct->bindInt(':manufacturers_id', $data['manufacturers_id']);
      $Qproduct->bindInt(':quantity_discount_groups_id', $data['quantity_discount_groups_id']);
      
      if (empty($data['products_attributes_groups_id'])) {
        $Qproduct->bindRaw(':products_attributes_groups_id', 'null');
      } else {
        $Qproduct->bindInt(':products_attributes_groups_id', $data['products_attributes_groups_id']);
      }
      
      $Qproduct->setLogging($_SESSION['module'], $id);
      $Qproduct->execute();

      if ($osC_Database->isError()) {
        $error = true;
      } else {
        if (is_numeric($id)) {
          $products_id = $id;
        } else {
          $products_id = $osC_Database->nextID();
        }
        
//products_to_categories
        $Qcategories = $osC_Database->query('delete from :table_products_to_categories where products_id = :products_id');
        $Qcategories->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
        $Qcategories->bindInt(':products_id', $products_id);
        $Qcategories->setLogging($_SESSION['module'], $products_id);
        $Qcategories->execute();

        if ($osC_Database->isError()) {
          $error = true;
        } else {
          if ( isset($data['categories']) && !empty($data['categories']) ) {
            foreach ($data['categories'] as $category_id) {
              $Qp2c = $osC_Database->query('insert into :table_products_to_categories (products_id, categories_id) values (:products_id, :categories_id)');
              $Qp2c->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
              $Qp2c->bindInt(':products_id', $products_id);
              $Qp2c->bindInt(':categories_id', $category_id);
              $Qp2c->setLogging($_SESSION['module'], $products_id);
              $Qp2c->execute();

              if ( $osC_Database->isError() ) {
                $error = true;
                break;
              }
            }
          }        
        }
      }
      
      //downloadable products & gift certificates
      if ($data['products_type'] == PRODUCT_TYPE_DOWNLOADABLE) {
        if (is_numeric($id)) {
          $Qdownloadables = $osC_Database->query('update :table_products_downloadables set number_of_downloads = :number_of_downloads, number_of_accessible_days = :number_of_accessible_days where products_id = :products_id');
        } else {
          $Qdownloadables = $osC_Database->query('insert into :table_products_downloadables (products_id, number_of_downloads, number_of_accessible_days) values (:products_id, :number_of_downloads, :number_of_accessible_days)');
        }
              
        $Qdownloadables->bindTable(':table_products_downloadables', TABLE_PRODUCTS_DOWNLOADABLES);
        $Qdownloadables->bindInt(':products_id', $products_id);
        $Qdownloadables->bindInt(':number_of_downloads', $data['number_of_downloads']);
        $Qdownloadables->bindInt(':number_of_accessible_days', $data['number_of_accessible_days']);
        $Qdownloadables->setLogging($_SESSION['module'], $products_id);
        $Qdownloadables->execute();
        
        if ($osC_Database->isError()) {
          $error = true;
        } else {
          $filename = null;
          $cache_filename = null;
          $file = new upload('downloadable_file');
          
          if ($file->exists()) {
            $file->set_destination(realpath('../download'));
  
            if ($file->parse() && $file->save()) {
              $filename = $file->filename;
              $cache_filename = md5($filename . time());
              rename(DIR_FS_DOWNLOAD . $filename, DIR_FS_DOWNLOAD . $cache_filename);
            }
          }
          
          if (!is_null($filename)) {
            if (is_numeric($id)) {
              $Qfile = $osC_Database->query('select cache_filename from :table_products_downloadables where products_id = :products_id');
              $Qfile->bindTable(':table_products_downloadables', TABLE_PRODUCTS_DOWNLOADABLES);
              $Qfile->bindInt(':products_id', $products_id);
              $Qfile->execute(); 
              
              if ($Qfile->numberOfRows() > 0) {
                $file = $Qfile->value('cache_filename');
                unlink(DIR_FS_DOWNLOAD . $file);
              }
            }
          
            $Qupdate = $osC_Database->query('update :table_products_downloadables set filename = :filename, cache_filename = :cache_filename where products_id = :products_id');
            $Qupdate->bindTable(':table_products_downloadables', TABLE_PRODUCTS_DOWNLOADABLES);
            $Qupdate->bindInt(':products_id', $products_id);
            $Qupdate->bindValue(':filename', $filename);
            $Qupdate->bindValue(':cache_filename', $cache_filename);
            $Qupdate->setLogging($_SESSION['module'], $products_id);
            $Qupdate->execute();   
          
            if ($osC_Database->isError()) {
              $error = true;
            } 
          }   
          
          if ($error === false) {
            $sample_filename = null;
            $cache_sample_filename = null;
            $sample_file = new upload('sample_downloadable_file');
            
            if ($sample_file->exists()) {
              $sample_file->set_destination(realpath('../download'));
    
              if ($sample_file->parse() && $sample_file->save()) {
                $sample_filename = $sample_file->filename;
                $cache_sample_filename = md5($sample_filename . time());
                @rename(DIR_FS_DOWNLOAD . $sample_filename, DIR_FS_DOWNLOAD . $cache_sample_filename);
              }
            }
            
            if (!is_null($sample_filename) && ($error === false)) {
              if (is_numeric($id)) {
                $Qfile = $osC_Database->query('select cache_sample_filename from :table_products_downloadables where products_id = :products_id');
                $Qfile->bindTable(':table_products_downloadables', TABLE_PRODUCTS_DOWNLOADABLES);
                $Qfile->bindInt(':products_id', $products_id);
                $Qfile->execute(); 
                
                if ($Qfile->numberOfRows() > 0) {
                  $file = $Qfile->value('cache_sample_filename');
                  unlink(DIR_FS_DOWNLOAD . $file);
                }
              }
            
              $Qfiles = $osC_Database->query('update :table_products_downloadables set sample_filename = :sample_filename, cache_sample_filename = :cache_sample_filename where products_id = :products_id');
              $Qfiles->bindTable(':table_products_downloadables', TABLE_PRODUCTS_DOWNLOADABLES);
              $Qfiles->bindInt(':products_id', $products_id);
              $Qfiles->bindValue(':sample_filename', $sample_filename);
              $Qfiles->bindValue(':cache_sample_filename', $cache_sample_filename);
              $Qfiles->setLogging($_SESSION['module'], $products_id);
              $Qfiles->execute();   
            
              if ($osC_Database->isError()) {
                $error = true;
              } 
            }               
          }
        }
      } else if ($data['products_type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) {
        if (is_numeric($id)) {
          $Qcertificates = $osC_Database->query('update :table_products_gift_certificates set gift_certificates_type = :gift_certificates_type, gift_certificates_amount_type = :gift_certificates_amount_type, open_amount_max_value = :open_amount_max_value, open_amount_min_value = :open_amount_min_value where products_id = :products_id');
        } else {
          $Qcertificates = $osC_Database->query('insert into :table_products_gift_certificates (products_id, gift_certificates_type, gift_certificates_amount_type, open_amount_max_value, open_amount_min_value) values (:products_id, :gift_certificates_type, :gift_certificates_amount_type, :open_amount_max_value, :open_amount_min_value)');
        }
                
        $Qcertificates->bindTable(':table_products_gift_certificates', TABLE_PRODUCTS_GIFT_CERTIFICATES);
        $Qcertificates->bindInt(':products_id', $products_id);
        $Qcertificates->bindInt(':gift_certificates_type', $data['gift_certificates_type']);
        $Qcertificates->bindInt(':gift_certificates_amount_type', $data['gift_certificates_amount_type']);
        $Qcertificates->bindValue(':open_amount_max_value', $data['open_amount_max_value']);
        $Qcertificates->bindValue(':open_amount_min_value', $data['open_amount_min_value']);
        $Qcertificates->setLogging($_SESSION['module'], $products_id);
        $Qcertificates->execute();
        
        if ($osC_Database->isError()) {
          $error = true;
        }
      }      

     // products_images
      if ($error === false) {
        $images = array();
        
        for ($i = 0; $i < sizeof($_FILES['products_image']['name']); $i++) {
          $_FILES['products_image' . $i] = array('name' => $_FILES['products_image']['name'][$i],
                                                 'type' => $_FILES['products_image']['type'][$i],
                                                 'size' => $_FILES['products_image']['size'][$i],
                                                 'tmp_name' => $_FILES['products_image']['tmp_name'][$i]);
          
          $products_image = new upload('products_image' . $i);
          
	        if ($products_image->exists()) {
	          $products_image->set_destination(realpath('../images/products/originals'));
	
	          if ($products_image->parse() && $products_image->save()) {
	            $images[] = $products_image->filename;
	          }
	        }
        }

        if (isset($data['localimages'])) {
          foreach ($data['localimages'] as $image) {
            $image = basename($image);

            if (file_exists('../images/products/_upload/' . $image)) {
              copy('../images/products/_upload/' . $image, '../images/products/originals/' . $image);
              @unlink('../images/products/_upload/' . $image);

              $images[] = $image;
            }
          }
        }

        $default_flag = 1;

        foreach ($images as $image) {
          $Qimage = $osC_Database->query('insert into :table_products_images (products_id, image, default_flag, sort_order, date_added) values (:products_id, :image, :default_flag, :sort_order, :date_added)');
          $Qimage->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
          $Qimage->bindInt(':products_id', $products_id);
          $Qimage->bindValue(':image', $image);
          $Qimage->bindInt(':default_flag', $default_flag);
          $Qimage->bindInt(':sort_order', 0);
          $Qimage->bindRaw(':date_added', 'now()');
          $Qimage->setLogging($_SESSION['module'], $products_id);
          $Qimage->execute();

          if ($osC_Database->isError()) {
            $error = true;
          } else {
            foreach ($osC_Image->getGroups() as $group) {
              if ($group['id'] != '1') {
                $osC_Image->resize($image, $group['id'], 'products');
              }
            }
          }

          $default_flag = 0;
        }
      }

      //products_description
      if ($error === false) {
        foreach ($osC_Language->getAll() as $l) {
          if (is_numeric($id)) {
            $Qpd = $osC_Database->query('update :table_products_description set products_name = :products_name, products_short_description = :products_short_description, products_description = :products_description, products_tags = :products_tags, products_url = :products_url, products_page_title = :products_page_title, products_meta_keywords = :products_meta_keywords, products_meta_description = :products_meta_description where products_id = :products_id and language_id = :language_id');
          } else {
            $Qpd = $osC_Database->query('insert into :table_products_description (products_id, language_id, products_name, products_short_description, products_description, products_tags, products_url, products_page_title, products_meta_keywords, products_meta_description) values (:products_id, :language_id, :products_name, :products_short_description, :products_description, :products_tags, :products_url, :products_page_title, :products_meta_keywords, :products_meta_description)');
          }

          $Qpd->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
          $Qpd->bindInt(':products_id', $products_id);
          $Qpd->bindInt(':language_id', $l['id']);
          $Qpd->bindValue(':products_name', $data['products_name'][$l['id']]);
          $Qpd->bindValue(':products_short_description', $data['products_short_description'][$l['id']]);
          $Qpd->bindValue(':products_description', $data['products_description'][$l['id']]);
          $Qpd->bindValue(':products_tags', $data['products_tags'][$l['id']]);
          $Qpd->bindValue(':products_url', $data['products_url'][$l['id']]);
          $Qpd->bindValue(':products_page_title', $data['products_page_title'][$l['id']]);
          $Qpd->bindValue(':products_meta_keywords', $data['products_meta_keywords'][$l['id']]);
          $Qpd->bindValue(':products_meta_description', $data['products_meta_description'][$l['id']]);
          $Qpd->setLogging($_SESSION['module'], $products_id);
          $Qpd->execute();

          if ($osC_Database->isError()) {
            $error = true;
            break;
          }
        }
      }

      //check table products_attributes_details and details value
      $is_variants_changed = true;
      if (is_numeric($id)) {
        $Qvariants = $osC_Database->query('select products_variants_id from :table_products_variants where products_id = :products_id');
        $Qvariants->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
        $Qvariants->bindInt(':products_id', $products_id);
        $Qvariants->execute();

        if ( $Qvariants->numberOfRows() === sizeof($data['variants_price']) ) {
          $old_variants = array();
          $new_variants = array();
          $old_values = array();
          $new_values = array();

          while ( $Qvariants->next() ) {
            $Qcheck = $osC_Database->query('select products_variants_groups_id, products_variants_values_id from :table_products_variants_entries where products_variants_id = :products_variants_id');
            $Qcheck->bindTable(':table_products_variants_entries', TABLE_PRODUCTS_VARIANTS_ENTRIES);
            $Qcheck->bindInt(':products_variants_id', $Qvariants->valueInt('products_variants_id'));
            $Qcheck->execute();

            $variants = array();
            while ( $Qcheck->next() ) {
              $variants[] = $Qcheck->valueInt('products_variants_groups_id') . '_' . $Qcheck->valueInt('products_variants_values_id');
              $old_values[] = $Qcheck->valueInt('products_variants_groups_id') . '_' . $Qcheck->valueInt('products_variants_values_id');
            }

            $old_variants[$Qvariants->valueInt('products_variants_id')] = $variants;
          }
          
          if (!empty($data['variants_price'])) {
            foreach ($data['variants_price'] as $key => $vaule) {
              $new_variants = explode('-', $key);
              
              foreach ($new_variants as $tmp) {
                $new_values[] = $tmp;
              }
            }
          }

          $result = array_diff(array_unique($old_values), array_unique($new_values));
          if ( empty($result) ) {
            $is_variants_changed = false;
          }
        }

	      if ($is_variants_changed === true) {
	        $Qdpve = $osC_Database->query('delete from :table_products_variants_entries where products_variants_id in ( select products_variants_id from :table_products_variants where products_id = :products_id )');
	        $Qdpve->bindTable(':table_products_variants_entries', TABLE_PRODUCTS_VARIANTS_ENTRIES);
	        $Qdpve->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
	        $Qdpve->bindInt(':products_id', $products_id);
	        $Qdpve->setLogging($_SESSION['module'], $products_id);
	        $Qdpve->execute();

	        if ($osC_Database->isError()) {
	          $error = true;
	        }

	        if ($error === false) {
	          $Qdpv = $osC_Database->query('delete from :table_products_variants where products_id = :products_id');
	          $Qdpv->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
	          $Qdpv->bindInt(':products_id', $products_id);
	          $Qdpv->setLogging($_SESSION['module'], $products_id);
	          $Qdpv->execute();

	          if ($osC_Database->isError()) {
	            $error = true;
	          }
	        }
	      }
      }

      //  insert or update products_attributes_details and detials value
      if ($error === false) {
	      if ( isset($data['variants_price']) ) {
          if ($is_variants_changed === true) {
	          $products_quantity = 0;
	          foreach ($data['variants_price'] as $key => $vaule) {
	            $Qpv = $osC_Database->query('insert into :table_products_variants (products_id, products_price, products_sku, products_model, products_quantity, products_weight, products_status) values (:products_id, :products_price, :products_sku, :products_model, :products_quantity, :products_weight, :products_status)');
	            $Qpv->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
	            $Qpv->bindInt(':products_id', $products_id);
	            $Qpv->bindValue(':products_price', $data['variants_price'][$key]);
	            $Qpv->bindValue(':products_sku', $data['variants_sku'][$key]);
	            $Qpv->bindValue(':products_model', $data['variants_model'][$key]);
	            $Qpv->bindValue(':products_quantity', $data['variants_quantity'][$key]);
	            $Qpv->bindValue(':products_weight', $data['variants_weight'][$key]);
	            $Qpv->bindValue(':products_status', $data['variants_status'][$key]);
	            $Qpv->execute();

	            if ($osC_Database->isError()) {
	              $error = true;
	              break;
	            }else{
	              $products_variants_id = $osC_Database->nextID();
	              if($data['variants_status'][$key] == '1') {
	                $products_quantity += $data['variants_quantity'][$key];
	              }
	            }

	            if ($error === false) {
	              $assigned_variants = explode('-', $key);

	              for($i = 0; $i < sizeof($assigned_variants); $i++) {
	                $assigned_variant = explode('_', $assigned_variants[$i]);

	                $Qpve = $osC_Database->query('insert into :table_products_variants_entries (products_variants_id, products_variants_groups_id, products_variants_values_id) values (:products_variants_id, :products_variants_groups_id, :products_variants_values_id)');
	                $Qpve->bindTable(':table_products_variants_entries', TABLE_PRODUCTS_VARIANTS_ENTRIES);
	                $Qpve->bindInt(':products_variants_id', $products_variants_id);
	                $Qpve->bindInt(':products_variants_groups_id', $assigned_variant[0]);
	                $Qpve->bindInt(':products_variants_values_id', $assigned_variant[1]);
	                $Qpve->setLogging($_SESSION['module'], $products_id);
	                $Qpve->execute();

	                if ($osC_Database->isError()) {
	                  $error = true;
	                  break;
	                }
	              }
	            }
	          }
	        } else {
            $products_quantity = 0;
            foreach ($data['variants_price'] as $key => $vaule) {
              $assigned_variants = explode('-', $key);
  
              foreach ($old_variants as $products_variants_id => $variants) {
                $result = array_diff($variants, $assigned_variants);
                
                if (empty($result)) {
                  $Qpv = $osC_Database->query('update :table_products_variants set products_price = :products_price, products_sku = :products_sku, products_model = :products_model, products_quantity = :products_quantity, products_weight = :products_weight, products_status = :products_status where products_variants_id = :products_variants_id');
                  $Qpv->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
                  $Qpv->bindInt(':products_variants_id', $products_variants_id);
                  $Qpv->bindValue(':products_price', $data['variants_price'][$key]);
                  $Qpv->bindValue(':products_sku', $data['variants_sku'][$key]);
                  $Qpv->bindValue(':products_model', $data['variants_model'][$key]);
                  $Qpv->bindValue(':products_quantity', $data['variants_quantity'][$key]);
                  $Qpv->bindValue(':products_weight', $data['variants_weight'][$key]);
                  $Qpv->bindValue(':products_status', $data['variants_status'][$key]);
                  $Qpv->setLogging($_SESSION['module'], $products_id);
                  $Qpv->execute();
  
                  //break variants search loop
                  if ($osC_Database->isError()) {
                    $error = true;
                    break;
                  }
                }
              }
  
              //break variants update loop
              if ($error === true) {
                break;
              }
  
              if($data['variants_status'][$key] == '1') {
                $products_quantity += $data['variants_quantity'][$key];
              }
            }
          }
  
          if ($error === false) {
            $osC_Database->simpleQuery('update ' . TABLE_PRODUCTS . ' set products_quantity = ' . $products_quantity . ' where products_id =' . $products_id);
  
            if ($osC_Database->isError()) {
              $error = true;
            }
          }
        }
      }

//  xsell products
      if ($error === false) {
        if (is_numeric($id)) {
          $Qdelete = $osC_Database->query('delete from :table_products_xsell where products_id = :products_id');
          $Qdelete->bindTable(':table_products_xsell', TABLE_PRODUCTS_XSELL);
          $Qdelete->bindInt(':products_id', $id);
          $Qdelete->setLogging($_SESSION['module'], $id);
          $Qdelete->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }

        if ($error === false) {
          if ( isset($data['xsell_id_array']) && !empty($data['xsell_id_array']) ) {
            foreach ($data['xsell_id_array'] as $xsell_products_id) {
              $Qxsell = $osC_Database->query('insert into :table_products_xsell (products_id, xsell_products_id) values (:products_id , :xsell_products_id )');
              $Qxsell->bindTable(':table_products_xsell', TABLE_PRODUCTS_XSELL);
              $Qxsell->bindInt(':products_id', $products_id);
              $Qxsell->bindInt(':xsell_products_id', $xsell_products_id);
              $Qxsell->setLogging($_SESSION['module'], $products_id);
              $Qxsell->execute();

              if ($osC_Database->isError()) {
                $error = true;
                break;
              }
            }
          }
        }
      }

      if ($error === false) {
        if (is_numeric($id)) {
          $Qdelete = $osC_Database->query('delete from :table_products_attributes where products_id = :products_id ');
          $Qdelete->bindTable(':table_products_attributes', TABLE_PRODUCTS_ATTRIBUTES);
          $Qdelete->bindInt(':products_id', $id);
          $Qdelete->setLogging($_SESSION['module'], $id);
          $Qdelete->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }

        if ($error === false) {
          if (!empty($data['products_attributes'])) {
            foreach ($data['products_attributes'] as $attribute) {
              $Qef = $osC_Database->query('insert into :table_products_attributes (products_id, products_attributes_values_id, language_id, value) values (:products_id , :products_attributes_values_id, :language_id, :value)');
              $Qef->bindTable(':table_products_attributes', TABLE_PRODUCTS_ATTRIBUTES);
              $Qef->bindInt(':products_id', $products_id);
              $Qef->bindInt(':products_attributes_values_id', $attribute['id']);
              $Qef->bindInt(':language_id', $attribute['language_id']);
              $Qef->bindValue(':value', $attribute['value']);
              $Qef->execute();

              if ($osC_Database->isError()) {
                $error = true;
                break;
              }
            }
          }
        }
      }

      if ($error === false) {
        $osC_Database->commitTransaction();

        osC_Cache::clear('categories');
        osC_Cache::clear('category_tree');
        osC_Cache::clear('also_purchased');
        osC_Cache::clear('sefu-products');
        osC_Cache::clear('new_products');
        osC_Cache::clear('feature_products');

        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }

    function delete($id, $categories = null) {
      global $osC_Database, $osC_Image;

      $delete_product = true;
      $error = false;

      $osC_Database->startTransaction();

      if (is_array($categories) && !empty($categories)) {
        $Qpc = $osC_Database->query('delete from :table_products_to_categories where products_id = :products_id and categories_id in :categories_id');
        $Qpc->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
        $Qpc->bindInt(':products_id', $id);
        $Qpc->bindRaw(':categories_id', '("' . implode('", "', $categories) . '")');
        $Qpc->setLogging($_SESSION['module'], $id);
        $Qpc->execute();

        if (!$osC_Database->isError()) {
          $Qcheck = $osC_Database->query('select products_id from :table_products_to_categories where products_id = :products_id limit 1');
          $Qcheck->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
          $Qcheck->bindInt(':products_id', $id);
          $Qcheck->execute();

          if ($Qcheck->numberOfRows() > 0) {
            $delete_product = false;
          }
        } else {
          $error = true;
        }
      }

      if (($error === false) && ($delete_product === true)) {
        $Qr = $osC_Database->query('delete from :table_reviews where products_id = :products_id');
        $Qr->bindTable(':table_reviews', TABLE_REVIEWS);
        $Qr->bindInt(':products_id', $id);
        $Qr->setLogging($_SESSION['module'], $id);
        $Qr->execute();

        if ($osC_Database->isError()) {
          $error = true;
        }

        if ($error === false) {
          $Qcb = $osC_Database->query('delete from :table_customers_basket where products_id = :products_id or products_id like :products_id');
          $Qcb->bindTable(':table_customers_basket', TABLE_CUSTOMERS_BASKET);
          $Qcb->bindInt(':products_id', $id);
          $Qcb->bindValue(':products_id', (int)$id . '#%');
          $Qcb->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }

        if ($error === false) {
          $Qp2c = $osC_Database->query('delete from :table_products_to_categories where products_id = :products_id');
          $Qp2c->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
          $Qp2c->bindInt(':products_id', $id);
          $Qp2c->setLogging($_SESSION['module'], $id);
          $Qp2c->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }

        if ($error === false) {
          $Qs = $osC_Database->query('delete from :table_specials where products_id = :products_id');
          $Qs->bindTable(':table_specials', TABLE_SPECIALS);
          $Qs->bindInt(':products_id', $id);
          $Qs->setLogging($_SESSION['module'], $id);
          $Qs->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }

        if ($error === false) {
          $Qxsell = $osC_Database->query('delete from :table_products_xsell where products_id = :products_id');
          $Qxsell->bindTable(':table_products_xsell', TABLE_PRODUCTS_XSELL);
          $Qxsell->bindInt(':products_id', $id);
          $Qxsell->setLogging($_SESSION['module'], $id);
          $Qxsell->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }

        if ($error === false) {
          $Qattributes = $osC_Database->query('delete from :table_products_attributes where products_id = :products_id ');
          $Qattributes->bindTable(':table_products_attributes', TABLE_PRODUCTS_ATTRIBUTES);
          $Qattributes->bindInt(':products_id', $id);
          $Qattributes->setLogging($_SESSION['module'], $id);
          $Qattributes->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }
        
        if ($error === false) {
          $Qattributes = $osC_Database->query('delete from :table_products_variants where products_id = :products_id ');
          $Qattributes->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
          $Qattributes->bindInt(':products_id', $id);
          $Qattributes->setLogging($_SESSION['module'], $id);
          $Qattributes->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }

        if ($error === false) {
          $Qpd = $osC_Database->query('delete from :table_products_description where products_id = :products_id');
          $Qpd->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
          $Qpd->bindInt(':products_id', $id);
          $Qpd->setLogging($_SESSION['module'], $id);
          $Qpd->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }
              
        if ($error === false) {
          $Qfiles = $osC_Database->query('select cache_filename, cache_sample_filename from :table_products_downloadables where products_id = :products_id');
          $Qfiles->bindTable(':table_products_downloadables', TABLE_PRODUCTS_DOWNLOADABLES);
          $Qfiles->bindInt(':products_id', $id);
          $Qfiles->execute(); 
          
          if ($Qfiles->numberOfRows() > 0) {
            $cache_filename = $Qfiles->value('cache_filename');
            $cache_sample_filename = $Qfiles->value('cache_sample_filename');
            
            unlink(DIR_FS_DOWNLOAD . $cache_filename);
            unlink(DIR_FS_DOWNLOAD . $cache_sample_filename);
                    
            $Qdownloadables = $osC_Database->query('delete from :table_products_downloadables where products_id = :products_id');
            $Qdownloadables->bindTable(':table_products_downloadables', TABLE_PRODUCTS_DOWNLOADABLES);
            $Qdownloadables->bindInt(':products_id', $id);
            $Qdownloadables->setLogging($_SESSION['module'], $id);
            $Qdownloadables->execute();
  
            if ($osC_Database->isError()) {
              $error = true;
            }
          }
        }
        
        if ($error === false) {
          $Qgc = $osC_Database->query('delete from :table_products_gift_certificates where products_id = :products_id');
          $Qgc->bindTable(':table_products_gift_certificates', TABLE_PRODUCTS_GIFT_CERTIFICATES);
          $Qgc->bindInt(':products_id', $id);
          $Qgc->setLogging($_SESSION['module'], $id);
          $Qgc->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }

        if ($error === false) {
          $Qp = $osC_Database->query('delete from :table_products where products_id = :products_id');
          $Qp->bindTable(':table_products', TABLE_PRODUCTS);
          $Qp->bindInt(':products_id', $id);
          $Qp->setLogging($_SESSION['module'], $id);
          $Qp->execute();

          if ($osC_Database->isError()) {
            $error = true;
          }
        }

        if ($error === false) {
          $Qim = $osC_Database->query('select id from :table_products_images where products_id = :products_id');
          $Qim->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
          $Qim->bindInt(':products_id', $id);
          $Qim->setLogging($_SESSION['module'], $id);
          $Qim->execute();

          while ($Qim->next()) {
            $osC_Image->delete($Qim->valueInt('id'));
          }
        }
      }

      if ($error === false) {
        $osC_Database->commitTransaction();

        osC_Cache::clear('categories');
        osC_Cache::clear('category_tree');
        osC_Cache::clear('also_purchased');
        osC_Cache::clear('sefu-products');
        osC_Cache::clear('new_products');

        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }

    function setDateAvailable($id, $data) {
      global $osC_Database;

      $Qproduct = $osC_Database->query('update :table_products set products_date_available = :products_date_available, products_last_modified = now() where products_id = :products_id');
      $Qproduct->bindTable(':table_products', TABLE_PRODUCTS);

      if (date('Y-m-d') < $data['date_available']) {
        $Qproduct->bindValue(':products_date_available', $data['date_available']);
      } else {
        $Qproduct->bindRaw(':products_date_available', 'null');
      }

      $Qproduct->bindInt(':products_id', $id);
      $Qproduct->setLogging($_SESSION['module'], $id);
      $Qproduct->execute();

      if (!$osC_Database->isError()) {
        return true;
      }

      return false;
    }
    
    function setFrontPage($id, $flag) {
      global $osC_Database;
      
      if($flag == 1) {
        $Qcheck = $osC_Database->query('select products_id from :table_products_frontpage where products_id = :products_id');
        $Qcheck->bindTable(':table_products_frontpage', TABLE_PRODUCTS_FRONTPAGE);
        $Qcheck->bindInt(':products_id', $id);
        $Qcheck->execute();
        
        if ($Qcheck->numberOfRows() > 0) {
          return true;
        }

        $Qorder = $osC_Database->query('select max(sort_order) as sort_order from :table_products_frontpage');
        $Qorder->bindTable(':table_products_frontpage', TABLE_PRODUCTS_FRONTPAGE);
        $Qorder->execute();

        $sort_order = $Qorder->valueInt('sort_order') + 1;
        
        $Qstatus = $osC_Database->query('insert into :table_products_frontpage (products_id, sort_order) values (:products_id, :sort_order)');
        $Qstatus->bindInt(':sort_order', $sort_order);
      } else {
        $Qstatus = $osC_Database->query('delete from :table_products_frontpage where products_id = :products_id');
      }
      
      $Qstatus->bindTable(':table_products_frontpage', TABLE_PRODUCTS_FRONTPAGE);
      $Qstatus->bindInt(':products_id', $id);
      $Qstatus->execute();
      
      if(!$osC_Database->isError()) {
        osC_Cache::clear('feature_products');
        
        return true;
      }

      return false;
    }
    
    function setStatus($id, $flag) {
      global $osC_Database;
    
      $Qstatus = $osC_Database->query('update :table_products set products_status = :products_status where products_id = :products_id');
      $Qstatus->bindTable(':table_products', TABLE_PRODUCTS);
      $Qstatus->bindInt(":products_id", $id);
      $Qstatus->bindValue(":products_status", $flag);
      $Qstatus->execute();
      
      if(!$osC_Database->isError()) {
        osC_Cache::clear('new_products');
        return true;
      }
      return false;
    }
  }
?>
