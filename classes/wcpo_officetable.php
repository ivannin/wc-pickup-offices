<?php
/**
 * Класс Табличный редактор пунктов самовывоза
 */
class WCPO_OfficeTable extends WP_HOT_Core
{
	/**
     * Ссылка на класс список пунктов самовывоза
     * @var WCPO_OfficeList
     */  	 
	protected $officeList;
		
    /**
     * Конструктор класса
	 *
     */
    public function __construct( $officeList )
    {
        parent::__construct();
		
		// Сохраним класс списка
		$this->officeList = $officeList;
		
        // Обработчик, который формирует список пунктов самовывоза для предзагрузки
		$this->loadHandler = 'WCPO_OfficeTable::getOffices';
		
		// AJAX обработчики
		add_action( 'wp_ajax_load_' 	. $this->ajaxAction, 'WCPO_OfficeTable::ajaxGetOffices' );	
		add_action( 'wp_ajax_save_' 	. $this->ajaxAction, 'WCPO_OfficeTable::ajaxSaveOffices' );
		add_action( 'wp_ajax_delete_' 	. $this->ajaxAction, 'WCPO_OfficeTable::ajaxDeleteOffices' );
    }
	
    /**
     * Метод формирует JSON код настроек таблицы
		*
     * @return string   Сформированный JSON код
     */        
    protected function getTableOptions()
    {   
        // Названия колонок
		$wcpo_id 				= __( 'ID', WCPO_TEXT_DOMAIN );
		$wcpo_point_id 			= __( 'Point ID', WCPO_TEXT_DOMAIN );
		$wcpo_city 				= __( 'City', WCPO_TEXT_DOMAIN );
		$wcpo_delivery_period 	= __( 'Delivery Period', WCPO_TEXT_DOMAIN );
		$wcpo_metro				= __( 'Subway', WCPO_TEXT_DOMAIN );
		$wcpo_zip 				= __( 'Zip', WCPO_TEXT_DOMAIN );
		$wcpo_address 			= __( 'Address', WCPO_TEXT_DOMAIN );
		$wcpo_open_hours		= __( 'Open Hours', WCPO_TEXT_DOMAIN );
		$wcpo_phone				= __( 'Phone', WCPO_TEXT_DOMAIN );
		$wcpo_email				= __( 'E-mail', WCPO_TEXT_DOMAIN );
		$wcpo_terminal			= __( 'Terminal', WCPO_TEXT_DOMAIN );
		$wcpo_max_weight		= __( 'Max. Weight', WCPO_TEXT_DOMAIN );
		$wcpo_href				= __( 'Href', WCPO_TEXT_DOMAIN );
		
		// Название пунктов меню
		$menu_insertBelow		= __( 'Insert below', WCPO_TEXT_DOMAIN );
		$menu_delete_row		= __( 'Delete row', WCPO_TEXT_DOMAIN );
		
		return "{
			minSpareRows: 1,
			colHeaders: ['$wcpo_id', '$wcpo_city', '$wcpo_point_id', '$wcpo_delivery_period', '$wcpo_metro', '$wcpo_zip', '$wcpo_address', '$wcpo_open_hours', '$wcpo_phone', '$wcpo_email', '$wcpo_terminal', '$wcpo_max_weight', '$wcpo_href'],
			columns: [
			{ data: 'id', 					type: 'numeric', 	readOnly: true },
			{ data: 'wcpo_city',			type: 'text' },			
			{ data: 'wcpo_point_id',		type: 'text' },		
			{ data: 'wcpo_delivery_period',	type: 'text' },
			{ data: 'wcpo_metro',			type: 'text' },
			{ data: 'wcpo_zip',				type: 'text' },
			{ data: 'wcpo_address',			type: 'text' },
			{ data: 'wcpo_open_hours',		type: 'text' },
			{ data: 'wcpo_phone',			type: 'text' },
			{ data: 'wcpo_email',			type: 'text' },		
			{ data: 'wcpo_terminal',		type: 'text' },	
			{ data: 'wcpo_max_weight',		type: 'text' },		
			{ data: 'wcpo_href',			type: 'text' }		
			],
			stretchH: 'all',
			rowHeaders: true,
			contextMenu: {
				items:{
					row_below:	{name:'$menu_insertBelow'},
					remove_row:	{name:'$menu_delete_row'}
				}
			},
			search: true,
			beforeRemoveRow: deleteRowCallback
		}";
    }

    /**
     * Метод формирует HTML код элементов управления перед таблицей
		*
     * @return string   Сформированный HTML код элементов управления перед таблицей
     */        
    protected function getHtmlControls()
    {
		// Переключатель типов пунктов
		$officeTypes = $this->officeList->getOfficeTypes();
		$officeTypesHTML = '<select id="office_type" class="office_type_select"><option value="0">' . __( 'All Office Types', WCPO_TEXT_DOMAIN ) . '</option>';
		foreach ($officeTypes as $officeTypeID => $officeTypeName)
			$officeTypesHTML .= "<option value=\"$officeTypeID\">$officeTypeName</option>";
		$officeTypesHTML .= '</select>';
			
        return
			$this->getHtmlSearch()      . ' | ' .		// Строка поиска
			$officeTypesHTML . 							// Переключатель типов пунктов
			$this->getHtmlLoadButton()  . ' ' . 		// Кнопка загрузить
			$this->getHtmlSaveButton();					// Кнопка сохранить
    }	
	
    /**
     * Метод формирует JavaScript код обработчиков который вставляется после инициализации таблицы
	 *
     * @return string   Сформированный JSON код
     */        
    protected function getJsHandlers()
    {
		$js = "var deleteAction = 'delete_{$this->ajaxAction}';" . PHP_EOL . // Название нового обработчика
			parent::getJsHandlers() . PHP_EOL .
			file_get_contents(WCPO_PATH . 'js/admin-post-type-select.js') . PHP_EOL .
			file_get_contents(WCPO_PATH . 'js/admin-ajax-load.js') . PHP_EOL .
			file_get_contents(WCPO_PATH . 'js/admin-ajax-save.js') . PHP_EOL .
			file_get_contents(WCPO_PATH . 'js/admin-ajax-delete.js');
		return $js;
	}
	
	/** 
	 * Получение списка пунктов
	 * Метод статичный, потому что вызывается Аяксом
	 */
    public static function getOffices( $officeTypeId = 0) 
    {
		
		$offices = array();
		
		// WP_Query arguments
		$args = array (
			'post_type'		=> array( 'pickup_office' ),
			'post_status'	=> array( 'publish' ),
			'meta_key' 		=> 'wcpo_city',
			'orderby'		=> 'meta_value',
			'order'			=> 'ASC',	
			'posts_per_page'=> -1,			
		);
		
		if ($officeTypeId > 0)
		$args['tax_query'] = array( 
			array (
				'taxonomy' => WCPO_OfficeList::OFFICE_TYPE,
				'field'    => 'id',
				'terms'    => $officeTypeId,
			)
		);
		
		// The Query
		$query = new WP_Query( $args );
		
		// The Loop
		if ( $query->have_posts() ) 
		{
			while ( $query->have_posts() ) 
			{
				$query->the_post();
				
				$post_id = get_the_id();
				$offices[] = array(
					'id'					=> $post_id,
					'wcpo_point_id'			=> get_the_title(),
					'wcpo_city'				=> get_post_meta( $post_id, 'wcpo_city', true ),
					'wcpo_delivery_period'	=> get_post_meta( $post_id, 'wcpo_delivery_period', true ),
					'wcpo_metro'			=> get_post_meta( $post_id, 'wcpo_metro', true ),
					'wcpo_zip'				=> get_post_meta( $post_id, 'wcpo_zip', true ),
					'wcpo_address'			=> get_post_meta( $post_id, 'wcpo_address', true ),
					'wcpo_open_hours'		=> get_post_meta( $post_id, 'wcpo_open_hours', true ),
					'wcpo_phone'			=> get_post_meta( $post_id, 'wcpo_phone', true ),
					'wcpo_email'			=> get_post_meta( $post_id, 'wcpo_email', true ),
					'wcpo_terminal'			=> get_post_meta( $post_id, 'wcpo_terminal', true ),
					'wcpo_max_weight'		=> get_post_meta( $post_id, 'wcpo_max_weight', true ),
					'wcpo_href'				=> get_post_meta( $post_id, 'wcpo_href', true ),
				);
			}
		}
		
		// Restore original Post Data
		wp_reset_postdata();
		
		return $offices;
	}
	
	/** 
	 * Ответ на запрос AJAX loadData
	 * Метод статичный, потому что вызывается Аяксом
	 */
    public static function ajaxGetOffices() 
    {
		// Значение элемента поиска
		$officeTypeId = isset( $_POST[ 'office_type' ] ) ? sanitize_text_field( $_POST[ 'office_type' ] ) : 0;
		$offices = self::getOffices( $officeTypeId );
		echo json_encode($offices);
		wp_die();
	}
	
	/** 
	 * Ответ на запрос AJAX saveData
	 * Метод статичный, потому что вызывается Аяксом
	 */
    public static function ajaxSaveOffices() 
    {
		// Значение элемента поиска
		$officeTypeId = isset( $_POST[ 'office_type' ] ) ? sanitize_text_field( $_POST[ 'office_type' ] ) : 0;
		
		// Данные по пунктам продаж
		$offices = isset( $_POST['offices'] ) ? json_decode( wp_unslash( $_POST['offices'] ) ) : array();
		//file_put_contents(WCPO_PATH . 'debug.log', 'POST: ' . var_export( $_POST, true ) . PHP_EOL . 'DATA: ' . var_export( $offices, true ));
		
		// Модифицируем данные
		foreach ($offices as $office)
		{
			
			// Для простого списка городов, если нет ID пункта берем его из города
			if ( empty ( $office->wcpo_point_id ) && ! empty ( $office->wcpo_city ) )
				$office->wcpo_point_id = $office->wcpo_city;
				
				
			if ( empty( $office->id ) && ! empty ( $office->wcpo_point_id )  )
			{
				// Добавляем запись
				$office->id = wp_insert_post( array (
					'post_title'	=> $office->wcpo_point_id,
					'post_status'	=> 'publish',
					'post_type'		=> WCPO_OfficeList::CPT,
				));
				
				// Если был указан тип, устанавливаем его таксономией
				if ($officeTypeId > 0)
					wp_set_post_terms( $office->id, array( $officeTypeId ), WCPO_OfficeList::OFFICE_TYPE );
			}
			
			if ( $office->id > 0 )
			{
				// Обновляем запись
				wp_update_post( array( 
					'ID'  			=> $office->id,
					'post_title'  	=> $office->wcpo_point_id,
				) );
				// Обновляем мету
				update_post_meta( $office->id, 'wcpo_city', 			$office->wcpo_city );
				update_post_meta( $office->id, 'wcpo_delivery_period',  $office->wcpo_delivery_period );
				update_post_meta( $office->id, 'wcpo_metro', 			$office->wcpo_metro );		
				update_post_meta( $office->id, 'wcpo_zip', 				$office->wcpo_zip );		
				update_post_meta( $office->id, 'wcpo_address', 			$office->wcpo_address );
				update_post_meta( $office->id, 'wcpo_open_hours', 		$office->wcpo_open_hours );
				update_post_meta( $office->id, 'wcpo_phone', 			$office->wcpo_phone );
				update_post_meta( $office->id, 'wcpo_email', 			$office->wcpo_email );
				update_post_meta( $office->id, 'wcpo_terminal', 		$office->wcpo_terminal );
				update_post_meta( $office->id, 'wcpo_max_weight', 		$office->wcpo_max_weight );				
				update_post_meta( $office->id, 'wcpo_href', 			$office->wcpo_href );				
			}
		}
		
		// Сброс кэша
		self::ajaxClearCache();
		
		$offices = self::getOffices( $officeTypeId );
		echo json_encode($offices);
		wp_die();
	}	
	
	/** 
	 * Ответ на запрос AJAX deleteData
	 * Метод статичный, потому что вызывается Аяксом
	 */
    public static function ajaxDeleteOffices() 
    {
		// массив ID на удаление
		$ids = isset( $_POST['ids'] ) ? json_decode( wp_unslash( $_POST['ids'] ) ) : array();
		
		// Удаляем записи 
		foreach ($ids as $id)
		{
			wp_delete_post( $id, true );
		}
		
		// Сброс кэша
		self::ajaxClearCache();
		
		
		echo json_encode(true);
		wp_die();
	}
	
	/** 
	 * Сброс кэша
	 * Метод статичный, потому что вызывается Аяксом
	 */
    public static function ajaxClearCache() 	
	{
		delete_transient( 'wcpo_cities' );
		delete_transient( 'wcpo_cities_data' );
		delete_transient( 'wcpo_office_types' );
		delete_transient( 'wcpo_offices' );
	}
	
}
