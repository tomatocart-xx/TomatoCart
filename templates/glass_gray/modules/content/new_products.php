<?php
/*
  $Id: new_products.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

<!-- module new_products start //-->
<div class="moduleBox">
  <h6><?php echo $osC_Box->getTitle(); ?></h6>
  
  <div class="content">  
    <?php 
      if ($current_category_id < 1) {
        $Qproducts = $osC_Database->query('select p.products_id, p.products_tax_class_id, p.products_price, pd.products_name, pd.products_keyword, i.image from :table_products p left join :table_products_images i on (p.products_id = i.products_id and i.default_flag = :default_flag), :table_products_description pd where p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :language_id order by p.products_date_added desc limit :max_display_new_products');
      } else {
        $Qproducts = $osC_Database->query('select distinct p.products_id, p.products_tax_class_id, p.products_price, pd.products_name, pd.products_keyword, i.image from :table_products p left join :table_products_images i on (p.products_id = i.products_id and i.default_flag = :default_flag), :table_products_description pd, :table_products_to_categories p2c, :table_categories c where c.parent_id = :parent_id and c.categories_id = p2c.categories_id and p2c.products_id = p.products_id and p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :language_id order by p.products_date_added desc limit :max_display_new_products');
        $Qproducts->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
        $Qproducts->bindTable(':table_categories', TABLE_CATEGORIES);
        $Qproducts->bindInt(':parent_id', $current_category_id);
      }
    
      $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
      $Qproducts->bindTable(':table_products_images', TABLE_PRODUCTS_IMAGES);
      $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qproducts->bindInt(':default_flag', 1);
      $Qproducts->bindInt(':language_id', $osC_Language->getID());
      $Qproducts->bindInt(':max_display_new_products', MODULE_CONTENT_NEW_PRODUCTS_MAX_DISPLAY);
    
      if (MODULE_CONTENT_NEW_PRODUCTS_CACHE > 0) {
        $Qproducts->setCache('new_products-' . $osC_Language->getCode() . '-' . $osC_Currencies->getCode() . '-' . $current_category_id, MODULE_CONTENT_NEW_PRODUCTS_CACHE);
      }
      $Qproducts->execute();
    
      if ($Qproducts->numberOfRows()) {
        $i = 0;
        while ($Qproducts->next()) {
          if(($i % 3 == 0) && ($i != 0))
            echo '<div style="clear:both"></div>';
    
          $osC_Product = new osC_Product($Qproducts->valueInt('products_id'));
          
          echo '<div style="margin-top: 10px; float:left; width: 33%; text-align: center">' .
                 '<span style="display:block; height: 32px; text-align: center">' . osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qproducts->value('products_id')), $Qproducts->value('products_name')) . '</span>' . 
                 osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qproducts->value('products_id')), $osC_Image->show($Qproducts->value('image'), $Qproducts->value('products_name'))) . 
                 '<span style="display:block; padding: 3px; text-align: center">' . $osC_Product->getPriceFormated(true) . '</span>' .
                 osc_link_object(osc_href_link(FILENAME_PRODUCTS, $Qproducts->value('products_id') . '&action=cart_add'), osc_draw_image_button('button_add_to_cart.png', $osC_Language->get('button_add_to_cart'))) .
               '</div>';
    
          $i++;
        }
        
        echo '<div style="clear:both"></div>';
      }
    
      $Qproducts->freeResult();
    ?>
  </div>
</div>
<!-- module new_products end //-->
<?php
  unset($osC_Box);
?>