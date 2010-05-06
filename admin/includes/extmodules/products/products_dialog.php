<?php
/*
  $Id: products_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.products.ProductDialog = function(config) {
  config = config || {};
  
  config.id = 'products-dialog-win';
  config.title = 'New Product';
  config.layout = 'fit';
  config.width = 790;
  config.height = 520;
  config.modal = true;
  config.iconCls = 'icon-products-win';
  config.productsId = config.products_id || null;
  config.items = this.buildForm(config.productsId);
  
  config.buttons = [
    {
      text:'Submit',
      handler: function(){
        this.submitForm();
      },
      scope:this
    },
    {
      text: 'Close',
      handler: function(){
        this.close();
      },
      scope:this
    }
  ];
    
  this.addEvents({'saveSuccess': true});      

  Toc.products.ProductDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.products.ProductDialog, Ext.Window, {
  buildForm: function(productsId) {
    this.pnlData = new Toc.products.DataPanel();
    this.pnlVariants = new Toc.products.VariantsPanel({productsId: productsId, pnlData: this.pnlData}); 
    this.pnlXsellProducts = new Toc.products.XsellProductsGrid({productsId: productsId});
    this.pnlAttributes = new Toc.products.AttributesPanel({productsId: productsId});
    
    this.pnlVariants.on('variantschange', this.pnlData.onVariantsChange, this.pnlData);
    
    tabProduct = new Ext.TabPanel({
      activeTab: 0,
      defaults:{
        hideMode:'offsets'
      },
      deferredRender: false,
      items: [
        new Toc.products.GeneralPanel(), 
        new Toc.products.MetaPanel(),
        this.pnlData,
        this.pnlCategories = new Toc.products.CategoriesPanel({productsId: productsId}),
        new Toc.products.ImagesPanel({productsId: productsId}), 
        this.pnlVariants, 
        this.pnlAttributes, 
        this.pnlXsellProducts
      ]
    }); 

    this.frmProduct = new Ext.form.FormPanel({
      layout: 'fit',
      fileUpload: true,
      url: Toc.CONF.CONN_URL,
      labelWidth: 120,
      baseParams: {  
        module: 'products',
        action: 'save_product'
      },
      items: tabProduct
    });

    return this.frmProduct;
  },
    
  show: function() {
    this.frmProduct.form.reset();  

    if (this.productsId > 0) {
      this.frmProduct.load({
        url: Toc.CONF.CONN_URL,
        params:{
          action: 'load_product',
          products_id: this.productsId
        },
        success: function(form, action) {
          this.pnlData.onPriceNetChange(); 
          this.pnlData.loadExtraOptionTab(action.result.data);   
          this.pnlCategories.setCategories(action.result.data);
          this.pnlAttributes.setAttributesGroupsId(action.result.data.products_attributes_groups_id);
          
          Toc.products.ProductDialog.superclass.show.call(this);
        },
        failure: function(form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        },
        scope: this       
      });
    } else {   
      Toc.products.ProductDialog.superclass.show.call(this);
    }
  },

  submitForm: function() {
    var params = {
      action: 'save_product',
      xsell_ids: this.pnlXsellProducts.getXsellProductIds(),
      products_variants: this.pnlVariants.getVariants(),
      products_id: this.productsId,
      categories_id: this.pnlCategories.getCategories()
    };
    
    if (this.productsId > 0) {
      params.products_type = this.pnlData.getProductsType();
    }

    this.frmProduct.form.submit({
      params: params,
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success:function(form, action){
        this.fireEvent('saveSuccess', action.result.feedback);
        this.close();  
      },    
      failure: function(form, action) {
        if(action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },
      scope: this
    });   
  }
});