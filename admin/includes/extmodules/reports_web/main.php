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

  echo 'Ext.namespace("Toc.reports_web");';
  
  include('includes/classes/piwik.php');
  include('visits_summary.php');
  include('traffic_source_summary.php');  
  include('visit_settings.php');
  include('visit_location.php'); 
  include('search_engines.php');  
  include('referer_websites.php');   
?>

Ext.override(TocDesktop.ReportsWebWindow, {

  createWindow: function() {
    desktop = this.app.getDesktop();
    win = desktop.getWindow(this.id);
     
    if(!win){
      if (this.params.report == 'visits-summary') {
        pnl = new Toc.reports_web.VisitsSummaryPanel();
      }

      if (this.params.report == 'traffic_source_summary') {
        pnl = new Toc.reports_web.TrafficSourceSummaryPanel();
      }     
      
      if (this.params.report == 'visit_settings') {
        pnl = new Toc.reports_web.VisitSettingsPanel();
      } 
      
      if (this.params.report == 'visit_location') {
        pnl = new Toc.reports_web.VisitLocationPanel();
      }  
      
      if (this.params.report == 'search_engines') {
        pnl = new Toc.reports_web.SearchEnginesPanel();
      }  
      
      if (this.params.report == 'referer_websites') {
        pnl = new Toc.reports_web.RefererWebsitesPanel();
      }                       
  
      win = desktop.createWindow({
        id: this.id,
        title: this.title,
        autoWidth: true,
        autoHeight: true,
        iconCls: this.iconCls,
        layout: 'fit',
        items: pnl
      });

      win.on('show', function() {pnl.onSearch();}, pnl);
    }
    
    win.show();
  }
});
