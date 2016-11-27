/**
 * Скрипт на странице Checkout
 */
jQuery(function($){
	'use stict';
	var debug = true;	
		
	// Требуемые поля, с которыми работаем
	var 
		billingPostCode 	= $('#billing_postcode'),
		billingCity 		= $('#billing_city'),
		billingState 		= $('#billing_state'),
		billingAddr1 		= $('#billing_address_1'),
		billingAddr2 		= $('#billing_address_2'),		
	
		shippingPostCode 	= $('#shipping_postcode'),
		shippingCity 		= $('#shipping_city'),
		shippingState 		= $('#shipping_state'),
		shippingAddr1 		= $('#shipping_address_1'),
		shippingAddr2 		= $('#shipping_address_2'),
		
		pickupCity 		= $('#pickup_office_city'),
		pickupMetro		= $('#pickup_office_metro'),
		pickupOffice	= $('#pickup_office_point'),
		pickupInfo		= $('#pickup_office_info'),
		radioShipping	= $('#shipping_method input.shipping_method'),
		
		
		// Мой контейнер
		container		= $('div#wcpo_frontend'),
		
		// Текстовые значения способов доставки, НАДО БРАТЬ из локализации
		methodCourier	= wcpo_frontend.courierRE,
		methodPickup	= wcpo_frontend.pickupRE,
		deliveryPerod	= wcpo_frontend.deliveryPerod;

	// Функция возвращает название выбранного метода доставки
	function getCurrentMethod() {
		var method = radioShipping.filter(':checked').parent().find('label').text();
		//debug && console.log('getCurrentMethod: ', method);
		return 	method;
	}
		
	// Функция возвращает true, если выбран указанный тип доставки	
	function isShippingMethod( method )	{
		var re = new RegExp(method, 'i');
		return re.test( getCurrentMethod() );
	}	
	
	// Обработчик переключения способов доставки
	$('#shipping_method input.shipping_method').on('change', function(){
		// Покажем нужные поля
			showShippingFields();
	});
	
	// Функция отрисовки нужных полей при разных методах доставки
	function showShippingFields() {
		//debug && console.log('showShippingFields ');
		// Текущий метод доставки
		var method = getCurrentMethod();
		
		// Если доставка в пункт самовывоза
		if ( isShippingMethod( methodPickup ) )
		{
			// Скроем поля из адреса ДОСТАВКИ
			billingPostCode.parent().hide();
			billingState.parent().hide();
			billingCity.parent().hide();
			billingAddr1.parent().hide();
			billingAddr2.parent().hide();
			
			shippingPostCode.parent().hide();
			shippingState.parent().hide();
			shippingCity.parent().hide();
			shippingAddr1.parent().hide();
			shippingAddr2.parent().hide();	

			container.show();
		}
		else
		{
			// Покажем все поля
			billingPostCode.parent().show();
			billingState.parent().show();
			billingCity.parent().show();
			billingAddr1.parent().show();
			billingAddr2.parent().show();	

			shippingPostCode.parent().show();
			shippingState.parent().show();
			shippingCity.parent().show();
			shippingAddr1.parent().show();
			shippingAddr2.parent().show();		

			container.hide();			
		}
		
	}
	// Покажем правильные поля прямо сейчас
	showShippingFields();
	
	

	// Функция устанавливает значение города по данным полей заказа
	function setCity()
	{
		if ( shippingCity.val() != '' )
			pickupCity.val(shippingCity.val());
		else
			pickupCity.val(billingCity.val());				
	}
	// Установим значение города прямо сейчас
	setCity();
	
	// Установим значение города при изменении полей заказа
	billingCity.on('blur', setCity);
	shippingCity.on('blur', setCity);

	// Автозаполнение городов, включается по фокусу на элементе
	pickupCity.on('focus', function(){
		// Очистим метро
		pickupMetro.val('');
		
		if ( isShippingMethod( methodCourier ) ){
			pickupCity.autocomplete({
				source: wcpo_frontend.courierCities,
				minLength: 0
			});
			//debug && console.log('pickupCity dataSource: ', wcpo_frontend.courierCities);
			pickupCity.autocomplete( 'search', pickupCity.val() );
		}
		else if ( isShippingMethod( methodPickup ) ) {
			pickupCity.autocomplete({
				source: wcpo_frontend.pickupCities,
				minLength: 0,
				change: function( event, ui ){
					var thisField = $(this);
					
					// Разрешаем только города из списка!
					//debug && console.log('pickupCity.autocomplete : ', ui.item);
					thisField.val( (ui.item ? ui.item.value : "" ) );
					
					// Поскольку billingCity обязательный, то заполняем его, если он сейчас пустой или поле скрыто
					if ( billingCity.val() == '' && thisField.val() != '' )
						billingCity.val( thisField.val() );
				}				
			});	
			//debug && console.log('pickupCity dataSource for methodPickup: ', wcpo_frontend.pickupCities);
			pickupCity.autocomplete( 'search', pickupCity.val() );
		}
		else{
			pickupCity.autocomplete({
				source: []
			});
			//debug && console.log('pickupCity dataSource: ', []);
		}
	});
	
	// Автозаполнение городов при доставке курьером, включается по фокусу на элементе billingCity
	billingCity.on('focus', function(){
		if ( isShippingMethod( methodCourier ) ){
			// Доставка курьером, включаем автозаполнение
			billingCity.autocomplete({
				source: wcpo_frontend.courierCities,
				minLength: 0,
				change: function( event, ui ){
					// Разрешаем только города из списка!
					//debug && console.log('billingCity.autocomplete : ', ui.item);
					$(this).val((ui.item ? ui.item.value : ""));
				}
			});
			//debug && console.log('billingCity dataSource: ', wcpo_frontend.courierCities);
			billingCity.autocomplete( 'search', pickupCity.val() );
		}
		else{
			// Доставка иная, выключаем автозаполнение
			billingCity.autocomplete({
				source: []
			});
			//debug && console.log('billingCity dataSource: ', []);
		}
	});	
	
	
	
	// Автозаполнение метро, включается по фокусу на элементе
	pickupMetro.on('focus', function(){
		// Только для достаки из пунктов самовывоза
		if ( ! isShippingMethod( methodPickup ) )
		{
			//debug && console.log('pickupMetro disabled');
			return;
		}
			
		
		// Найдем список метро в этом городе
		var metroStations = [];
		for (var officeId in wcpo_frontend.pickupOffices)
		{
			var office = wcpo_frontend.pickupOffices[officeId];
			
			if ( office.wcpo_city == pickupCity.val() && office.wcpo_metro != '' ) {
				if ( $.inArray( office.wcpo_metro, metroStations ) < 0 ) {
					metroStations.push( office.wcpo_metro );
				}
			}	
		}		
		pickupMetro.autocomplete({
			source: metroStations,
			minLength: 0
		});
		debug && console.log('pickupMetro dataSource: ', metroStations);
		pickupMetro.autocomplete( 'search', pickupMetro.val() );
	});	
	
	// Автозаполнение пунктов, включается по фокусу на элементе
	pickupOffice.on('focus', function(){
		// Только для достаки из пунктов самовывоза
		if ( ! isShippingMethod( methodPickup ) )
		return;
		
		// Найдем список пунктов в этом городе
		var points = [];
		for (var officeId in wcpo_frontend.pickupOffices)
		{
			var office = wcpo_frontend.pickupOffices[officeId];
			var point = office.wcpo_point_id + ', ' + office.wcpo_address;
			
			if ( office.wcpo_city == pickupCity.val()) {
				// Для городов без метро
				if ( pickupMetro.val() == '' ) {
					if ( $.inArray( point, points ) < 0 ) {
						points.push( point );
						continue;
					}						
				}
				
				// Если метро указано
				if ( pickupMetro.val() == office.wcpo_metro ) {
					if ( $.inArray( point, points ) < 0 ) {
						points.push( point );
						continue;
					}						
				}
			}	
		}		
		pickupOffice.autocomplete({
			source: points,
			minLength: 0,
			select: function (event, ui) { 
				debug && console.log('pickupOffice select ui: ', ui); 
				showPointInfo( ui.item.label );
			} 
		});
		debug && console.log('pickupOffice dataSource: ', points); 
		pickupOffice.autocomplete( 'search', pickupOffice.val() );
	});
	
	
	// Вывод данных о пункте самовывоза
	function showPointInfo( officeName )
	{
		debug && console.log('showPointInfo: ', officeName);
		pickupInfo.val( '' );
		
		var parts = officeName.split(',');
		
		for (var officeId in wcpo_frontend.pickupOffices)
		{
			var office = wcpo_frontend.pickupOffices[officeId];
			
			if ( office.wcpo_point_id == parts[0] ) {
					
				var text = 	
					'Срок доставки: ' + office.wcpo_delivery_period + '\n' + 
					office.wcpo_open_hours + '\n' +
					office.wcpo_phone;
					
					
				pickupInfo.val( text.trim() );
				return;
			}	
		}		
		
		
		pickupInfo.val( officeName );	
	}
});
