<?php
/**
 * ����� �������� �� �������� � ��������� ������ ������� ����������
 */
class WCPO_OfficeList
{
		
	/**
	 * ��������� �������� ������� ����������
	 * @var WCPO_OfficeTable
	 */
	protected $officeTable;
	
	/**
	 * ����������� ������
	 */
	public function __construct(  )
	{
			
		// ����������� Custom Post Type
		$this->registerCPT();
		
		// ����������� ���������� ����� �������
		$this->registerTypeTaxonomy();
		
		// � ������ �������
		if ( is_admin() ) 
		{
			// ������������� ���������
			add_action( 'load-post.php',     array( $this, 'initMetabox' ) );
			add_action( 'load-post-new.php', array( $this, 'initMetabox' ) );
			
			// ������� � �������
			add_filter('manage_' . self::CPT . '_posts_columns', 		array( $this, 'getPostColumnsList' ) );
			add_action('manage_' . self::CPT . '_posts_custom_column', 	array( $this, 'showColumnValue' ), 10, 2 );
			add_filter( 'request', array( $this, 'sortByColumn' ) );	// ���������� � �������
			
			// ��������� �������� �������
			add_submenu_page( 
				'edit.php?post_type=' . self::CPT, 
				__( 'Pickup Office Table', WCPO_TEXT_DOMAIN ), 
				__( 'Edit In Table', WCPO_TEXT_DOMAIN ), 
				'manage_options', 
				'pickup_office_table', 
				array( $this, 'renderTablePage') 
			);
			
			$this->officeTable = new WCPO_OfficeTable( $this );
			
			
		}		
	}
	
	/* -------------- ����������� Custom Post Type -------------- */
	/**
	 * @const 	��� �������
	 */
	const CPT = 'pickup_office';
	
	/**
	 * ����������� Custom Post Type
	 */	
	protected function registerCPT()
	{
		$labels = array(
			'name'                  => _x( 'Pickup Offices', 'Post Type General Name', WCPO_TEXT_DOMAIN ),
			'singular_name'         => _x( 'Pickup Office', 'Post Type Singular Name', WCPO_TEXT_DOMAIN ),
			'menu_name'             => __( 'Pickup Office List', WCPO_TEXT_DOMAIN ),
			'name_admin_bar'        => __( 'Pickup Office List', WCPO_TEXT_DOMAIN ),
			'archives'              => __( 'Pickup Offices Archives', WCPO_TEXT_DOMAIN ),
			'parent_item_colon'     => __( 'Parent Office:', WCPO_TEXT_DOMAIN ),
			'all_items'             => __( 'All Offices', WCPO_TEXT_DOMAIN ),
			'add_new_item'          => __( 'Add New Office', WCPO_TEXT_DOMAIN ),
			'add_new'               => __( 'Add Office', WCPO_TEXT_DOMAIN ),
			'new_item'              => __( 'New Office', WCPO_TEXT_DOMAIN ),
			'edit_item'             => __( 'Edit Office', WCPO_TEXT_DOMAIN ),
			'update_item'           => __( 'Update Office', WCPO_TEXT_DOMAIN ),
			'view_item'             => __( 'View Office', WCPO_TEXT_DOMAIN ),
			'search_items'          => __( 'Search Office', WCPO_TEXT_DOMAIN ),
			'not_found'             => __( 'Not found', WCPO_TEXT_DOMAIN ),
			'not_found_in_trash'    => __( 'Not found in Trash', WCPO_TEXT_DOMAIN ),
			'featured_image'        => __( 'Featured Image', WCPO_TEXT_DOMAIN ),
			'set_featured_image'    => __( 'Set featured image', WCPO_TEXT_DOMAIN ),
			'remove_featured_image' => __( 'Remove featured image', WCPO_TEXT_DOMAIN ),
			'use_featured_image'    => __( 'Use as featured image', WCPO_TEXT_DOMAIN ),
			'insert_into_item'      => __( 'Insert into office', WCPO_TEXT_DOMAIN ),
			'uploaded_to_this_item' => __( 'Uploaded to this office', WCPO_TEXT_DOMAIN ),
			'items_list'            => __( 'Offices list', WCPO_TEXT_DOMAIN ),
			'items_list_navigation' => __( 'Offices list navigation', WCPO_TEXT_DOMAIN ),
			'filter_items_list'     => __( 'Filter office list', WCPO_TEXT_DOMAIN ),
		);
		
		$args = array(
			'label'                 => __( 'Pickup Office', WCPO_TEXT_DOMAIN ),
			'description'           => __( 'Pickup Offices List', WCPO_TEXT_DOMAIN ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'comments', /*'custom-fields',*/ ),
			'taxonomies'            => null,
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 56,
			'menu_icon'             => 'dashicons-store',
			'show_in_admin_bar'     => false,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,		
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
		);
		
		register_post_type( self::CPT, $args );		
	}	
	
