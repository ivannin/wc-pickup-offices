/**
 * Скрипт на странице Checkout
 */
jQuery(function($){
	'use stict';
	var debug = true;	
		
	// Требуемые поля, с которыми работаем
	var billingCity 	= $('#billing_city'),
		shippingCity 	= $('#billing_city'),
		pickupCity 		= $('#pickup_office_city'),
		pickupMetro		= $('#pickup_office_metro'),
		pickupOffice	= $('#pickup_office_point'),
		pickupInfo		= $('#pickup_office_info'),
		radioShipping	= $('#shipping_method input.shipping_method'),
		
		// Мой контейнер
		container		= $('div#wcpo_frontend'),
		
		// Текстовые значения способов доставки, НАДО БРАТЬ из локализации
		methodCourier	= 'курьер',
		methodPickup	= 'пункт самовывоза',
		deliveryPerod	= wcpo_frontend.deliveryPerod;

	// Функция возвращает название выбранного метода доставки
	function getCurrentMethod() {
		var method = $('#shipping_method input.shipping_method[checked]').parent().find('label').text();
		debug && console.log('getCurrentMethod: ', method);
		return 	method;
	}

		
	// Функция возвращает true, если выбран указанный тип доставки	
	function isShippingMethod( method )	{
		var re = new RegExp(method, 'i');
		return re.test( getCurrentMethod() );
	}	

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
		if ( isShippingMethod( methodCourier ) ){
			pickupCity.autocomplete({
				source: wcpo_frontend.courierCities,
				minLength: 0
			});
			debug && console.log('pickupCity dataSource: ', wcpo_frontend.courierCities);
			pickupCity.autocomplete( 'search', pickupCity.val() );
		}
		else if ( isShippingMethod( methodPickup ) ) {
			pickupCity.autocomplete({
				source: wcpo_frontend.pickupCities,
				minLength: 0
			});	
			debug && console.log('pickupCity dataSource: ', wcpo_frontend.pickupCities);
			pickupCity.autocomplete( 'search', pickupCity.val() );
		}
		else{
			pickupCity.autocomplete({
				source: []
			});
			debug && console.log('pickupCity dataSource: ', []);
		}
	});
	
	// Автозаполнение метро, включается по фокусу на элементе
	pickupMetro.on('focus', function(){
		// Только для достаки из пунктов самовывоза
		if ( ! isShippingMethod( methodPickup ) )
		{
			debug && console.log('pickupMetro disabled');
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