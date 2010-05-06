<?php
/*
  $Id: main.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  echo 'Ext.namespace("Toc.homepage_meta_info");';
  include('home_meta_info_dialog.php');
?>

Ext.override(TocDesktop.HomepageMetaInfoWindow, {
  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('homepage_meta_info-win');
    
    if (!win) {
      win = desktop.createWindow({owner:this}, Toc.homepage_meta_info.HomepageMetaInfoDialog);
    }
    
    win.show();
  }
});
