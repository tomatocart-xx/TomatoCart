<?php
/*
  $Id: products_viewed_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

  Toc.reports_products.ProductsViewedGrid = function(config) {
    
    config = config || {};
    
    config.border = false;
    config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
    
    config.ds = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'reports_products',
        action: 'list_products_viewed'
      },
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY
      }, [
        'products_id',
        'products_name',
        'products_viewed',
        'language'
      ]),
      autoLoad: true
    });
    
    config.cm = new Ext.grid.ColumnModel([
      {id: 'products_name', header: '<?php echo $osC_Language->get('table_heading_products'); ?>',dataIndex: 'products_name'},
      {header: '<?php echo $osC_Language->get('table_heading_language'); ?>',dataIndex: 'language', align: 'center'},
      {header: '<?php echo $osC_Language->get('table_heading_total'); ?>',dataIndex: 'products_viewed', sortable: true, width: 150, align: 'right'}
    ]);
    config.autoExpandColumn = 'products_name';
    
    dsCategories = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'reports_products',
        action: 'get_categories'
      },
      autoLoad: true,
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY
      }, [
        'id',
        'text'
      ])
    });
    
    config.cboCategories = new Toc.CategoriesComboBox({
      store: dsCategories,
      valueField: 'id',
      displayField: 'text',
      readOnly: true,
      mode: 'local',
      emptyText: '<?php echo $osC_Language->get("top_category"); ?>',
      triggerAction: 'all',
      listeners: {
        select: this.onSearch,
        scope: this
      }
    });
    
    dsLanguages = new Ext.data.Store({
      url: Toc.CONF.CONN_URL,
      baseParams: {
        module: 'reports_products',
        action: 'get_languages'
      },
      autoLoad: true,
      reader: new Ext.data.JsonReader({
        root: Toc.CONF.JSON_READER_ROOT,
        totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY
      }, [
        'id',
        'text'
      ])
    });
    
    config.cboLanguages = new Ext.form.ComboBox({
      store: dsLanguages,
      valueField: 'id',
      displayField: 'text',
      readOnly: true,
      emptyText: '<?php echo $osC_Language->get("none"); ?>',
      triggerAction: 'all',
      listeners: {
        select: this.onSearch,
        scope: this
      }
    });
    
    config.tbar = [
      { 
        text: TocLanguage.btnRefresh,
        iconCls: 'refresh',
        handler: this.onRefresh,
        scope: this
      },
      '->',
      config.cboCategories,
      ' ',
      config.cboLanguages,
      ' ',
      { 
        text: '',
        iconCls: 'search',
        handler: this.onSearch,
        scope: this
      }
    ];
    
    config.bbar = new Ext.PageToolbar({
      pageSize: Toc.CONF.GRID_PAGE_SIZE,
      store: config.ds,
      steps: Toc.CONF.GRID_STEPS,
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
    
    Toc.reports_products.ProductsViewedGrid.superclass.constructor.call(this, config);
  };
  
  Ext.extend(Toc.reports_products.ProductsViewedGrid, Ext.grid.GridPanel, {
    onRefresh: function() {
      this.getStore().reload();
    },
    
    onSearch: function() {
      var categoriesId = this.cboCategories.getValue() || null;
      var languagesId = this.cboLanguages.getValue() || null;
      var store = this.getStore();
      
      store.baseParams['categories_id'] = categoriesId;
      store.baseParams['language_id'] = languagesId;
      store.reload();
    }
  });