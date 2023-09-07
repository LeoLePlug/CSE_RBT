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
}

// Fonction pour afficher le contenu de la page d'administration
function afficher_page_cse_rbt() {
    // Vous pouvez personnaliser le contenu de la page ici
    echo '<div class="wrap">';
    echo '<h2>Page d\'administration CSE-RBT</h2>';
    // Ajoutez ici les éléments de l'interface d'administration que vous souhaitez afficher
    echo '</div>';
}

// Action pour ajouter la page au menu WordPress
add_action('admin_menu', 'ajouter_page_cse_rbt');
