<?php
/*
  $Id: visits_summary.php $
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

Toc.reports_web.VisitsSummaryPanel = function(config) {
  config = config || {};
  
  config.width = 800;
  config.height = 460;
  config.modal = true;
  config.layout = 'border';
  config.border = false;
  
  var today = new Date();
  var start_date = today.add(Date.MONTH, -1).add(Date.DAY, -1);
  var date_created = new Date(<?php echo $year; ?>, <?php echo $month; ?>, <?php echo $day; ?>, 0, 0, 0);
  
  if (start_date < date_created) {
    start_date = date_created;
  }
  
  this.dtStart = new Ext.form.DateField({format: 'Y-m-d', readOnly: true, value: start_date, minValue: date_created, maxValue: today});
  this.dtEnd = new Ext.form.DateField({format: 'Y-m-d', readOnly: true, value: today, minValue: date_created, maxValue: today});
  this.cboPeriod = new Ext.form.ComboBox({
    store: new Ext.data.SimpleStore({
      fields: ['id', 'text'],
      data: 
        [
          ['day','<?php echo $osC_Language->get('period_day'); ?>'],
          ['week','<?php echo $osC_Language->get('period_week'); ?>'],
          ['month','<?php echo $osC_Language->get('period_month'); ?>'],
          ['year','<?php echo $osC_Language->get('period_year'); ?>']
        ]
    }),
    valueField: 'id',
    displayField: 'text',
    triggerAction: 'all',
    mode: 'local',
    value: 'day',
    width: 100,
    readOnly: true
  });
  
  config.items = [this.getFlashPanel(), this.getSouthPanel()];
  config.tbar = [
    '->',
    '<?php echo $osC_Language->get('field_start_date'); ?>',
    this.dtStart,
    '-', 
    '<?php echo $osC_Language->get('field_end_date'); ?>',
    this.dtEnd,
    '-', 
    '<?php echo $osC_Language->get('field_period'); ?>',
    this.cboPeriod,
    ' ', 
    {
      iconCls: 'search',
      handler: this.onSearch,
      scope: this
    }
  ];
  
  Toc.reports_web.VisitsSummaryPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.reports_web.VisitsSummaryPanel, Ext.Panel, {
  getFlashPanel: function() {
    this.pnlFlash = new Ext.Panel({
      layout: 'fit',
      region: 'center',
      border: false,
      style: 'padding-top: 10px',
      swf: 'external/open-flash-chart/open-flash-chart.swf',
      flashvars: { 
        //data: Toc.CONF.CONN_URL + '?module=reports_web&action=render_visits_summary_flash_data&start_date=' + start_date + '&end_date=' + end_date + '&period=' + period + '&type=visits',
        data: '',
        chartHeight: function() {return this.body.getSize().height - 5;},
        chartWidth: function() {return this.body.getSize().width - 5;}
      },
      plugins: new Ext.ux.FlashPlugin()
    }); 
    
    return this.pnlFlash;
  },
  
  getSouthPanel: function() {
    this.pnlSouth = new Ext.Panel({
      region: 'south',
      height: 150,
      layout: 'column',
      border: false, 
      items: [
        {
          columnWidth: .5,
          border: false,
          items: [
            this.pnlVisitors = new Ext.Panel({
              border: false,
              listeners: {
                render: function(c) {
                  c.body.on('click', function() { this.loadFlash('visits');}, this);
                },
                scope: this
              }
            }),
            this.pnlUniqueVisitors = new Ext.Panel({
              border: false,
              listeners: {
                render: function(c) {
                  c.body.on('click', function() { this.loadFlash('unique_visitors');}, this);
                },
                scope: this
              }
            }),
            this.pnlActions = new Ext.Panel({
              border: false,
              listeners: {
                render: function(c) {
                  c.body.on('click', function() { this.loadFlash('actions');}, this);
                },
                scope: this
              }
            })
          ]
        }
        ,
        {
          columnWidth: .5,
          border: false,
          items: [
            this.pnlVisitsLength = new Ext.Panel({
              border: false,
              listeners: {
                render: function(c) {
                  c.body.on('click', function() { this.loadFlash('visits_length_min');}, this);
                },
                scope: this
              }
            }),
            this.pnlMaxActions = new Ext.Panel({
              border: false,
              listeners: {
                render: function(c) {
                  c.body.on('click', function() { this.loadFlash('max_actions');}, this);
                },
                scope: this
              }
            }),
            this.pnlBounceRate = new Ext.Panel({
              border: false,
              listeners: {
                render: function(c) {
                  c.body.on('click', function() { this.loadFlash('bounce_rate');}, this);
                },
                scope: this
              }
            })
          ]
        }
      ]
    });
    
    return this.pnlSouth;
  },
  
  loadSouthPanel: function() {
    this.el.mask(TocLanguage.loadingText);
    Ext.Ajax.request({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'reports_web',
        action: 'load_visits_summary_panel',
        start_date: this.dtStart.getValue().format('Y-m-d'),
        end_date: this.dtEnd.getValue().format('Y-m-d'),
        period: this.cboPeriod.getValue()
      },
      callback: function(options, success, response) {
        this.el.unmask();
        
        result = Ext.decode(response.responseText);
        
        if(result.success == true){
          this.pnlVisitors.body.update(result.data.visits_panel);
          this.pnlUniqueVisitors.body.update(result.data.unique_visits_panel);
          this.pnlActions.body.update(result.data.actions_panel);
          
          this.pnlVisitsLength.body.update(result.data.visits_length_panel);
          this.pnlMaxActions.body.update(result.data.max_actions_panel);
          this.pnlBounceRate.body.update(result.data.bounce_rate_panel);
        }
      },
      scope: this
    }); 
  },
  
  loadFlash: function(type) {
    var start_date = this.dtStart.getValue().format('Y-m-d');
    var end_date = this.dtEnd.getValue().format('Y-m-d');
    var period = this.cboPeriod.getValue();
        
    this.pnlFlash.flashvars.data = Toc.CONF.CONN_URL + '?module=reports_web&action=render_visits_summary_flash_data&start_date=' + start_date + '&end_date=' + end_date + '&period=' + period + '&type=' + type; 
    this.pnlFlash.renderFlash();
  },
  
  onSearch: function() {
    if (this.dtStart.getValue() > this.dtEnd.getValue()) {
      alert('<?php echo $osC_Language->get('ms_error_end_date_smaller_than_start_date'); ?>');
      return;
    }
    
    this.loadSouthPanel();
    this.loadFlash('visits');
  }
});