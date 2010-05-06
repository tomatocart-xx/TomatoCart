<?php
/*
  $Id: exporter.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require_once('../ext/zip/pclzip.lib.php');

  class toC_Exporter {
    var $_csv_delimiter = ',',
        $_csv_enclosure = '',
        $_type = '',
        $_tmp_file;

    function toC_Exporter ($parameters) {
      $this->_csv_delimiter = $parameters['csv_field_seperator'];
      $this->_csv_enclosure = $parameters['csv_field_enclosed'];
      $this->_file_type = strtolower($parameters['file_type']);
      $this->_compression_type = strtolower($parameters['compression_type']);
      $this->_type = strtolower($parameters['type']);

      $this->_tmp_file = DIR_FS_CACHE . time() . rand() .  '.' . $this->_file_type;
    }

    function getExporter($parameters) {
      if ($parameters['type'] == 'customers') {
        return new toC_Customers_Exporter($parameters);
      }else if($parameters['type'] == 'products'){
        return new toC_Products_Exporter($parameters);
      }
    }

    function zipFile($filename) {
      $zip_filename = $filename . '.zip';
      
      $zip = new PclZip($filename . '.zip');
      
			$result = $zip->create($filename, PCLZIP_OPT_REMOVE_ALL_PATH);
			 
      if ($result == 0) {
        return false;
      } else {
        @unlink($this->_tmp_file);
        $this->_tmp_file = $zip_filename;
      }
    }

    function export() {
      switch ($this->_file_type) {
        case 'csv': $this->exportCsv(); break;
        case 'xml': $this->exportXml(); break;
      }

      if ( $this->_compression_type == 'zip' ) {
        $this->zipFile($this->_tmp_file);
      }

      return $this->_tmp_file;
    }

    function getContentType(){
      if ($this->_compression_type == 'zip') {
        return 'application/zip';
      } elseif ($this->_file_type == 'csv'){
        return 'application/vnd.ms-excel';
      } elseif ($this->_file_type == 'xml'){
        return 'text/xml';
      }
    }

    function getFileName(){
      $filename = $this->_type . '.' . $this->_file_type;
      if ($this->_compression_type == 'zip') {
        $filename .= '.' . $this->_compression_type;
      }

      return $filename;
    }

    function getTempFile(){
      return $this->_tmp_file;
    }

    function removeTempFile(){
      @unlink($this->_tmp_file);
    }

    function getSize(){
      return filesize($this->_tmp_file);
    }
  }

  class toC_Customers_Exporter extends toC_Exporter {
    var $_num_of_addresses = 3,
        $_customers_info = array(),
        $_address_book_info = array(),
        $_customers = array();

    function toC_Customers_Exporter($parameters) {
      parent::toC_Exporter($parameters);

      $this->_customers_colum = array('customers_id'            => 'ID',
                                      'customers_groups_id'     => 'CustomerGroupID',
                                      'customers_gender'        => 'Gender',
                                      'customers_firstname'     => 'Firstname',
                                      'customers_lastname'      => 'Lastname',
                                      'customers_dob'           => 'DateOfBirthday',
                                      'customers_email_address' => 'Email',
                                      'customers_telephone'     => 'Telephone',
                                      'customers_fax'           => 'Fax',
                                      'customers_status'        => 'Status');

      $this->_address_book_colum = array('entry_gender'         => 'Gender',
                                         'entry_company'        => 'Company',
                                         'entry_firstname'      => 'Firstname',
                                         'entry_lastname'       => 'Lastname',
                                         'entry_street_address' => 'Street',
                                         'entry_suburb'         => 'Suburb',
                                         'entry_postcode'       => 'Postcode',
                                         'entry_city'           => 'City',
                                         'entry_state'          => 'State',
                                         'entry_country_id'     => 'CountryId',
                                         'entry_zone_id'        => 'ZoneId',
                                         'entry_telephone'      => 'Telephone',
                                         'entry_fax'            => 'Fax',
                                         'countries_name'       => 'Country');

      $this->_customers = $this->renderData();
    }

    function renderData(){
      global $osC_Database;

      $Qcustomer = $osC_Database->query('select * from :table_customers order by customers_id');
      $Qcustomer->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qcustomer->execute();

      $customers = array();
      while ( $Qcustomer->next() ) {
        $customer = $Qcustomer->toArray();

        $Qaddressbook = $osC_Database->query('select * from :table_address_book ab left join :table_countries co on(ab.entry_country_id = co.countries_id) where ab.customers_id = :customers_id');
        $Qaddressbook->bindTable(':table_address_book', TABLE_ADDRESS_BOOK);
        $Qaddressbook->bindTable(':table_countries', TABLE_COUNTRIES);
        $Qaddressbook->bindInt(':customers_id', $Qcustomer->valueInt('customers_id'));
        $Qaddressbook->execute();
        
        $address_books = array();
        $address_books_default = array();
        while($Qaddressbook->next()){
          if($Qcustomer->valueInt('customers_default_address_id') == $Qaddressbook->valueInt('address_book_id'))
            $address_books_default[] = $Qaddressbook->toArray();
          else
            $address_books[] = $Qaddressbook->toArray();
        }
        $address_books = array_merge($address_books_default, $address_books);
        $customer['address_books'] = $address_books;

        $customers[] = $customer;
      }
      return $customers;
    }

    function exportCsv() {
      $handle = fopen($this->_tmp_file, "a");

//write header
      $columns = array();
      foreach($this->_customers_colum as $field => $column){
        $columns[] = $column;
      }

      for($i = 1; $i <= $this->_num_of_addresses; $i++){
        foreach($this->_address_book_colum as $field => $column){
          $columns[] = $column . '_' . $i;
        }
      }
      fputcsv($handle, $columns, $this->_csv_delimiter, $this->_csv_enclosure);

//write content
      $i = 0;
      foreach($this->_customers as $customer){
        $values = array();
        
        foreach($customer as $field => $value){
          if(isset($this->_customers_colum[$field])){
            $values[] = $value; 
          }

          if ( $field == 'address_books' && is_array($value) && !empty($value) ) {
            foreach ($value as $address) {
              foreach ($address as $field => $value) {
                if ( isset($this->_address_book_colum[$field]) ) {
                  $values[] = $value;
                }
              }
            }
          }
        }
        
        fputcsv($handle, $values, $this->_csv_delimiter, $this->_csv_enclosure);
      }

      fclose($handle);
    }

    function exportXml(){
      $handle = fopen($this->_tmp_file, "a");

      fwrite($handle, '<?xml version="1.0" encoding="utf-8" ?>' . "\n");
      fwrite($handle, '<Customers>' . "\n");

      foreach ($this->_customers as $customer) {
        fwrite($handle, '<Customer>' . "\n");

        foreach ($customer as $field => $value) {
          if( isset($this->_customers_colum[$field]) ){
            fwrite($handle, '<' . $this->_customers_colum[$field] . '><![CDATA[' . $value . ']]></' . $this->_customers_colum[$field] . '>' . "\n");
          }

          if ( $field == 'address_books' && is_array($value) && !empty($value) ) {
            fwrite($handle, '<AddressBooks>' . "\n");

            foreach ($customer['address_books'] as $address) {
              fwrite($handle, '<AddressBook>'."\n");

              foreach ($address as $field => $value) {
                if( isset($this->_address_book_colum[$field]) ){
                  fwrite($handle, '<' . $this->_address_book_colum[$field] . '><![CDATA[' . $value . ']]></' . $this->_address_book_colum[$field] . '>'."\n");
                }
              }

              fwrite($handle, '</AddressBook>' . "\n");
            }

            fwrite($handle, '</AddressBooks>' . "\n");
          }

        }

        fwrite($handle, '</Customer>' . "\n");
      }

      fwrite($handle, '</Customers>'."\n");

      fclose($handle);
    }
  }

  class toC_Products_Exporter extends toC_Exporter{
    var $_products_column = array(),
        $_products_description_column = array(),
        $_products = array(),
        $_num_of_description = 3;

    function toC_Products_Exporter($parameters){
      parent::toC_Exporter($parameters);

      $this->_products_column = array('products_id'               => 'ID',
                                      'products_type'             => 'Type', 
                                      'products_quantity'         => 'Quantity',
                                      'products_moq'              => 'Moq',
                                      'products_max_order_quantity' => 'MaxQuantity',  
                                      'products_price'            => 'Price',
                                      'products_sku'            => 'SKU',
                                      'products_model'            => 'Model',
                                      'products_weight'           => 'Weight',
                                      'products_weight_class'     => 'WeightClass',
                                      'products_status'           => 'Status',
                                      'products_tax_class_id'     => 'Tax',
                                      'manufacturers_id'          => 'Manufacturer',
                                      'quantity_unit_class'       => 'UnitClass',
                                      'order_increment'           => 'OrderIncrement',
      																'categories_id'             => 'CategoriesId',
                                      'image_url'                 => 'ImageUrl');

      $this->_products_description_column = array('products_name'              => 'ProductsName',
                                                  'products_short_description' => 'ProductsShortDescription',
                                                  'products_description'       => 'ProductsDescription',
                                                  'products_keyword'           => 'ProductsKeyword',
                                                  'products_tags'              => 'ProductsTags',
                                                  'products_url'               => 'ProductsUrl',
                                                  'products_page_title'        => 'ProductsPageTitle',
                                                  'products_meta_keywords'     => 'ProductsMetaKeywords',
                                                  'products_meta_description'  => 'ProductsMetaDescription',
                                                  'products_viewed'            => 'ProductsViewed');
      
      $this->_products_images_column = array('products_id'    => 'productsID',
                                             'image'          => 'ProductsImage',
                                             'sort_order'     => 'SortOrder',
                                             'default_flag'   => 'DefaultFlag');

      $this->_products = $this->renderData();
    }

    function renderData(){
      global $osC_Database, $osC_Language;

      $Qproducts = $osC_Database->query('select p.products_id, p.products_type, p.products_quantity, p.products_moq, p.products_max_order_quantity, p.products_price, p.products_sku, p.products_model, p.products_weight, p.products_weight_class, p.products_status, p.products_tax_class_id,  p.manufacturers_id, p.quantity_unit_class, p.order_increment, c.categories_id from :table_products p left join :table_weight_class wc on(wc.weight_class_id = p.products_weight_class and wc.language_id = :language_id) left join :table_manufacturers m on(m.manufacturers_id = p.manufacturers_id) left join :table_tax_class t on(t.tax_class_id = p.products_tax_class_id) left join :table_products_to_categories c on(c.products_id = p.products_id)');
      $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproducts->bindTable(':table_weight_class', TABLE_WEIGHT_CLASS);
      $Qproducts->bindTable(':table_manufacturers', TABLE_MANUFACTURERS);
      $Qproducts->bindTable(':table_tax_class', TABLE_TAX_CLASS);
      $Qproducts->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
      $Qproducts->bindInt(':language_id', $osC_Language->getID());
      $Qproducts->execute();

      $products = array();
      while ( $Qproducts->next() ) {
        $product = $Qproducts->toArray();
        
        $Qimage = $osC_Database->query('select products_id, image, default_flag from :table_products_images where products_id = :products_id');
        $Qimage->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
        $Qimage->bindInt(':products_id', $Qproducts->valueInt('products_id'));
        $Qimage->execute();
        
        $image = array();
        while ($Qimage->next()) {
          $image[] = $Qimage->toArray();
        }
        $product['image'] = $image;

        $Qdescription= $osC_Database->query('select language_id, products_name, products_short_description, products_description, products_keyword, products_keyword, products_tags, products_url, products_page_title, products_meta_keywords, products_meta_description, products_viewed from :table_products_description where products_id = :products_id');
        $Qdescription->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
        $Qdescription->bindInt(':products_id', $Qproducts->valueInt('products_id'));
        $Qdescription->execute();

        $description = array();
        while($Qdescription->next()){
          $description[$Qdescription->valueInt('language_id')] = $Qdescription->toArray();
        }
        $product['description'] = $description;
        
        $products[] = $product;
      }

      return $products;
    }

    function exportCsv() {
      global $osC_Language;

      $handle = fopen($this->_tmp_file, "a");

//write header
      $columns = array();
      foreach ($this->_products_column as $field => $title) {
        $columns[] = $title;
      }

      foreach ($osC_Language->getAll() as $l) {
        foreach($this->_products_description_column as $field => $description){
          $columns[] = $description . '_' . $l['code'];
        }
      }
      fputcsv($handle, $columns, $this->_csv_delimiter, $this->_csv_enclosure);

//write content
      $i = 0;
      foreach ($this->_products as $product) {
        $values = array();
        
        foreach ($product as $field => $value) {
          if ( isset($this->_products_column[$field]) ) {
            $values[] = $value;
          }
          
          if ( $field == 'image' && is_array($value) && !empty($value) ) {
            $images = null;
            foreach ($value as $image) {
              foreach ($image as $image_field => $image_value) {
                if ( $image_field == 'image' && !empty($image_value) ) {
                  $images[] = $image_value; 
                }
              }
            }
            $images = implode('#', $images);
            
            $values[] = $images;
          }

          if( $field == 'description' && is_array($value) && !empty($value) ) {
            foreach ($osC_Language->getAll() as $l) {
              if ( is_array($value) ) {
                foreach ($value as $language_id => $description) {
                 if ($language_id == $l['id']) {
                    foreach ($description as $field => $value) {
                      if ( isset($this->_products_description_column[$field]) ) {
                        $values[] = $value;
                      }
                    }
                  }
                }
              }
            }
          }
        }
        fputcsv($handle, $values, $this->_csv_delimiter, $this->_csv_enclosure);
      }

      fclose($handle);
    }

    function exportXml(){
      global $osC_Language;

      $handle = fopen($this->_tmp_file, "a");

      fwrite($handle, '<?xml version="1.0" encoding="utf-8" ?>' . "\n");
      fwrite($handle, '<Products>' . "\n");

      foreach($this->_products as $product){
        fwrite($handle, '<Product>' . "\n");
        foreach($product as $field => $value){
          if(isset($this->_products_column[$field])){
            fwrite($handle, '<' . $this->_products_column[$field] . '><![CDATA[' . $value . ']]></' . $this->_products_column[$field] . '>' . "\n");
          }

          if ( $field == 'description' && is_array($value) && !empty($value) ) {
            fwrite($handle, '<Descriptions>' . "\n");

            foreach ($osC_Language->getAll() as $l) {
              foreach ($value as $language_id => $description) {
                if ($language_id == $l['id']) {
                  fwrite($handle, '<Description code="' . $l['code'] . '">' . "\n");

                  foreach ($description as $field => $values) {
                    if ( isset($this->_products_description_column[$field]) ) {
                      fwrite($handle, '<' . $this->_products_description_column[$field] . '><![CDATA[' . $values . ']]></' . $this->_products_description_column[$field] . '>' . "\n");
                    }
                  }

                  fwrite($handle, '</Description>' . "\n");
                }
              }
            }

            fwrite($handle, '</Descriptions>' . "\n");
          }
          
          if ($field == 'image' && is_array($value) && !empty($value) ) {
            fwrite($handle, '<Images>' . "\n");
            
            foreach ($value as $image){
              fwrite($handle, '<Image>' . "\n");
              
              foreach ($image as $key_field => $value_field) {
                if ( isset($this->_products_images_column[$key_field]) ) {
                  fwrite($handle, '<' . $this->_products_images_column[$key_field] . '><![CDATA[' . $value_field . ']]></' . $this->_products_images_column[$key_field] . '>' . "\n");
                }
              }
              
              fwrite($handle, '</Image>' . "\n");
            }
            
            fwrite($handle, '</Images>' . "\n");
          }
          
          
        }
        fwrite($handle, '</Product>' . "\n");
      }

      fwrite($handle, '</Products>');

      fclose($handle);
    }
  }
?>
