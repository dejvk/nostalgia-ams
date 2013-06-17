<?php


  /// Abstraktní třída zajišťující přihlašování a odhlašování uživatelů.
  class Login
  {
    /// Vypíše přihlašovací dialog
    static function DrawDialogue ()
    {
    ?>

    <div id="logindialogue">
      <form class="logindialogue" action="Authentification.php" method="post">
        Přihlašovací jméno do hry:<br>
        <input type="text" name="username"><br><br>
        Heslo:<br>
        <input type="password" name="password"><br><br>
        <input type="submit" name="LogIn" value="Přihlásit">
      </form>
    </div>

    <?php
    }


    /// Odhlásí uživatele smazáním session. Pak uživatele přesměruje na index.
    static function Logout ()
    {
      $_SESSION['account_id'] = null;
      $_SESSION['username'] = null;
      $_SESSION['rights'] = null;
      header ("Location: ".$_SERVER['HTTP_REFERER']);
    }

    /// Ovládá postup registračním průvodcem na základě <tt>GET[step]</tt>.
    static function Registration ()
    {
      switch ($_GET['step'])
      {
        default:
        case  1:
          self::RegistrationStep1 ();
          break;
        case  2:
          self::RegistrationStep2 ();
          break;
        case  3:
          self::RegistrationStep3 ();
          break;
      }
    }

    /// Vypíše první krok registrace
    /** Informuje uživatele o základních zásadách serveru a vyzve k souhlasu
     *  s pravidly.*/
    static function RegistrationStep1 ()
    {
      ?>

        <h2>Registrace herního účtu, krok 1</h2>
        <div id="registrationwizard">
          <p>Před registrací na server Nostalgia RP, prosím, vemte na vědomí,
             že se jedná o striktní RP server a ujistěte se, že chápete všechny
             důsledky tohoto formátu serveru.
          <p>K hraní budete potřebovat herního klienta World of Warcraft ve
             verzi 3.3.5a (sestavení 12340) a také
             <a href="http://forum.pk-nostalgia.cz/viewtopic.php?f=40&t=118">
             Nostalgia Mod</a> v aktuální
             verzi. Bez těchto náležitostí vám nebude umožněno připojit se do
             herního světa. S instalací vám pomůže tento průvodce po registraci.
          <p>Před samotnou registrací byste si měli přečíst
             <a href="http://wiki.pk-nostalgia.cz/wiki/Pravidla">Pravidla
             serveru</a>,
             přesto níže nabízíme přehled nejvýznamnějších pravidel, které
             nejsou na ostatních serverech obvyklé. Porušování pravidel pro nás
             kontrolují automatizované systémy pracující v reálném čase a
             za jejich porušení hrozí tvrdé tresty.
          <ul>
            <li>III.3 Kontakt s členy Projektového týmu probíhá výlučně pomocí
                ticketového systému nebo pomocí Soukromých zpráv na fóru.
            <li>III.17 Hráči je zakázáno vlastnit více než jeden herní účet.
                Používání více herních účtů z jedné IP adresy je trestné.
                Hraní více různých Hráčů z jedné IP adresy je nutné hlásit
                předem členům Projektového týmu.
            <li>III.19 Hráči je zakázáno propagovat jakýmkoliv způsobem jiný
                projekt podobného zaměření a to včetně uvádění jeho názvu.
            <li>III.21 Hráči je zakázáno přesouvat peníze a výbavu mezi svými
                postavami posíláním poštou, předáváním přes ostatní Hráče či
                jakýmkoliv jiným způsobem bez souhlasu Projektového týmu.
            <li>IV.3 Postavy musí mít jméno nejméně tři znaky dlouhé.
                Jméno nesmí obsahovat neslušné výrazy, pohoršovat či zesměšňovat
                ostatní Hráče. Jméno musí odpovídat statusu jména, nesmí jít
                o „sérii náhodných znaků“, nesmí jít o jméno ani modifikace
                jména známé postavy z reálného či fantasy světa. Jméno nesmí
                připomínat jména, přezdívky či jména postav členů
                Projektového týmu.
          </ul>
          <p>Pokračováním v registraci souhlasíte s plným zněním
             <a href="http://wiki.pk-nostalgia.cz/wiki/Pravidla">Pravidel
             serveru</a>.

          <a class="continuebutton" href="?mode=registration&step=2">
            Pokračovat &raquo;</a>

        </div>

      <?php
    }


    /// Vypíše druhý krok registrace.
    /** Ověří, zda na se na IP již nenalézá nějaký účet. Pokud ano, uživatele
     *  o tom informuje.
     *  \todo výpis registračního formuláře */
    static function RegistrationStep2 ()
    {
      ?>

        <h2>Registrace herního účtu, krok 2</h2>
        <div id="registrationwizard">
          <?=self::CheckMultiacc();?>
          
          <table id="registrationdialogue">
          <form method="post" action="?mode=registration&step=3">
          <tr>
            <th><label for="username">Uživatelské jméno:</label>
            <td><input type="text" id="username" name="username">
          </tr>
          <tr>
            <th><label for="password">Heslo:</label>
            <td><input type="password" id="password" name="password">
          </tr>
          <tr>
            <th><label for="password2">Heslo znovu:</label>
            <td><input type="password" id="password2" name="password2">
          </tr>
          <tr>
            <th><label for="email">E-mail:</label>
            <td><input type="email" id="email" name="email" value="@">
          </tr>
          <tr>
            <th>
            <td><input class="continuebutton" type="submit"
                  value="Pokračovat &raquo;">
          </table>

        </div>

      <?php
    }
    
    
    /// Vyhodnotí požadavek na registraci a vypíše třetí krok
    /** V případě, že je username obsazeno nebo se neshodují hesla, vrátí
     *  uživatele zpět ke kroku 2 a zobrazí varování.
     *  V případě úspěšné registrace vypíše reg. informace a spustí průvodce
     *  instalací patche.
     *  \throw Exception K adrese bylo přistoupeno přímo, ne formulářem.
     *  \todo Průvodce instalací patche     */
    static function RegistrationStep3 ()
    {
      if ( empty($_POST) )
        throw new Exception ("Neplatný požadavek.");
      
      if ( ! self::UsernameAvailable() )
      {
        echo "<p class=\"registration-bad\">
                Uživatelské jméno ".$_POST['username']." je již obsazené.
                Zvolte prosím jiné.</p>";
        return self::RegistrationStep2 ();
      }
      
      if ( $_POST['password'] != $_POST['password2'] )
      {
        echo "<p class=\"registration-bad\">
                Zadaná hesla se neshodují.</p>";
        return self::RegistrationStep2 ();
      }
    }


    /// Ověří, zda na IP uživatele existují herní účty.
    /** \return Upozornění pro uživatele, pokud účty existují. Null pokud ne. */
    static function CheckMultiacc ()
    {
      global $db;
      $r = $db -> query ("SELECT id
                          FROM ".T_ACCOUNTS."
                          WHERE last_ip = '".$_SERVER['REMOTE_ADDR']."'");
      $cnt = $r -> num_rows;
      if ($cnt)
        return "<p class=\"registration-bad\">
                  Na tomto počítači již nějaké hráče registrujeme.
                  Počet účtů: $cnt.<br>Pakliže vás bude hrát více z jedné IP
                  adresy, je nutné to oznámit GM týmu skrze ticketový systém
                  co nejdříve po přihlášení do hry, ať předejdete nedorozuměním.
                </p>";
      return null;
    }
    
    
    /// Ověří, zda je uživatelské jméno předané v POST požadavku volné.
    /** \retval true Uživatelské jméno je volné.
     *  \retval false Uživatelské jméno je použité. */
    static function UsernameAvailable ()
    {
      global $db;
      $q = $db -> query ("SELECT COUNT(*) AS cnt
                          FROM ".T_ACCOUNTS."
                          WHERE username = '".$_POST['username']."'");
      echo $db -> error;                    
      $r = $q -> fetch_assoc ();
      $q -> free();
      return ($r['cnt']) ? false : true;
    }
  };

?>
