<?php

  require 'config.php';
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
    <!-- MENU -->
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
    2013 &copy; Nostalgia AMS
  </div>
</div>
</body>
</html>