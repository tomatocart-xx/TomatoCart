<?php
/*
  $Id: manufacturers_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.manufacturers.ManufacturersDialog = function(config) {

  config = config || {};
  
  config.id = 'manufacturers_dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_manufacturer'); ?>';
  config.width = 500;
  config.modal = true;
  config.layout = 'fit';
  config.iconCls = 'icon-manufacturers-win';
  config.items = this.buildForm();  
  
  config.buttons = [
    {
      text: TocLanguage.btnSave,
      handler: function() {
        this.submitForm();
      },
      scope: this
    },
    {
      text: TocLanguage.btnClose,
      handler: function() { 
        this.close();
      },
      scope: this
    }
  ];

  this.addEvents({'saveSuccess' : true});  
  
  Toc.manufacturers.ManufacturersDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.manufacturers.ManufacturersDialog, Ext.Window, {
  
  show: function (id) {
    var manufacturersId = id || null;
    
    this.frmManufacturer.form.reset();
    this.frmManufacturer.baseParams['manufacturers_id'] = manufacturersId;
    
    if (manufacturersId > 0) {
      this.frmManufacturer.load({
        url: Toc.CONF.CONN_URL,
        params: {
          module: 'manufacturers',
          action: 'load_manufacturer'
        },
        success: function(form, action) {
          var img = action.result.data.manufacturers_image;
          
          if (img) {
            var html = '<img src ="../images/manufacturers/' + img + '"  style = "margin-left: 110px; width: 80px; height: 80px" /><br/><span style = "padding-left: 110px;">/images/manufacturers/' + img + '</span>';
            this.frmManufacturer.findById('manufactuerer_image_panel').body.update(html);
          }          
          
          Toc.manufacturers.ManufacturersDialog.superclass.show.call(this);
        },
        failure: function() {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        },
        scope: this       
      });
    } else {   
      Toc.manufacturers.ManufacturersDialog.superclass.show.call(this);
    }
  },
      
  buildForm: function() {
    this.frmManufacturer = new Ext.form.FormPanel({
      url: Toc.CONF.CONN_URL,
      region: 'center',
      border: false,
      baseParams: {  
        module: 'manufacturers',
        action: 'save_manufacturer'
      }, 
      defaults: {
        anchor: '98%'
      },
      layoutConfig: {
        labelSeparator: ''
      },
      autoHeight: true,
      fileUpload: true,
      labelWidth: 100,
      items: [
        {
          xtype: 'textfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_name'); ?>', 
          name: 'manufacturers_name', 
          allowBlank: false
        },
        {
          xtype: 'panel',
          id: 'manufactuerer_image_panel',
          border: false,
          html: ''
        },
        {
          xtype: 'fileuploadfield', 
          fieldLabel: '<?php echo $osC_Language->get('field_image'); ?>', 
          name: 'manufacturers_image'
        }
      ]
    });
    
    <?php
        $i = 1;
        foreach ( $osC_Language->getAll() as $l ) {
          echo 'this.lang' . $l['id'] . ' = new Ext.form.TextField({name: "manufacturers_url[' . $l['id'] . ']",';
          
          if ($i == 1)
            echo 'fieldLabel:"' . $osC_Language->get('field_url') . '",';
          else
            echo 'fieldLabel: "&nbsp;",';  
          
          echo 'labelWidth: 50,';
          echo "labelStyle: 'background: url(../images/worldflags/" . $l['country_iso'] . ".png) no-repeat right center !important;', ";
          echo "value: 'http://', ";
          echo 'width: 300});';
          echo 'this.frmManufacturer.add(this.lang' . $l['id'] . ');';
          
          $i++;
        }
    ?>
    
    return this.frmManufacturer;
  },

  submitForm : function() {
    this.frmManufacturer.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action) {
         this.fireEvent('saveSuccess', action.result.feedback);
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