<?php

/*
Plugin Name: Admin Fields Renamer
Plugin URI: http://kateryna.blog
Description: This plugin renames fields in the admin area for posts
Author: Kateryna Kodonenko
Author URI: http://kateryna.blog
Text Domain: adminrenamer
Version: 1.0
*/


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

//Callback for the menu page

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

//Add settings page section

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


//Checkbox field

 add_settings_field(
   'renamer_settings_checkbox',
   __( 'Checkbox', 'adminrenamer'),
   'renamer_settings_checkbox_callback',
   'adminrenamer',
   'renamer_settings_section',
   [
     'label' => 'Remove the Date Column'
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

//callback for the settings sections

function renamer_settings_section_callback() {

    esc_html_e( 'Enter your custom names for the admin area sections', 'adminrenamer' );

}

//callback for the checkbox field

function renamer_settings_checkbox_callback( $args ) {
  $options = get_option( 'renamer_settings' );
  $checkbox = '';
  if( isset( $options[ 'checkbox' ] ) ) {
    $checkbox = esc_html( $options['checkbox'] );
}

  $html = '<input type="checkbox" id="renamer_settings_checkbox" name="renamer_settings[checkbox]" value="1"' . checked( 1, $checkbox, false ) . '/>';
	$html .= '&nbsp;';
	$html .= '<label for="renamer_settings_checkbox">' . $args['label'] . '</label>';

	echo $html;
}

//callback for the custom text

//custom text field in the settings sections callback

function renamer_settings_custom_text_callback() {

  $options = get_option( 'renamer_settings' );

	$custom_text = '';
	if( isset( $options[ 'custom_text' ] ) ) {
		$custom_text = esc_html( $options['custom_text'] );
	}

  echo '<input type="text" id="renamer_customtext" name="renamer_settings[custom_text]" value="' . $custom_text . '" />';

}

// code to rename the post columns
function rename_columns ( $columns ){
$options = get_option( 'renamer_settings' );
$columns ['author'] = esc_html( $options['custom_text'] );
return $columns;
}

add_filter ('manage_posts_columns', 'rename_columns', 30 );



//Unset post admin $columns

function my_manage_columns( $columns ) {
 unset($columns['date']);
 return $columns;
}

function my_column_init() {
  $options = get_option( 'renamer_settings' );
    if( isset( $options[ 'checkbox' ]) && $options['checkbox'] == '1' ) {
      add_filter( 'manage_posts_columns' , 'my_manage_columns' );
    }
}

add_action( 'admin_init' , 'my_column_init' );


?>
