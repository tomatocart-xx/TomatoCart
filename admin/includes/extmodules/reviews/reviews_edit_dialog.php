<?php
/*
  $Id: reviews_edit_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.reviews.ReviewsEditDialog = function (config) {
  config = config || {};
  
  config.id = 'reviews-dialog-win';
  config.title = '<?php echo $osC_Language->get("action_heading_new_special"); ?>';
  config.layout = 'fit';
  config.width = 525;
  config.autoHeight = true;
  config.modal = true;
  config.iconCls = 'icon-reviews-win';
  config.items = this.buildForm();
  
  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: function () {
        this.submitForm();
        this.disable();
      }, 
      scope: this
    }, 
    {
      text: TocLanguage.btnClose,
      handler: function () {
        this.close();
      },
      scope: this
    }
  ];
  
  this.addEvents({'saveSuccess': true});
  
  Toc.reviews.ReviewsEditDialog.superclass.constructor.call(this, config);
}
Ext.extend(Toc.reviews.ReviewsEditDialog, Ext.Window, {
  show: function (id) {
    var reviewsId = id || null;
    
    this.frmReviews.form.reset();
    this.frmReviews.form.baseParams['reviews_id'] = reviewsId;
    
    if (reviewsId > 0) {
      this.frmReviews.load({
        url: Toc.CONF.CONN_URL,
        params: {
          action: 'load_reviews'
        },
        success: function (form, action) {
          Toc.reviews.ReviewsEditDialog.superclass.show.call(this);
        },
        failure: function (form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, action.result.feedback);
        },
        scope: this
      });
    } else {
      Toc.reviews.ReviewsEditDialog.superclass.show.call(this);
    }
  },
  
  buildForm: function () {
    this.frmReviews = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'reviews',
        action: 'save_reviews'
      },
      labelWidth: 100,
      autoHeight: true,
      defaults: { 
        anchor: '97%' 
      },
      layoutConfig: { 
        labelSeparator: '' 
      },
      items: [
        {xtype: 'statictextfield', fieldLabel: '<?php echo $osC_Language->get("field_product"); ?>', name: 'products_name'},
        {xtype: 'statictextfield', fieldLabel: '<?php echo $osC_Language->get("field_author"); ?>', name: 'customers_name'},
        {xtype: 'statictextfield', fieldLabel: '<?php echo $osC_Language->get("field_date_added"); ?>', name: 'date_added'},
        {xtype: 'textarea', fieldLabel: '<?php echo $osC_Language->get("field_review"); ?>', name: 'reviews_text', height: 150, allowBlank: false}, 
        {
          xtype: 'panel',
          layout: 'table',
          defaultType: 'radio', 
          border: false,
          style: 'padding-left: 110px',
          items: [
            {xtype: 'label', text: '<?php echo $osC_Language->get("rating_bad"); ?>', style: 'padding-right: 10px'}, 
            {boxLabel: ' ', name: 'reviews_rating', inputValue: '1'},
            {boxLabel: ' ', name: 'reviews_rating', inputValue: '2'},
            {boxLabel: ' ', name: 'reviews_rating', inputValue: '3'},
            {boxLabel: ' ', name: 'reviews_rating', inputValue: '4'},
            {boxLabel: ' ', name: 'reviews_rating', inputValue: '5'},
            {xtype: 'label', text: '<?php echo $osC_Language->get("rating_good"); ?>', style: 'padding-left: 5px'}
          ]
        }
      ]
    });
    
    return this.frmReviews;
  },
  
  submitForm: function () {
    this.frmReviews.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function (form, action) {
        this.fireEvent('saveSuccess', action.result.feedback);
        this.close();
      },
      failure: function (form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },
      scope: this
    });
  }
});