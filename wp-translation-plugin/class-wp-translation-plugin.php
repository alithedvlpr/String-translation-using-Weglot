<?php

class WP_Translation_Plugin {
    private $weglot_api_key = 'wg_22965b8148fdb2fab51c41ada3ee0fdf9'; // Replace with your Weglot API key

    public function init() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_post_save_translation_string', [$this, 'handle_save_translation_string']);
        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }

    //Register REST API routes
    public function register_rest_routes() {
        register_rest_route('wp-translation-plugin/v2', '/translations', [
            'methods'  => 'GET',
            'callback' => [$this, 'handle_get_translations'],
            'args'     => [
                'language' => [
                    'required'    => true,
                    'validate_callback' => function($param, $request, $key) {
                        return in_array($param, ['pt-BR', 'nl', 'fr', 'de', 'it', 'ja', 'pl', 'pt', 'es', 'ru']);
                    }
                ],
            ],
            'permission_callback' => '__return_true', // Allow access without authentication
        ]);
    }

    //Return the response
    public function handle_get_translations($request) {
        global $wpdb;
        $language = sanitize_text_field($request->get_param('language'));
        $table_name = $wpdb->prefix . 'translations';

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE language = %s",
            $language
        ), ARRAY_A);

        return new WP_REST_Response($results, 200);
    }

    //Create a menu in admin dashboard
    public function add_admin_menu() {
        add_menu_page(
            'Translation Strings',
            'Translation Strings',
            'manage_options',
            'wp-translation-plugin',
            [$this, 'render_admin_page']
        );
    }

    //Create a form to enter String
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1>Translation Strings</h1>
            <?php
            if (isset($_GET['message']) && $_GET['message'] === 'success') {
                echo '<div class="notice notice-success is-dismissible"><p>String saved and translated successfully.</p></div>';
            }
            if (isset($_GET['message']) && $_GET['message'] === 'error') {
                echo '<div class="notice notice-error is-dismissible"><p>Error: ' . esc_html($_GET['error']) . '</p></div>';
            }
            ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('save_translation_nonce', 'security'); ?>
                <input type="hidden" name="action" value="save_translation_string">
                <textarea name="translation_string" rows="5" cols="50"><?php echo isset($_POST['translation_string']) ? esc_textarea($_POST['translation_string']) : ''; ?></textarea>
                <br>
                <input type="submit" value="Save Translation">
            </form>
        </div>
        <?php
    }

    public function handle_save_translation_string() {
        check_admin_referer('save_translation_nonce', 'security');

        if (!current_user_can('manage_options')) {
            wp_redirect(add_query_arg(['message' => 'error', 'error' => 'Permission denied'], admin_url('admin.php?page=wp-translation-plugin')));
            exit;
        }

        if (empty($_POST['translation_string'])) {
            wp_redirect(add_query_arg(['message' => 'error', 'error' => 'The translation string cannot be empty'], admin_url('admin.php?page=wp-translation-plugin')));
            exit;
        }

        $string = sanitize_text_field($_POST['translation_string']);
        $this->translate_and_store($string);

        wp_redirect(add_query_arg(['message' => 'success'], admin_url('admin.php?page=wp-translation-plugin')));
        exit;
    }

    //Weglot api integration
    private function translate_and_store($string) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'translations';
        $languages = ['pt-BR', 'nl', 'fr', 'de', 'it', 'ja', 'pl', 'pt', 'es', 'ru'];
        
        foreach ($languages as $lang) {
            // Set up the API request
            $response = wp_remote_post('https://api.weglot.com/translate?api_key=' . $this->weglot_api_key, [
                'body' => json_encode([
                    'l_from' => 'en', // Source language
                    'l_to' => $lang,  // Target language
                    'request_url' => home_url(), // URL of the page
                    'words' => [
                        ['w' => $string, 't' => 1] // Word to translate
                    ]
                ]),
                'method'    => 'POST',
                'headers'   => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->weglot_api_key
                ],
                'timeout'   => 45,
                'sslverify' => false // Set to true in production
            ]);

            if (is_wp_error($response)) {
                error_log('Weglot API error: ' . $response->get_error_message());
                continue;
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            // Log the full API response for debugging
            //error_log('Weglot API response for language ' . $lang . ': ' . print_r($data, true));
            
            if (isset($data['to_words'][0])) {
                $translation = $data['to_words'][0];

                // Check if the translation already exists
                $existing_translation = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM $table_name WHERE string = %s AND language = %s",
                    $string,
                    $lang
                ));

                if ($existing_translation) {
                    // Update existing translation
                    $wpdb->update(
                        $table_name,
                        ['translation' => $translation],
                        ['string' => $string, 'language' => $lang],
                        ['%s'],
                        ['%s', '%s']
                    );
                } else {
                    // Insert new translation
                    $wpdb->insert(
                        $table_name,
                        [
                            'string' => $string,
                            'language' => $lang,
                            'translation' => $translation,
                        ],
                        ['%s', '%s', '%s']
                    );
                }
            } else {
                error_log('Translation not found in response for language ' . $lang);
            }
        }
    }
}
