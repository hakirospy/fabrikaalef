(function(BX, $, window) {

	var FieldReference = BX.namespace('YandexMarket.Field.Reference');
	var OrderView = BX.namespace('YandexMarket.OrderView');

	var constructor = OrderView.BasketItem = FieldReference.Base.extend({

		defaults: {
			id: null,

			inputElement: '.js-yamarket-basket-item__data',
			checkElement: '.js-yamarket-basket-item__check'
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
			this.handleRowClick(true);
		},

		unbind: function() {
			this.handleRowClick(false);
		},

		handleRowClick: function(dir) {
			this.$el[dir ? 'on' : 'off']('click', $.proxy(this.onRowClick, this));
		},

		onRowClick: function(evt) {
			var tagName = (evt.target.tagName || '').toLowerCase();

			if (tagName !== 'input') {
				this.toggleCheck();
				evt.preventDefault();
			}
		},

		getId: function() {
			return this.options.id;
		},

		isChecked: function(check) {
			if (check == null) {
				check = this.getElement('check');
			}

			return check.prop('checked');
		},

		toggleCheck: function(dir) {
			var check = this.getElement('check');

			if (dir == null) { dir = !this.isChecked(check); }

			check.prop('checked', !!dir);
		},

		setBoxCount: function(count) {
			var boxCountInput = this.getInput('BOX_COUNT');

			if (boxCountInput != null) {
				boxCountInput.text(count);
			}
		},

	}, {
		dataName: 'orderViewBasketItem',
		pluginName: 'YandexMarket.OrderView.BasketItem',
	});

})(BX, jQuery, window);