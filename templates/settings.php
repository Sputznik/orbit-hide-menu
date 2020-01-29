<?php

global $wp_roles;
$all_roles = $wp_roles->roles;

$screens = array();
$i = 0;
foreach( $all_roles as $slug => $role ){
  $screens[ $slug ] = array( 'label' => $role['name'] );
  if( $i ){ $screens[ $slug ]['action'] = $slug; }
  $i++;
}

$active_tab = '';
?>
<div class="wrap">
	<h1>Hide Menus</h1>
	<h2 class="nav-tab-wrapper">
	<?php
		foreach ($screens as $slug => $screen) {
			$url = admin_url( 'options-general.php?page=hide-menus' );
			if ( isset( $screen['action'] ) ) {
				$url = esc_url( add_query_arg( array( 'action' => $screen['action']), admin_url( 'options-general.php?page=hide-menus' ) ) );
			}

			$nav_class = "nav-tab";

			if (isset($screen['action']) && isset($_GET['action']) && $screen['action'] == $_GET['action']) {
				$nav_class .= " nav-tab-active";
				$active_tab = $slug;
			}

			if (!isset($screen['action']) && !isset($_GET['action'])) {
				$nav_class .= " nav-tab-active";
				$active_tab = $slug;
			}

			echo '<a href="' . $url . '" class="' . $nav_class . '">' . $screen['label'] . '</a>';
		}
	?>
  </h2>

  <?php

    if( $_POST ){

      $selected_menu = is_array( $_POST['user_menu'] ) ? $_POST['user_menu'] : array();
      $selected_submenu = is_array( $_POST['user_submenu'] ) ? $_POST['user_submenu'] : array();

      echo "<pre>";
      //print_r( $_POST );
      //print_r( $selected_submenu );
      echo "</pre>";

      $this->update_selected_menu_for_role( $active_tab, $selected_menu, $selected_submenu );
      _e('<script>location.reload();</script>');
    }

  ?>

  <form method="POST">
	<?php $this->display_menu_items( $active_tab ); ?>
  <p class="submit"><input type="submit" name="submit-btn" class="button button-primary" value="Save Changes"></p>
  </form>
</div>
