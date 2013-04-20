<?php

  require 'config.php';

  $db = mysqli_connect (MYSQL_SERVER, MYSQL_USER, MYSQL_PASS);
  $db -> query ("SET NAMES 'utf8'");

  include 'Menu.php';
  /*if (M_UNIQUE || M_MYPROFILE || M_KARMAMGR)
    require 'player-ams.php';                    << Not implemented at the moment */
  if (M_UNIQUE)
    include 'Unique.php';

?>
<!doctype html>
<html lang="<?php echo L_LANG; ?>">
<head>
  <meta charset="UTF-8">
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
    $main_menu -> AddItem ("Domů", "/");
    if (M_UNIQUE)
      $main_menu -> AddItem ("Žádosti o podporu", "?mode=unique");
    if (M_LOGIN && M_MYPROFILE && Logged())
      $main_menu -> AddItem ("Profil účtu", "?mode=profile");
    if (M_LOGIN && M_KARMAMGR && Logged())
      $main_menu -> AddItem ("Zobrazení karmy", "?mode=karma");
      
    if (M_LOGIN && !Logged())
      $main_menu -> AddItem ("Přihlásit", "?mode=login");
    if (M_LOGIN && Logged())
      $main_menu -> AddItem ("Odhlásit", "?mode=logout");
      

    $main_menu->PrintVerticalMenu ();
    ?>
  </div>


  <div id="content">

    <?php
      try {

        if (!MYSQL_SERVER)
          throw new Exception ("Nebylo nastaveno žádné připojení k databázi.");

        switch ($_GET['mode'])
        {
          default: break;
          case "unique":
            $r = new CRequestList();
            $r -> Draw ();
            break;
        }

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
<?php

  function Logged()
  {
    return $_SESSION['accid'];
  }




?>
