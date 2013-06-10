<?php
  session_start();
  require './config/config.php';
  require './config/SecurityPolicy.php';

  try {

    if (!MYSQL_SERVER)
      throw new Exception ("Nebylo nastaveno žádné připojení k databázi.");

    $db = mysqli_init();
    $db -> options (MYSQLI_OPT_CONNECT_TIMEOUT, 5);
    if (!$db -> real_connect (MYSQL_SERVER, MYSQL_USER, MYSQL_PASS))
      throw new Exception ("Připojení k databázi selhalo.<br>".$db->error);
    $db -> query ("SET NAMES 'utf8'");

    $username = $_POST['username'];
    $password = $_POST['password'];

    $q = $db -> query ("SELECT a.id, a.username, a.sha_pass_hash, r.rights
                          FROM ".T_ACCOUNTS." a
                          LEFT JOIN ".T_AMS_USERS." r ON (a.id = r.id)
                          WHERE a.username = '$username'");

    if ($q)
      $r = $q -> fetch_array();
    else
      throw new Exception ("Špatné uživatelské jméno");

    if (strtoupper(sha1(strtoupper($username).":".strtoupper($password))) == strtoupper($r['sha_pass_hash']))
    {
      $_SESSION['account_id'] = $r['id'];
      $_SESSION['username'] = $r['username'];
      $_SESSION['rights'] = ($r['rights']) ? $r['rights'] : 1;
    }
    else
      throw new Exception ("Špatné uživatelské jméno či heslo");

    if ( ! SEC::Check(SEC::MIN_LOGIN) )
    {
      $_SESSION['account_id'] = null;
      $_SESSION['username'] = null;
      throw new Exception ("Nemáte dostatečná oprávnění pro přihlášení do aplikace.");
    }
  }
  catch (Exception $e)
  {
    echo $e->getMessage();
    return;
  }

  session_write_close();

  /*var_dump (strtoupper(sha1(strtoupper($username).":".strtoupper($password))));
  var_dump (strtoupper($r['sha_pass_hash']));*/

  header ("Location: index.php");

