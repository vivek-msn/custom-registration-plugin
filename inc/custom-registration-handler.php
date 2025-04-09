<?php
// Validate custom registration fields
function custom_registration_fields_validation($errors, $sanitized_user_login, $user_email) {
    if (empty($_POST['degree'])) {
        $errors->add('degree_error', __('Please enter your degree.', 'crp'));
    }
    if (empty($_POST['passing_year'])) {
        $errors->add('passing_year_error', __('Please enter your passing year.', 'crp'));
    }
    if (empty($_POST['percentage'])) {
        $errors->add('percentage_error', __('Please enter your passing percentage.', 'crp'));
    }
    if (empty($_POST['phone_number'])) {
        $errors->add('phone_number_error', __('Please enter your phone number.', 'crp'));
    }    
    return $errors;
}
add_filter('registration_errors', 'custom_registration_fields_validation', 10, 3);


// Save custom fields on user registration
function custom_save_registration_fields($user_id) {
    if (!empty($_POST['degree'])) {
        update_user_meta($user_id, 'degree', sanitize_text_field($_POST['degree']));
    }
    if (!empty($_POST['passing_year'])) {
        update_user_meta($user_id, 'passing_year', sanitize_text_field($_POST['passing_year']));
    }
    if (!empty($_POST['percentage'])) {
        update_user_meta($user_id, 'percentage', sanitize_text_field($_POST['percentage']));
    }
    if (!empty($_POST['phone_number'])) {
        update_user_meta($user_id, 'phone_number', sanitize_text_field($_POST['phone_number']));
    }    
}
add_action('user_register', 'custom_save_registration_fields');


function crp_handle_user_registration($user_id) {
    if (!isset($_POST['crp_nonce']) || !wp_verify_nonce($_POST['crp_nonce'], 'crp_register_nonce')) {
        return;
    }

    if (!isset($_POST['degree']) || !isset($_POST['passing_year']) || !isset($_POST['percentage'])) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_user_data';

    // Sanitize fields
    $name = sanitize_text_field($_POST['user_login']);
    $email = sanitize_email($_POST['user_email']);
    $degree = sanitize_text_field($_POST['degree']);
    $year = sanitize_text_field($_POST['passing_year']);
    $percentage = sanitize_text_field($_POST['percentage']);
    $phone_number = sanitize_text_field($_POST['phone_number']);


    // Handle file upload
    $file_url = '';
    if (!empty($_FILES['file_url']['name'])) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $uploaded = media_handle_upload('file_url', 0);
    
        if (!is_wp_error($uploaded)) {
            $file_url = wp_get_attachment_url($uploaded); // Get actual URL
            update_user_meta($user_id, 'file_url', esc_url_raw($file_url));
        }
    }
    

    // Insert data into custom table
    $wpdb->insert(
        $table_name,
        [
            'user_id'       => $user_id,
            'name'          => $name,
            'email'         => $email,
            'degree'        => $degree,
            'passing_year'  => $year,
            'percentage'    => $percentage,
            'file_url'       => $file_url,
            'phone_number'   => $phone_number,
        ]
    );
}
add_action('user_register', 'crp_handle_user_registration');

add_filter('upload_mimes', function($mimes) {
    $mimes['pdf'] = 'application/pdf';
    return $mimes;
});

