<?php
/*
  $Id: new_products.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Content_new_products extends osC_Modules {
    var $_title,
        $_code = 'new_products',
        $_author_name = 'osCommerce',
        $_author_www = 'http://www.oscommerce.com',
        $_group = 'content';

/* Class constructor */

    function osC_Content_new_products() {
      global $osC_Language;

      $this->_title = $osC_Language->get('new_products_title');
    }

    function initialize() {
      global $osC_Database, $osC_Services, $osC_Language, $osC_Currencies, $osC_Image, $osC_Specials, $current_category_id;

      if ($current_category_id < 1) {
        $Qnewproducts = $osC_Database->query('select p.products_id, p.products_tax_class_id, p.products_price, pd.products_name, pd.products_keyword, i.image from :table_products p left join :table_products_images i on (p.products_id = i.products_id and i.default_flag = :default_flag), :table_products_description pd where p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :language_id order by p.products_date_added desc limit :max_display_new_products');
      } else {
        $Qnewproducts = $osC_Database->query('select distinct p.products_id, p.products_tax_class_id, p.products_price, pd.products_name, pd.products_keyword, i.image from :table_products p left join :table_products_images i on (p.products_id = i.products_id and i.default_flag = :default_flag), :table_products_description pd, :table_products_to_categories p2c, :table_categories c where c.parent_id = :parent_id and c.categories_id = p2c.categories_id and p2c.products_id = p.products_id and p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :language_id order by p.products_date_added desc limit :max_display_new_products');
        $Qnewproducts->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
        $Qnewproducts->bindTable(':table_categories', TABLE_CATEGORIES);
        $Qnewproducts->bindInt(':parent_id', $current_category_id);
      }

      $Qnewproducts->bindTable(':table_products', TABLE_PRODUCTS);
      $Qnewproducts->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
      $Qnewproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qnewproducts->bindInt(':default_flag', 1);
      $Qnewproducts->bindInt(':language_id', $osC_Language->getID());
      $Qnewproducts->bindInt(':max_display_new_products', MODULE_CONTENT_NEW_PRODUCTS_MAX_DISPLAY);

      if (MODULE_CONTENT_NEW_PRODUCTS_CACHE > 0) {
        $Qnewproducts->setCache('new_products-' . $osC_Language->getCode() . '-' . $osC_Currencies->getCode() . '-' . $current_category_id, MODULE_CONTENT_NEW_PRODUCTS_CACHE);
      }

      $Qnewproducts->execute();

      if ($Qnewproducts->numberOfRows()) {
        $this->_content = '<div style="overflow: auto; height: 100%;">';

        $i = 0;
        while ($Qnewproducts->next()) {
          if(($i % 3 == 0) && ($i != 0))
            $this->_content .= '<div style="clear:both"></div>';

          $osC_Product = new osC_Product($Qnewproducts->valueInt('products_id'));
          
          $this->_content .= '<span style="width: 32%; float: left; text-align: center; padding: 2px;">' .
                             osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qnewproducts->value('products_id')), $osC_Image->show($Qnewproducts->value('image'), $Qnewproducts->value('products_name'))) . '<br />' .
                             osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qnewproducts->value('products_id')), $Qnewproducts->value('products_name')) . '<br />' .
                             $osC_Product->getPriceFormated(true) .
                             '</span>';

          $i++;
        }

        $this->_content .= '</div>';
      }

      $Qnewproducts->freeResult();
    }

    function install() {
      global $osC_Database;

      parent::install();

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Maximum Entries To Display', 'MODULE_CONTENT_NEW_PRODUCTS_MAX_DISPLAY', '9', 'Maximum number of new products to display', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Cache Contents', 'MODULE_CONTENT_NEW_PRODUCTS_CACHE', '60', 'Number of minutes to keep the contents cached (0 = no cache)', '6', '0', now())");
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('MODULE_CONTENT_NEW_PRODUCTS_MAX_DISPLAY', 'MODULE_CONTENT_NEW_PRODUCTS_CACHE');
      }

      return $this->_keys;
    }
  }
?>
