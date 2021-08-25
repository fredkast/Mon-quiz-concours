<?php
  /**
 *  @package Mon_quiz_concours
 *  @version 1.0.3
 */
/*
Plugin Name: Mon quiz concours
Description: Un plugin simple et léger qui vous permet de créer en 5 minutes un jeux concours avec quiz et tirage au sort!
Author: Frédéric Castel
Version: 1.0.3
*/


// Include mfp-functions.php, use require_once to stop the script if mfp-functions.php is not found
require_once plugin_dir_path(__FILE__) . 'includes/mqc_functions.php';

// ____________________________________

// Création des 2 tables dans la base de données $wpdb

global $mqc_db_version;
$mqc_db_version = '1.0';

function mqc_install() {
	global $wpdb;
	global $mqc_db_version;

	$mqc_plugin_game_settings = $wpdb->prefix . 'mqc_plugin_game_settings';
  $mqc_plugin_user = $wpdb->prefix . 'mqc_plugin_user';
	
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql_1 = "CREATE TABLE $mqc_plugin_game_settings (
		id int(11) NOT NULL AUTO_INCREMENT,
    start_date date NOT NULL,
    end_date date NOT NULL,
    gifts varchar(255) NOT NULL,
    winners_nbr int(11) NOT NULL DEFAULT '1',
    company_name varchar(255) NOT NULL,
    company_address varchar(255) NOT NULL,
    web_site varchar(255) NOT NULL,
    question_1 varchar(255) NOT NULL,
    answer_1_1 varchar(255) NOT NULL,
    answer_1_2 varchar(255) NOT NULL,
    answer_1_3 varchar(255) NOT NULL,
    question_2 varchar(255) NOT NULL,
    answer_2_1 varchar(255) NOT NULL,
    answer_2_2 varchar(255) NOT NULL,
    answer_2_3 varchar(255) NOT NULL,
    question_3 varchar(255) NOT NULL,
    answer_3_1 varchar(255) NOT NULL,
    answer_3_2 varchar(255) NOT NULL,
    answer_3_3 varchar(255) NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate";

  $sql_2 = "CREATE TABLE $mqc_plugin_user (
    id int(255) NOT NULL AUTO_INCREMENT,
    user_timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_gender enum('F','M') DEFAULT NULL,
    user_name varchar(30) DEFAULT NULL,
    user_firstname varchar(30) DEFAULT NULL,
    user_email varchar(50) DEFAULT NULL,
    user_birthdate date DEFAULT NULL,
    user_address text,
    user_answer_success varchar (50) DEFAULT NULL,
    PRIMARY KEY  (id)
  ) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql_1 );
  dbDelta( $sql_2 );
	add_option( 'mqc_db_version', $mqc_db_version );
}
// ____________________________________


// declanchement a l'activation du plugin
register_activation_hook( __FILE__, 'mqc_install' );



// ____________________________________

// chargement du fichier CSS du plugin

// chargement du fichier pour le menu backoffice
function add_my_stylesheet_backoffice() 
{
    wp_enqueue_style( 'main_backoffice', plugins_url('/css/mqc_back.css',__FILE__));
}

add_action('admin_print_styles', 'add_my_stylesheet_backoffice');


// chargement du fichier pour le shortcode du front
function add_my_stylesheet_frontoffice() 
{
    wp_enqueue_style( 'main_frontoffice', plugins_url('/css/mqc_front.css',__FILE__));
}

add_action('wp_enqueue_scripts','add_my_stylesheet_frontoffice');

// <!- ************************* copyright ***************************************
// ************** Plugin:  Mon qiz concours par Frederic Castel *************
// ******************************************************************************** -->