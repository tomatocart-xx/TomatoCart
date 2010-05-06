<?php
/*
  $Id: home_meta.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Access_Homepage_meta_info extends osC_Access {
    var $_module = 'homepage_meta_info',
        $_group = 'configuration',
        $_icon = 'articles.png',
        $_title,
        $_sort_order = 300;

    function osC_Access_Homepage_meta_info() {
      global $osC_Language;

      $this->_title = $osC_Language->get('access_home_meta_title');
    }
  }
?>