"use strict";

var AsproUI = AsproUI || {};

AsproUI.Popup = function ()
{

};

/**
 * AsproUI.Popup.Confirm
 * 
 * @returns {AsproUI.Popup.Confirm}
 */

AsproUI.Popup.Confirm = function (
  url,
  data,
  messages,
  popupSuffix
  )
{
  this.url = url;
  this.data = data;
  this.popupSuffix = popupSuffix ? popupSuffix : 'default';

  this._setMessages(messages);
  this._showPopup();
};

AsproUI.Popup.Confirm.prototype = {
  onSuccess: function (data)
  {

  },

  _setMessages: function (messages)
  {
    this.messages = {
      confirmMessage: 'All information related to this section will be deleted! <br> Proceed?',
      btnOk: 'Delete',
      btnCancel: 'Cancel'
    }

    if (messages) {
      this.messages = Object.assign(this.messages, messages);
    }
  },

  _showPopup: function ()
  {
    let self = this;

    let elContentContainer = document.createElement('div');
    elContentContainer.classList.add('aspro-ui-popup__content');
    elContentContainer.innerHTML = this.messages.confirmMessage;

    if (!(this.popup instanceof BX.PopupWindow)) {
      this.popup = BX.PopupWindowManager.create('popup_window_confirm_' + this.popupSuffix, null, {
        closeIcon: true,
        zIndex: 0,
        offsetLeft: 0,
        offsetTop: 0,
        draggable: false,
        overlay: {
          backgroundColor: 'black',
          opacity: '80'
        },
      });
    }

    this.popup.setContent(elContentContainer);

    let popup = this.popup;

    let btnOk = new BX.PopupWindowButton({
      text: self.messages.btnOk,
      className: 'ui-btn ui-btn-danger',
      events: {click: function ()
        {
          let btn = this;
          btn.addClassName('ui-btn-wait');

          BX.ajax({
            url: self.url,
            data: self.data,
            method: 'POST',
            dataType: 'json',
            onsuccess: function (data)
            {
              var message = '';
              if (Array.isArray(data.message)) {
                data.message.forEach(function (value)
                {
                  message += value + '<br\>';
                });
              } else {
                message = data.message;
              }

              if (data.result === false) {
                var bxAlert = new BX.UI.Alert({
                  text: message,
                  textCenter: true,
                  color: BX.UI.Alert.Color.DANGER,
                });

                elContentContainer.innerHTML = '';
                elContentContainer.appendChild(bxAlert.getContainer());
                popup.setContent(elContentContainer);
                popup.adjustPosition();
                btn.removeClassName('ui-btn-wait');
              }

              if (data.result === true) {
                if (data.redirect) {
                  window.location.href = data.redirect;
                } else {
                  btn.popupWindow.close();
                }

                self.onSuccess(data);
              }
            },
            onfailure: function ()
            {
              btn.removeClassName('ui-btn-wait');
            }
          });
        }}
    });

    let btnCancel = new BX.PopupWindowButton({
      text: self.messages.btnCancel,
      className: 'ui-btn ui-btn-light',
      events: {click: function ()
        {
          this.popupWindow.close();
        }}
    });

    this.popup.setButtons([
      btnOk,
      btnCancel
    ])

    this.popup.show();
  }
}
/**
 * AsproUI.Popup.ConfirmAction
 * 
 * @returns {AsproUI.Popup.ConfirmAction}
 */

AsproUI.Popup.ConfirmAction = function (
  actions,
  messages,
  classSettings,
  popupSuffix  
  )
{  
  this.popupSuffix = popupSuffix ? popupSuffix : 'action';
  
  this.actionBtnOk = null;
  if (actions.btnOk && typeof actions.btnOk == 'function') {
     this.actionBtnOk = actions.btnOk;
  }

  this._setClassSettings(classSettings);
  this._setMessages(messages);
  this._showPopup();
};

