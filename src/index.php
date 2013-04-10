<?php

  require 'config.php';
  include 'Menu.php';
  if (M_UNIQUE || M_MYPROFILE || M_KARMAMGR)
    require 'player-ams.php';

?>
<!doctype html>
<html lang="<?php echo L_LANG; ?>">
<head>
  <meta name="charset" content="utf8">
  <meta name="application-name" content="Nostalgia AMS">
  <meta name="author" content="David Knap">
  <link rel="stylesheet" type="text/css" href="<?php echo L_STYLE; ?>.css">
  <title><?php echo L_SITENAME; ?></title>
</head>

<body>
<div id="wrapper">
  <div id="header">
    <h1><?php echo L_SITENAME; ?></h1>
  </div>

  <div id="content-right">
    <h2 class="sidebar-title">Menu</h2>
    <?php
    $main_menu = new CMenu();
    $main_menu->AddItem ("Domů", "/");
    if (M_LOGIN && !$_SESSION['accid'])
      $main_menu->AddItem ("Přihlásit", "?mode=login");
    if (M_LOGIN && $_SESSION['accid'])
      $main_menu->AddItem ("Odhlásit", "?mode=logout");

    $main_menu->PrintVerticalMenu ();
    ?>
  </div>


  <div id="content">

    <?php
      try {

        if (!MYSQL_SERVER)
          throw new Exception ("Nebylo nastaveno žádné připojení k databázi.");

      } catch (Exception $e) {
        echo "<div class=\"fatalerror\">Aplikace byla ukončena kvůli následující chybě: <strong>".$e->getMessage()."</strong></div>";
      }
    ?>

  </div>

  <div id="footer">
    <div id="copyright">2013 &copy; <?php echo L_SITENAME; ?></div>
    <div id="poweredby">powered by <a href="http://www.github.com/dejvk/nostalgia-ams" class="external">Nostalgia AMS</a></div>
  </div>
</div>
</body>
</html>
