<?php
/*
  $Id: articles_categories_dialog.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

Toc.articles_categories.ArticlesCategoriesDialog = function(config) {
  
  config = config || {};
  
  config.id = 'articles_categories-dialog-win';
  config.title = '<?php echo $osC_Language->get('action_heading_new_category'); ?>';
  config.layout = 'fit';
  config.width = 440;
  config.autoHeight = true;
  config.modal = true;
  config.iconCls = 'icon-articles_categories-win';
  config.items = this.buildForm();
    
  config.buttons = [
    {
      text:TocLanguage.btnSave,
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
  
  Toc.articles_categories.ArticlesCategoriesDialog.superclass.constructor.call(this, config);
}

Ext.extend(Toc.articles_categories.ArticlesCategoriesDialog, Ext.Window, {
  
  show: function(id) {
    var categoriesId = id || null;
    
    this.frmArticlesCategory.form.reset();  
    this.frmArticlesCategory.form.baseParams['articles_categories_id'] = categoriesId;
    
    if (categoriesId > 0) {
      this.frmArticlesCategory.load({
        url: Toc.CONF.CONN_URL,
        params:{
          action: 'load_articles_categories'
        },
        success: function(form, action) {
          Toc.articles_categories.ArticlesCategoriesDialog.superclass.show.call(this);
        },
        failure: function(form, action) {
          Ext.Msg.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrLoadData);
        }, 
        scope: this       
      });
    } else {
      Toc.articles_categories.ArticlesCategoriesDialog.superclass.show.call(this);
    }
  },
    
  buildForm: function() {
    this.frmArticlesCategory = new Ext.form.FormPanel({ 
      url: Toc.CONF.CONN_URL,
      baseParams: {  
        module: 'articles_categories',
        action: 'save_articles_category'
      }, 
      autoHeight: true,
      defaults: {
          anchor: '98%'
      },
      layoutConfig: {
        labelSeparator: ''
      }
    });
    
    <?php
      $i = 1; 
      foreach ( $osC_Language->getAll() as $l ) {
        echo 'var txtLang' . $l['id'] . ' = new Ext.form.TextField({name: "articles_categories_name[' . $l['id'] . ']",';
        
        if ($i != 1 ) 
          echo ' fieldLabel:"&nbsp;", ';
        else
          echo ' fieldLabel:"' . $osC_Language->get('field_name') . '", ';
          
        echo 'labelWidth: 70,';
        echo 'allowBlank: false,';
        echo "labelStyle: 'background: url(../images/worldflags/" . $l['country_iso'] . ".png) no-repeat right center !important;'});";
        echo 'this.frmArticlesCategory.add(txtLang' . $l['id'] . ');';
        $i++;
      }     
    ?>
    
    var pnlPublish = {
      layout: 'column',
      border: false,
      items: [
        {
          width: 160,
          layout: 'form',
          labelSeparator: ' ',
          border: false,
          items: [
            {
              xtype: 'radio', 
              name: 'articles_categories_status', 
              fieldLabel: '<?php echo $osC_Language->get('field_publish'); ?>', 
              inputValue: '1', 
              boxLabel: '<?php echo $osC_Language->get('field_publish_yes'); ?>', 
              checked: true,
              anchor: ''
            }
          ]
        },
        {
          layout: 'form',
          border: false,
          items: [
            {
              xtype: 'radio', 
              hideLabel: true, 
              name: 'articles_categories_status', 
              inputValue: '0', 
              boxLabel: '<?php echo $osC_Language->get('field_publish_no'); ?>', 
              width: 150
            }
          ]
        }
      ]
    };
    this.frmArticlesCategory.add(pnlPublish);
    this.frmArticlesCategory.add({xtype: 'numberfield', id: 'articles_categories_order', name: 'articles_categories_order', fieldLabel: '<?php echo $osC_Language->get('field_articles_order'); ?>', allowBlank: false});
    
    return this.frmArticlesCategory;
  },

  submitForm : function() {
    this.frmArticlesCategory.form.submit({
      waitMsg: TocLanguage.formSubmitWaitMsg,
      success: function(form, action){
        this.fireEvent('saveSuccess', action.result.feedback);
        this.close();
      },    
      failure: function(form, action) {
        if(action.failureType != 'client') {
          Ext.MessageBox.alert(TocLanguage.msgErrTitle, action.result.feedback);
        }
      },
      scope: this
    });   
  }
});