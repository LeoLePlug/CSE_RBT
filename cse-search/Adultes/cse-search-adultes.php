<?php

if (!class_exists('CSE_Search_Adultes_Search')) {
    class CSE_Search_Adultes_Search
    {
        public function __construct()
        {
            add_action('rest_api_init', array($this, 'add_keywords_adultes_endpoint'));
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
            add_shortcode('cse_search_adultes_form', array($this, 'display_search_form_adultes'));
        }

        public function add_keywords_adultes_endpoint()
        {
            register_rest_route('cse-search-adultes/v1', '/keywords_adultes', array(
                'methods' => 'GET',
                'callback' => array($this, 'get_keywords_adultes'),
            ));
        }

        public function get_keywords_adultes()
        {
            global $wpdb;
            $table_name = $wpdb->prefix . 'mots_cles_adultes';
            $results = $wpdb->get_results("SELECT * FROM {$table_name}", ARRAY_A);
            $keywords_adultes = array();
            foreach ($results as $row) {
                // Compare the keyword with the user's input based on Levenshtein distance
            $input = $_GET['input'];
            if (isset($input) && levenshtein($input, $row['mot_cle_adultes']) < 2) {
                $keywords_adultes[$row['mot_cle_adultes']] = $row['adresse_web'];
            }
            }
            return $keywords_adultes;
        }

        public function enqueue_scripts()
        {
            wp_enqueue_script(
                'cse-search-adultes',
			  plugin_dir_url(__FILE__) . 'cse-search-adultes.js',
                array('jquery'),
                '1.0',
                true
            );

            wp_enqueue_style(
                'cse-search-adultes-style',
			    plugin_dir_url(__FILE__) . 'cse-search-adultes.css',
                array(),
                '1.0'
            );

            wp_localize_script('cse-search-adultes', 'CSESearchAdultes', array(
                'rest_url' => rest_url('cse-search-adultes/v1/keywords_adultes'),
                'nonce' => wp_create_nonce('cse_search_adultes_nonce'),
            ));
        }

        public function display_search_form_adultes()
        {
            ob_start();
            ?>
            <form id="custom-search-form-adultes" action="<?php echo esc_url(rest_url('cse-search-adultes/v1/keywords_adultes')); ?>" style="display: flex; flex-direction: column;">
                <input list="keywords_adultes" id="keyword-input-adultes" name="keyword" autocomplete="off">


                <datalist_adultes id="keywords_adultes"></datalist_adultes>
                <input type="submit" value="Valider">
            </form>
            <?php
            return ob_get_clean();
        }
    }

    new CSE_Search_Adultes_Search();
}