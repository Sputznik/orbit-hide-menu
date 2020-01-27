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
  <tr><td><?php $this->list_menu( $this->menu, $user_id_or_role );?></td></tr>
  <!-- END OF MAIN MENU ITEMS -->
</table>
<style>
  ul.hide-menu-items{ display: grid; grid-template-columns: 1fr 1fr 1fr; grid-gap: 20px; }
  ul.hide-submenu-items{ padding-top: 10px; padding-left: 20px; }
  .hide-menu-items .update-count, .hide-menu-items .plugin-count, .hide-menu-items .pending-count{ display:none; }
</style>
<script>
  jQuery(document).ready( function(){
    jQuery('.menu-checkbox').each( function(){
      var el = jQuery( this );
      el.check_to_hide_submenu = function(){
        // MAIN MENU LIST
        var list = el.closest('li');
        if( el.is(':checked') ){ list.find('ul.hide-submenu-items').hide(); }
        else{ list.find('ul.hide-submenu-items').show(); }
      };
      el.click( function(){ el.check_to_hide_submenu(); });
      el.check_to_hide_submenu();
    });
    jQuery('[data-behaviour~=toggle-menu-items]').each( function(){
      var el = jQuery( this );
      el.click(function(){
        if( el.is(':checked') ){ jQuery('.menu-checkbox:not(:checked)').click(); }
        else{ jQuery('.menu-checkbox').click(); }
      });
    });
  });
</script>
