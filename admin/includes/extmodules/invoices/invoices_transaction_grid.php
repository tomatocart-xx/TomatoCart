<?php
/*
  $Id: invoices_transaction_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.invoices.InvoicesTransactionGrid = function(config) {

  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('section_transaction_history'); ?>';
  config.viewConfig = {emptyText: TocLanguage.gridNoRecords};
  
  config.ds = new Ext.data.Store({
    url: Toc.CONF.CONN_URL,
    baseParams: {
      module: 'invoices',
      action: 'get_transaction_history',
      orders_id: config.ordersId     
    },
    reader: new Ext.data.JsonReader({
      root: Toc.CONF.JSON_READER_ROOT,
      id: 'date'
    },[
      'date',
      'status',
      'comments'
    ]),
    autoLoad: true
  });
  
  config.cm = new Ext.grid.ColumnModel([
    {header: '<?php echo $osC_Language->get('table_heading_date_added');?>', dataIndex: 'date', width: 140, align: 'center'},
    {header: '<?php echo $osC_Language->get('table_heading_status');?>', dataIndex: 'status', width: 120, align: 'center'},
    {id: 'invoices-transaction-comments', header: '<?php echo $osC_Language->get('table_heading_comments');?>', dataIndex: 'comments'}
  ]);
  config.autoExpandColumn = 'invoices-transaction-comments';
   
  Toc.invoices.InvoicesTransactionGrid.superclass.constructor.call(this, config);
};

Ext.extend(Toc.invoices.InvoicesTransactionGrid, Ext.grid.GridPanel);