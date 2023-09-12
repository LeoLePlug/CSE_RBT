<?php
if (!class_exists('CSE_Formulaire')) {
    class CSE_Formulaire {
        public function __construct() {
            global $wpdb;
            $this->wpdb = $wpdb;
            $this->table_name = $this->wpdb->prefix . 'mappings';
            $this->charset_collate = $this->wpdb->get_charset_collate();

        }

        public function check_and_create_or_update_table() {
            if($this->wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") != $this->table_name) {
                $this->create_mapping_table();
            } else {
                $columns = $this->wpdb->get_col("DESCRIBE {$this->table_name}");
                if (!in_array('ID_Telechargement', $columns) || !in_array('ID_SESA', $columns) || !in_array('ID_Nom_Activite', $columns) || !in_array('Section', $columns)) {
                    $this->create_mapping_table();
                }
            }
        }

        private function create_mapping_table() {
            $sql = "CREATE TABLE {$this->table_name} (
                ID mediumint(9) NOT NULL AUTO_INCREMENT,
                Section text NOT NULL,
                ID_Telechargement mediumint(9) NOT NULL,
                ID_SESA mediumint(9) NOT NULL,
                ID_Nom_Activite mediumint(9) NOT NULL,
                PRIMARY KEY  (ID)
            ) {$this->charset_collate};";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }

        public function display_mappings() {
            if (!current_user_can('manage_options')) {
                echo 'Vous n\'avez pas la permission de voir cette page';
                return;
            }
            
            // Gérer les soumissions de formulaire pour l'ajout, la modification et la suppression des entrées
            if (isset($_POST['action'])) {
                if ($_POST['action'] === 'add' && isset($_POST['Section']) && isset($_POST['ID_Telechargement']) && isset($_POST['ID_SESA']) && isset($_POST['ID_Nom_Activite'])) {
                    $section = sanitize_text_field($_POST['Section']);
                    $id_telechargement = sanitize_text_field($_POST['ID_Telechargement']);
                    $id_sesa = sanitize_text_field($_POST['ID_SESA']);
                    $id_nom_activite = sanitize_text_field($_POST['ID_Nom_Activite']);
                    $this->wpdb->insert(
                        $this->table_name,
                        array(
                            'Section' => $section,
                            'ID_Telechargement' => $id_telechargement,
                            'ID_SESA' => $id_sesa,
                            'ID_Nom_Activite' => $id_nom_activite
                        ),
                        array('%s', '%d', '%d', '%s')
                    );
                } elseif ($_POST['action'] === 'edit' && isset($_POST['edit_id'], $_POST['edit_section'], $_POST['edit_id_telechargement'], $_POST['edit_id_sesa'], $_POST['edit_id_nom_activite'])) {
                    $edit_id = absint($_POST['edit_id']);
                    $edit_section = sanitize_text_field($_POST['edit_section']);
                    $edit_id_telechargement = intval($_POST['edit_id_telechargement']);
                    $edit_id_sesa = intval($_POST['edit_id_sesa']);
                    $edit_id_nom_activite = sanitize_text_field($_POST['edit_id_nom_activite']);
                    $this->wpdb->update(
                        $this->table_name,
                        array(
                            'Section' => $edit_section,
                            'ID_Telechargement' => $edit_id_telechargement,
                            'ID_SESA' => $edit_id_sesa,
                            'ID_Nom_Activite' => $edit_id_nom_activite
                        ),
                        array('ID' => $edit_id)
                    );
                } elseif ($_POST['action'] === 'delete' && isset($_POST['delete'])) {
                    $this->wpdb->delete($this->table_name, ['ID' => $_POST['delete']]);
                }
            }
		  
            echo "<div class='wrap'>";
            echo "<h1>Gérer les Mappings</h1>";

            $results = $this->wpdb->get_results("SELECT * FROM {$this->table_name} ORDER BY Section ASC");

            echo "<h2>Mappings existants</h2>";
            echo "<table class='wp-list-table widefat fixed striped'>";
            echo "<thead><tr><th>Section</th><th>ID Téléchargement</th><th>ID SESA</th><th>ID Nom Activité</th><th>Actions</th></tr></thead>";
            echo "<tbody>";
            foreach($results as $row) {
                echo "<tr>";
                echo "<td>{$row->Section}</td>";
                echo "<td>{$row->ID_Telechargement}</td>";
                echo "<td>{$row->ID_SESA}</td>";
                echo "<td>{$row->ID_Nom_Activite}</td>";
                echo "<td>";

                // Edit 
                echo "<form class='edit-form' data-id='{$row->ID}' method='post' action=''>";
                echo "<input type='hidden' name='action' value='edit'>";
                echo "<input type='hidden' name='edit_id' value='{$row->ID}'>";
                echo "<input type='text' name='edit_section' value='{$row->Section}' required>";
                echo "<input type='text' name='edit_id_telechargement' value='{$row->ID_Telechargement}' required>";
                echo "<input type='text' name='edit_id_sesa' value='{$row->ID_SESA}' required>";
                echo "<input type='text' name='edit_id_nom_activite' value='{$row->ID_Nom_Activite}' required>";
                echo "<button class='button' type='submit'>Modifier</button>";
                echo "</form>";

                // Delete
                echo "<form class='delete-form' data-id='{$row->ID}' method='post' action=''>";
                echo "<input type='hidden' name='action' value='delete'>";
                echo "<input type='hidden' name='delete' value='{$row->ID}'>";
                echo "<button class='button' type='submit'>Supprimer</button>";
                echo "</form>";

                echo "</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";

            echo "<h2>Ajouter Mapping</h2>";
            echo "<form method='post' action=''>";
            echo "Section: <input type='text' name='Section' required>";
            echo "ID Téléchargement: <input type='text' name='ID_Telechargement' required>";
            echo "ID SESA: <input type='text' name='ID_SESA' required>";
            echo "ID Nom Activité: <input type='text' name='ID_Nom_Activite' required>";
	        echo "<input type='hidden' name='action' value='add'>";
            echo "<button class='button' type='submit'>Ajouter</button>";
            echo "</form>";
            
            echo "</div>";
        }
	}

    $cse_form = new CSE_Formulaire();
}
?>