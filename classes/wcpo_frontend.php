<?php
/**
 * Класс отвечает за отображение списка пунктов самовывоза во время заказа
 */
class WCPO_FrontEnd
{
	/**
     * Ссылка на объект менеджера
	 * @var WCPO_Manager
	 */
	protected $manager;
		
	/**
	 * Конструктор класса
	 * Инициализирует класс
	 * 
	 * @param WCPO_Manager	$manager	Ссылка на объект менеджера
	 */
	public function __construct( $manager )
	{
		$this->manager = $manager;
		
		// Добавляем шорткоды
		$this->addShortCodes();
		
		// Добавляем хуки WooCoomerce
		$this->setWCHooks();
	}
	
	/**
	 * Добавляем шорткоды
	 */
	public function addShortCodes()
	{
		// Вывод списка пунктов самовывоза
		add_shortcode( 'wcpo_offices', array( $this, 'getOfficeList') );
		
		// Вывод списка городов по типу пункта
		add_shortcode( 'wcpo_cities', array( $this, 'getCityList') );
	}
	
	/**
	 * Вывод списка пунктов самовывоза со всеми данными
	 * 
	 * @param mixed		$atts		Ассоциативный массив атрибутов указанных в шоткоде. По умолчанию пустая строка - атрибуты не переданы
	 * @param string	$content	Текст шоткода, когда используется закрывающая конструкция шотркода
	 * @param string	$tag		Тег шорткода. Может пригодится для передачи в доп. функции
	 */
	public function getOfficeList( $atts=array(), $content='', $tag='' )
	{
		// Устанавливаем ожидаемые параметры
		extract( shortcode_atts( array(
			'title'		=>	'',					// Название, которое выводится заголовком H2
			'type' 		=> 'pickup_office',		// Слаг таксономии "Тип пункта"
			'city'		=>	'',					// Город, для которого следует вывести список
			'cols'		=> 'wcpo_point_id,wcpo_city,wcpo_address,wcpo_open_hours,wcpo_phone,wcpo_terminal',
			'col_title'	=> __( 'Point ID,City,Address,Open Hours,Phone,Terminal', WCPO_TEXT_DOMAIN ),
		), $atts ) );
		
		// Формируем вывод
		$output = '<div class="pickup-office-list">' . PHP_EOL;
		
		// Выводим заголовок, если он есть
		if ( ! empty( $title ) )
			$output .= '<h2>' . esc_html( $title ) . '</h2>' . PHP_EOL;
		
		// Преобразуем список колонок и названий в массив
		$cols 		= explode( ',', $cols );
		$col_title 	= explode( ',', $col_title );
		
		// Если поля указаны, делаем запрос объекту wcpo_officelist
		if ( count( $cols ) && $cols[0] != '' )
		{
			// Список пунктов
			$offices = $this->manager->officeList->getOffices( $type, $city );
			
			// Если он не пустой, выводим
			if ( count( $offices ) > 0 )
			{
				// Формируем таблицу
				$output .= '<table>';
				
				// Формируем заголовки
				$output .= '<thead><tr>';
				foreach ($cols as $i => $col)
				{
					$output .= '<td>';
					$output .= ( isset( $col_title[$i] ) ) ? esc_html( $col_title[$i] ) : '&nbsp;';
					$output .= '</td>';
				}
				$output .= '</tr></thead>' . PHP_EOL;

				// Выводим данные
				$output .= '<tbody>'  . PHP_EOL;
				foreach ( $offices as $post_id => $office )
				{
					$output .= '<tr>';
					foreach ($cols as $i => $col)
					{
						$output .= '<td>';
						if ( ( $col == 'wcpo_point_id' || $col == 'wcpo_address' ) && ! empty( $office['wcpo_href'] ) )
						{
							$output .= '<a href="' . esc_attr( $office['wcpo_href'] ) . '" target="_blank" rel="nofollow">' . 
								( ( isset( $office[$col] ) ) ? esc_html( $office[$col] ) : '&nbsp;' ) . 
								'</a>';
						}
						else
						{
							$output .= ( isset( $office[$col] ) ) ? esc_html( $office[$col] ) : '&nbsp;';
						}
						$output .= '</td>';						
					}
					$output .= '</tr>' . PHP_EOL;
				}
				$output .= '</tbody>'  . PHP_EOL;
				
				$output .= '</table>' . PHP_EOL;
			}
			else
			{
				// Пунктов нет
				$output .= esc_html( __( 'There are not available pickup offices! ', WCPO_TEXT_DOMAIN) ) . PHP_EOL;
			}
		}
		else
		{ 
			// Поля не указаны
			$output .= '<!-- ' . esc_html( __( 'Columns are not specified! ', WCPO_TEXT_DOMAIN) ) . ' -->' . PHP_EOL;
		}
		
		// Вывод
		$output .= '</div><!--/pickup-office-list -->' . PHP_EOL;
		return $output;
	}
	