	/**
	 * @const 	���������� - ��� ����� ����������
	 */
	const OFFICE_TYPE = 'pickup_office_type';
	
	/**
	 * ����������� ���������� ����� �������
	 */	
	protected function registerTypeTaxonomy() 
	{
		$labels = array(
			'name'                       => _x( 'Pickup Office Types', 'Taxonomy General Name', WCPO_TEXT_DOMAIN ),
			'singular_name'              => _x( 'Pickup Office Type', 'Taxonomy Singular Name', WCPO_TEXT_DOMAIN ),
			'menu_name'                  => __( 'Pickup Office Type', WCPO_TEXT_DOMAIN ),
			'all_items'                  => __( 'All Office Types', WCPO_TEXT_DOMAIN ),
			'parent_item'                => __( 'Parent Office Type', WCPO_TEXT_DOMAIN ),
			'parent_item_colon'          => __( 'Parent Office Type:', WCPO_TEXT_DOMAIN ),
			'new_item_name'              => __( 'New Office Type', WCPO_TEXT_DOMAIN ),
			'add_new_item'               => __( 'Add Office Type', WCPO_TEXT_DOMAIN ),
			'edit_item'                  => __( 'Edit Office Type', WCPO_TEXT_DOMAIN ),
			'update_item'                => __( 'Update Office Type', WCPO_TEXT_DOMAIN ),
			'view_item'                  => __( 'View Office Type', WCPO_TEXT_DOMAIN ),
			'separate_items_with_commas' => __( 'Separate types with commas', WCPO_TEXT_DOMAIN ),
			'add_or_remove_items'        => __( 'Add or remove types', WCPO_TEXT_DOMAIN ),
			'choose_from_most_used'      => __( 'Choose from the most used', WCPO_TEXT_DOMAIN ),
			'popular_items'              => __( 'Popular types', WCPO_TEXT_DOMAIN ),
			'search_items'               => __( 'Search Office Types', WCPO_TEXT_DOMAIN ),
			'not_found'                  => __( 'Not Found', WCPO_TEXT_DOMAIN ),
			'no_terms'                   => __( 'No Office Types', WCPO_TEXT_DOMAIN ),
			'items_list'                 => __( 'Office Type List', WCPO_TEXT_DOMAIN ),
			'items_list_navigation'      => __( 'Office Type List navigation', WCPO_TEXT_DOMAIN ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => false,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => false,
		);
		register_taxonomy( self::OFFICE_TYPE, array( self::CPT ), $args );
	}
	
	
	
	/* -------------- �������� � �������� ������ -------------- */
	/**
	 * ������������� ���������
	 */
	public function initMetabox() 
	{	
		add_action( 'add_meta_boxes',        array( $this, 'addMetabox' )         );
		add_action( 'save_post',             array( $this, 'saveMetabox' ), 10, 2 );
		
	}
	
	/**
	 * ���������� ���������
	 */
	public function addMetabox() 
	{
		add_meta_box(												
			'wcpo-metabox',											// id		Meta box ID (used in the 'id' attribute for the meta box)
			__( 'Pickup Office Properties', WCPO_TEXT_DOMAIN ),		// title	Title of the meta box
			array( $this, 'renderMetabox' ),						// callback	Function that fills the box with the desired content. The function should echo its output.
			self::CPT,												// screen	The screen or screens on which to show the box (such as a post type, 'link', or 'comment')
			'advanced',												// context 	The context within the screen where the boxes should display. 
			'high'													// priority	The priority within the context where the boxes should show 
		);
	}

	/**
	 * ����������� ���������
	 */	
	public function renderMetabox( $post ) {
		
		// Add nonce for security and authentication.
		wp_nonce_field( 'wcpo_nonce_action', 'wcpo_nonce' );
		
		// Retrieve an existing value from the database.
		$wcpo_city 				= get_post_meta( $post->ID, 'wcpo_city', true );
		$wcpo_delivery_period 	= get_post_meta( $post->ID, 'wcpo_delivery_period', true );
		$wcpo_metro				= get_post_meta( $post->ID, 'wcpo_metro', true );
		$wcpo_zip 				= get_post_meta( $post->ID, 'wcpo_zip', true );
		$wcpo_address 			= get_post_meta( $post->ID, 'wcpo_address', true );
		$wcpo_open_hours 		= get_post_meta( $post->ID, 'wcpo_open_hours', true );
		$wcpo_phone 			= get_post_meta( $post->ID, 'wcpo_phone', true );
		$wcpo_email 			= get_post_meta( $post->ID, 'wcpo_email', true );
		$wcpo_terminal 			= get_post_meta( $post->ID, 'wcpo_terminal', true );
		$wcpo_max_weight 		= get_post_meta( $post->ID, 'wcpo_max_weight', true );

		// Set default values.
		if( empty( $wcpo_city ) ) 				$wcpo_city = '';
		if( empty( $wcpo_delivery_period ) ) 	$wcpo_delivery_period = '';
		if( empty( $wcpo_metro ) ) 				$wcpo_metro = '';
		if( empty( $wcpo_zip ) ) 				$wcpo_zip = '';
		if( empty( $wcpo_address ) ) 			$wcpo_address = '';
		if( empty( $wcpo_open_hours ) ) 		$wcpo_open_hours = '';
		if( empty( $wcpo_phone ) ) 				$wcpo_phone = '';
		if( empty( $wcpo_email ) ) 				$wcpo_email = '';
		if( empty( $wcpo_terminal ) ) 			$wcpo_terminal = false;
		if( empty( $wcpo_max_weight ) ) 		$wcpo_max_weight = '';
		
		/* Form fields:
		 *	title					��� ������ ���������� �� ���� ���������� ����� 
		 *	wcpo_city				����� 
		 * 	wcpo_delivery_period	���� ��������
		 * 	wcpo_metro				�����
		 * 	wcpo_zip				������
		 * 	wcpo_address			�����
		 * 	wcpo_open_hours			����� ������
		 * 	wcpo_phone				�������
		 * 	wcpo_email				��. �����
		 * 	wcpo_terminal			������� ��������� 
		 * 	wcpo_max_weight			����������� �� ����
		 */
		echo '<table class="form-table">';
		
		/* wcpo_city				����� */
		echo '	<tr>';
		echo '		<th><label for="wcpo_city" class="wcpo_city_label">' . __( 'City', WCPO_TEXT_DOMAIN ) . '</label></th>';
		echo '		<td>';
		echo '			<input type="text" id="wcpo_city" name="wcpo_city" class="wcpo_city_field" placeholder="' . esc_attr__( 'City', WCPO_TEXT_DOMAIN ) . '" value="' . esc_attr__( $wcpo_city ) . '">';
		echo '			<p class="description">' . __( 'The city of pickup office', WCPO_TEXT_DOMAIN ) . '</p>';
		echo '		</td>';
		echo '	</tr>';
		
		/* wcpo_delivery_period	���� �������� */
		echo '	<tr>';
		echo '		<th><label for="wcpo_delivery_period" class="wcpo_delivery_period_label">' . __( 'Estimated Delivery Period', WCPO_TEXT_DOMAIN ) . '</label></th>';
		echo '		<td>';
		echo '			<input type="text" id="wcpo_delivery_period" name="wcpo_delivery_period" class="wcpo_delivery_period_field" placeholder="' . esc_attr__( 'Estimated Delivery Period', WCPO_TEXT_DOMAIN ) . '" value="' . esc_attr__( $wcpo_delivery_period ) . '">';
		echo '			<p class="description">' . __( 'Estimated Delivery Period to this pickup office', WCPO_TEXT_DOMAIN ) . '</p>';
		echo '		</td>';
		echo '	</tr>';	

		/* wcpo_metro				����� */
		echo '	<tr>';
		echo '		<th><label for="wcpo_metro" class="wcpo_metro_label">' . __( 'Subway', WCPO_TEXT_DOMAIN ) . '</label></th>';
		echo '		<td>';
		echo '			<input type="text" id="wcpo_metro" name="wcpo_metro" class="wcpo_metro_field" placeholder="' . esc_attr__( 'Subway station', WCPO_TEXT_DOMAIN ) . '" value="' . esc_attr__( $wcpo_metro ) . '">';
		echo '			<p class="description">' . __( 'The nearest subway station', WCPO_TEXT_DOMAIN ) . '</p>';
		echo '		</td>';
		echo '	</tr>';			
		
		/* wcpo_zip				������ */
		echo '	<tr>';
		echo '		<th><label for="wcpo_zip" class="wcpo_zip_label">' . __( 'Zip', WCPO_TEXT_DOMAIN ) . '</label></th>';
		echo '		<td>';
		echo '			<input type="text" id="wcpo_zip" name="wcpo_zip" class="wcpo_zip_field" placeholder="' . esc_attr__( 'Zip', WCPO_TEXT_DOMAIN ) . '" value="' . esc_attr__( $wcpo_zip ) . '">';
		echo '			<p class="description">' . __( 'The zip of pickup office', WCPO_TEXT_DOMAIN ) . '</p>';
		echo '		</td>';
		echo '	</tr>';		
		
		/* wcpo_address			����� */
		echo '	<tr>';
		echo '		<th><label for="wcpo_address" class="wcpo_address_label">' . __( 'Address', WCPO_TEXT_DOMAIN ) . '</label></th>';
		echo '		<td>';
		echo '			<input type="text" id="wcpo_address" name="wcpo_address" class="wcpo_address_field" placeholder="' . esc_attr__( 'Address', WCPO_TEXT_DOMAIN ) . '" value="' . esc_attr__( $wcpo_address ) . '">';
		echo '			<p class="description">' . __( 'The address of pickup office', WCPO_TEXT_DOMAIN ) . '</p>';
		echo '		</td>';
		echo '	</tr>';
		
		/* wcpo_open_hours			����� ������ */
		echo '	<tr>';
		echo '		<th><label for="wcpo_open_hours" class="wcpo_open_hours_label">' . __( 'Open Hours', WCPO_TEXT_DOMAIN ) . '</label></th>';
		echo '		<td>';
		echo '			<textarea id="wcpo_open_hours" name="wcpo_open_hours" class="wcpo_open_hours_field" placeholder="' . esc_attr__( 'Open Hours', WCPO_TEXT_DOMAIN ) . '">' . esc_attr__( $wcpo_open_hours ) . '</textarea>';
		echo '			<p class="description">' . __( 'Open hours of pickup offce', WCPO_TEXT_DOMAIN ) . '</p>';
		echo '		</td>';
		echo '	</tr>';
		
		/* wcpo_phone				������� */
		echo '	<tr>';
		echo '		<th><label for="wcpo_phone" class="wcpo_phone_label">' . __( 'Phone', WCPO_TEXT_DOMAIN ) . '</label></th>';
		echo '		<td>';
		echo '			<input type="text" id="wcpo_phone" name="wcpo_phone" class="wcpo_phone_field" placeholder="' . esc_attr__( 'Phone', WCPO_TEXT_DOMAIN ) . '" value="' . esc_attr__( $wcpo_phone ) . '">';
		echo '			<p class="description">' . __( 'The phone of pickup office', WCPO_TEXT_DOMAIN ) . '</p>';
		echo '		</td>';
		echo '	</tr>';	

		/* wcpo_email				��. ����� */
		echo '	<tr>';
		echo '		<th><label for="wcpo_email" class="wcpo_email_label">' . __( 'E-mail', WCPO_TEXT_DOMAIN ) . '</label></th>';
		echo '		<td>';
		echo '			<input type="text" id="wcpo_email" name="wcpo_email" class="wcpo_email_field" placeholder="' . esc_attr__( 'E-mail', WCPO_TEXT_DOMAIN ) . '" value="' . esc_attr__( $wcpo_email ) . '">';
		echo '			<p class="description">' . __( 'The e-mail of pickup office', WCPO_TEXT_DOMAIN ) . '</p>';
		echo '		</td>';
		echo '	</tr>';	
		
		/* wcpo_terminal			������� ��������� */
		echo '	<tr>';
		echo '		<th><label for="wcpo_terminal" class="wcpo_terminal_label">' . __( 'Terminal', WCPO_TEXT_DOMAIN ) . '</label></th>';
		echo '		<td>';
		echo '			<input type="checkbox" id="wcpo_terminal" name="wcpo_terminal" class="wcpo_terminal_field" value="1" ' . checked( $wcpo_terminal, '1', false ) . ' />';
		echo '			<span class="description">' . __( 'Terminal is available', WCPO_TEXT_DOMAIN ) . '</span>';
		echo '		</td>';
		echo '	</tr>';
		
		/* wcpo_max_weight			����������� �� ���� */
		echo '	<tr>';
		echo '		<th><label for="wcpo_max_weight" class="wcpo_max_weight_label">' . __( 'Max. Weight', WCPO_TEXT_DOMAIN ) . '</label></th>';
		echo '		<td>';
		echo '			<input type="text" id="wcpo_max_weight" name="wcpo_max_weight" class="wcpo_max_weight_field" placeholder="' . esc_attr__( 'Max. Weight', WCPO_TEXT_DOMAIN ) . '" value="' . esc_attr__( $wcpo_max_weight ) . '">';
		echo '			<p class="description">' . __( 'Max. Weight Limit', WCPO_TEXT_DOMAIN ) . '</p>';
		echo '		</td>';
		echo '	</tr>';			
		
		echo '</table>';
		
	}

	/**
	 * ���������� ���������
	 */	
	public function saveMetabox( $post_id, $post ) {
		
		// Add nonce for security and authentication.
		$nonce_name   = isset( $_POST['wcpo_nonce'] ) ? $_POST['wcpo_nonce'] : '';
		$nonce_action = 'wcpo_nonce_action';
		
		// Check if a nonce is set.
		if ( ! isset( $nonce_name ) )
			return;
		
		// Check if a nonce is valid.
		if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) )
			return;
		
