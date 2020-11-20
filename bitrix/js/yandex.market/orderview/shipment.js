(function(BX, $, window) {

	var Plugin = BX.namespace('YandexMarket.Plugin');
	var Reference = BX.namespace('YandexMarket.Field.Reference');
	var OrderView = BX.namespace('YandexMarket.OrderView');

	var constructor = OrderView.Shipment = Reference.Complex.extend({

		defaults: {
			id: null,
			actionsElement: '.js-yamarket-shipment__actions',
			submitElement: '.js-yamarket-shipment-submit',
			printElement: '.js-yamarket-shipment-print',
			childElement: '.js-yamarket-shipment__child',
			inputElement: '.js-yamarket-shipment__input'
		},

		initialize: function() {
			this.callParent('initialize', constructor);
			this.bind();
		},

		destroy: function() {
			this.unbind();
			this.callParent('destroy', constructor);
		},

		bind: function() {
			this.handleBoxCollectionChange(true);
		},

		unbind: function() {
			this.handleBoxCollectionChange(false);
		},

		handleBoxCollectionChange: function(dir) {
			var boxCollection = this.getBoxCollection();

			BX[dir ? 'addCustomEvent' : 'removeCustomEvent'](boxCollection.el, 'yamarketOrderViewBoxCollectionAddItem', BX.proxy(this.onBoxCollectionModify, this));
			BX[dir ? 'addCustomEvent' : 'removeCustomEvent'](boxCollection.el, 'yamarketOrderViewBoxCollectionDeleteItem', BX.proxy(this.onBoxCollectionModify, this));
		},

		onBoxCollectionModify: function() {
			this.refreshEmptyState();
		},

		validate: function() {
			this.getBoxCollection().validate();
		},

		refreshEmptyState: function() {
			var boxCollection = this.getBoxCollection();
			var actions = this.getElement('actions');

			actions.toggleClass('is--hidden', boxCollection.isEmpty());
		},

		getCollection: function() {
			return this.getParentField();
		},

		getBoxCollection: function() {
			return this.getChildField('BOX');
		},

		getId: function() {
			return this.options.id;
		},

		getBoxCount: function() {
			return this.getBoxCollection().getActiveItems().length;
		},

		getBoxOffset: function() {
			var collection = this.getCollection();
			var siblingShipments = collection.getActiveItems();
			var siblingShipmentElement;
			var siblingShipment;
			var i;
			var result = 0;

			for (i = 0; i < siblingShipments.length; i++) {
				siblingShipmentElement = siblingShipments[i];

				if (this.el === siblingShipmentElement) {
					break;
				} else {
					siblingShipment = collection.getItemInstance(siblingShipmentElement);
					result += siblingShipment.getBoxCount();
				}
			}

			return result;
		},

		getOrderId: function() {
			var input = this.getInput('ORDER_ID');
			var result;

			if (input) {
				result = input.val();
			}

			return result;
		},

	}, {
		dataName: 'orderViewShipment',
		pluginName: 'YandexMarket.OrderView.Shipment',
	});

})(BX, jQuery, window);