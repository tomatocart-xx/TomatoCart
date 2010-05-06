<?php
/*
  $Id: json.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  header('Cache-Control: no-cache, must-revalidate');
  header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

  require('includes/application_top.php');
  require('includes/ext_config.php');
  require('includes/classes/json.php');
  require('chart.php');  
//  header('Content-Type: application/json, charset=utf-8');
  
  $dir_fs_www_root = dirname(__FILE__);
  
  $toC_Json = new toC_Json();
  $toC_Json->parse();
?>