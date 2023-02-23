<?php 
/*
Plugin Name: WP multisite more details
Plugin URI:  https://github.com/
Description: For stuff that's magical
Version:     1.0
Author:      Tom
Author URI:  http://bionicteaching.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: my-toolset

*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


function wpms101_enqueue_custom_admin_style() {
        wp_register_style( 'wpms101_admin_css', plugin_dir_url(__FILE__) . 'css/wpms101-admin-style.css', false, '1.0.0' );
        wp_enqueue_style( 'wpms101_admin_css' );
        wp_enqueue_script('wpms101_admin_js', plugin_dir_url(__FILE__) . 'js/wpms101-admin-details.js', false, '1.0.0', true);
}
add_action( 'admin_enqueue_scripts', 'wpms101_enqueue_custom_admin_style' );


add_filter( 'network_edit_site_nav_links', 'wpms101_new_siteinfo_tab' );

function wpms101_new_siteinfo_tab( $tabs ){

   $tabs['site-details'] = array(
      'label' => 'Details',
      'url' => 'sites.php?page=wpmsdetails',
      'cap' => 'manage_sites'
   );
   return $tabs;

}


/*
 * Add submenu page under Sites
 */
add_action( 'network_admin_menu', 'wpms101_new_page' );
function wpms101_new_page(){
   add_submenu_page(
      'sites.php',
      'Edit website', // will be displayed in <title>
      'Edit website', // doesn't matter
      'manage_network_options', // capabilities
      'wpmsdetails',
      'wpms101_handle_admin_page' // the name of the function which displays the page
   );
}


function wpms_details_checkbox($detail, $array){
   if (in_array($detail, $array)){
      return 'checked';
   } else {
      '';
   }
}

/*
 * Display the page and settings fields
 */
function wpms101_handle_admin_page(){

   // do not worry about that, we will check it too
   $id = $_REQUEST['id'];

   // you can use $details = get_site( $id ) to add website specific detailes to the title
   $title = 'Site Details';

   $perm = '';
   $portfolio = '';
   $course = '';
   $project = '';

   if(get_blog_option( $id, 'wpms_details')){
      $current_details = unserialize(get_blog_option( $id, 'wpms_details'));
      $perm = wpms_details_checkbox('permanent', $current_details);
      $portfolio = wpms_details_checkbox('portfolio', $current_details);
      $course = wpms_details_checkbox('course', $current_details);
      $project = wpms_details_checkbox('project', $current_details);
   }
   
   echo '<div class="wrap"><h1 id="edit-site">' . $title . '</h1>
   <p class="edit-site-actions"><a href="' . esc_url( get_home_url( $id, '/' ) ) . '">Visit</a> | <a href="' . esc_url( get_admin_url( $id ) ) . '">Dashboard</a></p>';

      // navigation tabs
      network_edit_site_nav( array(
         'blog_id'  => $id,
         'selected' => 'site-details' // current tab
      ) );

      echo '
     
      <form method="post" action="edit.php?action=wpmsdetailupdate">';
         wp_nonce_field( 'wpmsdetail-check' . $id );
         echo "
               <input type='hidden' name='id' value='{$id}' />
               <table class='form-table'>
                  <tr>
                     <th scope='row'><label for='wpms_details'>Detail Options</label></th>
                     <td>
                        <fieldset>
                            <legend>Choose your site details.</legend>
                            <div>
                              <input type='checkbox' id='perm' name='details[]' value='permanent' {$perm}>
                              <label for='perm'>Permanent</label>
                            </div>
                            <div>
                              <input type='checkbox' id='portfolio' name='details[]' value='portfolio' {$portfolio}>
                              <label for='portfolio'>Portfolio</label>
                            </div>
                            <div>
                              <input type='checkbox' id='course' name='details[]' value='course' {$course}>
                              <label for='course'>Course</label>
                            </div>
                            <div>
                              <input type='checkbox' id='project' name='details[]' value='project' {$project}>
                              <label for='project'>Project</label>
                            </div>
                        </fieldset>
                     </td>
                  </tr>
               </table>
         ";
         submit_button();
      echo '</form></div>';

}


