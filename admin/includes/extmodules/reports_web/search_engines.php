<?php
/*
  $Id: search_engines.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  $date = toC_Piwik::getWebsiteDateCreated();
  $year = substr($date, 0, 4);
  $month = (int)substr($date, 5, 2) - 1;
  $day = (int)substr($date, 8, 2);
?>

Toc.reports_web.SearchEnginesPanel = function(config) {
  config = config || {};

  config.width = 800;
  config.height = 320;
  config.modal = true;
  config.layout = 'column';
  config.border = false;
  
  var today = new Date();
  var start_date = today.add(Date.MONTH, -1).add(Date.DAY, -1);
  var date_created = new Date(<?php echo $year; ?>, <?php echo $month; ?>, <?php echo $day; ?>, 0, 0, 0);
  
  if (start_date < date_created) {
    start_date = date_created;
  }
  
  this.dtStart = new Ext.form.DateField({format: 'Y-m-d', readOnly: true, value: start_date, minValue: date_created, maxValue: today});
  this.dtEnd = new Ext.form.DateField({format: 'Y-m-d', readOnly: true, value: today, minValue: date_created, maxValue: today});
    
  config.items = this.buildPanel();
  config.tbar = [
    '->',
    '<?php echo $osC_Language->get('field_start_date'); ?>',
    this.dtStart,
    '-', 
    '<?php echo $osC_Language->get('field_end_date'); ?>',
    this.dtEnd,
    ' ', 
    {
      iconCls: 'search',
      handler: this.onSearch,
      scope: this
    }
  ];
  
  Toc.reports_web.SearchEnginesPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.reports_web.SearchEnginesPanel, Ext.Panel, {

  buildPanel: function() {
    this.grdKeywords = new Ext.grid.GridPanel({
      loadMask: true,
      border: true,
      height: 270,
      style: 'margin: 10px;',
      ds: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'reports_web', 
          action: 'get_keywords_data'
        },
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT,
          totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
          id: 'keywords'
         },
         ['keywords', 'visitors']
        ),
        autoLoad: false
      }),
      cm: new Ext.grid.ColumnModel([
        {id: 'reports_web_keywords',header: '<?php echo $osC_Language->get('table_heading_keywords'); ?>',dataIndex: 'keywords'},
        {header: '', dataIndex: 'visitors', align: 'center', width: 60}
      ]),
      autoExpandColumn: 'reports_web_keywords'
    });  
    
    this.grdSearchEngines = new Ext.grid.GridPanel({
      loadMask: true,
      border: true,
      height: 270,
      style: 'margin: 10px;',
      ds: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'reports_web', 
          action: 'get_search_engines_data'
        },
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT,
          totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
          id: 'search_engines'
         },
         ['search_engines', 'visitors']
        ),
        autoLoad: false
      }),
      cm: new Ext.grid.ColumnModel([
        {id: 'reports_web_search_engines',header: '<?php echo $osC_Language->get('table_heading_search_engines'); ?>',dataIndex: 'search_engines'},
        {header: '', dataIndex: 'visitors', align: 'center', width: 60}
      ]),
      autoExpandColumn: 'reports_web_search_engines'
    });          
      
    return [
      {
        columnWidth: .49,
        border: false,
        autoHeight: true,
        items: [
          this.grdKeywords
        ]
      },      
      {
        columnWidth: .49,
        border: false,
        autoHeight: true,
        items: [
          this.grdSearchEngines
        ]
      }
    ];
  },
  
  onSearch: function() {
    var start_date = this.dtStart.getValue().format('Y-m-d');
    var end_date = this.dtEnd.getValue().format('Y-m-d');
      
    this.grdKeywords.store.baseParams['start_date'] = start_date;
    this.grdKeywords.store.baseParams['end_date'] = end_date;
    this.grdKeywords.store.reload();
    
    this.grdSearchEngines.store.baseParams['start_date'] = start_date;
    this.grdSearchEngines.store.baseParams['end_date'] = end_date;
    this.grdSearchEngines.store.reload();    
  }
});