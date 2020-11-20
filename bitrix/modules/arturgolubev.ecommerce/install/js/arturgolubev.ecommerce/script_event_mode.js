window.ag_ec_request_count = 0;
window.ag_ec_request_active = 1;
window.ag_ec_request_sended = 0;

function ag_ecommerce_request(){
	if(!window.ag_ec_request_active){
		return false;
	}
	if(!window.ag_ec_request_count){
		return false;
	}
	
	// console.log('AG_Ecommerce: start r: c = ' + window.ag_ec_request_count + ', a = ' + window.ag_ec_request_active);
	
	window.ag_ec_request_active = 0;
	window.ag_ec_request_sended++;
	
	var x = window.location.pathname;
	
	BX.ajax({   
		url: '/bitrix/tools/arturgolubev.ecommerce/getscripts_v2.php',
		data: {'loc': x, 'rnum': window.ag_ec_request_sended},
		method: 'POST',
		dataType: 'script',
		timeout: 30,
		async: true,
		processData: true,
		scriptsRunFirst: true,
		// emulateOnload: true,
		start: true,
		cache: false,
		onsuccess: function(data){
			window.ag_ec_request_count--;
			window.ag_ec_request_active = 1;
			setTimeout('ag_ecommerce_request();', 1000);
		},
		onfailure: function(){
			window.ag_ec_request_count--;
			window.ag_ec_request_active = 1;
		}
	});
}


document.addEventListener('DOMContentLoaded', function(){
	BX.addCustomEvent('onAjaxSuccess', function(e, params){
		// console.log('AG_Ecommerce: BX ajax EVENT');
		
		if(typeof params === 'object' && params !== null)
		{
			if(params.url)
			{
				var work = 1;
				if(params.url == '/bitrix/tools/arturgolubev.ecommerce/getscripts_v2.php') work = 0;
				
				if(work)
				{
					window.ag_ec_request_count++;
					ag_ecommerce_request();
				}
			}
		}
		else
		{
			window.ag_ec_request_count++;
			ag_ecommerce_request();
		}
	});

	try {
		$(document).ajaxSuccess(function(event, request, settings){
			// console.log('AG_Ecommerce: JQuery ajax EVENT');
			// console.log(settings.url);
			
			var work = 1;
			/* ipol.sdek, ipol.kladr, intec.universe*/
			if(settings.url.indexOf("ipol.") >= 0) work = 0;
			if(settings.url.indexOf("intec.") >= 0) work = 0;
			
			// console.log(work);
			
			if(work)
			{
				window.ag_ec_request_count++;
				ag_ecommerce_request();
			}
		});
	}catch(err){
		// console.log("AG_Ecommerce: JQuery ajax not defined");
	}
});

BX.ready(function(){
	// console.log('AG_Ecommerce: start page request');
	
	window.ag_ec_request_count++;
	ag_ecommerce_request();
});