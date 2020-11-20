(function(BX, $, window) {

	var Plugin = BX.namespace('YandexMarket.Plugin');
	var OrderView = BX.namespace('YandexMarket.OrderView');

	var constructor = OrderView.ShipmentSubmit = Plugin.Base.extend({

		defaults: {
			url: 'yamarket_trading_shipment_submit.php',
			messageElement: '.js-yamarket-shipment-submit__message',
			shipmentElement: '.js-yamarket-shipment',

			lang: {},
			langPrefix: 'YANDEX_MARKET_T_TRADING_ORDER_VIEW_SHIPMENT_SUBMIT_'
		},

		initVars: function() {
			this.callParent('initVars', constructor);
			this._handleBoxCollectionChange = false;
		},

		destroy: function() {
			this.unbind();
			this.callParent('initialize', constructor);
		},

		unbind: function() {
			this.handleShipmentChange(false);
			this.handleBoxCollectionChange(false);
		},

		handleShipmentChange: function(dir) {
			var shipment = this.getShipmentNode();

			shipment[dir ? 'on' : 'off']('change', $.proxy(this.onShipmentChange, this));
		},

		handleBoxCollectionChange: function(dir) {
			if (this._handleBoxCollectionChange === dir) { return; }

			this._handleBoxCollectionChange = dir;

			BX[dir ? 'addCustomEvent' : 'removeCustomEvent']('yamarketOrderViewBoxCollectionAddItem', BX.proxy(this.onBoxCollectionModify, this));
			BX[dir ? 'addCustomEvent' : 'removeCustomEvent']('yamarketOrderViewBoxCollectionDeleteItem', BX.proxy(this.onBoxCollectionModify, this));
			BX[dir ? 'addCustomEvent' : 'removeCustomEvent']('yamarketOrderViewBoxItemCollectionAddItem', BX.proxy(this.onBoxCollectionModify, this));
			BX[dir ? 'addCustomEvent' : 'removeCustomEvent']('yamarketOrderViewBoxItemCollectionDeleteItem', BX.proxy(this.onBoxCollectionModify, this));
		},

		onShipmentChange: function() {
			this.handleShipmentChange(false);
			this.handleBoxCollectionChange(false);
			this.clear();
		},

		onBoxCollectionModify: function(instance) {
			var shipment = this.getShipmentNode();

			if ($.contains(shipment[0], instance.el)) {
				this.handleShipmentChange(false);
				this.handleBoxCollectionChange(false);
				this.clear();
			}
		},

		clear: function() {
			this.showMessage('', '');
		},

		activate: function() {
			if (this.el.disabled) { return; }

			if (this.validate()) {
				this.el.disabled = true;

				this.clear();
				this.query().then(
					$.proxy(this.activateEnd, this),
					$.proxy(this.activateStop, this)
				);
			}
		},

		activateStop: function(xhr, reason) {
			var message = this.getLang('FAIL', { 'REASON': reason });

			this.el.disabled = false;
			this.showMessage(message, 'error');

			this.handleShipmentChange(true);
			this.handleBoxCollectionChange(true);
		},

		activateEnd: function(data) {
			var message;
			var status;

			this.el.disabled = false;

			if (typeof data !== 'object' || data.status == null) {
				message = this.getLang('DATA_INVALID');
				status = 'error';
			} else {
				message = data.message;
				status = data.status;
			}

			this.showMessage(message, status);
			this.handleShipmentChange(true);
			this.handleBoxCollectionChange(true);

			BX.onCustomEvent(this.el, 'yamarketShipmentSubmitEnd', [status, message]);
		},

		validate: function() {
			var shipment = this.getShipment();
			var result = true;
			var confirmMessage;

			try {
				shipment.validate();
			} catch (e) {
				confirmMessage = this.getLang('VALIDATION_CONFIRM', { MESSAGE: e.message });
				result = confirm(confirmMessage || e.message);
			}

			return result;
		},

		query: function() {
			return $.ajax({
				url: this.options.url,
				type: 'POST',
				data: this.getQueryData(),
				dataType: 'json'
			});
		},

		getQueryData: function() {
			var data = this.getShipmentData();

			data.push({
				name: 'sessid',
				value: BX.bitrix_sessid()
			});

			return data;
		},

		showMessage: function(message, status) {
			var element = this.getElement('message', this.$el, 'siblings');

			element.attr('data-status', status);
			element.html(message || '');
		},

		getShipment: function() {
			var node = this.getShipmentNode();

			return Plugin.manager.getInstance(node);
		},

		getShipmentData: function() {
			var shipmentNode = this.getShipmentNode();

			return shipmentNode.find('input, textarea, select').serializeArray();
		},

		getShipmentNode: function() {
			return this.getElement('shipment', this.$el, 'closest');
		}

	}, {
		dataName: 'orderViewShipmentCollection',
		pluginName: 'YandexMarket.OrderView.ShipmentCollection',
	});

})(BX, jQuery, window);