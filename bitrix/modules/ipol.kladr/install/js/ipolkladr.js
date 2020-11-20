var KladrJsObj = {
	kladrdivid:                "ipolkladrform", //div в котором форма кладр
	container:                 false,
	city:                      false,
	street:                    false,
	building:                  false,
	room:                      false,
	noreload:                  false,
	arAdrSeq:                  ['region', 'city', 'street', 'building'],	

	ipolkladrfname:            false, //имя поля адреса
	ipolkladrlocation:         false, //имя поля местоположение
	roomnum:                   false,
	 
	lastobject:                {}, //для хранения объектов кладр в время аякс запросов в компоненте
	
	map:                       null,
	map_created:               false,
	placemark:                 null,
	
	kladrtownid:               false,
	
	lastLocationCode:          false, // bitrix location code
	profile:                   {isUsed: false, current: false, isChanged: false}, // user profile
	
	fancyForm:                 false,	
	newVersionTemplate:        false,
	zipPropId:                 false, // id поля индекса
	locPropId:                 false, // id поля локейшена
	locations_not_rus_checked: false, // по умолчанию чекбокс "Не Россия выключен"
	saveLoc:                   false, // храним локейшен, который пришел отбитрикса по названию кладра - debug purpose only
	kladr_city_obj:            false, // храним город от кладра
	smart_locid:               'SAdr_city', // kladr location input

	// Custom events:
	// onMapCreated - RunForm
	
	FormKladr: function(Klobj){//вызывает функции которые установят кладр, ajax - true или false	
		
		ajax=false;
		if(Klobj && !$.isEmptyObject(Klobj) && Klobj.ajax)
			ajax=Klobj.ajax;
				
		var notRussia=false;
		if(!$.isEmptyObject(Klobj) && !$.isEmptyObject(Klobj.kladr)){
			notRussia=Klobj.kladr.NotRussia;	
		}
		else{
			notRussia=$('.NotRussia:last').val();
			$('.NotRussia:last').remove();
		}
		
		if(notRussia==true)//ушатываем кладр, если это не Россия
			{KladrJsObj.FuckKladr();return;}

		//Если не пришло по-новому, пробуем по старому
		if(typeof(Klobj.kladr)=='undefined'){
			Klobj.kladr={'kltobl':{}};
		}
		
		if($('.kltobl:last').length && $.isEmptyObject(Klobj.kladr.kltobl))
			{
				t=$('.kltobl:last').text();
				Klobj.kladr.kltobl=t?JSON.parse(t):{};
				$('.kltobl:last').remove();
			}
		else{
			if(typeof(Klobj.kladr.kltobl)=='string' && Klobj.kladr.kltobl!='')
				Klobj.kladr.kltobl=JSON.parse(Klobj.kladr.kltobl);
			}// Теперь город в Klobj.kladr.kltobl
		
		if(!KladrJsObj.kladr_city_obj && !$.isEmptyObject(Klobj.kladr.kltobl))
			KladrJsObj.kladr_city_obj = Klobj.kladr.kltobl;
		
		//Итак ставим кладр для Klobj.kladr.kltobl
		//определяем поле адреса
		KladrJsObj.ipolkladrfname=false;
		KladrJsObj.prop_forms=new Array();
		
		if(typeof(KladrSettings.arNames)!="undefined"){
			KladrSettings.arNames.forEach(function(entry) {//определим какое поле из списка полей присутствует(поля для физиков и юриков)
				if($('[name ='+entry+']').length>0) 
					KladrJsObj.prop_forms[KladrJsObj.prop_forms.length]=entry;
				
			});
			KladrJsObj.ipolkladrfname=KladrJsObj.prop_forms.shift();
		}
		else console.warn('ipol.kladr error: no address fields found');
				
		//определяем поле местоположения
		KladrJsObj.ipolkladrlocation=false;
		
		//определяем поле индекса
		KladrJsObj.ipolkladrzip=false;

		// ставим zip в поле, если id поля известно (пока новый шаблон)
		if(typeof(Klobj.kladr.kltobl.zip) != "undefined" && KladrJsObj.zipPropId) {
			if(!$.isEmptyObject(KladrJsObj.lastobject.city) && !$.isEmptyObject(Klobj.kladr.kltobl)) {

				// только если город сменился
				if(KladrJsObj.lastobject.city.id != Klobj.kladr.kltobl.id) {
						$("[name='ORDER_PROP_"+KladrJsObj.zipPropId+"']").val(Klobj.kladr.kltobl.zip);				
				}
			
			} else {
				// либо последний объект не известен
				$("[name='ORDER_PROP_"+KladrJsObj.zipPropId+"']").val(Klobj.kladr.kltobl.zip);	
			}
		}		
		
		if(typeof(KladrJsObj.ipolkladrfname)=="undefined" || KladrJsObj.ipolkladrfname==false){
			console.warn('ipol.kladr error: ipolkladrfname not found');
			return;
		}		
		
		if(!$.isEmptyObject(Klobj.kladr) && !$.isEmptyObject(Klobj.kladr.kltobl) )
		{//восстановление старого объекта 
			KladrJsObj.UnBlockAdrProps();//убирает readonly - срабатывает по аяксу если не было начального города
			
			obj=Klobj.kladr.kltobl;
			if($.isEmptyObject(KladrJsObj.lastobject) || obj.id!=KladrJsObj.kladrtownid ){
				//если это новая страница или если город поменялся

				KladrJsObj.lastobject={};
				KladrJsObj.lastobject[obj.contentType]=obj;
				KladrJsObj.roomnum='';				
								
				// do not delete the address, it could be restored from the profile of the previous order
				if ((!KladrJsObj.profile.isUsed && KladrJsObj.kladrtownid) || (KladrJsObj.profile.isUsed && !KladrJsObj.profile.isChanged))				
				//if(KladrJsObj.kladrtownid)				
					KladrJsObj.CleanAdrProps();
				
				KladrJsObj.kladrtownid=obj.id;
				KladrJsObj.contentType=obj.contentType;

			}	
			
		}
		else //если объект пуст
			if($.isEmptyObject(KladrJsObj.lastobject) && KladrSettings.notShowForm){
				//если не нашли город, и не надо показывать форму
				KladrJsObj.SetAdrProp(KladrJsObj.ipolkladrfname,BX.message('RunFormnoktoblattr'));
				KladrJsObj.BlockAdrProps();
				return;
			}

		//узнаем, что там с адресом
		var adr="";	
		adr=$('[name ='+KladrJsObj.ipolkladrfname+']').val(); // Смотрим по улице, она всегда есть
		if(adr==BX.message('RunFormnoktoblattr')){
			adr='';
			$('[name ='+KladrJsObj.ipolkladrfname+']').val('');
		}	
		
		if(adr) //если есть предустановленный адрес
		{	//тогда не делать кладр, а выводить кнопку "изменить адрес"
			KladrJsObj.prop_forms.forEach(function(entry) {
				adr+=', '+$('[name ='+entry+']').val();
			});
			
			KladrJsObj.BlockAdrProps();
			
			$('[name ='+KladrJsObj.ipolkladrfname+']').after('<br><a class="nobasemessage" href="javascript:KladrJsObj.nobasemessage();">'+BX.message('CHANGEADR')+'</a><br>');
			
		}
		else{//адреса нет
			if(typeof($.fias)=="object") {
				KladrJsObj.PrintForm();
				KladrJsObj.RunForm();
			}
		}

	},

	PrintForm: function(){//выводит html код формы	
		KladrJsObj.map_created=false;//карты пока еще нет
		
		var inpclass,//класс для улицы
			disabled;//запрет для улиц
		
		inpclass = KladrJsObj.contentType=='city' ? "top" : "middleinput";
			
		formK = '<div id="'+KladrJsObj.kladrdivid+'" class="ipolkladrform">';
        
		oncl = '';
		if(KladrSettings.MakeFancy) oncl = 'javascript:KladrJsObj.FancyForm();';
		
		formK+='<div class="fancyback">';
		formK+='	</div><!-- от fancy -->';
		
			formK+='<div class="fancyform">';
			formK+='	<form class="js-form-address" onclick="'+oncl+'">';
				
				//город
				if(KladrJsObj.contentType!='city'){
					formK+='<div class="top"><input name="city" type="text" placeholder="'+BX.message('TAPETOWN')+'"></div>';	
					disabled = 'disabled="disabled"';
				}
				
				//улица
				if(KladrJsObj.lastobject.street) disabled = '';
				if(KladrJsObj.prop_forms.length==3) disabled += ' name="'+KladrJsObj.ipolkladrfname+'" id="'+KladrJsObj.ipolkladrfname+'" ';
				formK+='   <div class="'+inpclass+'"><input name="street" type="text" placeholder="'+BX.message('TAPESTREET')+'" '+ disabled +'></div>';
				
				//дом
				disabled = 'disabled="disabled"';
				if(KladrJsObj.lastobject.building) disabled = '';
				if(KladrJsObj.prop_forms.length==3) disabled += ' name="'+KladrJsObj.prop_forms[0]+'" id="'+KladrJsObj.prop_forms[0]+'" ';
				formK+='   <div class="bottom"><input name="building" type="text" placeholder="'+BX.message('TAPENUMPER')+'" '+disabled+'>';
				
				//квартира
				disabled = 'disabled="disabled"';
				if(KladrJsObj.lastobject.room) disabled = '';
				if(KladrJsObj.prop_forms.length==3) disabled += ' name="'+KladrJsObj.prop_forms[2]+'" id="'+KladrJsObj.prop_forms[2]+'" ';
				// formK+='<input class="room" type="text" placeholder="'+BX.message('TAPEROOM')+'" name="room" disabled="disabled"></div>';
				formK+='<input class="room placeholdered" type="text" name="room" disabled="disabled" value="'+BX.message('TAPEROOM')+'"></div>';
			   
			formK+=' </form>';
			
			//карта 
			if(KladrSettings.ShowMap) formK+=' <div id="map" class="panel-map"></div>';
			if(KladrSettings.ShowAddr){	
				formK+=' <div class="addition">';
				formK+='   <div class="block">';
				formK+='     <p id="address" class="value"></p>   ';                 
				formK+='   </div>';
				formK+='   <div class="block" style="display:none;">';
				formK+='                 <p class="title">Выбранный объект</p>';
				formK+='                 <ul class="js-log">';
				formK+='                     <li id="id" style="display: none;"><span class="name">Код:</span> <span class="value"></span></li>';
				formK+='                     <li id="zip" style="display: none;"><span class="name">Почтовый индекс:</span> <span class="value"></span></li>';
				formK+='                     <li id="name" style="display: none;"><span class="name">Название:</span> <span class="value"></span></li>';
				formK+='                     <li id="type" style="display: none;"><span class="name">Подпись:</span> <span class="value"></span></li>';
				formK+='                     <li id="typeShort" style="display: none;"><span class="name">Подпись коротко:</span> <span class="value"></span></li>';
				formK+='                     <li id="contentType" style="display: none;"><span class="name">Тип объекта:</span> <span class="value"></span>';
				formK+='                     <li id="okato" style="display: none;"><span class="name">ОКАТО:</span> <span class="value"></span>';
				formK+='                 </ul>';
				formK+='   </div>';
				formK+='  </div>';
			}
			if(KladrSettings.MakeFancy) formK+='<div class="unfancybutton" onclick="KladrJsObj.UnFancyForm()">'+BX.message('SAVEADR')+'</div>';
		formK+='	</div><!-- от fancy -->';
		formK+='</div><!-- от '+KladrJsObj.kladrdivid+' -->';
		formK+='<input name="ipolkladrnewcity" class="ipolkladrnewcity" type="hidden">';
		formK+='<input name="ipolkladrnewregion" class="ipolkladrnewregion" type="hidden">';
		formK+='<input name="ipolkladrlocation" class="ipolkladrlocation" type="hidden">';

		$('[name ='+KladrJsObj.ipolkladrfname+']').after(formK);
		KladrJsObj.HideAdrProps();
	},

	RunForm: function(){
		//ищем вставленный html формы
		KladrJsObj.container = $('#'+KladrJsObj.kladrdivid);
		if(!KladrJsObj.container.length)
		{
			console.warn('kladrdivid not found');
			return;
		}
		
		//ищем поля в форме
		KladrJsObj.city = KladrJsObj.container.find('[name="city"]');
		KladrJsObj.street = KladrJsObj.container.find('[name="street"]');
		KladrJsObj.building = KladrJsObj.container.find('[name="building"]');
		KladrJsObj.room = KladrJsObj.container.find( '[name="room"]' );
				
		//if(KladrSettings.kladripoltoken)
		//	$.fias.url = "https://kladr-api.com/api.php";
		
		//первая установка
		$.fias.setDefault({
			parentInput: '.js-form-address',
			verify: true,
			token: KladrSettings.kladripoltoken,
			labelFormat: function (obj, query) {
				var label = '';

				var name = obj.name.toLowerCase();
				query = query.name.toLowerCase();

				var start = name.indexOf(query);
				start = start > 0 ? start : 0;

				if (obj.typeShort) {
					label += obj.typeShort + '. ';
				}

				if (query.length < obj.name.length) {
					label += obj.name.substr(0, start);
					label += '<strong>' + obj.name.substr(start, query.length) + '</strong>';
					label += obj.name.substr(start + query.length, obj.name.length - query.length - start);
				} else {
					label += '<strong>' + obj.name + '</strong>';
				}

				if (obj.parents) {
					for (var k = obj.parents.length - 1; k > -1; k--) {
						var parent = obj.parents[k];
						if (parent.name) {
							if (label) label += '<small>, </small>';
							label += '<small>' + parent.name + ' ' + parent.typeShort + '.</small>';
						}
					}
				}

				return label;
			},
			change: function (obj) {
				
				if(obj){
					//пишем объект
					$.fias.getAddress('.js-form-address', function (objs) {
						$.extend(KladrJsObj.lastobject, objs);
					});
					
					//изменен город
					if(obj.contentType==$.fias.type.city ){
						
						if(!KladrJsObj.noreload && KladrSettings.hideLocation){
							/*//чистить улицу и дом
								KladrJsObj.lastobject={'city':KladrJsObj.lastobject.city};
								KladrJsObj.roomnum=false;
							if(KladrJsObj.ipolkladrlocation){//если есть куда писать
								$('.ipolkladrlocation').val(KladrJsObj.ipolkladrlocation);//чтобы php знал какой реквест ловить
								//запишем реквест
								$('.ipolkladrnewcity').val((obj.name));
								
								if(!$.isEmptyObject(obj.parents) && obj.parents[0].type!=BX.message('town')) 
								{//узнаем область				
									if(obj.parents[0].type==BX.message('RESP'))
										cityregion=obj.parents[0].type+' '+obj.parents[0].name;
									else
										cityregion=obj.parents[0].name+' '+obj.parents[0].type;
									$('.ipolkladrnewregion').val((cityregion));
								}
								
								if(!KladrJsObj.fancyForm) {
									
									if(KladrJsObj.newVersionTemplate)
										BX.Sale.OrderAjaxComponent.sendRequest();									
									else
										submitForm();
								
								}

							}*/	
						}
						
						else{
							KladrJsObj.street.removeAttr("disabled");
						}
						
					}
						
					/*убрать проверку на obj*/
					switch (obj.contentType) {
							case $.fias.type.city:
							KladrJsObj.street.removeAttr("disabled");
							break;

						case $.fias.type.street:
							KladrJsObj.building.removeAttr("disabled");
							break;

						case $.fias.type.building:
							KladrJsObj.room.removeAttr("disabled");
							break;
					} 
			
					setLabel($(this), obj.type);
				}
				
				KladrJsObj.log(obj);
				KladrJsObj.addressUpdate();
				KladrJsObj.mapUpdate();

			},
			
			check: function (obj) {

				switch ($(this).attr("name")) {
					case $.fias.type.city:
						if(!obj) $(this).val('');
					break;

					case $.fias.type.street:
						KladrJsObj.building.removeAttr("disabled");
					break;

					case $.fias.type.building:
						KladrJsObj.room.removeAttr("disabled");
					break;
					
				
				}
				if(!obj) KladrJsObj.addressUpdate();
			},
			checkBefore: function () {
				var $input = $(this);

				if (!$.trim($input.val())) {
					
					KladrJsObj.log(null);
					KladrJsObj.addressUpdate();
					KladrJsObj.mapUpdate();
					return false;
				}
				
			}
		});
		
		//иницируем форму
		if(KladrJsObj.city) KladrJsObj.city.fias({'type': $.fias.type.city});
		if(KladrJsObj.contentType=='region')//если тип есть и он область
			KladrJsObj.city.fias({'parentType': 'region','parentId':KladrJsObj.kladrtownid});
		
		KladrJsObj.street.fias('type', $.fias.type.street); 
		if(KladrJsObj.contentType=='city')//если тип есть и он город
			KladrJsObj.street.fias({'parentType': 'city','parentId':KladrJsObj.kladrtownid});
		
		KladrJsObj.building.fias('type', $.fias.type.building);
		
		// Включаем получение родительских объектов для населённых пунктов
		KladrJsObj.city.fias('withParents', true);
		KladrJsObj.street.fias('withParents', true);

		//если карты еще нет поставим ее
		if(KladrSettings.ShowMap && !KladrJsObj.map_created){
			ymaps.ready(function () {
				if (KladrJsObj.map_created) return;
				KladrJsObj.map_created = true;
				
				KladrJsObj.map = new ymaps.Map('map', {
					center: [55.76, 37.64],
					zoom: 2,
					controls: []
				});

				KladrJsObj.map.controls.add('zoomControl', {
					position: {
						right: 10,
						top: 10
					}
				});
				
				// Only creation
				if (!KladrJsObj.placemark) {
					KladrJsObj.placemark = new ymaps.Placemark([55.76, 37.64], {}, {});		
				}
				
				//и расставляем ранее стоявшие, как карта загрузилась
				KladrJsObj.setFromDefaultObj();
				
				// onMapCreated event
				if (typeof (KladrSettings.handlers.onMapCreated) != 'undefined' && KladrSettings.handlers.onMapCreated.length > 0)				
				{
					KladrJsObj.executeFunctionByName(KladrSettings.handlers.onMapCreated, window);
				}
			});
		}
		else{
			KladrJsObj.setFromDefaultObj();
		}
		
		function setLabel($input, text) {
			text = text.charAt(0).toUpperCase() + text.substr(1).toLowerCase();
			$input.parent().find('label').text(text);
		}
		
		KladrJsObj.room.focusout(function(){//ввели номер квартиры
			// записать комнату
			KladrJsObj.roomnum = KladrJsObj.room.val();
			if(KladrJsObj.roomnum && KladrJsObj.roomnum!=BX.message('TAPEROOM')){
				KladrJsObj.addressUpdate();
			}
			else{
				KladrJsObj.room.val(BX.message('TAPEROOM'));
				KladrJsObj.room.addClass('placeholdered');
			}
				
			
		});
		
		KladrJsObj.room.focusin(function(){//ввели номер квартиры
			// записать комнату
			if(KladrJsObj.room.val()==BX.message('TAPEROOM')){
				KladrJsObj.room.val('');
				KladrJsObj.room.removeClass('placeholdered');
			}		
		});
	},//не RunForm
	
	
	log: function(obj) {
		var log, i;

		$('.js-log li').hide();

		for (i in obj) {
			log = $('#' + i);

			if (log.length) {
				log.find('.value').text(obj[i]);
				log.show();
			}
		}
	},
		
	mapUpdate: function() {
		if(!KladrSettings.ShowMap) return;
		var zoom = 2;
		
		var address = $.fias.getAddress('.js-form-address', function (objs) {
			var result = '';
			
			// if(!KladrSettings.hideLocation){
				//при работе в старом режиме нет городов или областей, поэтому сначала дописываем
				zoom = 7;
				// objs=Object.assign(KladrJsObj.lastobject, objs);
				objs = $.extend({},KladrJsObj.lastobject,objs);
			// }
			
			if(!objs['city'] && KladrJsObj.kladr_city_obj)
				objs['city'] = KladrJsObj.kladr_city_obj;				
			
			KladrJsObj.arAdrSeq.forEach(function(item, i, arr) {
				if(!$.isEmptyObject(objs[item])){
					obj=objs[item];
					
					var name = '',
						cityregion = '',
						type = '';
						
					if ($.type(obj) === 'object') {
						name = obj.name;
						type = ' ' + obj.type;

						switch (obj.contentType) {
							case $.fias.type.city:
								zoom = 10;
								break;

							case $.fias.type.street:
								zoom = 13;
								break;

							case $.fias.type.building:
								zoom = 16;
								break;
						}
					}
					else {
						name = obj;
					}
					
					if (obj.contentType == $.fias.type.city && !$.isEmptyObject(obj.parents)) 
						cityregion=obj.parents[0].typeShort+'. '+obj.parents[0].name;
					
					
					if (result) result += ', ';
					if(cityregion) result += cityregion +', '
					result += type +' '+ name;
				}
			});

			return result;
		});			
		if(!address){
			address=BX.message('RF');
			zoom=2;
		}
		
		if (address && KladrJsObj.map_created) {
			var geocode = ymaps.geocode(address);
			geocode.then(function (res) {	
				KladrJsObj.map.geoObjects.each(function (geoObject) {
					if (geoObject.geometry.getType() == 'Point') {
						KladrJsObj.map.geoObjects.remove(geoObject);
					}						
				});

				var coords = res.geoObjects.get(0).geometry.getCoordinates();
				
				if (!KladrJsObj.placemark)
					KladrJsObj.placemark = new ymaps.Placemark(coords, {}, {});
				else
					KladrJsObj.placemark.geometry.setCoordinates(coords);
					
				KladrJsObj.map.geoObjects.add(KladrJsObj.placemark);
				KladrJsObj.map.setCenter(coords, zoom);
			});
		}
	},

	addressUpdate: function() {
		var address = $.fias.getAddress('.js-form-address',function (objs) {
			var result = '',
				zip='';
				
			// if(!KladrSettings.hideLocation){
				//при работе в старом режиме нет городов или областей, поэтому сначала дописываем
				zoom = 7;
				// objs=Object.assign(KladrJsObj.lastobject, objs);
				objs = $.extend({},KladrJsObj.lastobject,objs);
			// }
			
			if(!objs['city'] && KladrJsObj.kladr_city_obj)
				objs['city'] = KladrJsObj.kladr_city_obj;				
			
			if(Object.keys(objs).length <=1) return ''; // не вписывать город в адрес
			
			KladrJsObj.arAdrSeq.forEach(function(item, i, arr) {
				if(!$.isEmptyObject(objs[item])){
					obj=objs[item];
					var name = '',
						cityregion = '',
						type = '';

					if ($.type(obj) === 'object') {
						name = obj.name;
						type = ' ' + obj.typeShort+'.';
						if(obj.zip)
							zip = obj.zip;												
						
						if (!KladrSettings.dontAddRegionToAddr) {
							if (obj.contentType == $.fias.type.city && !$.isEmptyObject(obj.parents) && obj.parents[0].type!=BX.message('town')) {	
								var arrP = [];
									
								for (i in obj.parents) {
									arrP[i] = obj.parents[i].typeShort + '. ' + obj.parents[i].name;	
								}								
								cityregion = arrP.join(", ");
							}	
						}						
					}
					else {
						name = obj;
					}

					if (result) result += ', ';
					if(cityregion) result += cityregion +', '
					result += type +' '+ name;
				}
			});
			
			if(zip) 
			{
				if($('[name='+KladrJsObj.ipolkladrzip+']')) $('[name='+KladrJsObj.ipolkladrzip+']').val(zip);//фигня, нужно еще запускать смену скрипта
				
				if (!KladrSettings.dontAddZipToAddr)
					result = zip + ', ' + result;
				
				// ставим zip в поле, если id поля известно
				if(KladrJsObj.zipPropId)
					$("[name='ORDER_PROP_"+KladrJsObj.zipPropId+"']").val(zip);				
				
			}
			if(KladrJsObj.roomnum) result+=', '+BX.message('kv')+KladrJsObj.roomnum;
			
			return result;
		});
		
		KladrJsObj.WriteAdr(address);
		$('#address').text(address);
	},
		
	setFromDefaultObj: function(){
		if(!$.isEmptyObject(KladrJsObj.lastobject)){
			
			$.each(KladrJsObj.lastobject, function (i, obj){
				if(typeof(obj) =='string'){
					KladrJsObj["street"].val(obj);
				}
			});
			
			KladrJsObj.noreload=true;
			$.fias.setValues(KladrJsObj.lastobject, '#'+KladrJsObj.kladrdivid);
			KladrJsObj.noreload=false;
			
			// if(!KladrSettings.hideLocation)
			// {
				KladrJsObj.mapUpdate();
				KladrJsObj.addressUpdate();
			// }
			
			if(KladrJsObj.roomnum) KladrJsObj.room.val(KladrJsObj.roomnum);
			
		}
		else{
			KladrJsObj.mapUpdate();
			KladrJsObj.addressUpdate();
		}
	},
		
	
	HideAdrProps: function(){
		$.each(KladrJsObj.prop_forms, function (i, obj){//адрес трем совсем			
			$('[data-property-id-row='+obj.substr(11)+']').hide();
		});
		$('[name ='+ KladrJsObj.ipolkladrfname+']').hide();//поле улицы только стираем
		$('[data-property-id-row='+KladrJsObj.ipolkladrfname.substr(11)+']').find('.label').text('Адрес доставки');
	},
	
	ShowAdrProps: function(){
		if(typeof(KladrJsObj.prop_forms)!='undefined' && KladrJsObj.prop_forms.length){
			$.each(KladrJsObj.prop_forms, function (i, obj){
				$('[data-property-id-row='+obj.substr(11)+']').show();	
			});
		}
		if(KladrJsObj.ipolkladrfname){
			$('[name ='+ KladrJsObj.ipolkladrfname+']').show();
			$('[data-property-id-row='+KladrJsObj.ipolkladrfname.substr(11)+']').find('.label').text('Улица');
		}
	},
	
	BlockAdrProps: function(){
		a=$.merge([],KladrJsObj.prop_forms);
		a=$.merge(a,Array(KladrJsObj.ipolkladrfname));
		$.each(a, function (i, obj){
			$('[name ='+ obj+']').attr("readonly","readonly");
		});
	
	},
	UnBlockAdrProps: function(){
		a=[];
		if(typeof(KladrJsObj.ipolkladrfname)!='undefined' && KladrJsObj.ipolkladrfname)
			a=$.merge(a,Array(KladrJsObj.ipolkladrfname));
		
		if(typeof(KladrJsObj.prop_forms)!='undefined' && KladrJsObj.prop_forms.length)
			a=$.merge(a,KladrJsObj.prop_forms);
		
		if(a.length){	
			$.each(a, function (i, obj){
				if($('[name ='+ obj+']').attr("readonly")=='readonly') $('[name ='+obj+']').removeAttr("readonly");
			});
		}
	},
	
	CleanAdrProps: function(){
		a=$.merge([],KladrJsObj.prop_forms);
		allProps=$.merge(a,Array(KladrJsObj.ipolkladrfname));
		$.each(allProps, function (i, obj){
			KladrJsObj.SetAdrProp(obj,'');
		});
	},
	SetAdrProp:function(prop,val){		
		if($('textarea[name ='+prop+']').length){
			// if($('#ORDER_FORM').length)
				$('textarea[name ='+prop+']').text(val);
			// else
				$('textarea[name ='+prop+']').val(val);	
		}
		if($('input[name = '+prop+']').length) $('input[name ='+prop+']').val(val);
	},
	
	WriteAdr: function(address){
		if(KladrJsObj.prop_forms.length>0){
			adr=address.split(',');
			if(typeof(adr[2])!='undefined');
				KladrJsObj.SetAdrProp(KladrJsObj.ipolkladrfname,adr[2]);
			if(typeof(adr[3])!='undefined');
				KladrJsObj.SetAdrProp(KladrJsObj.prop_forms[0],adr[3]);
			if(typeof(adr[4])!='undefined');
				KladrJsObj.SetAdrProp(KladrJsObj.prop_forms[1],adr[4]);
		}
		else{
			KladrJsObj.SetAdrProp(KladrJsObj.ipolkladrfname,address)
		}
	},
	
	FancyForm: function(){
		if(!KladrJsObj.fancyForm)
		{
			$('.fancyback').fadeIn();
			$('.addition').addClass('fancyadd');
			$('.unfancybutton').addClass('fancybut');
			$('.fancyform').css('zIndex',10000001);
			$('#kladr_autocomplete ul').css('zIndex',10000001);
			$('#kladr_autocomplete .spinner').css('zIndex',10000001);

			KladrJsObj.fancyForm=true;
		}
	},
	
	UnFancyForm: function(){
		if(KladrJsObj.fancyForm)
		{
			$('.fancyback').fadeOut('fast',function(){
				$('.addition.fancyadd').removeClass('fancyadd');
				$('.unfancybutton.fancybut').removeClass('fancybut');
				$('.fancyform').css('zIndex',1);
				$('#kladr_autocomplete ul').css('zIndex',9999);
			$('#kladr_autocomplete .spinner').css('zIndex',9999);
			
				if(typeof(UnFancyKladr)=="function") UnFancyKladr();
				KladrJsObj.fancyForm=false;
					// если менялся город давай выполним функцию
					if(typeof(KladrJsObj.submitKladr)=='function') {
						KladrJsObj.submitKladr({'fulladdr':$('#address').text(),"kladrobj":KladrJsObj.lastobject});	
					} else {
						if(KladrJsObj.newVersionTemplate)
							BX.Sale.OrderAjaxComponent.sendRequest();
						else
							submitForm();
					}
					
			});
		}
	},
	
	nobasemessage: function(){
		//вызывается если нажать на кнопку "изменить адрес", убирает эту кнопку и вызывает форму
		KladrJsObj.UnBlockAdrProps();
		KladrJsObj.CleanAdrProps();
		$('.nobasemessage').remove();
		$('.nobasemessage_adr').remove();
		KladrJsObj.FormKladr({"ajax":false});//перезагружаем стартовы с пустым адресом
	},

	checkErrors: function(){
			if(KladrSettings.kladripoladmin){
				if (!window.jQuery) {alert(BX.message('nojquery'));}
			}
			if (typeof($.fias)!="object") { // значит неверно подключился
				$("script[src='/bitrix/js/ipol.kladr/jquery.fias.min.js']").remove();
				KladrJsObj.addScript("/bitrix/js/ipol.kladr/jquery.fias.min.js");				
				KladrJsObj.setCommerceToken.checker();
			}
	},

	FuckKladr: function(){
		//убрать кнопку или форму
		if($('#'+KladrJsObj.kladrdivid).length) $('#'+KladrJsObj.kladrdivid).remove();//убрать форму
		else if($('.nobasemessage').length) $('.nobasemessage').remove();//убрать кнопку
		KladrJsObj.ShowAdrProps();
		KladrJsObj.UnBlockAdrProps();
	},
	
	// дописываем в DOM checkbox - "Не Россия"
	initNotRusCheckbox: function(afterID){
		
		$(afterID).after('<input type="checkbox" id="SAdr_notrus_checkid" /> '+BX.message('notrussia'));
		$('#SAdr_notrus_checkid').prop("checked", KladrJsObj.locations_not_rus_checked);
				
		$("#SAdr_notrus_checkid").change(function(){
			
			if($(this).prop("checked")){
				KladrJsObj.locations_not_rus_checked = true;
			}
			else {
				KladrJsObj.locations_not_rus_checked = false;
			}
			
			BX.Sale.OrderAjaxComponent.sendRequest();
			
		});		

	},		

	changeLocationCode: function(code){
		$('[name=ORDER_PROP_'+KladrJsObj.locPropId+']').attr("value",code).val(code);
	},	
	
	getBitrixLocationCodeByName: function(city,region,type){
		
		var msg   = 'ipolkladrlocation=print&ipolkladrnewcity='+city+'&ipolkladrnewregion='+region+'&ipolkladrnewtype='+type;
		
		if(KladrSettings.country_rus_id)
			msg = msg + '&country_rus_id='+KladrSettings.country_rus_id; 
		if(KladrSettings.country_rus_code)
			msg = msg + '&country_rus_code='+KladrSettings.country_rus_code;

		$.ajax({
			type: 'GET',
			url: '/bitrix/js/ipol.kladr/getCode.php',
			data: msg,
			success: function(dat){
				
				if(dat && dat!='') {
					
					var obj = jQuery.parseJSON(dat);
					KladrJsObj.changeLocationCode(obj.code);
										
					// пришел регион или район вместо города
					KladrJsObj.saveLoc = obj;
					
					BX.Sale.OrderAjaxComponent.sendRequest();
					
				} else {
					console.warn('bad location code');
				}					

			}
		});
		
	},	
	
	initLocationInput: function(){
				
		//инициализирует новое поле "Местоположение"
		$("#"+KladrJsObj.smart_locid).fias({
			type: $.fias.type.city,
			withParents: true,
			verify: true,
			token: KladrSettings.kladripoltoken,						
			select: function (obj) {

				if(!$.isEmptyObject(obj) && obj.name){
					
					var kladrRegion='';
					
					var kladrRegionArr=[];
					obj.parents.forEach(function(el, index, array){

						if(el.type==BX.message('RESP'))
							kladrRegionArr[index] = el.type+' '+el.name;
						else if(el.type=="Город")
							kladrRegionArr[index] = el.name;							
						else
							kladrRegionArr[index] = el.name+' '+el.type.toLowerCase();
	
					});		

					kladrRegion = kladrRegionArr.join(',');
					KladrJsObj.getBitrixLocationCodeByName(obj.name, kladrRegion, obj.type.toLowerCase());
					
				}
				
			},			
			
			check: function(obj) {
							
				if(!$.isEmptyObject(obj) && obj.name){
					
						var objq = {};
						objq.type = $.fias.type.city;
						objq.token = KladrSettings.kladripoltoken;
						objq.url = "https://kladr-api.com/api.php";
						objq.query = obj.name;
						objq.withParent = true;

						$.fias.api(objq, function(answer){
							
							answer.forEach(function(el, index){
								if(obj.id == el.id)
									obj = el;

							})
							
							KladrJsObj.kladr_city_obj = obj;
							KladrJsObj.setvalueLocationInput(obj);
							var kladrRegion='';
							
							var kladrRegionArr=[];
							obj.parents.forEach(function(el, index, array){

								if(el.type==BX.message('RESP'))
									kladrRegionArr[index] = el.type+' '+el.name;
								else if(el.type=="Город")
									kladrRegionArr[index] = el.name;							
								else
									kladrRegionArr[index] = el.name+' '+el.type.toLowerCase();
			
							});		

							kladrRegion = kladrRegionArr.join(',');
							KladrJsObj.getBitrixLocationCodeByName(obj.name, kladrRegion, obj.type.toLowerCase());							
							
						})

				} else {				
					if(KladrJsObj.kladr_city_obj)
						KladrJsObj.setvalueLocationInput(KladrJsObj.kladr_city_obj);
					else
						KladrJsObj.setvalueLocationInput({name: ''});					
				}
	
			},
			
			change: function (obj) {
				//
				if(!$.isEmptyObject(obj))
					KladrJsObj.kladr_city_obj=obj;
			
			},
			
			close: function () {
				//				
			},
			
			labelFormat: function (obj, query) {
				var label = '';

				var n = obj.name.toLowerCase();
				query = query.name.toLowerCase();

				var start = n.indexOf(query);
				start = start > 0 ? start : 0;

				if (obj.typeShort) {
					label += obj.typeShort + '. ';
				}

				if (query.length < obj.name.length) {
					label += obj.name.substr(0, start);
					label += '<strong>' + obj.name.substr(start, query.length) + '</strong>';
					label += obj.name.substr(start + query.length, obj.name.length - query.length - start);
				} else {
					label += '<strong>' + obj.name + '</strong>';
				}

				if (obj.parents) {
					for (var k = obj.parents.length - 1; k > -1; k--) {
						var parent = obj.parents[k];
						if (parent.name) {
							if (label) label += '<small>, </small>';
							label += '<small>' + parent.name + ' ' + parent.typeShort + '.</small>';
						}
					}
				}

				return label;
			},
			valueFormat: function (obj, query) {			
				return KladrJsObj.getValFormatFromKladrCity(obj);
			}
			
		});
	
	},
	
	getValFormatFromKladrCity: function(obj){
		var label = '';

		if (obj.typeShort) {
			label += obj.typeShort + '. ';
		}

		label += obj.name;

		if (obj.parents) {
			for (var k = obj.parents.length - 1; k > -1; k--) {
				var parent = obj.parents[k];
				if (parent.name) {
					if (label)
						label += ', ' + parent.name + ' ' + parent.typeShort + '.';
				}
			}
		}
		
		return label;		
	},

	changeLocationInput: function(){
		
		var after = '[data-property-id-row='+KladrJsObj.locPropId+'] .bx-sls';		
		
		if(!$(after).length) 			
			after = '.KladrHideThisLocation';		
		
		if($(after).length) {
			
			if(!KladrJsObj.locations_not_rus_checked) {
											
				if (after != '.KladrHideThisLocation')
					$($(after).children()).each(function (i, obj) {
						if (!$(obj).hasClass('quick-locations'))
							$(obj).hide();						
					});				
				else
					$(after).hide();				
				
				$('.smartadr_location').remove();
				$(after).after('<div class="smartadr_location"><input type="text" id="'+KladrJsObj.smart_locid+'"></div>');
					
				// это поле добавлено как альтернативное адресу, удаляем его, раз уж мы заменяем функционал
				$("#altProperty").parent().hide();
				
				after = '#'+KladrJsObj.smart_locid;
				
				// SAdrJsObj.changeZipInput();
				KladrJsObj.initLocationInput();

			}				

			if(KladrSettings.locations_not_rus)
				KladrJsObj.initNotRusCheckbox(after);		

		}
		
	},
	
	setvalueLocationInput: function(obj){
		
		if(!KladrJsObj.locations_not_rus_checked)
			$("#"+KladrJsObj.smart_locid).fias('controller').setValue(obj);
			
	},
	
	setall: function(ajaxAns){//срабатывает по ajax
		
		if(KladrJsObj.newVersionTemplate) {
			
			if(Object.keys(ajaxAns).indexOf("order") !== -1) { // защита от пустых ajax-ов

				var loccode;
				var locPropId=KladrJsObj.locPropId;				
				var skipForThisDelivery = false;
					
				// buyer profiles 
				if (ajaxAns.order.USER_PROFILES && !$.isEmptyObject(ajaxAns.order.USER_PROFILES))
				{					
					var lastprofile = KladrJsObj.profile.current; // save previous selected profile ID
					
					KladrJsObj.profile.isUsed = true;					
					KladrJsObj.profile.current = false; 
					
					$.each(ajaxAns.order.USER_PROFILES, function(index, value) {				
						if (value.CHECKED == "Y") {
							KladrJsObj.profile.current = value.ID;							
						}
					});
					
					// profiles used and checked no one || IDs are not the same -> new profile selected					
					KladrJsObj.profile.isChanged = (KladrJsObj.profile.current === false || KladrJsObj.profile.current !== lastprofile);
				}				
							
				// check if kladr form not needed for this delivery (usually for pickup)			
				skipForThisDelivery = KladrJsObj.checkSkippedDelivery(ajaxAns.order.DELIVERY);
								
				// определяем ids свойств location и zip
				$.each(ajaxAns.order.ORDER_PROP.properties, function( index, value ) {
					
					if(value.IS_LOCATION == "Y") {
						loccode = value.VALUE[0] != "" ? value.VALUE[0] : value.DEFAULT_VALUE;
						KladrJsObj.locPropId = value.ID;
					} else if(value.IS_ZIP == "Y") {
						KladrJsObj.zipPropId = value.ID;
					}
				  
				});
								
				// if bitrix location code changes
				if (KladrJsObj.lastLocationCode != loccode)
					KladrJsObj.kladr_city_obj = false;
			
				// сразу же меняем location, если есть настройка
				if(KladrSettings.hideLocation && KladrJsObj.locPropId)
					KladrJsObj.changeLocationInput();
				
				// если поменялся id location (тип плательщика сменился), обнуляем город
				if(locPropId!=KladrJsObj.locPropId)
					KladrJsObj.kladr_city_obj=false;
								
				if(KladrJsObj.kladr_city_obj) {
					if(!$.isEmptyObject(KladrJsObj.kladr_city_obj)) {
						// установим город
						if(KladrSettings.hideLocation)
							KladrJsObj.setvalueLocationInput(KladrJsObj.kladr_city_obj);
				
						KladrJsObj.FuckKladr();
						obj = {'ajax':true};
						obj['kladr']={NotRussia:"0",contentType:"city",kladrid:KladrJsObj.kladr_city_obj.id,kltobl:KladrJsObj.kladr_city_obj};						
						if(!skipForThisDelivery)
							KladrJsObj.FormKladr(obj);
					}
				} else {				
					if(loccode != "") { // известен код локейшена
						KladrJsObj.lastLocationCode = loccode;
				
						$.ajax({
							type: 'GET',
							url: '/bitrix/js/ipol.kladr/getLoc.php',
							data: 'code='+loccode,
							success: function(res){
								
								if(res != "" && res.indexOf("error") == -1) {

									KladrJsObj.FuckKladr();
									
									var resJson = JSON.parse(res);
									obj = {'ajax':true};
									obj['kladr']={NotRussia:"0",contentType:"city",kladrid:resJson.id,kltobl:resJson};
									
									if(!skipForThisDelivery)
										KladrJsObj.FormKladr(obj);
									 
									if(KladrSettings.hideLocation && KladrJsObj.locPropId)
										KladrJsObj.setvalueLocationInput(resJson);
									
								} else {
									
									if(KladrSettings.notShowForm) {										
										KladrJsObj.FuckKladr();
									} else {
										KladrJsObj.kladrtownid=false;// убираем родителя
										KladrJsObj.contentType=false;// тип - начинать с города
										KladrJsObj.lastobject = {};// восстановления не нужно
										$('.kltobl:last').text('');// восстановления не нужно
										
										if(!skipForThisDelivery)
											KladrJsObj.FormKladr({'ajax':true});
									
									}
									
								}
								
							}			
						});	

					} else {
							
						if(KladrSettings.notShowForm) {							
							KladrJsObj.FuckKladr();
						} else {
							KladrJsObj.kladrtownid=false;// убираем родителя
							KladrJsObj.contentType=false;// тип - начинать с города
							KladrJsObj.lastobject = {};// восстановления не нужно
							$('.kltobl:last').text('');
							
							if(!skipForThisDelivery)
								KladrJsObj.FormKladr({'ajax':true});
						
						}
						
					}

				}

				return;

			}

		} else {
			// старый шаблон
			// вставить форму
			if(typeof(StartKladrObj)!= 'undefined' && !$.isEmptyObject(StartKladrObj) ){
				obj = StartKladrObj;
				StartKladrObj=false;
			}
			else
				obj = {'ajax':true};
			
			var newTemplateAjax = (typeof(ajaxAns) != 'undefined' && ajaxAns !== null && typeof(ajaxAns.kladr) == 'object') ? true : false;
			if(newTemplateAjax){
				KladrJsObj.FuckKladr();
				obj['kladr']=ajaxAns.kladr;
			}
			
			if($('#'+KladrJsObj.kladrdivid).length || $('.nobasemessage').length) return;
			KladrJsObj.FormKladr(obj);
		
		}

	},
	
	addScript(src){
	  var script = document.createElement('script');
	  script.src = src;
	  script.async = false;
	  document.head.appendChild(script);
	},
	
	checkYandexApi: function() {
		var path = "//api-maps.yandex.ru/2.1/?load=package.standard&mode=release&lang=ru-RU";		
		if (KladrSettings.YandexAPIkey.length > 0)
			path = path + "&apikey=" + KladrSettings.YandexAPIkey;
				
		if (KladrSettings.ShowMap && typeof(ymaps) == 'undefined') {			
			KladrJsObj.addScript(path);
			KladrJsObj.mapUpdate();
			console.log("Load Yandex maps API from ipol.kladr module");
		}
	},	
	
	setCommerceToken: {
		timer : false,
		checker : function() {
				
			if (KladrJsObj.setCommerceToken.timer && (typeof($.fias) == "object")) {								
				$.fias.token = KladrSettings.kladripoltoken;
				$.fias.url = 'https://kladr-api.com/api.php';
				
				clearTimeout(KladrJsObj.setCommerceToken.timer);
				KladrJsObj.setCommerceToken.timer = false;				
			}
			else {
				KladrJsObj.setCommerceToken.timer = setTimeout(KladrJsObj.setCommerceToken.checker, 1000);				
			}		
		},		
	},
	
	checkSkippedDelivery: function(deliveries) {
		var skipCurrentDelivery = false;
		
		if (!$.isEmptyObject(KladrSettings.skipDeliveries))
		{
			$.each(deliveries, function(index, value) {				
			if (value.CHECKED == "Y") {					
				if (KladrSettings.skipDeliveries.indexOf(value.ID) !== -1)
					skipCurrentDelivery = true;					
				}
			});
		}	
		
		return skipCurrentDelivery;		
	},
	
	executeFunctionByName: function(name, context /*, args */) {
		// Thanks Jason Bunting
		var args = Array.prototype.slice.call(arguments, 2);
		var namespaces = name.split(".");
		var func = namespaces.pop();
		for (var i = 0; i < namespaces.length; i++) {
			context = context[namespaces[i]];
		}
		
		return context[func].apply(context, args);
	},
}

$(document).ready(function(){
	
	KladrJsObj.setCommerceToken.checker();
		
	setTimeout(KladrJsObj.checkYandexApi, 5000);
	
	if(typeof(BX.Sale)!='undefined') {
		KladrJsObj.newVersionTemplate = typeof(BX.Sale.OrderAjaxComponent)!='undefined' ? true : false;
	}
	
	if(typeof BX !== 'undefined' && BX.addCustomEvent)
		BX.addCustomEvent('onAjaxSuccess', KladrJsObj.setall);

	if(KladrJsObj.newVersionTemplate)
		BX.Sale.OrderAjaxComponent.sendRequest();

});