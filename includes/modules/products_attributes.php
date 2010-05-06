<?php
/*
  $Id: products_attributes.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  if ( isset($current_category_id) && !empty($current_category_id) ) {

    $Qgroups = $osC_Database->query('select distinct(p.products_attributes_groups_id) from :table_products p, :table_products_to_categories ptc where p.products_id = ptc.products_id and p.products_attributes_groups_id is not null and ptc.categories_id = :categories_id ');
    $Qgroups->bindTable(':table_products', TABLE_PRODUCTS);
    $Qgroups->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
    $Qgroups->bindInt(':categories_id', $current_category_id);
    $Qgroups->execute();

    if ($Qgroups->numberOfRows() > 0) {
      echo '<div class="moduleBox"><h6>'.$osC_Language->get('products_attributes_filter').'</h6>' . "\n";
      echo '  <div id="productAttributes" class="content">' . "\n";
      echo '    <form name="filter" action="' . osc_href_link(FILENAME_DEFAULT) . '" method="get">' . osc_draw_hidden_field('cPath', isset($_GET['cPath'])? $_GET['cPath'] :'') . osc_draw_hidden_session_id_field();

      while ($Qgroups->next()) {

        $Qentries = $osC_Database->query('select * from :table_products_attributes_values where products_attributes_groups_id = :products_attributes_groups_id and language_id = :language_id  order by sort_order');
        $Qentries->bindTable(':table_products_attributes_values', TABLE_PRODUCTS_ATTRIBUTES_VALUES);
        $Qentries->bindInt(':products_attributes_groups_id', $Qgroups->valueInt('products_attributes_groups_id'));
        $Qentries->bindInt(':language_id', $osC_Language->getID());
        $Qentries->execute();
        
        while ($Qentries->next()) {
    
          $data = array();
          $data[] = array('id'=>'', 'text' => $osC_Language->get('pull_down_default'));
  
          if ($Qentries->value('module') == 'text_field') {
  
            $Qvalues = $osC_Database->query('select distinct value from :table_products_attributes where products_attributes_values_id = :products_attributes_values_id and language_id = :language_id ');
            $Qvalues->bindTable(':table_products_attributes', TABLE_PRODUCTS_ATTRIBUTES);
            $Qvalues->bindInt(':products_attributes_values_id', $Qentries->value('products_attributes_values_id'));
            $Qvalues->bindInt(':language_id', $osC_Language->getID());
            $Qvalues->execute();
  
            while ($Qvalues->next()){
              $fields_value = $Qvalues->value('value');
  
              if(!empty($fields_value))
                $data[] = array('id'=> $fields_value, 'text' => $fields_value);
            }
  
            $Qvalues->freeResult();
          }else{
            $values = explode(',', $Qentries->value('value'));
  
            for ($i = 1; $i <= sizeof($values); $i++) {
              $data[] = array('id' => $i, 'text' => $values[$i - 1]);
            }
          }
  
          $default = '';
          $products_attributes_values_id = $Qentries->value('products_attributes_values_id');
  
          if ( isset($_GET['products_attributes']) && is_array($_GET['products_attributes']) && isset($_GET['products_attributes'][$products_attributes_values_id]) ) {
            $default = $_GET['products_attributes'][$products_attributes_values_id];
          }
  
          echo '<span style="float: left; width: 49%">' . osc_draw_label($Qentries->value('name') . ' :', 'products_attributes[' . $products_attributes_values_id . ']') . '&nbsp;' . osc_draw_pull_down_menu('products_attributes[' . $products_attributes_values_id . ']', $data, $default, 'onchange="this.form.submit()"').'</span>';
      
        }
      }

      $Qgroups->freeResult();

      echo '    <div style="clear: both"></div>';
      echo '    </form>' . "\n";
      echo '  </div>
            </div>';
    }
  }
?>

