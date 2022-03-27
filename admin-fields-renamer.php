<?php

/*
Plugin Name: Admin Fields Renamer
Plugin URI: http://kateryna.blog
Description: This plugin renames fields in the admin area for posts
Author: Kateryna Kodonenko
Author URI: http://kateryna.blog
Text Domain: adminrenamer
Version: 2.0
*/

// ===================================================================================================
// SECTION 1
// Add menu settings page for the plugin


//Add menu page for the plugin

function renamer_settings_page() {
  add_menu_page(
    'Admin Renamer',
    'Admin Renamer',
    'manage_options',
    'adminrenamer',
    'adminrenamer_settings_page_callback',
    'dashicons-wordpress-alt',
    100
  );
}

add_action( 'admin_menu', 'renamer_settings_page');

// ===================================================================================================
// SECTION 2
// Callback for the menu section page;

function adminrenamer_settings_page_callback(){
  //Double check user capabilities
  if ( !current_user_can('manage_options') ) {
    return;
  }
  ?>
  <div class="wrap">
<!-- Form output on the settings page !-->
  <h1><?php esc_html_e( get_admin_page_title() ); ?></h1>

  <form method="post" action="options.php">
    <!-- Display necessary hidden fields for settings -->
    <?php settings_fields( 'renamer_settings' ); ?>
    <!-- Display the settings sections for the page -->
    <?php do_settings_sections( 'adminrenamer' ); ?>
    <!-- Default Submit Button -->
    <?php submit_button(); ?>
  </form>

</div>
<?php
}

// ===================================================================================================
// SECTION 3
// Add settings page section


//Check if the plugin settings exist and if don't, then create them
function renamer_settings() {
//if( !get_options( 'renamer_settings') ) {
//  add_option( 'renamer_settings' );
//}
  // Define (at least) one section for our fields
 add_settings_section(
   // Unique identifier for the section
   'renamer_settings_section',
   // Section Title
   __( 'Renamer Fields', 'adminrenamer' ),
   // Callback for an optional description
   'renamer_settings_section_callback',
   // Admin page to add section to
   'adminrenamer'
 );


//Checkbox field for Column Remover Checkbox

 add_settings_field(
   'renamer_settings_checkbox',
   __( 'Column Remover', 'adminrenamer'),
   'renamer_settings_checkbox_callback',
   'adminrenamer',
   'renamer_settings_section',
   [
     'label' => 'Remove the Date Column'
   ]

 );


 // Checkbox field for Subscriptions activating checkbox

 add_settings_field(
  'renamer_settings_checkbox2',
  __( 'Subscriptions Indicator', 'adminrenamer'),
  'renamer_settings_checkbox_callback2',
  'adminrenamer',
  'renamer_settings_section',
  [
    'label' => 'Show subscriptions'
  ]

);


add_settings_field(
  // Unique identifier for field
  'renamer_settings_custom_text',
  // Field Title
  __( 'Custom Text', 'adminrenamer'),
  // Callback for field markup
  'renamer_settings_custom_text_callback',
  // Page to go on
  'adminrenamer',
  // Section to go in
  'renamer_settings_section'
);

 register_setting(
   'renamer_settings',
   'renamer_settings'
 );
}

add_action( 'admin_init', 'renamer_settings' );

// ===================================================================================================
// SECTION 4
// Callbacks for the settings section;

//callback for the settings sections

function renamer_settings_section_callback() {

    esc_html_e( 'Enter your custom names for the admin area sections', 'adminrenamer' );

}

//callback for the checkbox field for Column Remover

function renamer_settings_checkbox_callback( $args ) {
  $options = get_option( 'renamer_settings' );
  error_log(print_r($options, true));
  $checkbox1 = '';
  if( isset( $options[ 'checkbox1' ] ) ) {
    $checkbox1 = esc_html( $options['checkbox1'] );
}

  $html = '<input type="checkbox" id="renamer_settings_checkbox" name="renamer_settings[checkbox1]" value="1"' . checked( 1, $checkbox1, false ) . '/>';
	$html .= '&nbsp;';
	$html .= '<label for="renamer_settings[checkbox1]">' . $args['label'] . '</label>';

	echo $html;
}


//callback for the checkbox field for Subscriptions Activation

function renamer_settings_checkbox_callback2( $args ) {
  $options = get_option( 'renamer_settings' );
  $checkbox2 = '';
  if( isset( $options[ 'checkbox2' ] ) ) {
    $checkbox2 = esc_html( $options['checkbox2'] );
}

  $html = '<input type="checkbox" id="renamer_settings_checkbox2" name="renamer_settings[checkbox2]" value="1"' . checked( 1, $checkbox2, false ) . '/>';
	$html .= '&nbsp;';
	$html .= '<label for="renamer_settings[checkbox2]">' . $args['label'] . '</label>';

	echo $html;
}



//callback for the Column Renamer custom text field


function renamer_settings_custom_text_callback() {

  $options = get_option( 'renamer_settings' );

	$custom_text = '';
	if( isset( $options[ 'custom_text' ] ) ) {
		$custom_text = esc_html( $options['custom_text'] );
	}

  echo '<input type="text" id="renamer_customtext" name="renamer_settings[custom_text]" value="' . $custom_text . '" />';

}

// ===================================================================================================
// SECTION 5
// Settings section performing custom actions

// rename the author column in post admin field - tied to custom text field
function rename_columns ( $columns ){
$options = get_option( 'renamer_settings' );
$columns ['author'] = esc_html( $options['custom_text'] );
return $columns;
}

add_filter ('manage_post_posts_columns', 'rename_columns', 30 );


// unset the date column on the posts admin field tied to the checkbox field for Column Remover

function my_manage_columns( $columns ) {
 unset($columns['date']);
 return $columns;
}

function my_column_init() {
  $options = get_option( 'renamer_settings' );
    if( isset( $options[ 'checkbox1' ]) && $options['checkbox1'] == '1' ) {
      add_filter( 'manage_post_posts_columns' , 'my_manage_columns' );
    }
}

add_action( 'admin_init' , 'my_column_init' );



// Subscriptions Activation section


//adding a new column tied to WooCommerce Subscriptions
 function add_product_column( $columns ) {
    //add column
    $columns['new_column'] = __( 'Simple Sub', 'woocommerce' );

    return $columns;
}
add_filter( 'manage_edit-product_columns', 'add_product_column', 10, 1 );


// function to populate the products column

add_action( 'manage_product_posts_custom_column', 'subs_populate_admin', 10, 2 );

// Subscription activation checkbox that will enable the subscription indicator column
// Tied to checkbox Subscriptions activation

$options = get_option( 'renamer_settings' );
if( isset( $options[ 'checkbox2' ]) && $options['checkbox1'] == '1' ) {
function subs_populate_admin( $column_name ) {

  if ( $column_name == 'new_column' ) {
    $product = wc_get_product ();

        if ( WC_Subscriptions_Product::is_subscription( $product) ) {
        echo 'yes';
    }

  }
}
}





?>