	/**
	 * Вывод списка городов по типу
	 * 
	 * @param mixed		$atts		Ассоциативный массив атрибутов указанных в шоткоде. По умолчанию пустая строка - атрибуты не переданы
	 * @param string	$content	Текст шоткода, когда используется закрывающая конструкция шотркода
	 * @param string	$tag		Тег шорткода. Может пригодится для передачи в доп. функции
	 */
	public function getCityList( $atts=array(), $content='', $tag='' )
	{
		// Устанавливаем ожидаемые параметры
		extract( shortcode_atts( array(
			'type' 		=> 'pickup_office',		// Слаг таксономии "Тип пункта"
		), $atts ) );

		// Формируем вывод
		$output = '<ul class="pickup-office-city-list">' . PHP_EOL;		
		
		// Список городов
		$cities = $this->manager->officeList->getCitiesData( $type );
		foreach ( $cities as $city => $cityData )
		{
			if ( ! empty( $cityData['wcpo_href'] ) )
			{
				$output .= '<li><a href="' .  esc_attr( $cityData['wcpo_href'] ) . '" target="_blank" rel="nofollow">' . esc_html( $city ) . ' (' . esc_html( $cityData['wcpo_delivery_period'] ) . ' дн.)</li>' . PHP_EOL;			
			}
			else
			{
				$output .= '<li>' . esc_html( $city ) . ' (' . esc_html( $cityData['wcpo_delivery_period'] ) . ' дн.)</li>' . PHP_EOL;				
			}
			
		}
		// Вывод
		$output .= '</ul><!--/pickup-office-city-list -->' . PHP_EOL;
		return $output;		
	}


	/**
	 * Добавляем хуки WooCommerce
	 */
	public function setWCHooks()
	{
		// Добавляем новое поле "Пункт самовывоза" в доставку
		// https://docs.woocommerce.com/document/tutorial-customising-checkout-fields-using-actions-and-filters/
		add_action( 'woocommerce_before_order_notes', 						array( $this, 'addPickupOfficeField' ) );
		add_filter( 'woocommerce_checkout_update_order_meta', 				array( $this, 'savePickupData' ) );
		add_filter( 'woocommerce_admin_order_data_after_shipping_address',	array( $this, 'showCheckoutField' ) );
	}
	
	/**
	 * Поле, добавляемое в заказ WooCommerce
	 * @static 
	 */
	const FIELD_PICKUP_OFFICE = 'pickup_office';

	/**
	 * Устанавливает новое поле для доставки "Пункт самовывоза"
	 * 
	 * @param mixed		$checkout		Объект заказа WooCommerce
	 */
	public function addPickupOfficeField( $checkout )
	{
		// Подгрузка данных и скрипта обработки
		$blocktId = strtolower( get_class( $this ) );
		wp_register_script( $blocktId, WCPO_URL . 'js/checkout.js', array('jquery', 'jquery-ui-autocomplete'));
		
		// Данные для скрипта
		$data = array(
			/* translators: This is the string for search selected sipping method on checkout page. Regular expression is avaliable */
			'courierRE'		=> __( 'courier', WCPO_TEXT_DOMAIN ),
			/* translators: This is the string for search selected sipping method on checkout page. Regular expression is avaliable */
			'pickupRE'		=> __( 'pickup', WCPO_TEXT_DOMAIN ),			
			'deliveryPerod'	=> __( 'Estimated Delivery Period', WCPO_TEXT_DOMAIN ),			
			'courierCities'	=> $this->manager->officeList->getCities( 'courier' ),
			'pickupCities'	=> $this->manager->officeList->getCities( 'pickup_office' ),
			'pickupOffices'	=> $this->manager->officeList->getOffices( 'pickup_office' ),
		);
		wp_localize_script( $blocktId, $blocktId, $data );
		wp_enqueue_script( $blocktId );	
		
		echo '<div id="', $blocktId ,'">';
		
		// Города
		$fieldCity = self::FIELD_PICKUP_OFFICE . '_city';
		woocommerce_form_field( $fieldCity, array(
			'type'          => 'text',
			'class'         => array('pickup-offce-class form-row-wide'),
			'label'         => __( 'City of pickup office', WCPO_TEXT_DOMAIN ),
		), $checkout->get_value( $fieldCity ));
		
		// Метро
		$fieldMetro = self::FIELD_PICKUP_OFFICE . '_metro';
		woocommerce_form_field( $fieldMetro, array(
			'type'          => 'text',
			'class'         => array('pickup-offce-class form-row-wide'),
			'label'         => __( 'Subway near pickup office', WCPO_TEXT_DOMAIN ),
		), $checkout->get_value( $fieldMetro ));		
		
		// Пункты
		$fieldOffice = self::FIELD_PICKUP_OFFICE . '_point';
		woocommerce_form_field( $fieldOffice, array(
			'type'          => 'text',
			'class'         => array('pickup-offce-class form-row-wide'),
			'label'         => __( 'Pickup Office', WCPO_TEXT_DOMAIN ),
			), $checkout->get_value( $fieldOffice ));
			
		// Пункты
		$fieldInfo = self::FIELD_PICKUP_OFFICE . '_info';
		woocommerce_form_field( $fieldInfo, array(
			'type'          	=> 'textarea',
			'class'         	=> array('pickup-offce-class form-row-wide'),
			'label'         	=> '',
			'placeholder'       => __( 'Pickup Office Information', WCPO_TEXT_DOMAIN ),
			'custom_attributes' => array( 'readonly' => 'readonly', 'rows' => '5' ),
		), $checkout->get_value( $fieldInfo ));
		
		
		echo '</div>';
	}
	
