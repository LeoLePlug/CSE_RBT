<?php

global $wpdb;
$table_name = $wpdb->prefix . 'mappings';

// Initialisation des variables
$field_ids = array();
$mapping_ids = array();

$raw_mapping_data = $wpdb->get_results("SELECT ID_Telechargement, ID_SESA, ID_Nom_Activite FROM {$table_name}", ARRAY_A);
if (empty($raw_mapping_data)) {
    return;  // Si aucun résultat n'est renvoyé, sortez de l'exécution
}

foreach ($raw_mapping_data as $row) {
    $field_ids[] = $row['ID_Telechargement'];
    $mapping_ids[$row['ID_Telechargement']] = array($row['ID_SESA'], $row['ID_Nom_Activite']);
}

add_action('frm_after_create_entry', 'frm_move_file_and_rename', 10, 2);
function frm_move_file_and_rename($entry_id, $form_id) {
    global $field_ids, $mapping_ids;

    if (empty($field_ids)) {
        return;  // Assurez-vous que la variable $field_ids est bien définie et non vide
    }

    foreach ($field_ids as $field_id) {
        if (isset($_POST['item_meta'][$field_id])) {
            if (!frm_filesystem_prepared()) {
                return;
            }

            $file_ids = array_filter((array)$_POST['item_meta'][$field_id], 'is_numeric');
            foreach ($file_ids as $file_id) {
                $file_id = absint($file_id);
                $old_source = get_attached_file($file_id);
                $file_name = basename($old_source);

                // Construction du nouveau nom de fichier
                $rename_ids = $mapping_ids[$field_id]; // Obtenez les IDs pour le renommage

                $value1 = sanitize_title($_POST['item_meta'][$rename_ids[0]]);
                $value2 = $rename_ids[1] === 'q' ? sanitize_title($_GET['q']) : sanitize_title($_POST['item_meta'][$rename_ids[1]]); // Utilisez $_GET['q'] si 'q' est dans le tableau de renommage

                if($field_id == 7){
    				$file_name = date('Ymd') . '_Facture_' . $value1 . '.' . pathinfo($file_name, PATHINFO_EXTENSION);
				} else {
   					 $file_name = date('Ymd') . '_' . $value2 . '_' . $value1 . '.' . pathinfo($file_name, PATHINFO_EXTENSION);
				}

                // Définition du nouveau nom de dossier
                $new_folder_name = 'formidable/' . $value1;

                $file_info = new FrmCreateFile(array('folder_name' => $new_folder_name, 'file_name' => $file_name));
                frm_create_directories($file_info);

                $destination = $file_info->uploads['basedir'] . '/' . $new_folder_name . '/' . $file_name;
                global $wp_filesystem;
                if ($wp_filesystem->move($old_source, $destination)) {
                    update_attached_file($file_id, $destination);

                    // Renommer et supprimer les versions de taille réduite des fichiers
                    $thumbnail_metadata = wp_get_attachment_metadata($file_id);
                    if (isset($thumbnail_metadata['sizes']) && is_array($thumbnail_metadata['sizes'])) {
                        foreach ($thumbnail_metadata['sizes'] as $size_name => $size_info) {
                            $thumbnail_path = dirname($destination) . '/' . $size_info['file'];
                            if($field_id == 7){
  								$new_thumbnail_name = date('Ymd') . '_Facture_' . $value1 . '_' . $size_name . '.' . pathinfo($thumbnail_path, PATHINFO_EXTENSION);
							} else {
   						 		$new_thumbnail_name = date('Ymd') . '_' . $value2 . '_' . $value1 . '_' . $size_name . '.' . pathinfo($thumbnail_path, PATHINFO_EXTENSION);
							}

                            // Renommer les versions de taille réduite des fichiers
                            $new_thumbnail_path = dirname($thumbnail_path) . '/' . $new_thumbnail_name;
                            $wp_filesystem->move($thumbnail_path, $new_thumbnail_path);

                            // Mettre à jour les informations de taille réduite dans les métadonnées
                            $thumbnail_metadata['sizes'][$size_name]['file'] = $new_thumbnail_name;
                        }

                        // Mettre à jour les métadonnées de l'attachment
                        wp_update_attachment_metadata($file_id, $thumbnail_metadata);
                    }
                }
            }
        }
    }

    // Supprimer les fichiers après l'envoi de l'email
    $entry = FrmEntry::getOne($entry_id);
    if ($entry) {
        $files_to_delete = FrmEntryMeta::getAll($entry_id, true, 'file');
        foreach ($files_to_delete as $file_meta) {
            $file_path = FrmProEntryMetaHelper::get_attached_file($file_meta);
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }
}

function frm_filesystem_prepared() {
    if (!is_admin() || !function_exists('get_filesystem_method')) {
        include_once(ABSPATH . 'wp-admin/includes/file.php');
    }

    $access_type = get_filesystem_method();
    if ($access_type === 'direct') {
        $creds = request_filesystem_credentials(site_url() . '/wp-admin/', '', false, false, array());
    }

    return (!empty($creds) && WP_Filesystem($creds));
}

function frm_create_directories($file_info) {
    global $wp_filesystem;

    $needed_dirs = frm_get_needed_dirs($file_info);
    foreach ($needed_dirs as $_dir) {
        if ($wp_filesystem->mkdir($_dir, $file_info->chmod_dir) || $wp_filesystem->is_dir($_dir)) {
            $index_path = $_dir . '/index.php';
            $wp_filesystem->put_contents($index_path, FS_CHMOD_FILE);
        }
    }
}

function frm_get_needed_dirs($file_info) {
    $dir_names = explode('/', $file_info->folder_name);
    $needed_dirs = array();

    $next_dir = '';
    foreach ($dir_names as $dir) {
        $next_dir .= '/' . $dir;
        $needed_dirs[] = $file_info->uploads['basedir'] . $next_dir;
    }

    return $needed_dirs;
}