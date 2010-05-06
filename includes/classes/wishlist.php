<?php
/*
  $Id: wishlist.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

	class toC_Wishlist {
	  var $_contents = array(),
	      $_wishlists_id = null,
	      $_token = null;
	      
	  function toC_Wishlist() {
	    if (!isset($_SESSION['toC_Wishlist_data'])) {
	      $_SESSION['toC_Wishlist_data'] = array('contents' => array(), 
	                                             'wishlists_id' => null, 
	                                             'token' => null);
	    }
	    
	    $this->_contents =& $_SESSION['toC_Wishlist_data']['contents'];
	    $this->_wishlists_id =& $_SESSION['toC_Wishlist_data']['wishlists_id'];
	    $this->_token =& $_SESSION['toC_Wishlist_data']['token'];
	  }
	  
	  function exists($products_id) {
	    return isset($this->_contents[$products_id]);	  
	  }
	  
	  function hasContents() {
	    return !empty($this->_contents);
	  }

	  function hasWishlistID() {
      return !empty($this->_wishlists_id);
    }
    
    function getToken() {
      return $this->_token;
    }
    	  
	  function reset() {
      $this->_wishlists_id = null;
      $this->_token = null;
	    $this->_contents = array();
    }
    
    function generateToken() {
      global $osC_Customer, $osC_Session;
      
      if ($osC_Customer->isLoggedOn()) {
        $token = md5($osC_Customer->getID() . time());
      } else {
        $token = md5($osC_Session->getID() . time());
      }
      
      return $token;
    }
    
	  function deleteGuestWishlist() {
      global $osC_Database;

      $error = false;

      $osC_Database->startTransaction();

      $Qdelete = $osC_Database->query('delete from :table_wishlists_products where wishlists_id = :wishlists_id');
      $Qdelete->bindTable(':table_wishlists_products', TABLE_WISHLISTS_PRODUCTS);
      $Qdelete->bindInt(':wishlists_id', $this->_wishlists_id);
      $Qdelete->execute();

      if (!$osC_Database->isError()) {
        $Qdelete = $osC_Database->query('delete from :table_wishlists where wishlists_id = :wishlists_id');
        $Qdelete->bindTable(':table_wishlists', TABLE_WISHLISTS);
        $Qdelete->bindInt(':wishlists_id', $this->_wishlists_id);
        $Qdelete->execute();

        if ($osC_Database->isError()) {
          $error = true;
        } else {
          $this->_wishlists_id = null;
          $this->_token = null;
        }
      }

      if ($error === false) {
        $osC_Database->commitTransaction();
        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }    
    
	  function synchronizeWithDatabase() {
      global $osC_Database, $osC_Services, $osC_Language, $osC_Customer, $osC_Image;

      if (!$osC_Customer->isLoggedOn()) {
        return false;
      }

      $Qcheck = $osC_Database->query('select wishlists_id, wishlists_token from :table_wishlists where customers_id = :customers_id');
      $Qcheck->bindTable(':table_wishlists', TABLE_WISHLISTS);
      $Qcheck->bindInt(':customers_id', $osC_Customer->getID());
      $Qcheck->execute();
      
	    if ($Qcheck->numberOfRows() > 0) {
        //remove anonymous wishlist from database
        $this->deleteGuestWishlist();
        
        $this->_wishlists_id = $Qcheck->valueInt('wishlists_id');
        $this->_token = $Qcheck->value('wishlists_token');
        
  	    // insert current wishlist in database
        if ($this->hasContents()) {
          foreach ($this->_contents as $products_id => $data) {
            $Qproduct = $osC_Database->query('select products_id from :table_wishlists_products where wishlists_id = :wishlists_id and products_id = :products_id');
            $Qproduct->bindTable(':table_wishlists_products', TABLE_WISHLISTS_PRODUCTS);
            $Qproduct->bindInt(':wishlists_id', $this->_wishlists_id);
            $Qproduct->bindInt(':products_id', $products_id);
            $Qproduct->execute();
  
            if (!($Qproduct->numberOfRows() > 0)) {
              $Qnew = $osC_Database->query('insert into :table_wishlists_products (wishlists_id, products_id, date_added, comments) values (:wishlists_id, :products_id, now(),:comments)');
              $Qnew->bindTable(':table_wishlists_products', TABLE_WISHLISTS_PRODUCTS);
              $Qnew->bindInt(':wishlists_id', $this->_wishlists_id);
              $Qnew->bindInt(':products_id', $products_id);
              $Qnew->bindValue(':comments', $data['comments']);
              $Qnew->execute();
            }
          }
        }
        
  	    // reset per-session cart contents, but not the database contents
        $this->_contents = array();
  
        $Qproducts = $osC_Database->query('select products_id, date_added, comments from :table_wishlist_products where wishlists_id = :wishlists_id');
        $Qproducts->bindTable(':table_wishlist_products', TABLE_WISHLISTS_PRODUCTS);
        $Qproducts->bindInt(':wishlists_id', $this->_wishlists_id);
        $Qproducts->execute();      
        
        while ($Qproducts->next()) {
          $osC_Product = new osC_Product($Qproducts->value('products_id'));
          $price = $osC_Product->getPrice();
  
          if ($osC_Services->isStarted('specials')) {
            global $osC_Specials;
  
            if ($new_price = $osC_Specials->getPrice(osc_get_product_id($Qproducts->value('products_id')))) {
              $price = $new_price;
            }
          }
  
          $this->_contents[$Qproducts->value('products_id')] = array('products_id' => $Qproducts->value('products_id'),
                                                                     'name' => $osC_Product->getTitle(),
                                                                     'image' => $osC_Product->getImage(),
                                                                     'price' => $price,
                                                                     'date_added' => osC_DateTime::getShort($Qproducts->value('date_added')),
                                                                     'comments' => $Qproducts->value('comments'));
        }
        
      } else {
        $token = $this->generateToken();
        
        $Qupdate = $osC_Database->query('update :table_wishlists set customers_id = :customers_id, wishlists_token = :wishlists_token where wishlists_id = :wishlists_id');
        $Qupdate->bindTable(':table_wishlists', TABLE_WISHLISTS);
        $Qupdate->bindInt(':customers_id', $osC_Customer->getID());
        $Qupdate->bindValue(':wishlists_token', $token);
        $Qupdate->bindInt(':wishlists_id', $this->_wishlists_id);
        $Qupdate->execute();
        
        $this->_token = $token;
      }
    }
	
    function add($products_id) {
      global $osC_Database, $osC_Services, $osC_Customer;
      
//if wishlist empty, create a new wishlist
      if (!$this->hasWishlistID()) {
        $token = $this->generateToken();
        
        $Qnew = $osC_Database->query('insert into :table_wishlists (customers_id, wishlists_token) values (:customers_id, :wishlists_token)');
        $Qnew->bindTable(':table_wishlists', TABLE_WISHLISTS);
        
        if (!$osC_Customer->isLoggedOn()) {
          $Qnew->bindRaw(':customers_id', 'null');
        } else {
          $Qnew->bindInt(':customers_id', $osC_Customer->getID());
        }      
        
        $Qnew->bindValue(':wishlists_token', $token);
        $Qnew->execute();
        
        $this->_wishlists_id = $osC_Database->nextID();
        $this->_token = $token;
      }
      
      $osC_Product = new osC_Product($products_id);

      if ($osC_Product->getID() > 0) {
        if (!$this->exists($products_id)) {
          $price = $osC_Product->getPrice();
          if ($osC_Services->isStarted('specials')) {
            global $osC_Specials;

            if ($new_price = $osC_Specials->getPrice($products_id)) {
              $price = $new_price;
            }
          }

          $this->_contents[$products_id]= array('products_id' => $products_id,
                                                'name' => $osC_Product->getTitle(),
                                                'image' => $osC_Product->getImage(),
                                                'price' => $price, 
                                                'date_added' => osC_DateTime::getShort(osC_DateTime::getNow()),
                                                'comments' => '');

          $Qnew = $osC_Database->query('insert into :table_wishlist_products (wishlists_id, products_id, date_added, comments) values (:wishlists_id, :products_id, now(), :comments)');
          $Qnew->bindTable(':table_wishlist_products', TABLE_WISHLISTS_PRODUCTS);
          $Qnew->bindInt(':wishlists_id', $this->_wishlists_id);
          $Qnew->bindInt(':products_id', $products_id);
          $Qnew->bindValue(':comments', '');
          $Qnew->execute();
        }
      }
    }
    
    function getProducts() {
      global $osC_Customer;
      
      $products = array();
      
      if ($this->hasContents()) {
        foreach ($this->_contents as $products_id => $data) {
          $products[] = $data;
        }

        return $products;        
      }
           
      return false;      
    }    
	  
    function updateWishlist($comments) {
      global $osC_Database, $osC_Customer;
      
      $error = false;
      
      foreach($comments as $products_id => $comment) {
        $this->_contents[$products_id]['comments'] = $comment;
        
        $Qupdate = $osC_Database->query('update :table_wishlist_products set comments = :comments where wishlists_id = :wishlists_id and products_id = :products_id');
        $Qupdate->bindTable(':table_wishlist_products', TABLE_WISHLISTS_PRODUCTS);
        $Qupdate->bindValue(':comments', $comment);
        $Qupdate->bindInt(':wishlists_id', $this->_wishlists_id);
        $Qupdate->bindInt(':products_id', $products_id);
        $Qupdate->execute();
        
        if ($osC_Database->isError()) {       
          $error = true;
          break;      
        }
      }
      
      if ($error === false) {
        return true;
      }
      
      return false;
    }
    
    function hasProduct($products_id) {
      if (isset($this->_contents[$products_id])) {
        return true;
      }
      
      return false;
    }
    
    function deleteProduct($products_id) {
      global $osC_Customer, $osC_Database;
      
      $Qdelete = $osC_Database->query('delete from :table_wishlist_products where products_id = :products_id and wishlists_id = :wishlists_id');
      $Qdelete->bindTable(':table_wishlist_products', TABLE_WISHLISTS_PRODUCTS);
      $Qdelete->bindInt(':products_id', $products_id);
      $Qdelete->bindInt(':wishlists_id', $this->_wishlists_id);
      $Qdelete->execute();
        
      if (!$osC_Database->isError()) {
        
                  
        if (isset($this->_contents[$products_id])) {
          unset($this->_contents[$products_id]);
        }
        
        if ((!$this->hasContents()) && (!$osC_Customer->isLoggedOn())) {
          $Qdelete = $osC_Database->query('delete from :table_wishlist where wishlists_id = :wishlists_id');
          $Qdelete->bindTable(':table_wishlist', TABLE_WISHLISTS);
          $Qdelete->bindInt(':wishlists_id', $this->_wishlists_id);
          $Qdelete->execute();
          
          if ($osC_Database->isError()) {
            return false;
          }
          
          $this->_wishlists_id = null;
          $this->_token = null;
        }
        
        return true;
      }
      
      return false;
    }
  }
?>