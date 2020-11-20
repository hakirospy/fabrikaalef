(function(BX, window) {

	var YandexMarket = BX.namespace('YandexMarket');

	// constructor

	YandexMarket.PrintDialog = function(arParams) {
		YandexMarket.PrintDialog.superclass.constructor.apply(this, arguments);
	};

	BX.extend(YandexMarket.PrintDialog, BX.CAdminDialog);

	YandexMarket.PrintDialog.prototype.SetContent = function(html) {
		var contents;
		var callback;
		var _this = this;

		YandexMarket.PrintDialog.superclass.SetContent.call(this, html);

		if (html != null) {
			contents = this.PARTS.CONTENT_DATA;
			callback = function() {
				_this.adjustSizeEx();
				BX.removeCustomEvent('onAjaxSuccessFinish', callback);
				BX.onCustomEvent(BX(contents), 'onYaMarketContentUpdate', [
					{ target: contents }
				]);
			};

			BX.addCustomEvent('onAjaxSuccessFinish', callback);
		}
	};

	YandexMarket.PrintDialog.prototype.adjustSizeEx = function() {
		BX.defer(this.__adjustSizeEx, this)();
	};

	YandexMarket.PrintDialog.prototype.__adjustSizeEx = function() {
		var contentElement = this.PARTS.CONTENT_DATA;
		var contentHeight = contentElement.scrollHeight || contentElement.clientHeight;
		var contentWidth = contentElement.scrollWidth || contentElement.clientWidth;

		if (this.PARAMS.min_width > 0 && contentWidth < this.PARAMS.min_width) {
			contentWidth = this.PARAMS.min_width;
		} else if (this.PARAMS.max_width > 0 && contentWidth > this.PARAMS.max_width) {
			contentWidth = this.PARAMS.max_width;
		}

		if (this.PARAMS.min_height > 0 && contentHeight < this.PARAMS.min_height) {
			contentHeight = this.PARAMS.min_height;
		} else if (this.PARAMS.max_height > 0 && contentHeight > this.PARAMS.max_height) {
			contentHeight = this.PARAMS.max_height;
		}

		this.PARTS.CONTENT_DATA.style.width = contentWidth + 'px';
		this.PARTS.CONTENT_DATA.style.height = contentHeight + 'px';

		this.adjustPos();
	};

	// buttons

	YandexMarket.PrintDialog.prototype.btnSave = YandexMarket.PrintDialog.btnSave = {
		title: BX.message('YANDEX_MARKET_PRINT_DIALOG_SUBMIT'),
		id: 'savebtn',
		name: 'savebtn',
		className: 'adm-btn-save yamarket-dialog-btn',
		action: function () {
			this.parentWindow.GetForm().submit();
			this.parentWindow.Close();
		}
	};

	YandexMarket.PrintDialog.btnCancel = YandexMarket.PrintDialog.superclass.btnCancel;
	YandexMarket.PrintDialog.btnClose = YandexMarket.PrintDialog.superclass.btnClose;

})(BX, window);