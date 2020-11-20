;(function(window, document){
    'use strict';

    $(window).load(function(){
        if(window.poscreditSettings.order) {
            window.poscreditSettings.order = window.poscreditSettings.order.map(function(order){
                order.price = parseFloat(order.price);
                order.quantity = parseInt(order.quantity);
                return order
            });
        }

        $(document).on('click', '.poscredit-oneclick-button-submit', function(){
            if($('input#fullName.poscredit-oneclick-button-control').val()) {
                var clientName = $('input#fullName.poscredit-oneclick-button-control').val();
                window.poscreditSettings.fullName = clientName;
                window.poscreditData['ORDER']['FIO'] = clientName;
            }
            if($('input#phone.poscredit-oneclick-button-control').val()) {
                var clientPhone = $('input#phone.poscredit-oneclick-button-control').val();
                window.poscreditSettings.phone = clientPhone;
                window.poscreditData['ORDER']['PHONE'] = clientPhone;
            }
        });

        $(document).on('click', '#poscredit_oneclick_button', function(event){
			event.preventDefault();

			window.poscreditData['ACTION'] = "GET_SETTINGS";

			$.ajax({
                url: window.poscreditUrl,
                cache: false,
				dataType: "json",
                data: window.poscreditData,
                success: function(result) {
					if(result.settings.accessID) {
						window.poscreditAccessID = result.settings.accessID;
						window.poscreditTradeID = result.settings.tradeID;
						window.poscreditOrderNum = result.settings.orderID;

						poscredit_init();						
					} else {
						alert('Error...');
					}
                }
            });
        });
    });

})(window, document);