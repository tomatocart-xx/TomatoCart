<?php
/*
  $Id: piwik.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/


  class osC_Services_piwik {
    function start() {
      global $osC_Database, $osC_Language, $toC_Piwik;

      include('includes/classes/piwik.php');
      $toC_Piwik = new toC_Piwik();

      return true;
    }

    function stop() {
      return true;
    }
  }
?>
