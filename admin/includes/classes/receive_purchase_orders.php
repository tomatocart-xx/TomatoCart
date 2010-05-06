<?php
/*
  $Id: receive_purchase_orders.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
	
  class toC_Receive_Purchase_Orders_Admin {
  	function save($id, $data) {
      global $toC_Json, $osC_Database;
      
      $error = false;
      
      $osC_Database->startTransaction();
      
      if ( is_numeric($id) ) {
        $Qorder = $osC_Database->query('update :table_receive_purchase_orders set receive_purchase_orders_no = :receive_purchase_orders_no, purchase_orders_id = :purchase_orders_id, administrators_id = :administrators_id, status = :status, date_received = :date_received, date_modified = :date_modified, description = :description where receive_purchase_orders_id = :receive_purchase_orders_id ');
        $Qorder->bindInt(':receive_purchase_orders_id', $id);
      } else {
        $Qorder = $osC_Database->query('insert into :table_receive_purchase_orders (receive_purchase_orders_no, purchase_orders_id, administrators_id, status, date_received, date_modified, description) values (:receive_purchase_orders_no, :purchase_orders_id, :administrators_id, :status, :date_received, :date_modified, :description)');
      }

      $Qorder->bindTable(':table_receive_purchase_orders', TABLE_RECEIVE_PURCHASE_ORDERS);
      $Qorder->bindValue(':receive_purchase_orders_no', $data['receive_purchase_orders_no']);
      $Qorder->bindInt(':purchase_orders_id', $data['purchase_orders_id']);
      $Qorder->bindInt(':administrators_id', $data['administrators_id']);
      $Qorder->bindValue(':status', $data['status']);
      $Qorder->bindValue(':date_received', $data['date_received']);
      $Qorder->bindRaw(':date_modified', 'now()');
      $Qorder->bindValue(':description', $data['description']);
      $Qorder->setLogging($_SESSION['module'], $id);
      $Qorder->execute();
      
      if ($osC_Database->isError()) {
        $error = true;
      }
      
      if (!$error) {
        if (is_numeric($id)) {
          $receive_purchase_orders_id = $id;
          
          $QdeleteProducts = $osC_Database->query('delete from :table_receive_purchase_orders_products where receive_purchase_orders_id = :receive_purchase_orders_id');
          $QdeleteProducts->bindTable(':table_receive_purchase_orders_products', TABLE_RECEIVE_PURCHASE_ORDERS_PRODUCTS);
          $QdeleteProducts->bindInt(':receive_purchase_orders_id', $receive_purchase_orders_id);
          $QdeleteProducts->execute();
        } else {
          $receive_purchase_orders_id = $osC_Database->nextID();
        }
        
        $products_list = $toC_Json->decode($data['products_list']);
        
        foreach ($products_list as $product) {
          $Qproducts = $osC_Database->query('insert into :table_receive_purchase_orders_products (receive_purchase_orders_id, purchase_orders_products_id, quantity, remarks) values (:receive_purchase_orders_id, :purchase_orders_products_id, :quantity, :remarks)');
          $Qproducts->bindTable(':table_receive_purchase_orders_products', TABLE_RECEIVE_PURCHASE_ORDERS_PRODUCTS);
          $Qproducts->bindInt(':receive_purchase_orders_id', $receive_purchase_orders_id);
          $Qproducts->bindValue(':purchase_orders_products_id', $product->products_id);
          $Qproducts->bindValue(':quantity', $product->new_qty);
          $Qproducts->bindValue(':remarks', $product->remarks);
          $Qproducts->setLogging($_SESSION['module'], $id);
          $Qproducts->execute();
          
          if ($osC_Database->isError()) {
            $error = true;
            break;
          }
        }
        if ($osC_Database->isError()) {
          $error = true;
        }
      }
     
      if ($error === false) {
        $osC_Database->commitTransaction();
      	return true;	       
      }
      
      $osC_Database->rollbackTransaction();
      return false;
    }
  
  	function listAdministrators() {
  		global $osC_Language, $osC_Database;
  		
  		$Qadmins = $osC_Database->query('select id, user_name from :table_administrators order by user_name');
      $Qadmins->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
      $Qadmins->execute();
      
      $data = array(array('id' => '', 'text' => $osC_Language->get('filter_all')));
      while ( $Qadmins->next() ) {
        $data[] = array('id' => $Qadmins->valueInt('id'), 'text' => $Qadmins->value('user_name'));
      }
      $Qadmins->freeResult();
      
			return $data;      
  	}

  	function delete($id) {
  		global $osC_Database;
      
      $osC_Database->startTransaction();
      $error = false;
      
      $Qadjustment = $osC_Database->query('delete from :table_receive_purchase_orders where receive_purchase_orders_id = :receive_purchase_orders_id');
      $Qadjustment->bindTable(':table_receive_purchase_orders', TABLE_RECEIVE_PURCHASE_ORDERS);
      $Qadjustment->bindInt(':receive_purchase_orders_id', $id);
      $Qadjustment->setLogging($_SESSION['module'], $id);      
      $Qadjustment->execute();
      
      if ( $osC_Database->isError() ) {
        $error = true;
      }
      
      if ($error === false) {
        $Qentry = $osC_Database->query('delete from :table_receive_purchase_orders_products where receive_purchase_orders_id  = :receive_purchase_orders_id');
        $Qentry->bindTable(':table_receive_purchase_orders_products', TABLE_RECEIVE_PURCHASE_ORDERS_PRODUCTS);
        $Qentry->bindInt(':receive_purchase_orders_id', $id);
        $Qentry->setLogging($_SESSion['module'], $id);
        $Qentry->execute();
      }
      
      if ( $osC_Database->isError() ) {
        $error = true;
      }
      
      if ($error === false) { 
        $osC_Database->commitTransaction();     
        return true;
      } else {
        $osC_Database->rollbackTransaction();
        return false;
      }
  	}
  }
?>