<?php
if (!class_exists('CSE_Search_Adultes_Admin')) {
    class CSE_Search_Adultes_Admin
    {
        public function __construct()
        {
            global $wpdb;
            $this->wpdb = $wpdb;
            $this->table_name = $this->wpdb->prefix . 'mots_cles_adultes';
            $this->charset_collate = $this->wpdb->get_charset_collate();

            // Utilisez la même méthode pour vérifier et créer la table que dans le code 2.
            $this->check_and_create_or_update_table();

            add_action('admin_menu', array($this, 'add_custom_menu'));
        }

        public function check_and_create_or_update_table()
        {
            if ($this->wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") != $this->table_name) {
                $this->create_keywords_table();
            } else {
                $columns = $this->wpdb->get_col("DESCRIBE {$this->table_name}");
                if (!in_array('id', $columns) || !in_array('section', $columns) || !in_array('mot_cle_adultes', $columns) || !in_array('adresse_web', $columns)) {
                    $this->create_keywords_table();
                }
            }
        }

        public function create_keywords_table()
        {
            $sql = "CREATE TABLE {$this->table_name} (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                section varchar(255) NOT NULL,
                mot_cle_adultes text NOT NULL,
                adresse_web text NOT NULL,
                PRIMARY KEY  (id)
            ) {$this->charset_collate};";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }

        public function display_keywords_adultes()
        {
            if (!current_user_can('manage_options')) {
                return;
            }

            // Gérer les soumissions de formulaire pour l'ajout et la modification des entrées
            if (isset($_POST['action'])) {
                if ($_POST['action'] === 'add' && isset($_POST['section'], $_POST['newkeyword'], $_POST['newurl']) && !empty($_POST['section']) && !empty($_POST['newkeyword']) && !empty($_POST['newurl'])) {
                    $section = sanitize_text_field(stripslashes($_POST['section'])); // Ajout de stripslashes ici
                    $newkeyword = sanitize_text_field(stripslashes($_POST['newkeyword'])); // Ajout de stripslashes ici
                    $newurl = esc_url_raw($_POST['newurl']);
                    $this->wpdb->insert($this->table_name, ['section' => $section, 'mot_cle_adultes' => $newkeyword, 'adresse_web' => $newurl]);
                } elseif ($_POST['action'] === 'edit' && isset($_POST['edit_id'], $_POST['edit_section'], $_POST['edit_keyword'], $_POST['edit_url']) && !empty($_POST['edit_id']) && !empty($_POST['edit_section']) && !empty($_POST['edit_keyword']) && !empty($_POST['edit_url'])) {
                    $edit_id = absint($_POST['edit_id']);
                    $edit_section = sanitize_text_field(stripslashes($_POST['edit_section'])); // Ajout de stripslashes ici
                    $edit_keyword = sanitize_text_field(stripslashes($_POST['edit_keyword'])); // Ajout de stripslashes ici
                    $edit_url = esc_url_raw($_POST['edit_url']);
                    $this->wpdb->update($this->table_name, ['section' => $edit_section, 'mot_cle_adultes' => $edit_keyword, 'adresse_web' => $edit_url], ['id' => $edit_id]);
                }
            }

            if (isset($_POST['delete'])) {
                $this->wpdb->delete($this->table_name, ['id' => $_POST['delete']]);
            }

            $section_filter = '';
            if (isset($_GET['section_filter']) && !empty($_GET['section_filter'])) {
                $section_filter = sanitize_text_field($_GET['section_filter']);
            }

            $order_by = 'section ASC, mot_cle_adultes ASC'; // Tri par défaut
            $valid_order_by = array('section', 'mot_cle_adultes');
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
            echo '<h1 class="wp-heading-inline">Gestion des mots-clés adultes</h1>';

            echo '<form method="GET" action="' . admin_url('admin.php') . '">';
            echo '<input type="hidden" name="page" value="gestion_mots_cles_adultes">';
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
            echo $order_by === 'section ASC' ? '<th><a href="' . admin_url('admin.php?page=gestion_mots_cles_adultes&order_by=section%20DESC') . '">Section &#9650;</a></th>' : '<th><a href="' . admin_url('admin.php?page=gestion_mots_cles_adultes&order_by=section%20ASC') . '">Section</a></th>';
            echo $order_by === 'mot_cle_adultes ASC' ? '<th><a href="' . admin_url('admin.php?page=gestion_mots_cles_adultes&order_by=mot_cle_adultes%20DESC') . '">Mot clé &#9650;</a></th>' : '<th><a href="' . admin_url('admin.php?page=gestion_mots_cles_adultes&order_by=mot_cle_adultes%20ASC') . '">Mot clé</a></th>';
            echo '<th>URL</th><th>Action</th></tr></thead>';
            echo '<tbody id="the-list">';

            if ($results) {
                $counter = 1; // Compteur initialisé à 1
                foreach ($results as $row) {
                    echo "<tr><td class='title column-title has-row-actions column-primary'>{$counter}</td>"; // Affichage du numéro
                    echo "<td>{$row->section}</td>";
                    echo "<td>{$row->mot_cle_adultes}</td>";
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
                    echo '<tr><th><label for="edit_section">Section</label></th><td><input type="text" name="edit_section" id="edit_section" value="' . esc_attr(stripslashes($entry->section)) . '"></td></tr>'; // Ajout de stripslashes ici
                    echo '<tr><th><label for="edit_keyword">Mot clé</label></th><td><input type="text" name="edit_keyword" id="edit_keyword" value="' . esc_attr(stripslashes($entry->mot_cle_adultes)) . '"></td></tr>'; // Ajout de stripslashes ici
                    echo '<tr><th><label for="edit_url">URL</label></th><td><input type="url" name="edit_url" id="edit_url" value="' . esc_url($entry->adresse_web) . '"></td></tr>';
                    echo '</table>';
                    echo '<input class="button button-primary" type="submit" value="Modifier">';
                    echo '</form>';
                }
            }

            echo '</div>';
        }
    }

    $CSE_Adultes_Admin = new CSE_Search_Adultes_Admin();
}
?>