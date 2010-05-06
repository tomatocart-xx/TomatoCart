<?php
/*
  $Id: reviews_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  
?>
Toc.reviews.ReviewsGrid = function (config) {
  config = config || {};
  
  config.border = false;
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'reviews',
      action: 'list_reviews'
    },
    autoLoad: true,
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
      id: 'reviews_id'
    }, [
      'reviews_id',
      'date_added',
      'reviews_rating',
      'products_name',
      'code'
    ])
  });
  
  var rowActions = new Ext.ux.grid.RowActions({
    header: '<?php echo $osC_Language->get("table_heading_action"); ?>',
    actions: [
      {iconCls: 'icon-edit-record', qtip: TocLanguage.tipEdit}, 
      {iconCls: 'icon-delete-record', qtip: TocLanguage.tipDelete}
    ],
    widthIntercept: Ext.isSafari ? 4: 2
  });
  rowActions.on('action', this.onRowAction, this);
  config.plugins = rowActions;
  
  config.sm = new Ext.grid.CheckboxSelectionModel();
  config.cm = new Ext.grid.ColumnModel([
    config.sm,
    {
      id: 'reviews_products_name',
      header: '<?php echo $osC_Language->get("table_heading_products"); ?>',
      dataIndex: 'products_name'
    }, 
    {
      header: '<?php echo $osC_Language->get("table_heading_language"); ?>',
      dataIndex: 'code',
      align: 'center'
    },  
    {
      header: '<?php echo $osC_Language->get("table_heading_rating"); ?>',
      dataIndex: 'reviews_rating',
      align: 'center'
    },  
    {
      header: '<?php echo $osC_Language->get("table_heading_date_added"); ?>',
      dataIndex: 'date_added',
      align: 'center'
    }, 
    rowActions
  ]);
  config.autoExpandColumn = 'reviews_products_name';
  
  config.tbar = [
    {
      text: TocLanguage.btnDelete,
      iconCls: 'remove',
      handler: this.onBatchDelete,
      scope: this
    }, 
    '-', 
    {
      text: TocLanguage.btnRefresh,
      iconCls: 'refresh',
      handler: this.onRefresh,
      scope: this
    }
  ];
  
  var thisObj = this;
  config.bbar = new Ext.PageToolbar({
    pageSize: Toc.CONF.GRID_PAGE_SIZE,
    store: config.ds,
    steps: Toc.CONF.GRID_STEPS,
    btnsConfig:[
      {
        text: TocLanguage.btnDelete,
        iconCls:'remove',
        handler: function() {
          thisObj.onBatchDelete();
        }
      }
    ],
    beforePageText: TocLanguage.beforePageText,
    firstText: TocLanguage.firstText,
    lastText: TocLanguage.lastText,
    nextText: TocLanguage.nextText,
    prevText: TocLanguage.prevText,
    afterPageText: TocLanguage.afterPageText,
    refreshText: TocLanguage.refreshText,
    displayInfo: true,
    displayMsg: TocLanguage.displayMsg,
    emptyMsg: TocLanguage.emptyMsg,
    prevStepText: TocLanguage.prevStepText,
    nextStepText: TocLanguage.nextStepText
  });
  
  Toc.reviews.ReviewsGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.reviews.ReviewsGrid, Ext.grid.GridPanel, {
  onEdit: function (record) {
    var reviewsId = record.get('reviews_id');
    var dlg = this.owner.createReviewsEditDialog();
    dlg.setTitle(record.get('products_name'));
    
    dlg.on('saveSuccess', function() {
      this.onRefresh();
    }, this);
    
    dlg.show(reviewsId);
  },
  
  onDelete: function (record) {
    var reviewsId = record.get('reviews_id');
    
    Ext.MessageBox.confirm(
      TocLanguage.msgWarningTitle, 
      TocLanguage.msgDeleteConfirm, 
      function (btn) {
        if (btn == 'yes') {
          Ext.Ajax.request({
            waitMsg: TocLanguage.formSubmitWaitMsg,
            url: Toc.CONF.CONN_URL,
            params: {
              module: 'reviews',
              action: 'delete_review',
              reviews_id: reviewsId
            },
            callback: function (options, success, response) {
              var result = Ext.decode(response.responseText);
              
              if (result.success == true) {
                this.owner.app.showNotification({
                  title: TocLanguage.msgSuccessTitle,
                  html: result.feedback
                });
                
                this.getStore().reload();
              } else {
                Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
              }
            },
            scope: this
          });
        }
      }, 
      this
    );
  },
  
  onBatchDelete: function () {
    var keys = this.getSelectionModel().selections.keys;
    
    if (keys.length > 0) {
      batch = keys.join(',');
      
      Ext.MessageBox.confirm(
        TocLanguage.msgWarningTitle, 
        TocLanguage.msgDeleteConfirm, 
        function (btn) {
          if (btn == 'yes') {
            Ext.Ajax.request({
              waitMsg: TocLanguage.formSubmitWaitMsg,
              url: Toc.CONF.CONN_URL,
              params: {
                module: 'reviews',
                action: 'delete_reviews',
                batch: batch
              },
              callback: function (options, success, response) {
                var result = Ext.decode(response.responseText);
                
                if (result.success == true) {
                  this.owner.app.showNotification({
                    title: TocLanguage.msgSuccessTitle,
                    html: result.feedback
                  });
                  
                  this.getStore().reload();
                } else {
                  Ext.MessageBox.alert(TocLanguage.msgErrTitle, result.feedback);
                }
              },
              scope: this
            });
          }
        }, 
        this
      );
    } else {
      Ext.MessageBox.alert(TocLanguage.msgInfoTitle, TocLanguage.msgMustSelectOne);
    }
  },
  
  onRefresh: function () {
    this.getStore().reload();
  },
  
  onRowAction: function (grid, record, action, row, col) {
    switch (action) {
      case 'icon-delete-record':
        this.onDelete(record);
        break;
      case 'icon-edit-record':
        this.onEdit(record);
        break;
    }
  }
});