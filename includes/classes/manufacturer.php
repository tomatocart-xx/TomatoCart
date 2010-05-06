<?php
/*
  $Id: manufacturer.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Manufacturer {
    var $_data = array();

    function osC_Manufacturer($id) {
      global $osC_Database;

      $Qmanufacturer = $osC_Database->query('select manufacturers_id as id, manufacturers_name as name, manufacturers_image as image from :table_manufacturers where manufacturers_id = :manufacturers_id');
      $Qmanufacturer->bindTable(':table_manufacturers', TABLE_MANUFACTURERS);
      $Qmanufacturer->bindInt(':manufacturers_id', $id);
      $Qmanufacturer->execute();

      if ($Qmanufacturer->numberOfRows() === 1) {
        $this->_data = $Qmanufacturer->toArray();
      }
    }

    function getID() {
      if (isset($this->_data['id'])) {
        return $this->_data['id'];
      }

      return false;
    }

    function getTitle() {
      if (isset($this->_data['name'])) {
        return $this->_data['name'];
      }

      return false;
    }

    function getImage() {
      if (isset($this->_data['image'])) {
        return $this->_data['image'];
      }

      return false;
    }
  }
?>