/*
 * Save settings
 */
add_action('network_admin_edit_wpmsdetailupdate',  'wpmsdetails_save_options');
function wpmsdetails_save_options() {
   $blog_id = $_POST['id'];

   check_admin_referer('wpmsdetail-check' . $blog_id); // security check

   $details = $_POST['details']; 

   update_blog_option( $blog_id, 'wpms_details', serialize($details) );

   wp_redirect( add_query_arg( array(
      'page' => 'wpmsdetails',
      'id' => $blog_id,
      'updated' => 'true'), network_admin_url("sites.php")
   ));
   // redirect to /wp-admin/sites.php?page=mishapage&blog_id=ID&updated=true
   //?page=wpmsdetails&blog_id={$blog_id}&updated=true

   exit;

}


add_action( 'network_admin_notices', 'wpmsdetails_notice' );
function wpmsdetails_notice() {

   if( isset( $_GET['updated'] ) && isset( $_GET['page'] ) && $_GET['page'] == 'wpmsdetails' ) {

      echo '<div id="message" class="updated notice is-dismissible">
         <p>Congratulations!</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
      </div>';

   }

}

//add to sites display
class Add_Blog_ID {
   
   public static function init() {
      $class = __CLASS__ ;

      if ( empty( $GLOBALS[ $class ] ) )
         $GLOBALS[ $class ] = new $class;
   }
   
   public function __construct() {
      
      add_filter( 'wpmu_blogs_columns', array( $this, 'get_id' ) );
      add_action( 'manage_sites_custom_column', array( $this, 'add_columns' ), 10, 2 );
      add_action( 'manage_blogs_custom_column', array( $this, 'add_columns' ), 10, 2 );
   }
   
   public function add_columns( $column_name, $blog_id ) {
      //var_dump($column_name);
      if ( 'blog_id' === $column_name )
         echo $blog_id;
      
      return $column_name;
   }

   // Add in a column header
   public function get_id( $columns ) {
      
      $columns['blog_id'] = __('ID');
      
      return $columns;
   }
   
}
add_action( 'init', array( 'Add_Blog_ID', 'init' ) );


class Add_Blog_Details {
   
   public static function init() {
      $class = __CLASS__ ;

      if ( empty( $GLOBALS[ $class ] ) )
         $GLOBALS[ $class ] = new $class;
   }
   
   public function __construct() {
      
      add_filter( 'wpmu_blogs_columns', array( $this, 'get_details' ) );
      add_action( 'manage_sites_custom_column', array( $this, 'add_columns' ), 10, 2 );
      add_action( 'manage_blogs_custom_column', array( $this, 'add_columns' ), 10, 2 );
   }
   
   public function add_columns( $column_name, $blog_id ) {
      $details_string = 'f';
      if ( 'blog_details' === $column_name )
      if(get_blog_option( $blog_id, 'wpms_details') !== FALSE) {
          $details = unserialize(get_blog_option( $blog_id, 'wpms_details'));
          $details_string = implode(', ', $details);
         echo $details_string;
      }
        
      
      return $column_name;
   }

   // Add in a column header
   public function get_details( $columns ) {
      
      $columns['blog_details'] = __('Details');
      
      return $columns;
   }
   

}
add_action( 'init', array( 'Add_Blog_Details', 'init' ) );




//LOGGER -- like frogger but more useful

if ( ! function_exists('write_log')) {
   function write_log ( $log )  {
      if ( is_array( $log ) || is_object( $log ) ) {
         error_log( print_r( $log, true ) );
      } else {
         error_log( $log );
      }
   }
}

  //print("<pre>".print_r($a,true)."</pre>");


