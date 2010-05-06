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
  include('templates/default/extensions/uploadpanel/all.js');  

  echo 'Ext.namespace("Toc.products");';
  
  include('products_grid.php');
  include('general_panel.php');
  include('meta_panel.php');
  include('data_panel.php');
  include('downloadables_panel.php');
  include('gift_certificates_panel.php');
  include('categories_panel.php');
  include('images_grid.php');
  include('images_panel.php');
  include('variants_panel.php');
  include('xsell_products_panel.php');  
  include('attributes_panel.php');
  include('products_dialog.php');
?>

Ext.override(TocDesktop.ProductsWindow, {

  createWindow : function(){
    if(this.id == 'products-dialog-win') {
      win = this.createProductDialog();
    } else {
      win = this.createProductsWindow();
    }    
    
    win.show();
  },


  createProductsWindow: function(productId) {
    var desktop = this.app.getDesktop();
    win = desktop.getWindow('products-win');

    if(!win){
      grdProducts = new Toc.products.ProductsGrid({owner: this});

      win = desktop.createWindow({
        id: 'products-win',
        title:'<?php echo $osC_Language->get('heading_title'); ?>',
        width:800,
        height:400,
        iconCls: 'icon-products-win',
        layout: 'fit',
        items: grdProducts
      });
    }

    return win;
  },

  createProductDialog: function(productId) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('products-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({products_id: productId}, Toc.products.ProductDialog);
    }
        
    return dlg;
  },
  
  createCategoryMoveDialog: function(productId) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('products-move-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({}, Toc.products.CategoriesMoveDialog);
      
      dlg.on('saveSuccess', function (feedback) {
        this.app.showNotification({
          title: TocLanguage.msgSuccessTitle,
          html: feedback
        });
      }, this);
    }
    
    return dlg;
  }
});
