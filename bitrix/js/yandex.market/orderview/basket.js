(function(BX, $, window) {

	var Plugin = BX.namespace('YandexMarket.Plugin');
	var FieldReference = BX.namespace('YandexMarket.Field.Reference');
	var OrderView = BX.namespace('YandexMarket.OrderView');

	var constructor = OrderView.Basket = FieldReference.Collection.extend({

		defaults: {
			itemElement: '.js-yamarket-basket-item',
			checkElement: '.js-yamarket-basket__check',

			boxSelectElement: '.js-yamarket-basket__box-select',
			boxAddElement: '.js-yamarket-basket__box-add',

			shipmentCollectionElement: '#YAMARKET_SHIPMENT_COLLECTION',

			lang: {},
			langPrefix: 'YANDEX_MARKET_T_TRADING_ORDER_VIEW_'
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
			this.handleCheckChange(true);
			this.handleBoxAddClick(true);
			this.handleBoxCollectionChange(true);
			this.handleBoxItemCollectionChange(true);
			this.handleBoxItemCountChange(true);
			this.handleBoxSelectChange(true);
		},

		unbind: function() {
			this.handleCheckChange(false);
			this.handleBoxAddClick(false);
			this.handleBoxCollectionChange(false);
			this.handleBoxItemCollectionChange(false);
			this.handleBoxItemCountChange(false);
			this.handleBoxSelectChange(false);
		},

		handleCheckChange: function(dir) {
			var check = this.getElement('check');

			check[dir ? 'on' : 'off']('change', $.proxy(this.onCheckChange, this));
		},

		handleBoxAddClick: function(dir) {
			var boxAdd = this.getElement('boxAdd');

			boxAdd[dir ? 'on' : 'off']('click', $.proxy(this.onBoxAddClick, this));
		},

		handleBoxCollectionChange: function(dir) {
			BX[dir ? 'addCustomEvent' : 'removeCustomEvent']('yamarketOrderViewBoxCollectionAddItem', BX.proxy(this.onBoxCollectionAdd, this));
			BX[dir ? 'addCustomEvent' : 'removeCustomEvent']('yamarketOrderViewBoxCollectionDeleteItem', BX.proxy(this.onBoxCollectionDelete, this));
		},

		handleBoxItemCollectionChange: function(dir) {
			BX[dir ? 'addCustomEvent' : 'removeCustomEvent']('yamarketOrderViewBoxItemCollectionAddItem', BX.proxy(this.onBoxItemCollectionAdd, this));
			BX[dir ? 'addCustomEvent' : 'removeCustomEvent']('yamarketOrderViewBoxItemCollectionDeleteItem', BX.proxy(this.onBoxItemCollectionDelete, this));
		},

		handleBoxItemCountChange: function(dir) {
			BX[dir ? 'addCustomEvent' : 'removeCustomEvent']('yamarketOrderViewBoxItemCountChange', BX.proxy(this.onBoxItemCountChange, this));
		},

		handleBoxSelectChange: function(dir) {
			var boxSelect = this.getElement('boxSelect');

			boxSelect[dir ? 'on' : 'off']('change', $.proxy(this.onBoxSelectChange, this));
		},

		onCheckChange: function(evt) {
			var isChecked = !!evt.target.checked;

			this.callItemList('toggleCheck', [isChecked]);
		},

		onBoxAddClick: function(evt) {
			var isDisabled = evt.target.classList.contains('adm-btn-disabled');

			!isDisabled && this.addBoxItems();
			evt.preventDefault();
		},

		onBoxCollectionAdd: function() {
			this.updateBoxSelect();
		},

		onBoxCollectionDelete: function(boxCollection, box) {
			var itemIdList = box.getItemCollection().getItemIdList();
			var itemIdIndex;

			for (itemIdIndex = 0; itemIdIndex < itemIdList.length; itemIdIndex++) {
				this.refreshBoxCount(itemIdList[itemIdIndex]);
			}

			this.updateBoxSelect();
		},

		onBoxItemCollectionAdd: function(boxItemCollection, boxItem) {
			var _this = this;

			setTimeout(function() {
				_this.refreshBoxCount(boxItem.getId());
			}, 0);
		},

		onBoxItemCollectionDelete: function(boxItemCollection, boxItem) {
			this.refreshBoxCount(boxItem.getId());
		},

		onBoxItemCountChange: function(boxItem) {
			this.refreshBoxCount(boxItem.getId());
		},

		onBoxSelectChange: function(evt) {
			var select = evt.target; // boxSelect
			var isDisabledAdd = (select.value === '');

			this.disableBoxAdd(isDisabledAdd);
		},

		getItemById: function(id) {
			var result;

			this.callItemList(function(instance) {
				if (instance.getId() == id) {
					result = instance;
				}
			});

			return result;
		},

		refreshBoxCount: function(itemId) {
			var item = this.getItemById(itemId);
			var shipmentCount = this.getShipmentBoxProductCount(itemId);

			if (item != null) {
				item.setBoxCount(shipmentCount);
			}
		},

		addBoxItems: function() {
			var box = this.getSelectedBox();
			var items = this.getSelectedItems();

			if (box == null) {
				alert(this.getLang('BOX_NOT_FOUND'));
				return;
			}

			this.copyItemsToBox(box, items);
		},

		getSelectedItems: function() {
			var result = [];

			this.callItemList(function(instance) {
				if (instance.isChecked()) {
					result.push(instance);
				}
			});

			return result;
		},

		getSelectedBox: function() {
			var boxSelectValue = this.getBoxSelectValue();
			var shipmentCollection = this.getShipmentCollection();
			var shipment = shipmentCollection.getItemById(boxSelectValue.shipment);
			var boxCollection = shipment.getBoxCollection();
			var itemElement;
			var result;

			if (boxSelectValue.box === 'new') {
				result = boxCollection.addItem();
			} else {
				itemElement = boxCollection.getItem(boxSelectValue.box);

				if (itemElement != null) {
					result = boxCollection.getItemInstance(itemElement);
				}
			}

			return result;
		},

		getShipmentCollection: function() {
			var shipmentCollectionElement = this.getElement('shipmentCollection');
			var pluginName = shipmentCollectionElement.data('plugin');
			var plugin = Plugin.manager.getPlugin(pluginName);

			return plugin.getInstance(shipmentCollectionElement);
		},

		getBoxSelectValue: function() {
			var boxSelect = this.getElement('boxSelect');
			var optionList = boxSelect.find('option');
			var optionIndex;
			var option;
			var boxIndex;
			var result;

			for (optionIndex = 0; optionIndex < optionList.length; optionIndex++) {
				option = optionList[optionIndex];

				if (option.selected) {
					boxIndex = parseInt(option.value);

					result = {
						shipment: option.getAttribute('data-shipment'),
						box: isNaN(boxIndex) ? 'new' : boxIndex
					};
					break;
				}
			}

			return result;
		},

		updateBoxSelect: function() {
			var boxSelect = this.getElement('boxSelect');
			var summary = this.getShipmentBoxCountSummary();
			var summaryIndex;
			var summaryItem;
			var totalBoxIndex = 0;
			var optgroup;
			var optionList;
			var optionCount;
			var optionLimit;
			var optionIndex;
			var boxIndex;
			var option;

			for (summaryIndex = 0; summaryIndex < summary.length; summaryIndex++) {
				summaryItem = summary[summaryIndex];
				optgroup = this.getBoxShipmentOptionGroup(boxSelect, summaryItem.shipmentId);
				optionList = optgroup.find('option');
				optionCount = optionList.length;
				boxIndex = 0;
				optionLimit = Math.max(optionCount, summaryItem.boxCount);

				for (optionIndex = 0; optionIndex < optionLimit; optionIndex++) {
					option = optionList[optionIndex];

					if (option && (option.value === 'new' || option.value === '')) {
						++optionLimit;
					} else if (boxIndex < summaryItem.boxCount) {
						if (!option) {
							option = document.createElement('option');
							optgroup.append(option);
						}

						option.value = boxIndex;
						option.setAttribute('data-shipment', summaryItem.shipmentId);
						option.textContent = this.getLang('BOX_OPTION', { NUMBER: totalBoxIndex + 1 });

						++boxIndex;
						++totalBoxIndex;
					} else if (option) {
						option.parentNode.removeChild(option);
					}
				}
			}
		},

		disableBoxAdd: function(dir) {
			var boxAdd = this.getElement('boxAdd');

			boxAdd.toggleClass('adm-btn-disabled', !!dir);
		},

		getBoxShipmentOptionGroup: function(boxSelect, shipmentId) {
			var optgroupList = boxSelect.find('optgroup');
			var optgroupIndex;
			var optgroup;
			var result;

			if (optgroupList.length === 0) {
				result = boxSelect;
			} else {
				for (optgroupIndex = 0; optgroupIndex < optgroupList.length; optgroupIndex++) {
					optgroup = optgroupList[optgroupIndex];

					if (optgroup.getAttribute('data-id') == shipmentId) {
						result = $(optgroup);
						break;
					}
				}
			}

			return result;
		},

		getShipmentBoxProductCount: function(itemId) {
			var shipmentCollection = this.getShipmentCollection();
			var shipmentList = shipmentCollection.getActiveItems();
			var shipmentIndex;
			var shipmentElement;
			var shipment;
			var boxCollection;
			var boxList;
			var boxIndex;
			var boxElement;
			var box;
			var boxItem;
			var result = 0;

			for (shipmentIndex = 0; shipmentIndex < shipmentList.length; shipmentIndex++) {
				shipmentElement = shipmentList[shipmentIndex];
				shipment = shipmentCollection.getItemInstance(shipmentElement);
				boxCollection = shipment.getBoxCollection();
				boxList = boxCollection.getActiveItems();

				for (boxIndex = 0; boxIndex < boxList.length; boxIndex++) {
					boxElement = boxList[boxIndex];
					box = boxCollection.getItemInstance(boxElement);
					boxItem = box.getItemCollection().getItemById(itemId);

					if (boxItem != null) {
						result += boxItem.getCount();
					}
				}
			}

			return result;
		},

		getShipmentBoxCountSummary: function() {
			var shipmentCollection = this.getShipmentCollection();
			var shipmentList = shipmentCollection.getActiveItems();
			var shipmentIndex;
			var shipmentElement;
			var shipment;
			var boxCollection;
			var boxList;
			var result = [];

			for (shipmentIndex = 0; shipmentIndex < shipmentList.length; shipmentIndex++) {
				shipmentElement = shipmentList[shipmentIndex];
				shipment = shipmentCollection.getItemInstance(shipmentElement);
				boxCollection = shipment.getBoxCollection();
				boxList = boxCollection.getActiveItems();

				result.push({
					shipmentId: shipment.getId(),
					boxCount: boxList.length
				});
			}

			return result;
		},

		copyItemsToBox: function(box, items) {
			var boxItemCollection = box.getItemCollection();
			var boxItem;
			var isNewBoxItem;
			var itemIndex;
			var item;
			var itemValue;
			var boxValue;
			var itemId;

			for (itemIndex = 0; itemIndex < items.length; itemIndex++) {
				item = items[itemIndex];
				itemId = item.getId();
				itemValue = item.getValue();
				boxItem = boxItemCollection.getItemById(itemId);
				isNewBoxItem = false;

				if (boxItem == null) {
					isNewBoxItem = true;
					boxItem = boxItemCollection.addItem();
					boxItem.setId(itemId);
				}

				boxValue = this.modifyItemValueForBox(itemValue, boxItem.getValue());
				boxItem.setValue(boxValue);

				if (!isNewBoxItem) {
					this.refreshBoxCount(boxItem.getId());
				}
			}
		},

		modifyItemValueForBox: function(itemValue, boxItemValue) {
			var result = BX.merge({}, itemValue);
			var itemCount;
			var boxItemCount;

			result['COUNT_TOTAL'] = result['COUNT'];

			if (result['BOX_COUNT'] != null) {
				result['COUNT'] = Math.max(0, result['COUNT'] - result['BOX_COUNT']);
			}

			if (boxItemValue['COUNT'] != null) {
				itemCount = parseFloat(result['COUNT']) || 0;
				boxItemCount = parseFloat(boxItemValue['COUNT']) || 0;

				result['COUNT'] = Math.min(itemCount + boxItemCount, result['COUNT_TOTAL']);
			}

			return result;
		},

		getItemPlugin: function() {
			return OrderView.BasketItem;
		}

	}, {
		dataName: 'orderViewBasket',
		pluginName: 'YandexMarket.OrderView.Basket',
	});

})(BX, jQuery, window);