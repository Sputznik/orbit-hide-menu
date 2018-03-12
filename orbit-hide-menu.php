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
			
			/* SHOW EXTRA FIELDS */
			add_action( 'show_user_profile', array( $this, 'extra_user_profile_fields' ) );
			add_action( 'edit_user_profile', array( $this, 'extra_user_profile_fields' ) );
			
			/* SAVE EXTRA FIELDS */
			add_action( 'personal_options_update', array( $this, 'save_extra_user_profile_fields' ) );
			add_action( 'edit_user_profile_update', array( $this, 'save_extra_user_profile_fields' ) );
			
			/* HIDE MENU ITEMS */
			add_action( 'admin_init', array( $this, 'hide_menu_items' ) );
			
		}
		
		function hide_menu_items(){
			global $menu, $submenu;
				
			/* SAVING THE OLD MENU FOR REUE IN THE USER FIELDS */
			$this->menu = $menu;
			$this->submenu = $submenu;
				
			$current_user = wp_get_current_user();
				
			/* IF NOT LOGGED IN THEN RETURN */
			if( !isset( $current_user->ID ) ) return false;
				
			/* SELECTED MENU ITEMS FOR HIDING */
			$menu_arr = is_array( get_user_meta( $current_user->ID, 'user_menu', true ) ) ? get_user_meta( $current_user->ID, 'user_menu', true ) : array();
			$submenu_arr = is_array( get_user_meta( $current_user->ID, 'user_submenu', true ) ) ? get_user_meta( $current_user->ID, 'user_submenu', true ) : array();
				
			/* FINALLY HIDE MENU PAGES */
			foreach( $menu_arr as $menu_item ){ remove_menu_page( $menu_item ); }
			
			//print_r( $submenu_arr );
			
			
			/* FINALLY HIDE SUBMENU PAGES */
			foreach( $submenu_arr as $submenu_item ){ 
				$submenu_item = explode(':', $submenu_item );
				if( count( $submenu_item ) > 1 ){
					remove_submenu_page( $submenu_item[0], $submenu_item[1] );
				}
				
				
			}
			
			//wp_die();
		}
		
		function list_menu_item( $user, $menu, $main_menu = false){
			
			$list_class = 'hide-menu-items';
			$list_item_class = 'hide-menu-item';
			$checkbox_class = 'menu-checkbox';
			$form_name = 'user_menu[]';
			$selected_menu = is_array( get_user_meta( $user->ID, 'user_menu', true ) ) ? get_user_meta( $user->ID, 'user_menu', true ) : array();
			
			if( $main_menu ){
				$form_name = 'user_submenu[]';
				$list_class = 'hide-submenu-items';
				$list_item_class = 'hide-submenu-item';
				$checkbox_class = 'submenu-checkbox';
				$selected_menu = is_array( get_user_meta( $user->ID, 'user_submenu', true ) ) ? get_user_meta( $user->ID, 'user_submenu', true ) : array();
			}
			
			
			
			_e("<ul class='".$list_class."'>");
			foreach( $menu as $menu_item ): if( isset( $menu_item[0] ) && $menu_item[0] && isset( $menu_item[2] ) && $menu_item[2] ):
				
		?>
			
			<li class="<?php _e( $list_item_class );?>">
				<label>
					<?php
						$menu_val = $menu_item[2];
						if( $main_menu ){
							$menu_val = $main_menu.":".$menu_item[2];
						}
					?>
					<input class="<?php _e( $checkbox_class );?>" <?php if( in_array( $menu_val, $selected_menu ) )_e("checked='checked'");?> type="checkbox" name="<?php _e( $form_name );?>" value="<?php echo $menu_val; ?>" />
					<?php 
						_e( $menu_item[0] );
						if( !$main_menu && isset( $this->submenu[ $menu_item[2] ] ) && $this->submenu[ $menu_item[2] ] ){
							$this->list_menu_item( $user, $this->submenu[ $menu_item[2] ], $menu_item[2] );
						}
					?>
				</label>
			</li>
		<?php
			
			endif;endforeach;
			_e("</ul>");
		}
		
		function extra_user_profile_fields( $user ) { 
			
		?>
			<h3><?php _e("Hide Menu Items"); ?></h3>
			<table class="form-table">
				<tr>
					<td>
						<label>
							<input type="checkbox" data-behaviour='toggle-menu-items' />
							Hide All
						</label>
					</td>
				</tr>
				<!-- MAIN MENU ITEMS -->
				<tr><td><?php $this->list_menu_item( $user, $this->menu );?></td></tr>
				<!-- END OF MAIN MENU ITEMS -->
			</table>
			<style>
				ul.hide-menu-items{
					display: grid;
					grid-template-columns: 1fr 1fr 1fr;
					grid-gap: 20px;
				}
				
				ul.hide-submenu-items{
					padding-top: 10px;
					padding-left: 20px;
						
				}
				.hide-menu-items .update-count, .hide-menu-items .plugin-count, .hide-menu-items .pending-count{ display:none; }
				
				
			</style>
			<script>
				jQuery(document).ready( function(){
					
					jQuery('.menu-checkbox').each( function(){
						
						var el = jQuery( this );
						
						el.check_to_hide_submenu = function(){
							// MAIN MENU LIST
							var list = el.closest('li');
							
							if( el.is(':checked') ){
								list.find('ul.hide-submenu-items').hide();
							}
							else{
								list.find('ul.hide-submenu-items').show();
							}	
						};
						
						el.click( function(){
							el.check_to_hide_submenu();
						});
						
						el.check_to_hide_submenu();
						
					});
					
					jQuery('[data-behaviour~=toggle-menu-items]').each( function(){
						
						var el = jQuery( this );
						
						el.click(function(){
							
							if( el.is(':checked') ){
								jQuery('.menu-checkbox:not(:checked)').click();	
							}
							else{
								jQuery('.menu-checkbox').click();	
							}
							
						});
						
					});
				});
			</script>
		<?php }
		
		function save_extra_user_profile_fields( $user_id ) {
			if ( !current_user_can( 'edit_user', $user_id ) ) { 
				return false; 
			}
			update_user_meta( $user_id, 'user_menu', $_POST['user_menu'] );
			update_user_meta( $user_id, 'user_submenu', $_POST['user_submenu'] );
			
		}
		
	}
	
	new ORBIT_HIDE_MENU;