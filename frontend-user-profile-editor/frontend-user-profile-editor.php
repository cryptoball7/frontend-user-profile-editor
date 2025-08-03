<?php
/*
Plugin Name: Front-End User Profile Editor
Description: Allows users to edit their profile from the front end.
Version: 1.0
Author: Cryptoball cryptoball7@gmail.com
*/

// Enqueue styles and scripts
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('frontend-user-profile-editor-style', plugin_dir_url(__FILE__) . 'style.css');
});

// Shortcode to render the profile form
add_shortcode('frontend_profile_editor', 'frontend_profile_editor_form');

function frontend_profile_editor_form() {
    if (!is_user_logged_in()) {
        return '<p>You must be logged in to edit your profile.</p>';
    }

    $user = wp_get_current_user();

    // Optional: restrict to specific roles
    $allowed_roles = ['subscriber', 'editor', 'author'];
    if (!array_intersect($allowed_roles, $user->roles)) {
        return '<p>You do not have permission to edit your profile.</p>';
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['frontend_profile_nonce'])) {
        if (!wp_verify_nonce($_POST['frontend_profile_nonce'], 'save_profile')) {
            return '<p>Security check failed.</p>';
        }

        $user_data = [
            'ID'           => $user->ID,
            'first_name'   => sanitize_text_field($_POST['first_name']),
            'last_name'    => sanitize_text_field($_POST['last_name']),
            'user_email'   => sanitize_email($_POST['user_email']),
            'description'  => sanitize_textarea_field($_POST['description']),
        ];

        $user_id = wp_update_user($user_data);

        if (is_wp_error($user_id)) {
            echo '<p>Error updating profile: ' . $user_id->get_error_message() . '</p>';
        } else {
            echo '<p>Profile updated successfully.</p>';
            $user = get_userdata($user->ID); // Refresh user data
        }
    }

    ob_start(); ?>
    <form method="post" class="frontend-profile-form">
        <?php wp_nonce_field('save_profile', 'frontend_profile_nonce'); ?>
        <p>
            <label>First Name<br>
                <input type="text" name="first_name" value="<?php echo esc_attr($user->first_name); ?>">
            </label>
        </p>
        <p>
            <label>Last Name<br>
                <input type="text" name="last_name" value="<?php echo esc_attr($user->last_name); ?>">
            </label>
        </p>
        <p>
            <label>Email<br>
                <input type="email" name="user_email" value="<?php echo esc_attr($user->user_email); ?>">
            </label>
        </p>
        <p>
            <label>Bio<br>
                <textarea name="description"><?php echo esc_textarea($user->description); ?></textarea>
            </label>
        </p>
        <p>
            <input type="submit" value="Update Profile">
        </p>
    </form>
    <?php
    return ob_get_clean();
}
