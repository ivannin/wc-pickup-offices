<?php
/**
 * Основной класс плагина
 */
class WCPO_Manager
{
	/**
	 * @var WCPO_OfficeList Класс работы со списком пунктов самовывоза
	 */
	public $officeList;
	
	/**
	 * @var string 			Путь к файлам плагина
	 */
	protected $baseDir;
	
	/**
	 * @var string 			URL плагина
	 */
	protected $baseURL;
	
	
	
	/**
	 * Конструктор класса
	 * @param string $baseDir	Путь к файлам плагина
	 * @param string $baseURL	URL плагина
	 */
	public function __construct( $baseDir, $baseURL )
	{
		// Инициализируем свойства
		$this->baseDir = $baseDir;
		$this->baseURL = $baseURL;
		$this->officeList = new WCPO_OfficeList();
		
		// В режиме админки
		if ( is_admin() ) 
		{
			// Загрузка стилей и скриптов
			add_action( 'admin_enqueue_scripts', array( $this, 'loadAdminStyles') );

		}
			
			
	}
	
	/**
	 * Загрузка скриптов и CSS для админки
	 */
	public function loadAdminStyles()
	{
		wp_register_style( 'wcpo_admin_css', $this->baseURL . 'css/admin.css', false, '1.0.0' );
		wp_enqueue_style( 'wcpo_admin_css' );		
	}	
}