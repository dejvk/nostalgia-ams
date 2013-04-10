<?php

  function print_menu()
  {
    // edit your menu below
    
    $menu_cnt = 0;
    
    // format: $menu_items[$menu_cnt++] = [ name, address, flag ];
    
    
    // do not edit below this unless you know what you are doing
    
    ?>
    <h2 class="sidebar-title">Menu</h2>
    <ul class="sidebar-menu">
      <li><a href="/">Domů</a>
      <?php
        if (M_LOGIN && !$_SESSION['accid'])
        {
          ?><li><a href="?mode=login">Přihlásit</a><?php
        }
        if (M_LOGIN && $_SESSION['accid'])
        {
          ?><li><a href="?mode=logout">Odhlásit</a><?php
        }
      ?>
    </ul>
    <?php
  }

?>
