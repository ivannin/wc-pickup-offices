<?php
/**
 * Demo class of Metabox
 */
class WCPO_Meta_Box {

	public function __construct() {

		if ( is_admin() ) {
			add_action( 'load-post.php',     array( $this, 'init_metabox' ) );
			add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
		}

	}

	public function init_metabox() {

		add_action( 'add_meta_boxes',        array( $this, 'add_metabox' )         );
		add_action( 'save_post',             array( $this, 'save_metabox' ), 10, 2 );

	}

	public function add_metabox() {

		add_meta_box(
			'wcpo-metabox',
			__( 'Pickup Office Properties', 'wc-pickup-offices' ),
			array( $this, 'renderMetabox' ),
			'pickup_office',
			'advanced',
			'default'
		);

	}

	public function renderMetabox( $post ) {

		// Add nonce for security and authentication.
		wp_nonce_field( 'wcpo_nonce_action', 'wcpo_nonce' );

		// Retrieve an existing value from the database.
		$wcpo_city = get_post_meta( $post->ID, 'wcpo_city', true );
		$wcpo_address = get_post_meta( $post->ID, 'wcpo_address', true );
		$wcpo_zip = get_post_meta( $post->ID, 'wcpo_zip', true );
		$wcpo_open_hours = get_post_meta( $post->ID, 'wcpo_open_hours', true );
		$wcpo_terminal = get_post_meta( $post->ID, 'wcpo_terminal', true );

		// Set default values.
		if( empty( $wcpo_city ) ) $wcpo_city = '';
		if( empty( $wcpo_address ) ) $wcpo_address = '';
		if( empty( $wcpo_zip ) ) $wcpo_zip = '';
		if( empty( $wcpo_open_hours ) ) $wcpo_open_hours = '';
		if( empty( $wcpo_terminal ) ) $wcpo_terminal = '';

		// Form fields.
		echo '<table class="form-table">';

		echo '	<tr>';
		echo '		<th><label for="wcpo_city" class="wcpo_city_label">' . __( 'City', 'wc-pickup-offices' ) . '</label></th>';
		echo '		<td>';
		echo '			<input type="text" id="wcpo_city" name="wcpo_city" class="wcpo_city_field" placeholder="' . esc_attr__( 'City', 'wc-pickup-offices' ) . '" value="' . esc_attr__( $wcpo_city ) . '">';
		echo '			<p class="description">' . __( 'The city of pickup office', 'wc-pickup-offices' ) . '</p>';
		echo '		</td>';
		echo '	</tr>';

		echo '	<tr>';
		echo '		<th><label for="wcpo_address" class="wcpo_address_label">' . __( 'Address', 'wc-pickup-offices' ) . '</label></th>';
		echo '		<td>';
		echo '			<input type="text" id="wcpo_address" name="wcpo_address" class="wcpo_address_field" placeholder="' . esc_attr__( 'Address', 'wc-pickup-offices' ) . '" value="' . esc_attr__( $wcpo_address ) . '">';
		echo '			<p class="description">' . __( 'The address of pickup office', 'wc-pickup-offices' ) . '</p>';
		echo '		</td>';
		echo '	</tr>';

		echo '	<tr>';
		echo '		<th><label for="wcpo_zip" class="wcpo_zip_label">' . __( 'Zip', 'wc-pickup-offices' ) . '</label></th>';
		echo '		<td>';
		echo '			<input type="text" id="wcpo_zip" name="wcpo_zip" class="wcpo_zip_field" placeholder="' . esc_attr__( '', 'wc-pickup-offices' ) . '" value="' . esc_attr__( $wcpo_zip ) . '">';
		echo '			<p class="description">' . __( 'The zip of pickup office', 'wc-pickup-offices' ) . '</p>';
		echo '		</td>';
		echo '	</tr>';

		echo '	<tr>';
		echo '		<th><label for="wcpo_open_hours" class="wcpo_open_hours_label">' . __( 'Open hours', 'wc-pickup-offices' ) . '</label></th>';
		echo '		<td>';
		echo '			<input type="text" id="wcpo_open_hours" name="wcpo_open_hours" class="wcpo_open_hours_field" placeholder="' . esc_attr__( 'Open hours', 'wc-pickup-offices' ) . '" value="' . esc_attr__( $wcpo_open_hours ) . '">';
		echo '			<p class="description">' . __( 'Open hours of pickup offce', 'wc-pickup-offices' ) . '</p>';
		echo '		</td>';
		echo '	</tr>';

		echo '	<tr>';
		echo '		<th><label for="wcpo_terminal" class="wcpo_terminal_label">' . __( 'Terminal', 'wc-pickup-offices' ) . '</label></th>';
		echo '		<td>';
		echo '			<label><input type="checkbox" id="wcpo_terminal" name="wcpo_terminal" class="wcpo_terminal_field" value="' . $wcpo_terminal . '" ' . checked( $wcpo_terminal, 'checked', false ) . '> ' . __( '', 'wc-pickup-offices' ) . '</label>';
		echo '			<span class="description">' . __( 'Terminal is available', 'wc-pickup-offices' ) . '</span>';
		echo '		</td>';
		echo '	</tr>';

		echo '</table>';

	}

	public function save_metabox( $post_id, $post ) {

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
		$wcpo_new_address = isset( $_POST[ 'wcpo_address' ] ) ? sanitize_text_field( $_POST[ 'wcpo_address' ] ) : '';
		$wcpo_new_zip = isset( $_POST[ 'wcpo_zip' ] ) ? sanitize_text_field( $_POST[ 'wcpo_zip' ] ) : '';
		$wcpo_new_open_hours = isset( $_POST[ 'wcpo_open_hours' ] ) ? sanitize_text_field( $_POST[ 'wcpo_open_hours' ] ) : '';
		$wcpo_new_terminal = isset( $_POST[ 'wcpo_terminal' ] ) ? 'checked'  : '';

		// Update the meta field in the database.
		update_post_meta( $post_id, 'wcpo_city', $wcpo_new_city );
		update_post_meta( $post_id, 'wcpo_address', $wcpo_new_address );
		update_post_meta( $post_id, 'wcpo_zip', $wcpo_new_zip );
		update_post_meta( $post_id, 'wcpo_open_hours', $wcpo_new_open_hours );
		update_post_meta( $post_id, 'wcpo_terminal', $wcpo_new_terminal );

	}

}

new WCPO_Meta_Box;