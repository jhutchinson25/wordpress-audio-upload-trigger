<?php
/**
 * Plugin Name: Audio Upload Trigger for n8n
 * Description: Sends a POST request to an n8n webhook when an audio file is uploaded. Webhook URL can be configured in the admin settings.
 * Version: 1.1
 * Author: John Hutchinson
 */

defined('ABSPATH') or die('No script kiddies please!');

// Constants
const N8N_AUDIO_OPTION_KEY = 'n8n_audio_upload_webhook_url';

//  1. Hook into WordPress media upload
add_action('wp_handle_upload', 'n8n_audio_upload_trigger');

function n8n_audio_upload_trigger($upload) {
    $webhook_url = get_option(N8N_AUDIO_OPTION_KEY);
    if (empty($webhook_url)) {
        return $upload;
    }

    $file_type = $upload['type'];
    if (strpos($file_type, 'audio/') !== 0) {
        return $upload;
    }

    $payload = [
        'file_name'    => basename($upload['file']),
        'file_url'     => $upload['url'],
        'file_type'    => $file_type,
        'upload_time'  => current_time('mysql'),
    ];

    $response = wp_remote_post($webhook_url, [
        'method'  => 'POST',
        'headers' => ['Content-Type' => 'application/json'],
        'body'    => json_encode($payload),
        'timeout' => 10,
    ]);

    if (is_wp_error($response)) {
        error_log('[n8n audio upload] Webhook request failed: ' . $response->get_error_message());
    }

    return $upload;
}

// 2. Add settings menu in admin
add_action('admin_menu', 'n8n_audio_add_admin_menu');

function n8n_audio_add_admin_menu() {
    add_options_page(
        'Audio Upload to n8n',
        'Audio Upload to n8n',
        'manage_options',
        'n8n-audio-upload',
        'n8n_audio_options_page'
    );
}

// 3. Register and render settings
add_action('admin_init', 'n8n_audio_settings_init');

function n8n_audio_settings_init() {
    register_setting('n8nAudioSettings', N8N_AUDIO_OPTION_KEY, [
        'sanitize_callback' => 'esc_url_raw'
    ]);

    add_settings_section(
        'n8n_audio_section',
        'n8n Webhook Configuration',
        null,
        'n8nAudioSettings'
    );

    add_settings_field(
        N8N_AUDIO_OPTION_KEY,
        'n8n Webhook URL',
        'n8n_audio_field_render',
        'n8nAudioSettings',
        'n8n_audio_section'
    );
}

function n8n_audio_field_render() {
    $value = esc_url(get_option(N8N_AUDIO_OPTION_KEY, ''));
    echo "<input type='url' name='" . esc_attr(N8N_AUDIO_OPTION_KEY) . "' value='$value' style='width: 400px;' required />";
    echo "<p class='description'>Enter the full webhook URL from your n8n workflow (e.g., https://your-n8n.com/webhook/audio-upload).</p>";
}

function n8n_audio_options_page() {
    ?>
    <div class="wrap">
        <h1>Audio Upload Trigger for n8n</h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('n8nAudioSettings');
            do_settings_sections('n8nAudioSettings');
            submit_button('Save Webhook URL');
            ?>
        </form>
    </div>
    <?php
}
