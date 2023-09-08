<?php
/**
 * Plugin Name: CSE-RBT
 * Plugin URI: https://leoleplug.com/
 * Description: Ce plugin permet de faire fonctionner les fonctionnalités de remboursement sur facture du site (module de recherche, envoi, mail, etc...). #made by leoleplug agency, votre agence web de confiance.
 * Version: 0.2
 * Author: Leoleplug Agency
 * Author URI: https://leoleplug.com/
 */

// Inclure le fichier panel-admin.php
include_once(plugin_dir_path(__FILE__) . 'admin-panel.php');

// Inclure le fichier mail-cse.php
require (plugin_dir_path(__FILE__) . 'mail-cse/mail-cse.php');

?>
