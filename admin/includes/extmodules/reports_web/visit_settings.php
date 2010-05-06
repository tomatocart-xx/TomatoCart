<?php
/*
  $Id: visit_settings.php $
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

Toc.reports_web.VisitSettingsPanel = function(config) {
  config = config || {};

  config.width = 800;
  config.height = 460;
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
  
  Toc.reports_web.VisitSettingsPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.reports_web.VisitSettingsPanel, Ext.Panel, {

  buildPanel: function() {
    this.grdBrowsers = new Ext.grid.GridPanel({
      loadMask: true,
      border: true,
      height: 200,
      style: 'margin: 10px;',
      ds: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        remoteSort: true,
        baseParams: {
          module: 'reports_web', 
          action: 'get_browsers_data' 
        },
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT,
          totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
          id: 'browser'
         },
         ['browser','visitors']
        ),
        autoLoad: false
      }),
      cm: new Ext.grid.ColumnModel([
        {id: 'visit_settings_browser',header: '<?php echo $osC_Language->get('table_heading_browsers'); ?>',dataIndex: 'browser'},
        {header: '',dataIndex: 'visitors',align: 'center',width: 60}
      ]),
      autoExpandColumn: 'visit_settings_browser'
    });    
    
    this.grdConfigurations = new Ext.grid.GridPanel({
      loadMask: true,
      border: true,
      height: 200,
      style: 'margin: 10px;',
      ds: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'reports_web', 
          action: 'get_configurations_data' 
        },
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT,
          totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
          id: 'configurations'
         },
         ['configurations','visitors']
        ),
        autoLoad: false
      }),
      cm: new Ext.grid.ColumnModel([
        {id: 'reports_web_configuration',header: '<?php echo $osC_Language->get('table_heading_configurations'); ?>',dataIndex: 'configurations'},
        {header: '',dataIndex: 'visitors',align: 'center',width: 60}
      ]),
      autoExpandColumn: 'reports_web_configuration'
    }); 
    
    this.grdResolutions = new Ext.grid.GridPanel({
      loadMask: true,
      border: true,
      height: 200,
      style: 'margin: 10px;',
      ds: new Ext.data.Store({
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'reports_web', 
          action: 'get_resolution_data' 
        },
        reader: new Ext.data.JsonReader({
          root: Toc.CONF.JSON_READER_ROOT,
          totalProperty: Toc.CONF.JSON_READER_TOTAL_PROPERTY,
          id: 'resolutions'
         },
         ['resolutions','visitors']
        ),
        autoLoad: false
      }),
      cm: new Ext.grid.ColumnModel([
        {id: 'reports_web_resolutions',header: '<?php echo $osC_Language->get('table_heading_resolutions'); ?>',dataIndex: 'resolutions'},
        {header: '',dataIndex: 'visitors',align: 'center',width: 60}
      ]),
      autoExpandColumn: 'reports_web_resolutions'
    });         
      
    return [
      {
        columnWidth: .49,
        border: false,
        items: [
          this.pnlPieFlash = new Ext.Panel({
            title: '<?php echo $osC_Language->get('flash_chart_heading_browser_families'); ?>',
            border: true,
            height: 200,
            style: 'margin: 10px;',
            swf: 'external/open-flash-chart/open-flash-chart.swf',
            flashvars: {},
            plugins: new Ext.ux.FlashPlugin()
          }),
          this.grdConfigurations
        ]
      },      
      {
        columnWidth: .49,
        border: false,
        items: [
          this.grdBrowsers,
          this.grdResolutions
        ]
      }
    ];
  },
  
  onSearch: function() {
    if (this.dtStart.getValue() > this.dtEnd.getValue()) {
      alert('<?php echo $osC_Language->get('ms_error_end_date_smaller_than_start_date'); ?>');
      return;
    }
    
    var start_date = this.dtStart.getValue().format('Y-m-d');
    var end_date = this.dtEnd.getValue().format('Y-m-d');
    
    this.grdBrowsers.store.baseParams['start_date'] = start_date;
    this.grdBrowsers.store.baseParams['end_date'] = end_date;
    this.grdBrowsers.store.reload();
    
    this.grdConfigurations.store.baseParams['start_date'] = start_date;
    this.grdConfigurations.store.baseParams['end_date'] = end_date;
    this.grdConfigurations.store.reload();
      
    this.grdResolutions.store.baseParams['start_date'] = start_date;
    this.grdResolutions.store.baseParams['end_date'] = end_date;
    this.grdResolutions.store.reload();
    
    this.pnlPieFlash.flashvars.data = Toc.CONF.CONN_URL + '?module=reports_web&action=render_browser_families_pie_chart_data&start_date=' + start_date + '&end_date=' + end_date;     
    this.pnlPieFlash.renderFlash();   
  }
});