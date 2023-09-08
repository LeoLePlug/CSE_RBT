<?php
// Fonction pour ajouter la page d'administration au menu WordPress
function ajouter_page_cse_rbt() {
    add_menu_page(
        'CSE-RBT', // Titre de la page dans le menu
        'CSE-RBT', // Texte dans le menu
        'manage_options', // Capacité requise pour accéder à la page
        'cse_rbt_settings', // Identifiant unique de la page
        'afficher_page_cse_rbt', // Fonction qui affiche le contenu de la page
        'dashicons-admin-tools', // Icône (clé à molette)
        80 // Position dans le menu
    );

    // Ajouter les sous-pages pour les options
    add_submenu_page(
        'cse_rbt_settings', // Slug de la page parente
        'Mot-Clés Adultes', // Titre de la sous-page
        'Mot-Clés Adultes', // Texte dans le menu
        'manage_options',
        'mot_cles_adultes', // Slug de la sous-page
        'afficher_page_mot_cles_adultes' // Fonction pour afficher la sous-page
    );

    add_submenu_page(
        'cse_rbt_settings',
        'Mot-Clés Enfants',
        'Mot-Clés Enfants',
        'manage_options',
        'mot_cles_enfants',
        'afficher_page_mot_cles_enfants'
    );

    add_submenu_page(
        'cse_rbt_settings',
        'Formulaires',
        'Formulaires',
        'manage_options',
        'formulaires',
        'afficher_page_formulaires'
    );
}

// Fonction pour afficher le contenu de la page d'administration principale
function afficher_page_cse_rbt() {
    echo '<div class="wrap">';
    echo '<h2>Page d\'administration CSE-RBT</h2>';
    echo '<p>Page d\'administration du plugin CSE RBT fait par <a href="https://leoleplug.com/">LeoLePlug Agency</a>.</p>';
    echo '<br>'; // Ajoutez plusieurs sauts de ligne
    echo '<br>';
    echo '<br>';
    echo '<h3>Options :</h3>';
    echo '<ul>';
    echo '<li><a href="admin.php?page=mot_cles_adultes">Mot-Clés Adultes</a></li>';
    echo '<li><a href="admin.php?page=mot_cles_enfants">Mot-Clés Enfants</a></li>';
    echo '<li><a href="admin.php?page=formulaires">Formulaires</a></li>';
    echo '</ul>';
    echo '</div>';
}

// Fonction pour afficher le contenu de la page "Mot-Clés Adultes"
function afficher_page_mot_cles_adultes() {
    echo '<div class="wrap">';
    echo '<h2>Mot-Clés Adultes</h2>';
    // Ajoutez ici les éléments de la page "Mot-Clés Adultes"
    echo '</div>';
}

// Fonction pour afficher le contenu de la page "Mot-Clés Enfants"
function afficher_page_mot_cles_enfants() {
    echo '<div class="wrap">';
    echo '<h2>Mot-Clés Enfants</h2>';
    // Ajoutez ici les éléments de la page "Mot-Clés Enfants"
    echo '</div>';
}

function afficher_page_formulaires() {
    echo '<div class="wrap">';
    echo '<h2>Formulaires</h2>';

    // Incluez le fichier manage-mappings.php depuis le répertoire mail-cse
    include_once(plugin_dir_path(__FILE__) . 'mail-cse/mail-cse-mapping.php');
    $cse_form = new CSE_Formulaire();
    $cse_form->display_mappings();

    echo '</div>';
}

// Action pour ajouter la page au menu WordPress
add_action('admin_menu', 'ajouter_page_cse_rbt');
?>
