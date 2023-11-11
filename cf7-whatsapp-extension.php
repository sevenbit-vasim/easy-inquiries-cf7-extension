<?php
/*
Plugin Name: Easy Inquiries - CF7 Extension
Description: A plugin to send Contact Form 7 inquiries to WhatsApp.
Version: 1.0.0
Stable tag: 1.0.0  // Specify the stable tag
Author: Vasim Shaikh
*/


// Hook into admin_init to check if Contact Form 7 is active
add_action('admin_init', 'check_cf7_active');

function check_cf7_active() {
    // Check if Contact Form 7 is not active
    if (!is_plugin_active('contact-form-7/wp-contact-form-7.php')) {
        // Deactive plugin
        deactivate_plugins(plugin_basename(__FILE__));
        // CF7 is not active, show a notice or take appropriate action
        add_action('admin_notices', 'cf7_whatsapp_extension_activation_notice');
    }
}

function cf7_whatsapp_extension_activation_notice() {
    echo '<div class="error"><p>Contact Form 7 is required for CF7 to WhatsApp Extension. Please install and activate CF7 to use this extension.</p></div>';
}



// Add a new action hook to send the inquiry to WhatsApp.
add_action( 'wpcf7_after_submit', 'cf7_whatsapp_extension_send_inquiry' );

// Send the inquiry to WhatsApp.
function cf7_whatsapp_extension_send_inquiry( $contact_form ) {

    // Get the recipient's WhatsApp phone number.
    $whatsapp_number = get_option( 'cf7_whatsapp_extension_phone_number' );

    // Get the inquiry data.
    $inquiry_data = $contact_form->posted_data;

    // Create a WhatsApp message.
    $whatsapp_message = '';
    foreach ( $inquiry_data as $field_name => $field_value ) {
        $whatsapp_message .= "$field_name: $field_value\n";
    }

    // Send the WhatsApp message.
    wp_remote_get( "https://wa.me/$whatsapp_number/?text=$whatsapp_message" );
}


// Settings to store Whatsapp number where details are forwarded
function cf7_whatsapp_extension_admin_init() {
    add_settings_section('cf7_whatsapp_extension_section', 'CF7 to WhatsApp Extension Settings', 'cf7_whatsapp_extension_section_callback', 'general');
    
    add_settings_field('cf7_whatsapp_extension_phone_number', 'WhatsApp Number', 'cf7_whatsapp_extension_phone_number_callback', 'general', 'cf7_whatsapp_extension_section');
    
    register_setting('general', 'cf7_whatsapp_extension_phone_number');
}

function cf7_whatsapp_extension_section_callback() {
    echo '<p>Please add full number with country codd. Dont prefix Plus (+) Sign. For example, India +91 then 91789456123</p>';
}

function cf7_whatsapp_extension_phone_number_callback() {
    $value = get_option('cf7_whatsapp_extension_phone_number', '');
    echo '<input type="text" id="cf7_whatsapp_extension_phone_number" name="cf7_whatsapp_extension_phone_number" value="' . esc_attr($value) . '" />';
}

add_action('admin_init', 'cf7_whatsapp_extension_admin_init');