AsproUI.Popup.ConfirmAction.prototype = {
  onSuccess: function (data)
  {

  },

  _setMessages: function (messages)
  {
    this.messages = {
      confirmMessage: 'All information related to this section will be deleted! <br> Proceed?',
      btnOk: 'Delete',
      btnCancel: 'Cancel'
    }

    if (messages) {
      this.messages = Object.assign(this.messages, messages);
    }
  },

  _showPopup: function ()
  {
    let self = this;

    let elContentContainer = document.createElement('div');
    elContentContainer.classList.add('aspro-ui-popup__content'); 
    elContentContainer.innerHTML = this.messages.confirmMessage;

    if (!(this.popup instanceof BX.PopupWindow)) {
      this.popup = BX.PopupWindowManager.create('popup_window_confirm_' + this.popupSuffix, null, {
        closeIcon: true,
        zIndex: 0,
        offsetLeft: 0,
        offsetTop: 0,
        draggable: false,
        overlay: {
          backgroundColor: 'black',
          opacity: '80'
        },
      });
    }

    this.popup.setContent(elContentContainer);

    let popup = this.popup;

    let btnOk = null
    if(this.actionBtnOk) {
      btnOk = new BX.PopupWindowButton({
        text: self.messages.btnOk,
        className: this.classes.btnOk,
        events: {click: this.actionBtnOk}
      });
    }

    let btnCancel = new BX.PopupWindowButton({
      text: self.messages.btnCancel,
      className: this.classes.btnCancel,
      events: {click: function ()
        {
          this.popupWindow.close();
        }}
    });

    this.popup.setButtons([
      btnOk,
      btnCancel
    ])

    this.popup.show();
  },
  
  _setClassSettings: function (settings)
  {
    this.classes = {
      btnOk: 'ui-btn ui-btn-danger',
      btnCancel: 'ui-btn ui-btn-light',
      content: ''
    }

    if (settings) {
      this.classes = Object.assign(this.classes, settings)
    }
  },
}

/**
 * AsproUI.Popup.Alert
 * 
 * @returns {AsproUI.Popup.Alert}
 */

AsproUI.Popup.Alert = function (
  alertMessage,
  color,
  messages,
  popupSuffix
  )
{
  this.alertMessage = alertMessage;
  this.popupSuffix = popupSuffix ? popupSuffix : 'default';

  if (!color) {
    this.color = BX.UI.Alert.Color.DANGER
  }
  
  this._setMessages(messages);
  this._showPopup();
};

AsproUI.Popup.Alert.prototype = {
  onSuccess: function (data)
  {

  },

  _setMessages: function (messages)
  {
    this.messages = {
      btnClose: 'Close'
    }

    if (messages) {
      this.messages = Object.assign(this.messages, messages);
    }
  },

  _showPopup: function ()
  {
    let self = this;
    
    let elContentContainer = document.createElement('div');
    elContentContainer.classList.add('aspro-ui-popup__content');

     if (!(this.popup instanceof BX.PopupWindow)) {
      this.popup = BX.PopupWindowManager.create('popup_window_alert_' + this.popupSuffix, null, {
        closeIcon: true,
        zIndex: 0,
        offsetLeft: 0,
        offsetTop: 0,
        draggable: false,
        overlay: {
          backgroundColor: 'black',
          opacity: '80'
        },
      });
    }

    this.popup.setContent(elContentContainer);

    let popup = this.popup;

    let btnClose = new BX.PopupWindowButton({
      text: self.messages.btnClose,
      className: 'ui-btn ui-btn-light',
      events: {click: function ()
        {
          this.popupWindow.close();
        }}
    });

    let bxAlert = new BX.UI.Alert({
      text: this.alertMessage,
      textCenter: true,
      color: this.color,
    });
    
    elContentContainer.innerHTML = '';
    elContentContainer.appendChild(bxAlert.getContainer());

    this.popup.setButtons([
      btnClose
    ])

    this.popup.show();
  }
}