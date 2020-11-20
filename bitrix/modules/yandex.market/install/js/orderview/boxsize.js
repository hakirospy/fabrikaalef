(function(BX, $, window) {

	var Reference = BX.namespace('YandexMarket.Field.Reference');
	var OrderView = BX.namespace('YandexMarket.OrderView');

	var constructor = OrderView.BoxSize = Reference.Base.extend({

		defaults: {
			inputElement: '.js-yamarket-box-size__input',
			minDensity: 0.1,
			maxDensity: 21.5,

			lang: {},
			langPrefix: 'YANDEX_MARKET_T_TRADING_ORDER_VIEW_BOX_SIZE_'
		},

		validate: function() {
			var o = this.options;
			var density = this.getDensity();

			if (o.minDensity != null && density < o.minDensity) {
				throw new Error(this.getLang('DENSITY_LESS_MINIMAL'));
			} else if (o.maxDensity != null && density > o.maxDensity) {
				throw new Error(this.getLang('DENSITY_MORE_MAXIMUM'));
			}
		},

		getDensity: function() {
			return this.getWeight() / this.getVolume();
		},

		getVolume: function() {
			return this.getSize('WIDTH') * this.getSize('HEIGHT') * this.getSize('DEPTH');
		},

		getWeight: function() {
			return this.getSize('WEIGHT');
		},

		getSize: function(key) {
			var input = this.getInput(key);
			var label;
			var value;

			if (input == null) {
				throw new Error(this.getLang('INPUT_NOT_FOUND', { KEY: key }));
			}

			value = parseFloat(input.val()) || 0;

			if (value <= 0) {
				label = this.getInputLabel(input) || key;
				throw new Error(this.getLang('SIZE_MUST_BE_POSITIVE', { LABEL: label }));
			}

			return value;
		},

		getInputLabel: function(input) {
			var label = input.siblings('label');
			var text = label.text() || '';

			text = text.replace(/,.*$/, '');

			return text;
		},

	}, {
		dataName: 'orderViewBoxSize',
		pluginName: 'YandexMarket.OrderView.BoxSize',
	});

})(BX, jQuery, window);