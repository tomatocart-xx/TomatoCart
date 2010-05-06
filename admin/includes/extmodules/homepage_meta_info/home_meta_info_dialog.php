<?php
/*
  $Id: specials_grid.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  
?>
Toc.metas.HomePageMetaInfoDialog = function (config) {
  config = config || {};
  
  config.id = "homepage_meta_info-win";
  config.title = '<?php echo $osC_Language->get('heading_title'); ?>';
  config.layout = 'fit';
  config.width = 450;
  config.modal = true;
  config.height = 300;
  config.iconCls = 'icon-homepage_meta_info-win';
  config.border =  false;
  config.items = this.buildForm();
  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: function(){
        this.submitForm();
      },
      scope:this
    },
    {
      text: TocLanguage.btnClose,
      handler: function(){
        this.close();
      },
      scope:this
    }
  ];
  
  Toc.metas.HomePageMetaInfoDialog.superclass.constructor.call(this, config);
};

Ext.extend(Toc.metas.HomePageMetaInfoDialog, Ext.Window, {
  show: function() {
    this.frmMetas.load({
      url: Toc.CONF.CONN_URL,
      params: {
        module: 'home_meta',
        action: 'load_meta_info'
      },
      scope: this
    }, this);
    Toc.metas.HomePageMetaInfoDialog.superclass.show.call(this);
  },
  
  buildForm: function () {
    tabMetas = new Ext.TabPanel({
      activeTab: 0,
      border: false,
      deferredRender: false
    });
     <?php
      foreach ($osC_Language->getAll() as $l) {
        $code = strtoupper($l['code']);
        echo 'var lang' . $l['code'] . ' = new Ext.Panel({
          title:\'' . $l['name'] . '\',
          iconCls: \'icon-' . $l['country_iso'] . '-win\',
          layout: \'form\',
          labelSeparator: \' \',
          style: \'padding: 6px\',
          defaults: {
            anchor: \'96%\'
          },
          items: [
            {xtype: \'textfield\', fieldLabel: \'' . $osC_Language->get('field_meta_keywords') . '\', name: \'HOME_META_KEYWORD[' . $code . ']\'},
            {xtype: \'textarea\', height: 130, fieldLabel: \'' . $osC_Language->get('field_meta_description') . '\', name: \'HOME_META_DESCRIPTION[' . $code . ']\'}            
            ]
        });
        
        tabMetas.add(lang' . $l['code'] . ');
        ';
      }
    ?>
    this.frmMetas = new Ext.form.FormPanel({
      border: false,
      url: Toc.CONF.CONN_URL,
      layout: 'fit',
      baseParams: {  
        module: 'home_meta',
        action : 'save_meta_info'
      }, 
      items: tabMetas
    });
    return this.frmMetas;
  },
  
  submitForm: function() {
    this.frmMetas.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
        this.owner.app.showNotification({title: TocLanguage.msgSuccessTitle, html: action.result.feedback});
        this.close();   
      },    
      failure: function(form, action) {
        if (action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },  
      scope: this
    }); 
  }
});