		// Check if the user has permissions to save data.
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return;
		
		// Check if it's not an autosave.
		if ( wp_is_post_autosave( $post_id ) )
			return;
		
		// Check if it's not a revision.
		if ( wp_is_post_revision( $post_id ) )
			return;
		
		// Sanitize user input.
		$wcpo_new_city = isset( $_POST[ 'wcpo_city' ] ) ? sanitize_text_field( $_POST[ 'wcpo_city' ] ) : '';
		$wcpo_new_delivery_period = isset( $_POST[ 'wcpo_delivery_period' ] ) ? sanitize_text_field( $_POST[ 'wcpo_delivery_period' ] ) : '';
		$wcpo_new_metro = isset( $_POST[ 'wcpo_metro' ] ) ? sanitize_text_field( $_POST[ 'wcpo_metro' ] ) : '';		
		$wcpo_new_zip = isset( $_POST[ 'wcpo_zip' ] ) ? sanitize_text_field( $_POST[ 'wcpo_zip' ] ) : '';		
		$wcpo_new_address = isset( $_POST[ 'wcpo_address' ] ) ? sanitize_text_field( $_POST[ 'wcpo_address' ] ) : '';
		$wcpo_new_open_hours = isset( $_POST[ 'wcpo_open_hours' ] ) ? implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST['wcpo_open_hours'] ) ) ) : '';
		$wcpo_new_phone = isset( $_POST[ 'wcpo_phone' ] ) ? sanitize_text_field( $_POST[ 'wcpo_phone' ] ) : '';		
		$wcpo_new_email = isset( $_POST[ 'wcpo_email' ] ) ? sanitize_text_field( $_POST[ 'wcpo_email' ] ) : '';			
		$wcpo_new_terminal = isset( $_POST[ 'wcpo_terminal' ] ) ? '1'  : '0';
		$wcpo_new_max_weight = isset( $_POST[ 'wcpo_max_weight' ] ) ? sanitize_text_field( $_POST[ 'wcpo_email' ] ) : '';			
		
		// Update the meta field in the database.
		update_post_meta( $post_id, 'wcpo_city', 			$wcpo_new_city );
		update_post_meta( $post_id, 'wcpo_delivery_period', $wcpo_new_delivery_period );
		update_post_meta( $post_id, 'wcpo_metro', 			$wcpo_new_metro );		
		update_post_meta( $post_id, 'wcpo_zip', 			$wcpo_new_zip );		
		update_post_meta( $post_id, 'wcpo_address', 		$wcpo_new_address );
		update_post_meta( $post_id, 'wcpo_open_hours', 		$wcpo_new_open_hours );
		update_post_meta( $post_id, 'wcpo_phone', 			$wcpo_new_phone );
		update_post_meta( $post_id, 'wcpo_email', 			$wcpo_new_email );
		update_post_meta( $post_id, 'wcpo_terminal', 		$wcpo_new_terminal );
		update_post_meta( $post_id, 'wcpo_max_weight', 		$wcpo_new_max_weight );
	}
	
	/* -------------- ������ � ������� Post Manage Page -------------- */
	/**
	 * ��������� ������� � ������� 
	 * @param mixed $columns	������ �������
	 * @retun mixed
	 */
	public function getPostColumnsList ( $columns ) 
	{
		 $columns['title'] 			= __( 'Pickup Office ID', 	WCPO_TEXT_DOMAIN );
		 $columns['wcpo_city'] 		= __( 'City', 				WCPO_TEXT_DOMAIN );
		 $columns['wcpo_metro'] 	= __( 'Subway',				WCPO_TEXT_DOMAIN );
		 $columns['wcpo_zip'] 		= __( 'Zip', 				WCPO_TEXT_DOMAIN );
		 $columns['wcpo_address'] 	= __( 'Address', 			WCPO_TEXT_DOMAIN );
		 return $columns;
	}
	
	/**
	 * ����� �������� ������� � ������� 
	 * @param string $column	��� �������
	 * @param string $post_id	ID ������
	 * @retun mixed
	 */
	public function showColumnValue ( $column, $post_id ) 
	{
		switch ( $column ) 
		{
			case 'wcpo_city' :
				echo get_post_meta( $post_id , 'wcpo_city' , true ); 
				break;
				
			case 'wcpo_metro' :
				echo get_post_meta( $post_id , 'wcpo_metro' , true ); 
				break;				
			
			case 'wcpo_zip' :
				echo get_post_meta( $post_id , 'wcpo_zip' , true ); 
				break;
			
			case 'wcpo_address' :
				echo get_post_meta( $post_id , 'wcpo_address' , true ); 
				break;
		}
	}

	/**
	 * ���������� � ������� 
	 * @param ����� $vars	��� �������
	 * @param string $post_id	ID ������
	 * @retun mixed
	 */	
	function sortByColumn( $vars ) {
		if ( isset( $vars['post_type'] ) && $vars['post_type'] == self::CPT )
		{
			// ���� ���������� �� �����������
			if ( ! isset( $vars['orderby'] ) ) 
			{
				$vars = array_merge( $vars, array(
				'meta_key' => 'wcpo_city',
				'orderby' => 'meta_value',
				'order' => 'asc' // don't use this; blocks toggle UI
				) );
			}
		}
		return $vars;
	}	

	/* -------------- ��������� �������� ������� ���������� -------------- */
	/**
	 * ��������� ������� � ������� 
	 */
	public function renderTablePage ( ) 
	{
		echo '<h1>', __( 'Pickup Office List', WCPO_TEXT_DOMAIN ), '</h1>';
		echo $this->officeTable;
	}
	
	
	/* -------------- �������� � ������� -------------- */
	/**
	 * ���������� ������ �������
	 * @retun mixed
	 */
	public function getCities() 
	{
		global $wpdb;
		$values = $wpdb->get_col("SELECT meta_value
			FROM $wpdb->postmeta WHERE meta_key = 'wcpo_city'" );		
		return $values;
	}	
	
	/**
	 * ���������� ������ ����� ������
	 * @retun mixed
	 */
	public function getOfficeTypes() 
	{
		// ���� ������	
		$offceTypes = array();
		
		// The Term Query
		$term_query = new WP_Term_Query( array (
		'taxonomy'	=> array( self::OFFICE_TYPE ),
		'fields'    => 'id=>name',
		'get'		=> 'all',
		));
		
		// The Loop
		if ( ! empty( $term_query ) && ! is_wp_error( $term_query ) ) 
		{
			$offceTypes = $term_query->terms;
		}
		
		return $offceTypes;
	}	
	
	
	
	
	
}