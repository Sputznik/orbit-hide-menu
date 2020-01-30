<?php
	/*
    Plugin Name: Orbit Hide Menu
    Plugin URI: http://sputznik.com
    Description: Hide menu items
    Author: Samuel Thomas
    Version: 1.0
    Author URI: http://sputznik.com
    */

	class ORBIT_HIDE_MENU{

		var $menu;
		var $submenu;

		function __construct(){

			add_action('admin_menu', function(){
				add_submenu_page(
					'options-general.php',
					'Hide Menus',
					'Hide Menus',
					'manage_options',
					'hide-menus',
					array( $this, 'settings_page' )
				);
			});

			/* SHOW EXTRA FIELDS */
			add_action( 'show_user_profile', array( $this, 'extra_user_profile_fields' ) );
			add_action( 'edit_user_profile', array( $this, 'extra_user_profile_fields' ) );

			/* SAVE EXTRA FIELDS */
			add_action( 'personal_options_update', array( $this, 'save_extra_user_profile_fields' ) );
			add_action( 'edit_user_profile_update', array( $this, 'save_extra_user_profile_fields' ) );

			/* HIDE MENU ITEMS */
			add_action( 'admin_init', array( $this, 'hide_menu_items' ) );

		}

		function settings_page(){
			include( plugin_dir_path(__FILE__).'templates/settings.php' );
		}

		function display_menu_items( $user_id_or_role ){
			include( plugin_dir_path(__FILE__).'templates/menu-items.php' );
		}

		function hide_mainmenu( $menus ){ foreach( $menus as $menu_item ){ remove_menu_page( $menu_item ); } }
		function hide_submenu( $submenus ){
			//print_r( $submenus );
			foreach( $submenus as $submenu_item ){
				$submenu_item = explode(':', $submenu_item );
				if( count( $submenu_item ) > 1 ){
					remove_submenu_page( $submenu_item[0], $submenu_item[1] );
				}
			}
		}

		function hide_menu_items(){
			global $menu, $submenu;

			/* SAVING THE OLD MENU FOR REUE IN THE USER FIELDS */
			$this->menu = $menu;
			$this->submenu = $submenu;

			$current_user = wp_get_current_user();
			$role = isset( $current_user->roles ) && count( $current_user->roles ) ? $current_user->roles[0] : false;

			/* IF NOT LOGGED IN THEN RETURN */
			if( !isset( $current_user->ID ) ) return false;

			/* SELECTED MENU ITEMS FOR HIDING */
			$menu_arr = is_array( get_user_meta( $current_user->ID, 'user_menu', true ) ) ? get_user_meta( $current_user->ID, 'user_menu', true ) : array();
			if( $role ){
				$menu_arr1 = $this->get_selected_menu_for_role( $role );
				$menu_arr = array_unique( array_merge( $menu_arr, $menu_arr1 ) );
			}
			$this->hide_mainmenu( $menu_arr );

			$submenu_arr = is_array( get_user_meta( $current_user->ID, 'user_submenu', true ) ) ? get_user_meta( $current_user->ID, 'user_submenu', true ) : array();
			if( $role ){
				$submenu_arr1 = $this->get_selected_menu_for_role( $role, true );
				$submenu_arr = array_unique( array_merge( $submenu_arr, $submenu_arr1 ) );
			}
			$this->hide_submenu( $submenu_arr );


			//wp_die();

		}

		function get_option(){
			$options = get_option( 'hide_menu_settings' );
			return $options;
		}

		function get_selected_menu_for_role( $role, $submenu = false ){
			$menu = 'menu';
			if( $submenu ){ $menu = 'submenu'; }

			$settings = $this->get_option();
			$settings = is_array( $settings ) ? $settings : array();

			if( isset( $settings[$role] ) && isset( $settings[$role][$menu] ) ) return $settings[$role][$menu];
			return array();
		}

		function update_selected_menu_for_role( $role, $selected_menu, $selected_submenu ){
			$settings = $this->get_option();
			$settings = is_array( $settings ) ? $settings : array();
			$settings[$role] = array(
				'menu'		=> $selected_menu,
				'submenu'	=> $selected_submenu
			);
			update_option( 'hide_menu_settings', $settings );
		}

		function list_menu( $menu, $user_id_or_role, $submenu = false ){

			$list_class 			= 'hide-menu-items';
			$list_item_class 	= 'hide-menu-item';
			$checkbox_class 	= 'menu-checkbox';
			$form_name 				= 'user_menu[]';

			if( is_int( $user_id_or_role ) ){
				$selected_menu 		= is_array( get_user_meta( $user_id_or_role, 'user_menu', true ) ) ? get_user_meta( $user_id_or_role, 'user_menu', true ) : array();
			}
			else{
				$selected_menu 		= $this->get_selected_menu_for_role( $user_id_or_role );
			}

			if( $submenu ){
				$form_name = 'user_submenu[]';
				$list_class = 'hide-submenu-items';
				$list_item_class = 'hide-submenu-item';
				$checkbox_class = 'submenu-checkbox';
				if( is_int( $user_id_or_role ) ){
					$selected_menu 		= is_array( get_user_meta( $user_id_or_role, 'user_submenu', true ) ) ? get_user_meta( $user_id_or_role, 'user_submenu', true ) : array();
				}
				else{
					$selected_menu 		= $this->get_selected_menu_for_role( $user_id_or_role, true );
				}
			}

			_e("<ul class='".$list_class."'>");
			foreach( $menu as $menu_item ){
				if( isset( $menu_item[0] ) && $menu_item[0] && isset( $menu_item[2] ) && $menu_item[2] ){
					include( plugin_dir_path(__FILE__).'templates/menu-item.php' );
				}
			}

			_e("</ul>");

		}

		function extra_user_profile_fields( $user ) {
			_e( "<h3>Hide Menu Items</h3>" );
			$this->display_menu_items( $user->ID );
		}

		function save_extra_user_profile_fields( $user_id ) {
			if ( !current_user_can( 'edit_user', $user_id ) ) {
				return false;
			}
			if( isset( $_POST['user_menu'] ) && is_array( $_POST['user_menu'] ) ){
				update_user_meta( $user_id, 'user_menu', $_POST['user_menu'] );
			}
			if( isset( $_POST['user_submenu'] ) && is_array( $_POST['user_submenu'] ) ){
				update_user_meta( $user_id, 'user_submenu', $_POST['user_submenu'] );
			}
		}

	}

	new ORBIT_HIDE_MENU;
