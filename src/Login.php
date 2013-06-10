<?php


  /// Abstraktní třída zajišťující přihlašování a odhlašování uživatelů.
  class Login
  {
    /// Vypíše přihlašovací dialog
    static function DrawDialogue ()
    {
    ?>

    <form class="logindialogue" action="Authentification.php" method="post">
      Přihlašovací jméno do hry:<br>
      <input type="text" name="username"><br><br>
      Heslo:<br>
      <input type="password" name="password"><br><br>
      <input type="submit" name="LogIn" value="Přihlásit">
    </form>

    <?php
    }


    /// Odhlásí uživatele smazáním session. Pak uživatele přesměruje na index.
    static function Logout ()
    {
      $_SESSION['account_id'] = null;
      $_SESSION['username'] = null;
      $_SESSION['rights'] = null;
      header ("Location: index.php");
    }
  };

?>
