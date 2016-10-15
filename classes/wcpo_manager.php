<?php
/**
 * Основной класс плагина
 */
class WCPO_Manager
{
	/**
	 * Путь к файлам плагина
	 * @var string
	 */
	protected $baseDir;
	
	/**
	 * URL плагина
	 * @var string 			
	 */
	protected $baseURL;
	
	/**
	 * Объект работы со списком пунктов самовывоза
	 * @var WCPO_OfficeList 
	 */
	public $officeList;

	/**
	 * Объект работы с фронтэндом
	 * @var WCPO_FrontEnd
	 */
	public $frontend;	
	
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
		
		// Инициализируем объекты
		$this->officeList 	= new WCPO_OfficeList();
		$this->frontend 	= new WCPO_FrontEnd( $this );
		
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