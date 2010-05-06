<?php
/*
  $Id: products.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  include('includes/classes/category_tree.php');
  include('includes/classes/products.php');
  include('includes/classes/image.php');
        
  class toC_Json_Products {
  
    function assignLocalImages() {
      global $toC_Json, $osC_Database, $osC_Language;

      if (isset($_REQUEST['products_id']) && isset($_REQUEST['localimages'])) {
        $osC_Image = new osC_Image_Admin();
            
        $localimages = explode(',', $_REQUEST['localimages']);  
        $default_flag = 1;

        $Qcheck = $osC_Database->query('select id from :table_products_images where products_id = :products_id and default_flag = :default_flag limit 1');
        $Qcheck->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
        $Qcheck->bindInt(':products_id', $_REQUEST['products_id']);
        $Qcheck->bindInt(':default_flag', 1);
        $Qcheck->execute();

        if ($Qcheck->numberOfRows() === 1) {
          $default_flag = 0;
        }

        foreach ($localimages as $image) {
          $image = basename($image);

          if (file_exists('../images/products/_upload/' . $image)) {
            copy('../images/products/_upload/' . $image, '../images/products/originals/' . $image);
            @unlink('../images/products/_upload/' . $image);

            if (isset($_REQUEST['products_id'])) {
              $Qimage = $osC_Database->query('insert into :table_products_images (products_id, image, default_flag, sort_order, date_added) values (:products_id, :image, :default_flag, :sort_order, :date_added)');
              $Qimage->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
              $Qimage->bindInt(':products_id', $_REQUEST['products_id']);
              $Qimage->bindValue(':image', $image);
              $Qimage->bindInt(':default_flag', $default_flag);
              $Qimage->bindInt(':sort_order', 0);
              $Qimage->bindRaw(':date_added', 'now()');
              $Qimage->setLogging($_SESSION['module'], $_REQUEST['products_id']);
              $Qimage->execute();

              foreach ($osC_Image->getGroups() as $group) {
                if ($group['id'] != '1') {
                  $osC_Image->resize($image, $group['id']);
                }
              }
            }
          }
        }
      }
      
      $response['success'] = true;
      $response['feedback'] = $osC_Language->get('ms_success_action_performed');
    
      echo $toC_Json->encode($response);
    }
  
    function setDefault() {
      global $toC_Json, $osC_Language;
      
      $osC_Image = new osC_Image_Admin();
      
      if ($osC_Image->setAsDefault($_REQUEST['image'])) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }      
    
      echo $toC_Json->encode($response);
    
    }
  
    function deleteImage() {
      global $toC_Json, $osC_Language;
      
      $osC_Image = new osC_Image_Admin();
      
      if ($osC_Image->delete($_REQUEST['image'])) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }   
      
      echo $toC_Json->encode($response);
    }
  
    function deleteProducts() {
      global $toC_Json, $osC_Language, $osC_Image;
      
      $osC_Image = new osC_Image_Admin();   
      
      $error = false;
      
      $batch = explode(',', $_REQUEST['batch']);
      foreach ($batch as $id) {
        if ( !osC_Products_Admin::delete($id) ) {
          $error = true;
          break;
        }
      }

      if ($error === false) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }      

      echo $toC_Json->encode($response);
    }
  
    function deleteProduct() {
      global $toC_Json, $osC_Language, $osC_Image;
      
      $osC_Image = new osC_Image_Admin();      
      
      if (osC_Products_Admin::delete($_REQUEST['products_id'])) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }   
      
      echo $toC_Json->encode($response);
    }
  
    function getLocalImages() {
      global $toC_Json;
      
      $osC_DirectoryListing = new osC_DirectoryListing('../images/products/_upload', true);
      $osC_DirectoryListing->setCheckExtension('gif');
      $osC_DirectoryListing->setCheckExtension('jpg');
      $osC_DirectoryListing->setCheckExtension('png');
      $osC_DirectoryListing->setIncludeDirectories(false);

      $records = array();
      foreach ($osC_DirectoryListing->getFiles() as $file) {
        $records[] = array('id' => $file['name'], 
                           'text' => $file['name']);
      }
      
      $response = array(EXT_JSON_READER_TOTAL => sizeof($records),
                        EXT_JSON_READER_ROOT => $records);
                        
      echo $toC_Json->encode($response);  
    }
  
    function uploadImage() {
      global $toC_Json, $osC_Database;
      
      $osC_Image = new osC_Image_Admin();
      
      if (is_array($_FILES)) {
        $products_image = array_keys($_FILES);
        $products_image = $products_image[0];
      }
      
      if (isset($_REQUEST['products_id'])) {
        $products_image = new upload($products_image);
 
        if ($products_image->exists()) {
          $products_image->set_destination(realpath('../images/products/originals'));
          if ($products_image->parse() && $products_image->save()) {
            $default_flag = 1;
            $Qcheck = $osC_Database->query('select id from :table_products_images where products_id = :products_id and default_flag = :default_flag limit 1');
            $Qcheck->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
            $Qcheck->bindInt(':products_id', $_REQUEST['products_id']);
            $Qcheck->bindInt(':default_flag', 1);
            $Qcheck->execute();

            if ($Qcheck->numberOfRows() === 1) {
              $default_flag = 0;
            }

            $Qimage = $osC_Database->query('insert into :table_products_images (products_id, image, default_flag, sort_order, date_added) values (:products_id, :image, :default_flag, :sort_order, :date_added)');
            $Qimage->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
            $Qimage->bindInt(':products_id', $_REQUEST['products_id']);
            $Qimage->bindValue(':image', $products_image->filename);
            $Qimage->bindInt(':default_flag', $default_flag);
            $Qimage->bindInt(':sort_order', 0);
            $Qimage->bindRaw(':date_added', 'now()');
            $Qimage->setLogging($_SESSION['module'], $_REQUEST['products_id']);
            $Qimage->execute();

            foreach ($osC_Image->getGroups() as $group) {
              if ($group['id'] != '1') {
                $osC_Image->resize($products_image->filename, $group['id']);
              }
            }
          }
        }
      }      
      
      header('Content-Type: text/html');
      
      $response['success'] = true;
      $response['feedback'] = $osC_Language->get('ms_success_action_performed');
    
      echo $toC_Json->encode($response);
    }
  
    function getImages() {
      global $toC_Json, $osC_Database;
    
      $osC_Image = new osC_Image_Admin();

      $records = array();

      $Qimages = $osC_Database->query('select id, image, default_flag from :table_products_images where products_id = :products_id order by sort_order');
      $Qimages->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
      $Qimages->bindInt(':products_id', $_REQUEST['products_id']);
      $Qimages->execute();
  
      while ($Qimages->next()) {
        $records[] = array('id' => $Qimages->valueInt('id'),
                           'image' => '<img src="' . DIR_WS_HTTP_CATALOG . 'images/products/mini/'. $Qimages->value('image') . '" border="0" width="' . $osC_Image->getWidth('mini') . '" />',
                           'name' => $Qimages->value('image'),
                           'size' => number_format(@filesize(DIR_FS_CATALOG . DIR_WS_IMAGES . 'products/originals/' . $Qimages->value('image'))) . ' bytes',
                           'default' => $Qimages->valueInt('default_flag'));
      }
      
      $response = array(EXT_JSON_READER_TOTAL => sizeof($records),
                        EXT_JSON_READER_ROOT => $records);
                        
      echo $toC_Json->encode($response);   
    } 
  
    function listProducts() {
      global $toC_Json, $osC_Database, $osC_Language, $osC_Currencies;
      
      require_once('../includes/classes/currencies.php');
      $osC_Currencies = new osC_Currencies();
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];  
      $current_category_id = end(explode( '_' ,(empty($_REQUEST['categories_id']) ? 0 : $_REQUEST['categories_id']))); 
      
      if ( $current_category_id > 0 ) {
        $osC_CategoryTree = new osC_CategoryTree_Admin();
        $osC_CategoryTree->setBreadcrumbUsage(false);
    
        $in_categories = array($current_category_id);
    
        foreach($osC_CategoryTree->getTree($current_category_id) as $category) {
          $in_categories[] = $category['id'];
        }
    
        $Qproducts = $osC_Database->query('select distinct p.products_id, p.products_type, pd.products_name, p.products_quantity, p.products_price, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status from :table_products p, :table_products_description pd, :table_products_to_categories p2c where p.products_id = pd.products_id and pd.language_id = :language_id and p.products_id = p2c.products_id and p2c.categories_id in (:categories_id)');
        $Qproducts->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
        $Qproducts->bindRaw(':categories_id', implode(',', $in_categories));
      } else {
        $Qproducts = $osC_Database->query('select p.products_id, p.products_type, pd.products_name, p.products_quantity, p.products_price, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status from :table_products p, :table_products_description pd where p.products_id = pd.products_id and pd.language_id = :language_id');
      }
    
      if ( !empty($_REQUEST['search']) ) {
        $Qproducts->appendQuery('and pd.products_name like :products_name');
        $Qproducts->bindValue(':products_name', '%' . $_REQUEST['search'] . '%');
      }
    
      $Qproducts->appendQuery(' order by pd.products_name');
      $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qproducts->bindInt(':language_id', $osC_Language->getID());
      $Qproducts->setExtBatchLimit($start, $limit);
      $Qproducts->execute();
      
      $records = array();
      while ($Qproducts->next()) {
        $products_price = $osC_Currencies->format($Qproducts->value('products_price'));
        
        if ($Qproducts->valueInt('products_type') == PRODUCT_TYPE_GIFT_CERTIFICATE) {
          $Qcertificate = $osC_Database->query('select open_amount_min_value, open_amount_max_value from :table_products_gift_certificates where gift_certificates_amount_type = :gift_certificates_amount_type and products_id = :products_id');
          $Qcertificate->bindTable(':table_products_gift_certificates', TABLE_PRODUCTS_GIFT_CERTIFICATES);
          $Qcertificate->bindInt(':gift_certificates_amount_type', GIFT_CERTIFICATE_TYPE_OPEN_AMOUNT);
          $Qcertificate->bindInt(':products_id', $Qproducts->value('products_id'));
          $Qcertificate->execute();
          
          if ($Qcertificate->numberOfRows() > 0) {
            $products_price = $osC_Currencies->format($Qcertificate->value('open_amount_min_value')) . ' ~ ' . $osC_Currencies->format($Qcertificate->value('open_amount_max_value'));
          }
        }
        
        $Qstatus = $osC_Database->query('select products_id from :table_products_frontpage where products_id = :products_id');
        $Qstatus->bindInt(':products_id', $Qproducts->value('products_id'));
        $Qstatus->bindTable(':table_products_frontpage', TABLE_PRODUCTS_FRONTPAGE);
        $Qstatus->execute();
        
        if ($Qstatus->numberOfRows() > 0) {
          $products_frontpage = 1;
        } else {
          $products_frontpage = 0;
        }
        
        $records[] = array(
          'products_id'         => $Qproducts->value('products_id'),
          'products_name'       => $Qproducts->value('products_name'),
          'products_frontpage'  => $products_frontpage,
          'products_status'     => $Qproducts->value('products_status'),
          'products_price'      => $products_price,
          'products_quantity'   => $Qproducts->value('products_quantity')
        );
      }
  
      $response = array(EXT_JSON_READER_TOTAL => $Qproducts->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records);
                        
      echo $toC_Json->encode($response);    
    }
    
    function getTaxClasses() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      require_once('includes/classes/tax.php');
      $osC_Tax = new osC_Tax_Admin();
      
      $Qtc = $osC_Database->query('select tax_class_id, tax_class_title from :table_tax_class order by tax_class_title');
      $Qtc->bindTable(':table_tax_class', TABLE_TAX_CLASS);
      $Qtc->execute();
    
      $tax_class_array = array(array('id' => '0',
                                     'rate' => '0',
                                     'text' => $osC_Language->get('none')));
      while ($Qtc->next()) {
        $tax_class_array[] = array('id' => $Qtc->valueInt('tax_class_id'),
                                   'rate' => $osC_Tax->getTaxRate($Qtc->valueInt('tax_class_id')),
                                   'text' => $Qtc->value('tax_class_title'));
      }

      $response = array(EXT_JSON_READER_ROOT => $tax_class_array);     
                         
      echo $toC_Json->encode($response);
    }
      
    function getManufacturers() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $Qmanufacturers = $osC_Database->query('select manufacturers_id, manufacturers_name from :table_manufacturers order by manufacturers_name');
      $Qmanufacturers->bindTable(':table_manufacturers', TABLE_MANUFACTURERS);
      $Qmanufacturers->execute();
    
      $manufacturers_array = array(array('id' => '0',
                                         'text' => $osC_Language->get('none')));
      while ($Qmanufacturers->next()) {
        $manufacturers_array[] = array('id' => $Qmanufacturers->valueInt('manufacturers_id'),
                                       'text' => $Qmanufacturers->value('manufacturers_name'));
      }

      $response = array(EXT_JSON_READER_ROOT => $manufacturers_array);   
                           
      echo $toC_Json->encode($response);
    }
    
    function getQuantityUnits(){
      global $toC_Json, $osC_Database, $osC_Language;
      
      $Qunits = $osC_Database->query('select quantity_unit_class_id, quantity_unit_class_title from :table_quantity_unit_classes where language_id = :language_id order by quantity_unit_class_title');
      $Qunits->bindTable(':table_quantity_unit_classes', TABLE_QUANTITY_UNIT_CLASSES);
      $Qunits->bindInt(':language_id', $osC_Language->getID());
      $Qunits->execute();
    
      $units_array = array();
      while ($Qunits->next()) {
        $units_array[] = array('id' => $Qunits->valueInt('quantity_unit_class_id'),
                               'text' => $Qunits->value('quantity_unit_class_title'));
      }

      $response = array(EXT_JSON_READER_ROOT => $units_array);   
                           
      echo $toC_Json->encode($response);
    }
        
    function getCategories() {
      global $toC_Json, $osC_Language;
      
      $osC_CategoryTree = new osC_CategoryTree_Admin();
      
      $categories_array = array();
      if (isset($_REQUEST['top']) && ($_REQUEST['top'] == '1')) {
        $categories_array = array(array('id' => '', 'text' => $osC_Language->get('top_category')));
      }
      
      foreach ($osC_CategoryTree->getTree() as $value) {
        $categories_array[] = array('id' => $value['id'],
                                    'text' => $value['title']);
      }

      $response = array(EXT_JSON_READER_ROOT => $categories_array);    
                          
      echo $toC_Json->encode($response);
    }
    
    function getWeightClasses() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $Qwc = $osC_Database->query('select weight_class_id, weight_class_title from :table_weight_class where language_id = :language_id order by weight_class_title');
      $Qwc->bindTable(':table_weight_class', TABLE_WEIGHT_CLASS);
      $Qwc->bindInt(':language_id', $osC_Language->getID());
      $Qwc->execute();
    
      $weight_class_array = array();
      while ($Qwc->next()) {
        $weight_class_array[] = array('id' => $Qwc->valueInt('weight_class_id'),
                                      'text' => $Qwc->value('weight_class_title'));
      }
      
      $response = array(EXT_JSON_READER_ROOT => $weight_class_array);   
                           
      echo $toC_Json->encode($response);
    }
    
    function getQuantityDiscountGroups() {
      global $toC_Json, $osC_Database, $osC_Language;
     
      $Qgroups = $osC_Database->query('select quantity_discount_groups_id, quantity_discount_groups_name from :table_quantity_discount_groups order by quantity_discount_groups_id');
      $Qgroups->bindTable(':table_quantity_discount_groups', TABLE_QUANTITY_DISCOUNT_GROUPS);
      $Qgroups->execute();
      $quantity_discount_groups = array();
    
      $quantity_discount_groups = array(array('id' => '0',
                                              'text' => $osC_Language->get('none')));
      while ($Qgroups->next()) {
        $quantity_discount_groups[] = array('id' =>$Qgroups->valueInt('quantity_discount_groups_id'),
                                            'text' => $Qgroups->value('quantity_discount_groups_name'));
      }    
    
      $response = array(EXT_JSON_READER_ROOT => $quantity_discount_groups);      
                        
      echo $toC_Json->encode($response);
    }
    
    function getVariantsProducts() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $Qvariants = $osC_Database->query('select * from :table_products_variants where products_id = :products_id order by products_variants_id');
      $Qvariants->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
      $Qvariants->bindInt(':products_id', $_REQUEST['products_id']);
      $Qvariants->execute();
      
      $records = array();
      $variants_groups = array();
      $variant_values = array();
      
      while ($Qvariants->next()) {
        $Qentries = $osC_Database->query('select * from :table_products_variants_entries where products_variants_id = :products_variants_id order by products_variants_entries_id');
        $Qentries->bindTable(':table_products_variants_entries', TABLE_PRODUCTS_VARIANTS_ENTRIES);
        $Qentries->bindInt(':products_variants_id', $Qvariants->valueInt('products_variants_id'));
        $Qentries->execute();
        
        $id = array();
        $data = array();
        while ($Qentries->next()) {
          $groups_id = $Qentries->valueInt('products_variants_groups_id');
          $values_id = $Qentries->valueInt('products_variants_values_id');
          
          $id[] = $groups_id . '_' . $values_id;
          
          if (!isset($variants_groups[$groups_id])) {
            $Qname = $osC_Database->query('select * from :products_variants_groups where products_variants_groups_id = :products_variants_groups_id and language_id = :language_id');
            $Qname->bindTable(':products_variants_groups', TABLE_PRODUCTS_VARIANTS_GROUPS);
            $Qname->bindInt(':products_variants_groups_id', $groups_id);
            $Qname->bindInt(':language_id', $osC_Language->getID());
            $Qname->execute();
            
            $variants_groups[$groups_id] = $Qname->value('products_variants_groups_name');
          }
          
          if (!isset($variant_values[$values_id])) {
            $Qname = $osC_Database->query('select * from :products_variants_values where products_variants_values_id = :products_variants_values_id and language_id = :language_id');
            $Qname->bindTable(':products_variants_values', TABLE_PRODUCTS_VARIANTS_VALUES);
            $Qname->bindInt(':products_variants_values_id', $Qentries->valueInt('products_variants_values_id'));
            $Qname->bindInt(':language_id', $osC_Language->getID());
            $Qname->execute();
            
            $variant_values[$values_id] = $Qname->value('products_variants_values_name');
          }
          
          $data[$groups_id] = $variant_values[$values_id];
        }
        
        $data['id'] = implode('-', $id);
        $data['qty'] = $Qvariants->valueInt('products_quantity');
        $data['price'] = $Qvariants->valueInt('products_price');
        $data['sku'] = $Qvariants->value('products_sku');
        $data['model'] = $Qvariants->value('products_model');
        $data['weight'] = $Qvariants->value('products_weight');
        $data['status'] = ($Qvariants->value('products_status') == 1) ? true : false;

        $records[] = $data;
      }
      
      if (sizeof($records) > 0) {
        $response['success'] = true;
        $response['variants_groups_ids'] = array_keys($variants_groups);
        $response['variants_groups_names'] = array_values($variants_groups);
        $response['data'] = array(EXT_JSON_READER_TOTAL => sizeof($records),
                                  EXT_JSON_READER_ROOT => $records);
      } else {
        $response['success'] = false;
      }
                        
      echo $toC_Json->encode($response);    
    }
    
    function getVariants() {
      global $toC_Json, $osC_Database, $osC_Language;
    
      $Qgroups = $osC_Database->query('select products_variants_groups_id as groups_id, products_variants_groups_name as groups_name from :table_products_variants_groups where language_id = :language_id order by products_variants_groups_name');
      $Qgroups->bindTable(':table_products_variants_groups', TABLE_PRODUCTS_VARIANTS_GROUPS);
      $Qgroups->bindInt(':language_id', $osC_Language->getID());
      $Qgroups->execute();
    
      $variants_groups = array();
      while ($Qgroups->next()) {
        $group = array();
        $group['id'] = $Qgroups->value('groups_id');
        $group['text'] = $Qgroups->value('groups_name');
        $group['expanded'] = true;
    
        $Qvalues = $osC_Database->query('select pvv.products_variants_values_id as values_id, pvv.products_variants_values_name as values_name from :table_products_variants_values pvv, :table_products_variants_values_to_products_variants_groups pvv2pvg where pvv2pvg.products_variants_groups_id = :products_variants_groups_id and pvv2pvg.products_variants_values_id = pvv.products_variants_values_id and pvv.language_id = :language_id order by pvv.products_variants_values_name');
        $Qvalues->bindTable(':table_products_variants_values', TABLE_PRODUCTS_VARIANTS_VALUES);
        $Qvalues->bindTable(':table_products_variants_values_to_products_variants_groups', TABLE_PRODUCTS_VARIANTS_VALUES_TO_PRODUCTS_VARIANTS_GROUPS);
        $Qvalues->bindInt(':products_variants_groups_id', $Qgroups->value('groups_id'));
        $Qvalues->bindInt(':language_id', $osC_Language->getID());
        $Qvalues->execute();
    
        $values = array();
        if ($Qvalues->numberOfRows() > 0) {
          while ($Qvalues->next()) {
            $value = array();
            $value['id'] = $Qgroups->value('groups_id') . '_' . $Qvalues->valueInt('values_id');
            $value['text'] = $Qvalues->value('values_name');
            $value['leaf'] = true;

            if (isset($_REQUEST['products_id']) && is_numeric($_REQUEST['products_id'])) {
              $Qcheck = $osC_Database->query('select pve.* from :table_products_variants_entries pve, :table_products_variants pv where pv.products_id = :products_id and pv.products_variants_id = pve.products_variants_id and pve.products_variants_groups_id = :products_variants_groups_id and pve.products_variants_values_id = :products_variants_values_id');
              $Qcheck->bindTable(':table_products_variants_entries', TABLE_PRODUCTS_VARIANTS_ENTRIES);
              $Qcheck->bindTable(':table_products_variants', TABLE_PRODUCTS_VARIANTS);
              $Qcheck->bindInt(':products_id', $_REQUEST['products_id']);
              $Qcheck->bindInt(':products_variants_groups_id', $Qgroups->value('groups_id'));
              $Qcheck->bindInt(':products_variants_values_id', $Qvalues->valueInt('values_id'));
              $Qcheck->execute();
              
              if ($Qcheck->numberOfRows() > 0) {
                $value['checked'] = true;
              } else {
                $value['checked'] = false;
              }
            }
            
            $values[] = $value;
          }
          
          $group['children'] = $values;
          $variants_groups[] = $group;
        }
      }
    
      echo $toC_Json->encode($variants_groups);
    }
    
    function saveProduct() {
      global $toC_Json, $osC_Database, $osC_Language, $osC_Image;
      
      $osC_Image = new osC_Image_Admin();
      
      $data = array('products_type' => $_REQUEST['products_type'],
                    'quantity' => isset($_REQUEST['products_quantity']) ? $_REQUEST['products_quantity'] : 0,
                    'products_moq' => $_REQUEST['products_moq'],
                    'products_max_order_quantity' => isset($_REQUEST['products_max_order_quantity']) ? $_REQUEST['products_max_order_quantity'] : -1,
                    'order_increment' => $_REQUEST['order_increment'],
                    'quantity_unit_class' => $_REQUEST['quantity_unit_class'],
                    'price' => $_REQUEST['products_price'],
                    'weight' => $_REQUEST['products_weight'],
                    'quantity_discount_groups_id' => $_REQUEST['quantity_discount_groups_id'],
                    'weight_class' => $_REQUEST['products_weight_class'],
                    'status' => $_REQUEST['products_status'],
                    'tax_class_id' => $_REQUEST['products_tax_class_id'],
                    'manufacturers_id' => $_REQUEST['manufacturers_id'],
                    'date_available' => $_REQUEST['products_date_available'],
                    'products_name' => $_REQUEST['products_name'],
                    'products_short_description' => $_REQUEST['products_short_description'],
                    'products_description' => $_REQUEST['products_description'],
                    'products_sku' => $_REQUEST['products_sku'],
                    'products_model' => $_REQUEST['products_model'],
                    'products_tags' => $_REQUEST['products_tags'],
                    'products_url' => $_REQUEST['products_url'],
                    'products_page_title' => $_REQUEST['products_page_title'],
                    'products_meta_keywords' => $_REQUEST['products_meta_keywords'],
                    'products_meta_description' => $_REQUEST['products_meta_description'],
                    'products_attributes_groups_id' => $_REQUEST['products_attributes_groups_id']);

      if ($_REQUEST['products_type'] == PRODUCT_TYPE_DOWNLOADABLE) {
        $data['number_of_downloads'] = $_REQUEST['number_of_downloads'];
        $data['number_of_accessible_days'] = $_REQUEST['number_of_accessible_days'];
      } else if ($_REQUEST['products_type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) {
        $data['gift_certificates_type'] = $_REQUEST['gift_certificates_type'];
        $data['gift_certificates_amount_type'] = $_REQUEST['gift_certificates_amount_type'];
        $data['open_amount_min_value'] = isset($_REQUEST['open_amount_min_value']) ? $_REQUEST['open_amount_min_value'] : 0;
        $data['open_amount_max_value'] = isset($_REQUEST['open_amount_max_value']) ? $_REQUEST['open_amount_max_value'] : 0;
      } 
      
      $data['products_attributes'] = array();
      if(isset($_REQUEST['products_attributes_select']) && is_array($_REQUEST['products_attributes_select'])) {
        foreach ($_REQUEST['products_attributes_select'] as $id => $value) {
          foreach ($osC_Language->getAll() as $l) {
            $data['products_attributes'][]=array( 'id' => $id,
                                                  'value' => $value,
                                                  'language_id' => $l['id']);
          }
        }
      }
      
      if(isset($_REQUEST['products_attributes_text']) && is_array($_REQUEST['products_attributes_text'])) {
        foreach ($_REQUEST['products_attributes_text'] as $id => $values) {
          foreach ($values as $language_id => $value) {
            $data['products_attributes'][]=array( 'id' => $id,
                                                  'value' => $value,
                                                  'language_id' => $language_id);
          }
        }
      }
      
      if (isset($_REQUEST['xsell_ids'])) {
        $xsell_ids = explode(';', $_REQUEST['xsell_ids']);
        $data['xsell_id_array'] = $xsell_ids;
      }
      
      if (isset($_REQUEST['categories_id'])) {
        $data['categories'] = explode(',', $_REQUEST['categories_id']);
      }
      
      if (isset($_REQUEST['localimages']) && !empty($_REQUEST['localimages'])) {
        $localimages = explode(',', $_REQUEST['localimages']);
        $data['localimages'] = $localimages;
      }
      
      if (isset($_REQUEST['products_variants']) && !empty($_REQUEST['products_variants'])) {
        $products_variants = explode(';', $_REQUEST['products_variants']);
        
        $data['variants_quantity'] = array();
        $data['variants_status'] = array();
        $data['variants_price'] = array();
        $data['variants_sku'] = array();
        $data['variants_model'] = array();
        $data['variants_weight'] = array();
        foreach ($products_variants as $variant) {
          $variants = explode(':', $variant);
          
          $data['variants_quantity'][$variants[0]] = $variants[1];
          $data['variants_price'][$variants[0]] = $variants[2];
          $data['variants_sku'][$variants[0]] = $variants[3];
          $data['variants_model'][$variants[0]] = $variants[4];
          $data['variants_weight'][$variants[0]] = $variants[5];
          $data['variants_status'][$variants[0]] = (($variants[6] == 'true') ? 1 : 0);
        }
      }
      
      if (osC_Products_Admin::save((isset($_REQUEST['products_id']) && (is_numeric($_REQUEST['products_id']) && ($_REQUEST['products_id'] != '-1')) ? $_REQUEST['products_id'] : null), $data)) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }      
      
      header('Content-Type: text/html');
      echo $toC_Json->encode($response);
    }
    
    function getProducts() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit'];
      
      $Qproducts = $osC_Database->query('select p.products_id, pd.products_name, p.products_quantity, p.products_price, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status from :table_products p, :table_products_description pd where p.products_id = pd.products_id and pd.language_id = :language_id ');
      
      if ( isset($_REQUEST['products_id']) && is_numeric($_REQUEST['products_id']) ) {
        $Qproducts->appendQuery(' and p.products_id <> :products_id ');
        $Qproducts->bindInt(':products_id', $_REQUEST['products_id']);
      }
      
      $Qproducts->appendQuery(' order by pd.products_name ');
      $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qproducts->bindInt(':language_id', $osC_Language->getID());
      $Qproducts->setExtBatchLimit($start, $limit);
      $Qproducts->execute();
            
      $products = array();
      while ($Qproducts->next()) {
        $products[] = array('id' => $Qproducts->value('products_id'),
                            'text' => $Qproducts->value('products_name'));
      }
            
      $response = array(EXT_JSON_READER_TOTAL => $Qproducts->getBatchSize(),
                        EXT_JSON_READER_ROOT => $products);   
      $Qproducts->freeResult();

      echo $toC_Json->encode($response);
    }
    
    function getXsellProducts() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $response = array(EXT_JSON_READER_TOTAL => 0, EXT_JSON_READER_ROOT => array());  
      if (isset($_REQUEST['products_id']) && ($_REQUEST['products_id'] > 0)) {
        $Qxsell = $osC_Database->query('select pd.products_id, pd.products_name from :table_products_xsell px, :table_products_description pd where px.products_id = :products_id and px.xsell_products_id = pd.products_id and pd.language_id = :language_id');
        $Qxsell->bindTable(':table_products_xsell', TABLE_PRODUCTS_XSELL);
        $Qxsell->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
        $Qxsell->bindInt(':products_id', $_REQUEST['products_id']);
        $Qxsell->bindInt(':language_id', $osC_Language->getID());
        $Qxsell->execute();
    
        $products = array();
        while ($Qxsell->next()) {
          $products[] = array('products_id' => $Qxsell->value('products_id'),
                              'products_name' => $Qxsell->value('products_name'));
        }
        $Qxsell->freeResult();
        
        $response = array(EXT_JSON_READER_TOTAL => sizeof($products),
                          EXT_JSON_READER_ROOT => $products); 
      }
      
      echo $toC_Json->encode($response);
    }
    function getAttributes() {
      global $toC_Json, $osC_Database;
      
      $attributes_groups_id = null;
      $products_id = null;
      if (isset($_REQUEST['products_id']) && !empty($_REQUEST['products_id'])) {
        $products_id = $_REQUEST['products_id'];
        
        $Qattributes = $osC_Database->query('select products_attributes_groups_id from :table_products where products_id = :products_id');
        $Qattributes->bindTable(':table_products', TABLE_PRODUCTS);
        $Qattributes->bindInt(':products_id', $products_id);
        $Qattributes->execute();
        
        $attributes_groups_id = $Qattributes->valueInt('products_attributes_groups_id');
        
        $Qattributes->freeResult();
      } else {
        $attributes_groups_id = $_REQUEST['products_attributes_groups_id'];
      }
      
      $attributes = osC_Products_Admin::getAttributes($attributes_groups_id, $products_id);
      
      if ($attributes !== false) {
        $response = array('success' => true, 'attributes' => $attributes);   
      } else {
        $response = array('success' => false);
      }
      
      echo $toC_Json->encode($response);
    }
    
    function loadProduct() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $data = osC_Products_Admin::getData($_REQUEST['products_id']);
      if(!empty($data['products_date_available'])){
        $date = explode(' ', $data['products_date_available']);
        $data['products_date_available'] = $date[0];
      }
      
      if($data['products_type'] == PRODUCT_TYPE_DOWNLOADABLE) {
        $Qdownloadables = $osC_Database->query('select filename, cache_filename, sample_filename, cache_sample_filename, number_of_downloads, number_of_accessible_days from :table_products_downloadables where products_id = :products_id');
        $Qdownloadables->bindTable(':table_products_downloadables', TABLE_PRODUCTS_DOWNLOADABLES);
        $Qdownloadables->bindInt(':products_id', $_REQUEST['products_id']);
        $Qdownloadables->execute();
        
        $data['filename'] = $Qdownloadables->value('filename');
        $data['cache_filename_url'] = HTTP_SERVER . DIR_WS_HTTP_CATALOG . FILENAME_DOWNLOAD . '?id=' . $_REQUEST['products_id'] . '&cache_filename=' . $Qdownloadables->value('cache_filename');
        $data['sample_filename'] = $Qdownloadables->value('sample_filename');
        $data['cache_sample_filename_url'] = HTTP_SERVER . DIR_WS_HTTP_CATALOG . FILENAME_DOWNLOAD . '?id=' . $_REQUEST['products_id'] . '&cache_sample_filename=' . $Qdownloadables->value('cache_sample_filename');
        $data['number_of_downloads'] = $Qdownloadables->valueInt('number_of_downloads');
        $data['number_of_accessible_days'] = $Qdownloadables->valueInt('number_of_accessible_days');
        
        $Qdownloadables->freeResult();
      } else if($data['products_type'] == PRODUCT_TYPE_GIFT_CERTIFICATE) {
        $Qcertifcate = $osC_Database->query('select gift_certificates_type, gift_certificates_amount_type, open_amount_min_value, open_amount_max_value from :table_products_gift_certificates where products_id = :products_id');
        $Qcertifcate->bindTable(':table_products_gift_certificates', TABLE_PRODUCTS_GIFT_CERTIFICATES);
        $Qcertifcate->bindInt(':products_id', $_REQUEST['products_id']);
        $Qcertifcate->execute();
        
        $data['gift_certificates_type'] = $Qcertifcate->valueInt('gift_certificates_type');
        $data['gift_certificates_amount_type'] = $Qcertifcate->valueInt('gift_certificates_amount_type');
        $data['open_amount_min_value'] = $Qcertifcate->valueInt('open_amount_min_value');
        $data['open_amount_max_value'] = $Qcertifcate->valueInt('open_amount_max_value');
      }
      
      $Qpd = $osC_Database->query('select products_name, products_short_description, products_description, products_tags, products_url, products_page_title, products_meta_keywords, products_meta_description, language_id from :table_products_description where products_id = :products_id');
      $Qpd->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qpd->bindInt(':products_id', $_REQUEST['products_id']);
      $Qpd->execute();

      $products_name = array();
      $products_description = array();
      $products_model = array();
      $products_tags = array();
      $products_url = array();
  
      while ($Qpd->next()) {
        $data['products_name[' . $Qpd->valueInt('language_id') . ']'] = $Qpd->value('products_name');
        $data['products_short_description[' . $Qpd->valueInt('language_id') . ']'] = $Qpd->value('products_short_description');
        $data['products_description[' . $Qpd->valueInt('language_id') . ']'] = $Qpd->value('products_description');
        $data['products_tags[' . $Qpd->valueInt('language_id') . ']'] = $Qpd->value('products_tags');
        $data['products_url[' . $Qpd->valueInt('language_id') . ']'] = $Qpd->value('products_url');
        $data['products_page_title[' . $Qpd->valueInt('language_id') . ']'] = $Qpd->value('products_page_title');
        $data['products_meta_keywords[' . $Qpd->valueInt('language_id') . ']'] = $Qpd->value('products_meta_keywords');
        $data['products_meta_description[' . $Qpd->valueInt('language_id') . ']'] = $Qpd->value('products_meta_description');
      }
      $Qpd->freeResult();

      $Qcategories = $osC_Database->query('select categories_id from :table_products_to_categories where products_id = :products_id');
      $Qcategories->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
      $Qcategories->bindInt(':products_id', $_REQUEST['products_id']);
      $Qcategories->execute();
  
      $product_categories_array = array();
      while ($Qcategories->next()) {
        $product_categories_array[] = $Qcategories->valueInt('categories_id');
      }
      $Qcategories->freeResult();
      
      $data['categories_id'] = implode(',', $product_categories_array);
      
      $response = array('success' => true, 'data' => $data);     
       
      echo $toC_Json->encode($response);
    }
    
    function getAttributeGroups() {
      global $osC_Database, $toC_Json, $osC_Language;
      
      $Qgroups = $osC_Database->query('select products_attributes_groups_id, products_attributes_groups_name from :table_products_attributes_groups ');
      $Qgroups->bindTable(':table_products_attributes_groups', TABLE_PRODUCTS_ATTRIBUTES_GROUPS);
      $Qgroups->execute();
      
      $records = array(array('id' => '0', 'text' => $osC_Language->get('parameter_none')));
      while ( $Qgroups->next() ) {
        $records[] = array('id' => $Qgroups->ValueInt('products_attributes_groups_id'),
                           'text' => $Qgroups->Value('products_attributes_groups_name'));
      }
      $Qgroups->freeResult();
      
      $response = array(EXT_JSON_READER_TOTAL => sizeof($records),
                        EXT_JSON_READER_ROOT => $records); 
                        
      echo $toC_Json->encode($response);
    }
  
    function getCategoriesTree() {
      global $toC_Json;
      
      $osC_CategoryTree = new osC_CategoryTree();
      $categories_array = $osC_CategoryTree->buildExtJsonTreeArray();

      echo $toC_Json->encode($categories_array);                          
    }

    function setFrontPage() {
      global $toC_Json, $osC_Language;
 
      if ( isset($_REQUEST['products_id']) && osC_Products_Admin::setFrontPage($_REQUEST['products_id'], (isset($_REQUEST['flag']) ? $_REQUEST['flag'] : null)) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
  
      echo $toC_Json->encode($response);
    }
    
    function setStatus() {
      global $toC_Json, $osC_Language;
 
      if ( isset($_REQUEST['products_id']) && osC_Products_Admin::setStatus($_REQUEST['products_id'], (isset($_REQUEST['flag']) ? $_REQUEST['flag'] : 1)) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
  
      echo $toC_Json->encode($response);
    }
    
    function batchSetStatus() {
      global $toC_Json, $osC_Language;
      
      $error = false;
      $batch = explode(',', $_REQUEST['batch']);
      
      foreach ($batch as $id) {
        if (!osC_Products_Admin::setStatus($id, isset($_REQUEST['status']) ? $_REQUEST['status'] : 1)) {
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