	/**
	 * Сохраняет данные в заказе
	 * 
	 * @param int		$order_id		Номер заказа WooCommerce
	 */
	function savePickupData( $order_id )
	{
		// DEBUG: Выводим сожержание POST
		if ( WP_DEBUG ) file_put_contents( WCPO_PATH.'wc_post.log', var_export( $_POST, true ) );
		
		// Order fields
		$fieldCity = self::FIELD_PICKUP_OFFICE . '_city';
		$fieldMetro = self::FIELD_PICKUP_OFFICE . '_metro';
		$fieldOffice = self::FIELD_PICKUP_OFFICE . '_point';
		$fieldInfo = self::FIELD_PICKUP_OFFICE . '_info';
		
		// Sanitize user input
		$city = isset( $_POST[ $fieldCity ] ) ? sanitize_text_field( $_POST[ $fieldCity ] ) : '';
		$metro = isset( $_POST[ $fieldMetro ] ) ? sanitize_text_field( $_POST[ $fieldMetro ] ) : '';		
		$office = isset( $_POST[ $fieldOffice ] ) ? sanitize_text_field( $_POST[ $fieldOffice ] ) : '';		
		$info = isset( $_POST[ $fieldInfo ] ) ? sanitize_text_field( $_POST[ $fieldInfo ] ) : '';		
		
		// Update data
		update_post_meta( $order_id, __( 'City of pickup office', WCPO_TEXT_DOMAIN ),		$city );
		update_post_meta( $order_id, __( 'Subway near pickup office', WCPO_TEXT_DOMAIN ), 	$metro );
		update_post_meta( $order_id, __( 'Pickup Office', WCPO_TEXT_DOMAIN ), 				$office );
		update_post_meta( $order_id, __( 'Pickup Office Information', WCPO_TEXT_DOMAIN ), 	$info );
		
		// Если указан пункт самовывоза, то мы в заказе заменим адрес доставки на данные самовывоза
		if ( ! empty( $office ))
		{
			// Заменяем существующие значения полей WooCoomerce
			update_post_meta( $order_id, '_shipping_postcode',	'' );
			update_post_meta( $order_id, '_shipping_state',		'' );
			update_post_meta( $order_id, '_shipping_city',		$city );
			update_post_meta( $order_id, '_shipping_address_1',	$office );
			update_post_meta( $order_id, '_shipping_address_2',	'' );

			// Записываем данные о пункте в заметку заказа
			$orderPost = get_post( $order_id );
			$orderPost->post_excerpt .= PHP_EOL . '<br>' . PHP_EOL . $info;
			wp_update_post($orderPost);	
		}
	}	
	
	/**
	 * Показывает поля "Пункт самовывоза" в админке
	 * 
	 * @param mixed		$order		Заказ WooCommerce
	 */
	function showCheckoutField( $order )
	{
		$city 	= get_post_meta( $order->id, __( 'City', WCPO_TEXT_DOMAIN ), true );
		$metro 	= get_post_meta( $order->id, __( 'Subway', WCPO_TEXT_DOMAIN ), true );
		$office = get_post_meta( $order->id, __( 'Pickup Office', WCPO_TEXT_DOMAIN ), true );
		$info 	= get_post_meta( $order->id, __( 'Pickup Office Information', WCPO_TEXT_DOMAIN ), true );
		
		
		if ( ! empty ( $city ) )
			echo '<p><strong>'. __( 'City', WCPO_TEXT_DOMAIN ).':</strong> ' . esc_html( $city ) . '</p>';
		if ( ! empty ( $metro ) )
			echo '<p><strong>'. __( 'Subway', WCPO_TEXT_DOMAIN ).':</strong> ' . esc_html( $metro ) . '</p>';
		if ( ! empty ( $office ) )
			echo '<p><strong>'. __( 'Pickup Office', WCPO_TEXT_DOMAIN ).':</strong> ' . esc_html( $office ) . '</p>';
		if ( ! empty ( $info ) )
			echo '<p><strong>'. __( 'Pickup Office Information', WCPO_TEXT_DOMAIN ).':</strong> ' . esc_html( $info ) . '</p>';
	}	
	
}