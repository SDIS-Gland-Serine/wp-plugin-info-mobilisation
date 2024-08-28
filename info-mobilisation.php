<?php
/*
Plugin Name: INFO MOBILISATION
Description: -
Version: 1.0
Author: Nicolas Barbay
Author URI: http://
Plugin URI: http://
*/

define('PANN_MOB_DIR', plugin_dir_path(__FILE__));
define('PANN_MOB_URL', plugin_dir_url(__FILE__));



function pann_mob_load(){

}



function pann_mob_activation() {
	//actions to perform once on plugin activation go here    
	
	//register uninstaller
	register_uninstall_hook(__FILE__, 'pann_mob_uninstall');
}

function pann_mob_deactivation() {
	// actions to perform once on plugin deactivation go here
	
}

function pann_mob_uninstall(){
	//actions to perform once on plugin uninstall go here
	
}



function pann_mob_admin_menu() {
	add_menu_page( 'Info mobilisation', 'Info mobilisation', 'read', 'info-mobilisation', 'displayInfoMobilisation', 'dashicons-bell', 82);
	//$page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position
	//add_submenu_page( 'archives', 'Archives', 'Archives', 'general','archives', 'archives');

}

function parseMessage($message) {
	$obj = new stdClass();
	$message = preg_replace('/ - /','#',$message,1);
	$parsed1 = explode('#',$message,2);
	$obj->start = str_replace('DECLENCHEMENT SYSTEMATIQUE DEPART : ','',trim($parsed1[0]));
	$parsed2 = explode('(',$parsed1[1],2);
	$obj->alarm = trim($parsed2[0]);
	$obj->place = str_replace(';','; ',rtrim(trim($parsed2[1]),')'));
	
	return $obj;	
}

function displayInfoMobilisation() {
?>
	<div class="wrap listPostBackend">
		<h2>Info mobilisation <span>(30 dernières)</span></h2>
		<?php
			$args = array( 'posts_per_page' => 30, 'offset'=> 0, 'category' => "50", 'post_status' => array('publish', 'private') );
			$myposts = get_posts( $args );
			foreach ( $myposts as $post ) :
				$meta = get_post_meta($post->ID);
				$alb = "";
				if(array_key_exists('album_photo',$meta)) {
					$alb = $meta['album_photo'][0];
				}
				$parsed = parseMessage($post->post_content);
		?>
			<hr id="article-<?php echo $post->ID; ?>" />
			<div class="wp-editor-container info-mob" style="margin-top:20px; padding:20px; overflow:auto;">
				<p class="date"><?php echo date('d.m.Y H:i:s',strtotime($post->post_date)); ?></p>
				<p class="vhc"><?php echo $parsed->start; ?></p>
				<h3><?php echo $parsed->alarm; ?></h3>
				<p class="place"><?php echo $parsed->place; ?></p>
				<?php 
					if($alb!='') {
						echo '<p class="place"><a href="/photos/interventions/?album='.$alb.'">Voir l’album photo</a></p>';
					}
				?>
			</div>
		<?php endforeach; ?>		
	</div>
<?php
}

function widgetInfoMobilisation() {
?>
	<?php
		$args = array( 'posts_per_page' => 1, 'offset'=> 0, 'category' => "50", 'post_status' => array('publish', 'private') );
		$myposts = get_posts( $args );
		foreach ( $myposts as $post ) :  
			$meta = get_post_meta($post->ID);
			$alb = "";
			if(array_key_exists('album_photo',$meta)) {
				$alb = $meta['album_photo'][0];
			}
			$parsed = parseMessage($post->post_content);
	?>
		<div class="info-mob">
				<p class="date"><?php echo date('d.m.Y H:i:s',strtotime($post->post_date)); ?></p>
				<p class="vhc"><?php echo $parsed->start; ?></p>
				<h3><?php echo $parsed->alarm; ?></h3>
				<p class="place"><?php echo $parsed->place; ?></p>
				<?php 
					if($alb!='') {
						echo '<p class="place"><a href="/photos/interventions/?album='.$alb.'">Voir l’album photo</a></p>';
					}
				?>
		</div>
	<?php endforeach; ?>
	<p style="text-align:right;"><a href="admin.php?page=info-mobilisation">Voir toutes les dernières mobilisations</a></p>	
<?php
}



// RUN

pann_mob_load();

register_activation_hook(__FILE__, 'pann_mob_activation');
register_deactivation_hook(__FILE__, 'pann_mob_deactivation');

add_action( 'admin_menu', 'pann_mob_admin_menu' );

/**
 * Add function as widget to the dashboard.
 */
function pann_mob_add_dashboard_widgets() {

	wp_add_dashboard_widget(
                 'InfoMob',			// Widget slug.
                 'INFO MOBILISATION',	// Title.
                 'widgetInfoMobilisation'	// Display function.
        );
	
	global $wp_meta_boxes;

    $my_widget = $wp_meta_boxes['dashboard']['normal']['core']['InfoMob'];
    unset($wp_meta_boxes['dashboard']['normal']['core']['InfoMob']);
    $wp_meta_boxes['dashboard']['side']['core']['InfoMob'] = $my_widget;

}
add_action( 'wp_dashboard_setup', 'pann_mob_add_dashboard_widgets' );


// Register style sheet.
add_action( 'admin_init', 'register_pann_mob_styles' );

/**
 * Register style sheet.
 */
function register_pann_mob_styles() {
	wp_register_style( $handle='pann', $src=plugins_url( 'info-mobilisation/assets/css/info-mobilisation.css' ), $deps = array(), $ver = get_ress_version() );
	wp_enqueue_style( 'pann' );
}

