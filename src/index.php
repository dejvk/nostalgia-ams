<?php

/** \mainpage Nostalgia IS 0.2
 *    Informační systém pro server Nostalgia RP.
 *
 *    Všechny vytvořené třídy a metody dokumentujte!
 *
 *    Co je potřeba udělat?
 *    \li Podávání žádostí o customky.
 *    \li Možnost podat si profil postavy.
 *    \li Systém na životopisy.
 *    \li Sloučit třídy Character a CharacterSheet
 *    \li Vytvořit výjimky UnauthorizedException a NotLoggedException
 *
 *
 *
 */
    session_start();

    require './config/config.php';
    require './config/SecurityPolicy.php';

    include './classes/Menu.php';
    include './classes/Character.php';

    if (M_UNIQUE || M_MYPROFILE || M_KARMAMGR)
      include './classes/Player.php';
      
    if (M_KARMAMGR)
      include './classes/Karma.php';

    if (M_LOGIN)
      include 'Login.php';

    if (M_UNIQUE)
      include './classes/Unique.php';

    if (M_CHRREGISTER)
      include './classes/ChrRegister.php';

    if (M_TICKETS)
      include './classes/Ticket.php';

    if (M_GUILDMGR)
      include './classes/Guild.php';

?>
<!doctype html>
<html lang="<?php echo L_LANG; ?>">
<head>
  <meta charset="UTF-8">
  <meta name="application-name" content="Nostalgia IS">
  <meta name="author" content="David Knap">
  <link rel="stylesheet" type="text/css" href="/public_html/<?php echo L_STYLE; ?>.css">
  <title><?php echo L_SITENAME; ?></title>
</head>

<body>
<div id="wrapper">
  <div id="header">
    <?php if (L_LOGO) echo "<div id=\"sitelogo\"><img src=\"".L_LOGO."\"></div>"; ?>
    <h1><?php echo L_SITENAME; ?></h1>
  </div>

  <div id="content-right">
    <h2 class="sidebar-title">Menu</h2>
    <?php
    $main_menu = new Menu();
    $main_menu -> AddItem ("Domů", "/");
    if (M_UNIQUE)
      $main_menu -> AddItem ("Žádosti o podporu", "?mode=unique");
    if (M_CHRREGISTER)
      $main_menu -> AddItem ("Databáze postav", "?mode=chrregister");
    if (M_GUILDMGR)
      $main_menu -> AddItem ("Databáze cechů", "?mode=guilds");
    if (M_LOGIN && ! Logged ())
    {
      $main_menu -> AddItem ("Přihlásit", "?mode=login");
    }

    $main_menu -> PrintVerticalMenu ();



    if (Logged())
    {
      ?>
      <h2 class="sidebar-title"><?php echo ucfirst(strtolower($_SESSION["username"]))." <div>R".Rights()."</div>"; ?></h2>
      <?php

      $logged_menu = new Menu ();

      if (M_LOGIN && M_MYPROFILE)
        $logged_menu -> AddItem ("Profil účtu", "?mode=myaccount");
      if (M_LOGIN && CHRREGISTER)
        $logged_menu -> AddItem ("Mé postavy", "?mode=mycharacters");
      if (M_LOGIN && M_KARMAMGR)
        $logged_menu -> AddItem ("Zobrazení karmy", "?mode=karma");
      if (M_LOGIN && M_TICKETS && Rights() >= SEC::MIN_TICKETS)
        $logged_menu -> AddItem ("Zobrazení ticketů", "?mode=tickets");

      $logged_menu -> AddItem ("Odhlásit", "?mode=logout");

      $logged_menu -> PrintVerticalMenu ();

    }

    ?>
  </div>


  <div id="content">

    <?php

      try {

        if (!MYSQL_SERVER)
          throw new Exception ("Nebylo nastaveno žádné připojení k databázi.");

        $db = mysqli_init();
        $db -> options (MYSQLI_OPT_CONNECT_TIMEOUT, 5);
        if (!$db -> real_connect (MYSQL_SERVER, MYSQL_USER, MYSQL_PASS))
          throw new Exception ("Připojení k databázi selhalo.<br>".$db->error);
        $db -> query ("SET NAMES 'utf8'");


        GetContent ();

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
    return $_SESSION['account_id'];
  }
  
  function Account()
  {
    return $_SESSION['account_id'];
  }

  function Rights()
  {
    return $_SESSION['rights'];
  }

  function GetContent ()
  {
    switch ($_GET['mode'])
    {
      default: break;
      case "unique":
        if ($_GET['req'])
          Request::DrawSingle ($_GET['req']);
        else
        {
          $r = new RequestList();
          $r -> Draw ();
        }
        break;
      case "chrregister":
        $r = new CharacterSheetRegister ();
        $r -> Draw ();
        break;
      case "mycharacters":
        $r = new CharacterList ();
        $r -> Draw ();
        break;
      case "myaccount":
        $r = new Player();
        $r -> DrawProfile ();
        break;
      case "character":
        $r = new Character ();
        $r -> DrawProfile ();
        break;
      case "karma":
        $r = new KarmaList ();
        $r -> Draw ();
        break;
      case "guilds":
        if ( ! $_GET['guild'] )
          Guild::DrawListOfGuilds();
        else
        {
          $r = new Guild ($_GET['guild']);
          $r -> Draw ();
        }
        break;
      case "tickets":
        $r = new TicketList ();
        $r -> Draw ();
        break;
      case "registration":
        Login::Registration ();
        break;
      case "login":
        Login::DrawDialogue ();
        break;
      case "logout":
        Login::Logout ();
        break;
    }
  }


?>
