<?php
/*
  $Id: auto_completer.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

class toC_Json_Auto_Completer {

  function getProducts() {  
    global $osC_Database, $osC_Language, $toC_Json;
    
    $products = array();
    if (isset($_POST['keywords']) && !empty($_POST['keywords'])) {
      $Qproducts = $osC_Database->query("select distinct products_name from :table_products_description where products_name like :keywords and language_id =" . $osC_Language->getID());
      $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
      $Qproducts->bindValue(':keywords', '%' . $_POST['keywords'] . '%');
      $Qproducts->execute();
      
      while($Qproducts->next()) {
        $products[] = $Qproducts->value('products_name');        
      }
    }
    
    echo $toC_Json->encode($products);
  }
}
?>