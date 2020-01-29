<li class="<?php _e( $list_item_class );?>">
  <label>
    <?php
      $menu_val = $menu_item[2];
      if( $submenu ){
        $menu_val = $submenu.":".$menu_item[2];
      }
    ?>
    <input class="<?php _e( $checkbox_class );?>" <?php if( in_array( $menu_val, $selected_menu ) )_e("checked='checked'");?> type="checkbox" name="<?php _e( $form_name );?>" value="<?php echo $menu_val; ?>" />
    <?php
      _e( $menu_item[0] );
      if( !$submenu && isset( $this->submenu[ $menu_item[2] ] ) && $this->submenu[ $menu_item[2] ] ){
        $this->list_menu( $this->submenu[ $menu_item[2] ], $user_id_or_role, $menu_item[2] );
      }
    ?>
  </label>
</li>
