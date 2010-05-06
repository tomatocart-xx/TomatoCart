/*
  $Id: checkout.js $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

var Checkout = new Class({
  Implements: Options,
  
  openedForm: null,
  shipToBillingAddress: false,
  isTotalZero: false,
  paymentParams: null,
  
  options: {
    remoteUrl: 'json.php',
    sessionName: 'sid',
    sessionId: null,
    isLoggedOn: false,
    isVirtualCart: false
  },
  
  steps: {
    checkoutMethodForm: 1,
    billingInformationForm: 2,
    shippingInformationForm: 3,
    shippingMethodForm: 4,
    paymentInformationForm: 5,
    orderConfirmationForm: 6
  },

  sendRequest: function(data, fnSuccess) {
    data.module = 'checkout';
    data[this.options.sessionName] = this.options.sessionId;
    
    var loadRequest = new Request({
      url: this.options.remoteUrl,
      data: data,
      onSuccess: fnSuccess.bind(this)
    }).send();
  },

  initialize: function(options) {
    this.isTotalZero = options.isTotalZero;
    
    this.paymentParams = {};
    this.setOptions(options);
    this.iniCheckoutForms();
  },
  
  iniCheckoutForms: function() {
    if (this.options.isLoggedOn == false) {
      this.loadCheckoutMethodForm();
    } else {
      this.loadBillingInformationForm();
    }
    
    $$('.formHeader').each( function(form_header, i) {
      form_header.addEvent('click', function(e){
        var formName = form_header.getParent().id;
        
        if (this.shipToBillingAddress == true) {
          if ((formName == 'shippingInformationForm')) {
            return;
          }
        }
        
        if (this.options.isVirtualCart == true) {
          if ((formName == 'shippingInformationForm') || (formName == 'shippingMethodForm')) {
            return;
          }
        }
        
        if (this.isTotalZero == true) {
          if (formName == 'paymentInformationForm') {
            return;
          }
        }
        
        if (this.steps[formName] < this.steps[this.openedForm]) { 
          this.gotoPanel(formName);
        }
      }.bind(this));
      
      if (i != 0) {
        form_header.getNext().setStyle('display', 'none');
      } else {
        this.openedForm = form_header.getParent().id;
        form_header.getElement('span').set('html', '-');
      }
    }.bind(this));  
  },
 
  loadCheckoutMethodForm: function() {
    var params = {action: 'load_checkout_method_form'};
    
    this.sendRequest(params, function(response){
      var result = JSON.decode(response);
      
      if (result.success == true) {
        $('checkoutMethodForm').getElement('div').set('html', result.form);
        
        $('btnNewCustomer').addEvent('click', function(e) {
          this.loadBillingInformationForm();
        }.bind(this));
      }
    });
  },
  
  loadBillingInformationForm: function() {
    var params = {action: 'load_billing_information_form'};
    
    this.sendRequest(params, function(response){
      var result = JSON.decode(response);
      
      if (result.success == true) {
        $('billingInformationForm').getElement('div').set('html', result.form);
        this.gotoPanel('billingInformationForm');
        
        //create new billing address
        if ($defined($('create_billing_address'))) {
          $('create_billing_address').addEvent('click', function(e) {
            if ($('create_billing_address').checked == true) {
              $('billingAddressDetails').setStyle('display', '');
            } else {
              $('billingAddressDetails').setStyle('display', 'none');
            }
          });
        }
        
        //billing country change
        $('billing_country').addEvent('change', function(e) {
          this.countryChange('billing');
        }.bind(this));
        
        //save billing information
        $('btnSaveBillingInformation').addEvent('click', function(e) {
          this.btnSaveBillingInformationClick();          
        }.bind(this));
      }
    });
  },
  
  btnSaveBillingInformationClick: function() {
    var isLoggedOn = this.options.isLoggedOn;
    var isVirtualCart = this.options.isVirtualCart;
    var params = {
      action: 'save_billing_address'
    };
    
    if (isVirtualCart == false) {
      params.ship_to_this_address = (($('ship_to_this_address').checked == true) ? 1 : 0);
      
      this.shipToBillingAddress = $('ship_to_this_address').checked;
    }
    
    if((isLoggedOn == false) || (isLoggedOn == true && $('create_billing_address').checked == true)) {
      if ( $defined($('billing_gender1')) ) {
        if ($('billing_gender1').checked == true) {
          params.billing_gender = 'm';  
        } else {
          params.billing_gender = 'f';
        }
      } else {
        params.billing_gender = '';
      }
      
      params.billing_firstname = $('billing_firstname').value;
      params.billing_lastname = $('billing_lastname').value;
      params.billing_company = ($defined($('billing_company')) ? $('billing_company').value : '');
      params.billing_street_address = $('billing_street_address').value;
      params.billing_suburb = ($defined($('billing_suburb')) ? $('billing_suburb').value : '');
      params.billing_postcode = ($defined($('billing_postcode')) ? $('billing_postcode').value : '');
      params.billing_city = $('billing_city').value;
      params.billing_state = ($defined($('billing_state')) ? $('billing_state').value : '');
      params.billing_country = $('billing_country').value;
      params.billing_telephone = ($defined($('billing_telephone')) ? $('billing_telephone').value : '');
      params.billing_fax = ($defined($('billing_fax')) ? $('billing_fax').value : '');
      
      if ($defined($('create_billing_address')) && ($('create_billing_address').checked == true)) {
        params.create_billing_address = 1;
      }
    }
    
    if (isLoggedOn == true) {
      if ($('create_billing_address').checked == false) {
        params.billing_address_id = $('sel_billing_address').options[$('sel_billing_address').selectedIndex].value;
      }
    } else {
      params.billing_email_address = $('billing_email_address').value;
      params.billing_password = $('billing_password').value;
      params.billing_confirm_password = $('billing_confirm_password').value;
    }
    
    this.sendRequest(params, function(response){
      var result = JSON.decode(response);
      
      if (result.success == true) {
        //if isVirtualCart skip shipping form and shipping method
        if (isVirtualCart == true) {
          $('paymentInformationForm').getElement('div').set('html', result.form);
          this.gotoPanel('paymentInformationForm');
          
          eval(result.javascript);
          
          //btnSaveBillingInformation
          if($defined($('payment_method_store_credit'))) {
            $('payment_method_store_credit').addEvent('click', this.onChkUseStoreCreditChecked.bind(this));
          }  
          
          //btnSaveBillingInformation
          $('btnSavePaymentMethod').addEvent('click', function(e) {
            if (this.isTotalZero == false) {
              var result = check_form();
              
              if (result == true) {
                this.btnSavePaymentMethodCllick();
              }
            } else {
              this.btnSavePaymentMethodCllick();
            }
          }.bind(this));  
        } else {
          //if ship to this address          
          if ($('ship_to_this_address').checked == true) {
            $('shippingMethodForm').getElement('div').set('html', result.form);
            this.gotoPanel('shippingMethodForm');
            
            //save shipping method
            $('btnSaveShippingMethod').addEvent('click', function(e) {
              this.btnSaveShippingMethodClick();          
            }.bind(this));                
          } else {
            $('shippingInformationForm').getElement('div').set('html', result.form);
            this.gotoPanel('shippingInformationForm');
            
            //create new shipping address
            if ($defined($('create_shipping_address'))) {
              $('create_shipping_address').addEvent('click', function(e) {
                if ($('create_shipping_address').checked == true) {
                  $('shippingAddressDetails').setStyle('display', '');
                  
                } else {
                  $('shippingAddressDetails').setStyle('display', 'none');
                }
              });
            }
            
            //shipping country change
            $('shipping_country').addEvent('change', function(e) {
              this.countryChange('shipping');
            }.bind(this));
            
            //save shipping information
            $('btnSaveShippingInformation').addEvent('click', function(e) {
              this.btnSaveShippingInformationClick();          
            }.bind(this)); 
          }
        }
      } else {
        alert(result.errors.join('\n'));
      }
    });
  },
  
  btnSaveShippingInformationClick: function() {
    var isLoggedOn = this.options.isLoggedOn;
    var params = {action: 'save_shipping_address'};
    
    if((isLoggedOn == false) || (isLoggedOn == true && $('create_shipping_address').checked == true)) {
      if ( $defined($('shipping_gender1')) ) {
        if ($('shipping_gender1').checked == true) {
          shipping_gender = 'm';  
        } else {
          shipping_gender = 'f';
        }
      } else {
        shipping_gender = '';
      }
      
      params.shipping_gender = shipping_gender;
      params.shipping_firstname = $('shipping_firstname').value;
      params.shipping_lastname = $('shipping_lastname').value;
      params.shipping_company = ($defined($('shipping_company')) ? $('shipping_company').value : '');
      params.shipping_street_address = $('shipping_street_address').value;
      params.shipping_suburb = ($defined($('shipping_suburb')) ? $('shipping_suburb').value : '');
      params.shipping_postcode = ($defined($('shipping_postcode')) ? $('shipping_postcode').value : '');
      params.shipping_city = $('shipping_city').value;
      params.shipping_state = ($defined($('shipping_state')) ? $('shipping_state').value : '');
      params.shipping_country = $('shipping_country').value;
      params.shipping_telephone = ($defined($('shipping_telephone')) ? $('shipping_telephone').value : '');
      params.shipping_fax = ($defined($('shipping_fax')) ? $('shipping_fax').value : '');
      
      if ($defined($('create_shipping_address')) && ($('create_shipping_address').checked == true)) {
        params.create_shipping_address = 1;
      }
    }
    
    if(this.options.isLoggedOn == true && $('create_shipping_address').checked == false) {
      params.shipping_address_id = $('sel_shipping_address').options[$('sel_shipping_address').selectedIndex].value;
    }
    
    this.sendRequest(params, function(response) {
      var result = JSON.decode(response);
    
      if (result.success == true) {
        $('shippingMethodForm').getElement('div').set('html', result.form);
        this.gotoPanel('shippingMethodForm');
        
        //btnSaveBillingInformation
        $('btnSaveShippingMethod').addEvent('click', function(e) {
          this.btnSaveShippingMethodClick();          
        }.bind(this));         
      } else {
        alert(result.errors.join('\n'));
      }
    });
  },  
  
  btnSaveShippingMethodClick: function() {
    var shipping_methods = document.getElementsByName("shipping_mod_sel"); 
    var shipping_method = null;
    $each(shipping_methods, function(method) {
      if (method.type == 'radio') {
        if (method.checked) {
          shipping_method = method.value;
        }
      } else if (method.type == 'hidden') {
        shipping_method = method.value;
      }
    });
    
    if (shipping_method != null) {
      var params = {
        action: 'save_shipping_method',
        shipping_mod_sel: shipping_method,
        shipping_comments: $('shipping_comments').value
      };

      this.sendRequest(params, function(response) {
        var result = JSON.decode(response);
        
        $('paymentInformationForm').getElement('div').set('html', result.form);        
        this.gotoPanel('paymentInformationForm');
        
        eval(result.javascript);
        
        if($defined($('payment_method_store_credit'))) {
          $('payment_method_store_credit').addEvent('click', this.onChkUseStoreCreditChecked.bind(this));
        }  
        
        //btnSavePaymentMethod
        $('btnSavePaymentMethod').addEvent('click', function(e) {
          if (this.isTotalZero == false) {
            var result = check_form();
            
            if (result == true) {
              this.btnSavePaymentMethodCllick();
            }
          } else {
            this.btnSavePaymentMethodCllick();
          }
          
        }.bind(this));      
      });       
    } else {
      alert('Please select a shipping method!');
    }
  },
  
  onChkUseStoreCreditChecked: function() {
     var params = {
        action: 'use_store_credit',
        value: $('payment_method_store_credit').checked
     };
     
     this.sendRequest(params, function(response) {
       var result = JSON.decode(response);
        
       if (result.success == true) {
         this.isTotalZero = result.isTotalZero;
         
         if (this.isTotalZero == true) {
            $('payment_methods').setStyle('display', 'none');
         } else {
            $('payment_methods').setStyle('display', '');
         }
       } else {
         $('payment_method_store_credit').checked = !$('payment_method_store_credit').checked;
         
         alert(result.errors);
       }
     });
  },

  btnSavePaymentMethodCllick: function() {
    var params = {
      action: 'save_payment_method',
      payment_comments: $('payment_comments').value
    };
     
    if ($defined($('conditions'))) {
      params.conditions = (($('conditions').checked == true) ? 1 : 0);
    } 
          
    if (this.isTotalZero == false) {
      var payment_methods = document.getElementsByName("payment_method"); 
      var payment_method = null;
      
      $each(payment_methods, function(method) {
        if (method.type == 'radio') {
          if (method.checked) {
            payment_method = method.value;
          }
        } else if (method.type == 'hidden') {
          payment_method = method.value;
        }
      });
      
      if (payment_method != null) {
        //params['payment_method'] = payment_method;
        this.paymentParams['payment_method'] = payment_method;
        
        //get all the inputs
        var divPayment = $('payment_method_' + payment_method);
        var inputs = divPayment.getElements('input');
        $each(inputs, function(input){
          if (input.type == 'text') {
            this.paymentParams[input.name] = input.value;
          } else if ((input.type == 'checkbox') || (input.type == 'radio')) {
            if (input.checked == true) {
              this.paymentParams[input.name] = input.value;
            }
          }
        }.bind(this));
        
        var selects = divPayment.getElements('select');
        $each(selects, function(select) {
          this.paymentParams[select.name] = select.options[select.selectedIndex].value;
        }.bind(this));
        
        params = $merge(params, this.paymentParams);
      } else {
        alert('Please select a payment method!');
      }
    }
    
    this.sendRequest(params, function(response) {
      var result = JSON.decode(response);
      
      if (result.success == true) {
        this.loadOrderInformationForm(result.form);
      } else {
        alert(result.errors);
      } 
    });
  },
  
  loadOrderInformationForm: function(form) {
    $('orderConfirmationForm').getElement('div').set('html', form);
    this.gotoPanel('orderConfirmationForm');
    
    if ($defined($('btnRedeemCoupon'))) {
      $('btnRedeemCoupon').addEvent('click', function(e) {
        this.btnRedeemCouponClick();          
      }.bind(this)); 
    }
    
    if ($defined($('btnDeleteCoupon'))) {
      $('btnDeleteCoupon').addEvent('click', function(e) {
        this.btnDeleteCouponClick();          
      }.bind(this)); 
    }
    
    if ($defined($('btnRedeemGiftCertificate'))) {
      $('btnRedeemGiftCertificate').addEvent('click', function(e) {
        this.btnRedeemGiftCertificateClick();          
      }.bind(this)); 
    }
    
    $$('.btnDeleteGiftCertificate').each(function(btn){
      btn.addEvent('click', function(e) {
        this.btnDeleteGiftCertificateClick(btn.getParent().id);          
      }.bind(this)); 
    }.bind(this));
  },
  
  btnRedeemCouponClick: function() {
    var params = {
      action: 'redeem_coupon',
      coupon_redeem_code: $('coupon_redeem_code').value
    };
    params = $merge(params, this.paymentParams);
    
    this.sendRequest(params, function(response) {
      var result = JSON.decode(response);
        
      if (result.success == true) {
        this.loadOrderInformationForm(result.form);
        
        this.isTotalZero = result.isTotalZero;
      } else {
        alert(result.errors);
      }
    });
  },
  
  btnDeleteCouponClick: function() {
    var params = {
      action: 'delete_coupon'
    };
    params = $merge(params, this.paymentParams);
    
    this.sendRequest(params, function(response) {
      var result = JSON.decode(response);
        
      if (result.success == true) {
        this.loadOrderInformationForm(result.form);
        
        if (result.go_to_payment_form == true) {
          $('payment_methods').setStyle('display', '');
          this.gotoPanel('paymentInformationForm');
        }
        
        this.isTotalZero = false;
      }
    });
  },  
   
  btnRedeemGiftCertificateClick: function() {
    var params = {
      action: 'redeem_gift_certificate',
      gift_certificate_code: $('gift_certificate_code').value
    };
    params = $merge(params, this.paymentParams);
    
    this.sendRequest(params, function(response) {
      var result = JSON.decode(response);
        
      if (result.success == true) {
        this.loadOrderInformationForm(result.form);
        
        this.isTotalZero = result.isTotalZero;
      } else {
        alert(result.errors);
      }
    });
  },    
  
  btnDeleteGiftCertificateClick: function(gift_certificate_code) {
    var params = {
      action: 'delete_gift_certificate',
      gift_certificate_code: gift_certificate_code
    };
    params = $merge(params, this.paymentParams);

    this.sendRequest(params, function(response) {
      var result = JSON.decode(response);
        
      if (result.success == true) {
        this.loadOrderInformationForm(result.form);
        
        if (result.go_to_payment_form == true) {
          $('payment_methods').setStyle('display', '');
          this.gotoPanel('paymentInformationForm');
        }
        
        this.isTotalZero = false;
      }
    });
  },
  
  countryChange: function(type) {
    country_id = $(type + '_country').value;
    
    if (country_id > 0) {
      var params = {
        action: 'country_change',
        country_id: country_id,
        type: type
      };
    
      this.sendRequest(params, function(response) {
        var result = JSON.decode(response);

        if (result.success == true) {
          $(type + '-state').set('html', result.html);
        }
      });
    }
  },

  gotoPanel: function(formName) {
    this.openedForm = formName;
  
    $$('.formHeader').each( function(form_header) {
      var form_name = form_header.getParent().id;
      var form_body = form_header.getNext();
      var span = form_header.getElement('span');
      
      if (formName != form_name) {
        form_body.setStyle('display', 'none');
        span.set('html', '+');
      } else {
        form_body.setStyle('display', '');
        span.set('html', '-');
      }
    });
  }
});


function selectRowEffect(form, object) {
  $$('#' + form + ' .moduleRowSelected').each(function(row) {
    row.className = 'moduleRow';
  });

  object.className = 'moduleRowSelected';
}

function mod10(number) {
  var nCheck = 0;
  var nDigit = 0;
  var bEven = false;

  for (n = number.length - 1; n >= 0; n--) {
    var cDigit = number.charAt(n);
    var nDigit = parseInt(cDigit, 10);
    if (bEven) {
      if ((nDigit *= 2) > 9) {
        nDigit -= 9;
      }
    }
    nCheck += nDigit;
    bEven = !bEven;
  }

  return (nCheck % 10) == 0;
}