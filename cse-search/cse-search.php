<?php
/*
Plugin Name: CSE-Search
Plugin URI: https://leoleplug.com/plugins/cse-search
Description: CSE-Search est un plugin développé par LeoLePlug Agency qui offre une fonctionnalité de recherche améliorée pour https://factures.csehorizon.com/. Ce plugin permet une auto-complétion de la barre de recherche avec une liste prédéfinie de mots clés. Chaque mot clé est associé à une URL spécifique, et la sélection de ce mot clé redirige l'utilisateur vers cette URL.
Version: 1.3.7
Author: LeoLePlug Agency
Author URI: https://leoleplug.com/
*/

if (!class_exists('CSE_Search')) {
    class CSE_Search
    {
        public function __construct()
        {
            global $wpdb;
            $this->wpdb = $wpdb;
            $this->table_name = $this->wpdb->prefix . 'mots_cles';
            $this->charset_collate = $this->wpdb->get_charset_collate();

            register_activation_hook(__FILE__, array($this, 'create_keywords_table'));
            add_action('admin_menu', array($this, 'add_custom_menu'));
            add_action('rest_api_init', array($this, 'add_keywords_endpoint'));
        }

        public function create_keywords_table()
        {
            $sql = "CREATE TABLE {$this->table_name} (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                section varchar(255) NOT NULL,
                mot_cle text NOT NULL,
                adresse_web text NOT NULL,
                PRIMARY KEY  (id)
            ) {$this->charset_collate};";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }

        public function add_custom_menu()
        {
            add_menu_page('Paramètres de CSE-Search', 'CSE-Search', 'manage_options', 'gestion_mots_cles', array($this, 'display_keywords_table'));
        }

        public function display_keywords_table()
        {
            if (!current_user_can('manage_options')) {
                return;
            }

            // Gérer les soumissions de formulaire pour l'ajout et la modification des entrées
            if (isset($_POST['action'])) {
                if ($_POST['action'] === 'add' && isset($_POST['section'], $_POST['newkeyword'], $_POST['newurl']) && !empty($_POST['section']) && !empty($_POST['newkeyword']) && !empty($_POST['newurl'])) {
                    $section = sanitize_text_field($_POST['section']);
                    $newkeyword = sanitize_text_field($_POST['newkeyword']);
                    $newurl = esc_url_raw($_POST['newurl']);
                    $this->wpdb->insert($this->table_name, ['section' => $section, 'mot_cle' => $newkeyword, 'adresse_web' => $newurl]);
                } elseif ($_POST['action'] === 'edit' && isset($_POST['edit_id'], $_POST['edit_section'], $_POST['edit_keyword'], $_POST['edit_url']) && !empty($_POST['edit_id']) && !empty($_POST['edit_section']) && !empty($_POST['edit_keyword']) && !empty($_POST['edit_url'])) {
                    $edit_id = absint($_POST['edit_id']);
                    $edit_section = sanitize_text_field($_POST['edit_section']);
                    $edit_keyword = sanitize_text_field($_POST['edit_keyword']);
                    $edit_url = esc_url_raw($_POST['edit_url']);
                    $this->wpdb->update($this->table_name, ['section' => $edit_section, 'mot_cle' => $edit_keyword, 'adresse_web' => $edit_url], ['id' => $edit_id]);
                }
            }

            if (isset($_POST['delete'])) {
                $this->wpdb->delete($this->table_name, ['id' => $_POST['delete']]);
            }

            $section_filter = '';
            if (isset($_GET['section_filter']) && !empty($_GET['section_filter'])) {
                $section_filter = sanitize_text_field($_GET['section_filter']);
            }

            $order_by = 'section ASC, mot_cle ASC'; // Tri par défaut
            $valid_order_by = array('section', 'mot_cle');
            if (isset($_GET['order_by']) && in_array($_GET['order_by'], $valid_order_by)) {
                $order_by = sanitize_text_field($_GET['order_by']);
            }

            $query = "SELECT * FROM {$this->table_name}";
            if ($section_filter != '') {
                $query .= $this->wpdb->prepare(" WHERE section = %s", $section_filter);
            }
            $query .= " ORDER BY {$order_by}";
            $results = $this->wpdb->get_results($query);

            echo '<div class="wrap">';
            echo '<h1 class="wp-heading-inline">Gestion des mots-clés</h1>';

            echo '<form method="GET" action="' . admin_url('admin.php') . '">';
            echo '<input type="hidden" name="page" value="gestion_mots_cles">';
            echo '<label for="section_filter">Filtrer par section :</label>';
            echo '<select name="section_filter" id="section_filter">';
            echo '<option value="">Toutes les sections</option>';

            // Récupérer les sections distinctes depuis la base de données
            $sections = $this->wpdb->get_col("SELECT DISTINCT section FROM {$this->table_name}");
            foreach ($sections as $section) {
                $selected = ($section == $section_filter) ? 'selected' : '';
                echo '<option value="' . esc_attr($section) . '" ' . $selected . '>' . esc_html($section) . '</option>';
            }

            echo '</select>';
            echo '<input class="button" type="submit" value="Filtrer">';
            echo '</form>';

            echo '<form method="POST" action="">';
            echo '<table class="wp-list-table widefat fixed striped pages">';
            echo '<thead><tr><th class="manage-column column-title column-primary">#</th>';
            echo $order_by === 'section ASC' ? '<th><a href="' . admin_url('admin.php?page=gestion_mots_cles&order_by=section%20DESC') . '">Section &#9650;</a></th>' : '<th><a href="' . admin_url('admin.php?page=gestion_mots_cles&order_by=section%20ASC') . '">Section</a></th>';
            echo $order_by === 'mot_cle ASC' ? '<th><a href="' . admin_url('admin.php?page=gestion_mots_cles&order_by=mot_cle%20DESC') . '">Mot clé &#9650;</a></th>' : '<th><a href="' . admin_url('admin.php?page=gestion_mots_cles&order_by=mot_cle%20ASC') . '">Mot clé</a></th>';
            echo '<th>URL</th><th>Action</th></tr></thead>';
            echo '<tbody id="the-list">';

            if ($results) {
                $counter = 1; // Compteur initialisé à 1
                foreach ($results as $row) {
                    echo "<tr><td class='title column-title has-row-actions column-primary'>{$counter}</td>"; // Affichage du numéro
                    echo "<td>{$row->section}</td>";
                    echo "<td>{$row->mot_cle}</td>";
                    echo "<td>{$row->adresse_web}</td>";
                    echo "<td><button type='submit' name='edit' value='{$row->id}'>Modifier</button> <button type='submit' name='delete' value='{$row->id}'>Supprimer</button></td></tr>";
                    $counter++; // Incrémenter le compteur pour la prochaine ligne
                }
            }

            echo '</tbody>';
            echo '</table>';
            echo '</form>';

            echo '<form method="POST" action="">';
            echo '<h1 class="wp-heading-inline">Ajouter un mot-clé</h1><br><br>';
            echo '<h2 class="wp-heading-inline">Attention aux espaces des urls dans les copiers/collers</h2>';
            echo '<h2 class="wp-heading-inline">Attention aux apostrophes (mettez des espaces)</h2><br>';
            echo '<input type="hidden" name="action" id="action" value="add">';
            echo '<table class="form-table">';
            echo '<tr><th><label for="section">Section</label></th><td><input type="text" name="section" id="section"></td></tr>';
            echo '<tr><th><label for="newkeyword">Mot clé</label></th><td><input type="text" name="newkeyword" id="newkeyword"></td></tr>';
            echo '<tr><th><label for="newurl">URL</label></th><td><input type="url" name="newurl" id="newurl"></td></tr>';
            echo '</table>';
            echo '<input class="button button-primary" type="submit" value="Ajouter">';
            echo '</form>';

            if (isset($_POST['edit'])) {
                $edit_id = absint($_POST['edit']);
                $entry = $this->wpdb->get_row("SELECT * FROM {$this->table_name} WHERE id = {$edit_id}");
                if ($entry) {
                    echo '<form method="POST" action="">';
                    echo '<h1 class="wp-heading-inline">Modifier un mot-clé</h1>';
                    echo '<input type="hidden" name="action" id="action" value="edit">';
                    echo '<input type="hidden" name="edit_id" id="edit_id" value="' . $edit_id . '">';
                    echo '<table class="form-table">';
                    echo '<tr><th><label for="edit_section">Section</label></th><td><input type="text" name="edit_section" id="edit_section" value="' . $entry->section . '"></td></tr>';
                    echo '<tr><th><label for="edit_keyword">Mot clé</label></th><td><input type="text" name="edit_keyword" id="edit_keyword" value="' . $entry->mot_cle . '"></td></tr>';
                    echo '<tr><th><label for="edit_url">URL</label></th><td><input type="url" name="edit_url" id="edit_url" value="' . $entry->adresse_web . '"></td></tr>';
                    echo '</table>';
                    echo '<input class="button button-primary" type="submit" value="Modifier">';
                    echo '</form>';
                }
            }

            echo '</div>';
        }

        public function add_keywords_endpoint()
        {
            register_rest_route('cse-search/v1', '/keywords', array(
                'methods' => 'GET',
                'callback' => array($this, 'get_keywords'),
            ));
        }

        public function get_keywords()
        {
            $results = $this->wpdb->get_results("SELECT * FROM {$this->table_name}", ARRAY_A);
            $keywords = array();
            foreach ($results as $row) {
                $keywords[$row['mot_cle']] = $row['adresse_web'];
            }
            return $keywords;
        }
    }

    add_action('plugins_loaded', function () {
        new CSE_Search();
    });

    add_action('wp_enqueue_scripts', function () {
        wp_enqueue_script(
            'cse-search',
            plugin_dir_url(__FILE__) . 'cse-search.js',
            array('jquery'),
            '1.0',
            true
        );

        wp_enqueue_style(
            'cse-search-style',
            plugin_dir_url(__FILE__) . 'cse-search.css',
            array(),
            '1.0'
        );

        wp_localize_script('cse-search', 'CSESearch', array(
            'rest_url' => rest_url('cse-search/v1/keywords'),
            'nonce' => wp_create_nonce('cse_search_nonce'),
        ));
    });

    add_shortcode('cse_search_form', function () {
	  ob_start();
	  ?>
	  <form id="custom-search-form" action="<?php echo esc_url(rest_url('cse-search/v1/keywords')); ?>" style="display: flex; gap: 1em;">
		  <input list="keywords" id="keyword-input" name="keyword">
		  <datalist id="keywords"></datalist>
		  <input type="submit" value="Valider">
	  </form>
	  <?php
	  return ob_get_clean();
	});
}