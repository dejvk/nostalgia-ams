<?php

  /// Reprezentuje jednoho hráče (jeden herní účet)
  class Player
  {
    private $_id;
    private $_username;
    private $_karma;
    private $_weightedKarma;
    private $_activeKarma;
    private $_characters;
    private $_email;
    private $_registered;
    private $_lastLogin;
    private $_banned;
    private $_support;
    private $_totalPlayed;
    private $_ip;
    private $_customs;
    
    
    /// Konstruktor. Vytvoří objekt na základě přihlášení uživatele.
    /** \throw Exception Nepřihlášený uživatel. */
    public function __construct ()
    {
      if (!Logged())
        throw new Exception ("Nepřihlášený uživatel");
      
      global $db;
      $q = $db -> query ("SELECT r.id, r.username, r.email, r.joindate AS registered,
                                 r.last_login, r.last_ip
                          FROM ".T_ACCOUNTS." r
                          WHERE r.id = ".Account());
      echo $db -> error;
      $r = $q -> fetch_assoc ();
      
      $this->_username   = $r['username'];
      $this->_email      = $r['email'];
      $this->_registered = date("d.m.Y G:i", strtotime($r['registered']));
      $this->_lastLogin  = $r['last_login'];
      $this->_ip         = $r['last_ip'];
      $this->setKarmas();
    }
    
    
    /// Vypíše profil herního účtu.
    /** \throw Exception Nepřihlášený uživatel. */
    public function DrawProfile ()
    {
      if (!Logged())
        throw new Exception ("Nepřihlášený uživatel");
        
      ?>
      
      <table id="accountprofile">
      <tr>
        <th>Uživatelské jméno
        <td><?=$this->_username;?>
      </tr>
      <tr>
        <th>Počet postav
        <td><?=$this->_characters["num_rows"];?>
      </tr>
      <tr>
        <th>Karma
        <td><?=$this->_karma;?>
      </tr>
      <tr>
        <th>Vážená karma
        <td><?=$this->_weightedKarma;?>
      </tr>
      <tr>
        <th>Žádostí o unique
        <td><?=$this->_customs["num_rows"];?>
      </tr>
      <tr>
        <th>Banováno
        <td><?=$this->_banned;?>
      </tr>
      <tr>
        <th>Podpora
        <td><?=$this->_support;?>
      </tr>
      <tr>
        <th>E-mail
        <td><?=$this->_email;?>
      </tr>
      <tr>
        <th>Založeno
        <td><?=$this->_registered;?>
      </tr>
      </table>
      
      <?
    }
    
    
    /// Zpracuje hodnoty Absolutní, Vážené a Aktivní vážené karmy.
    /** Absolutní karma: sum{hodnocení}
     *  \par Vážená karma: sum{hodnocení od hráčů}
     *     + 2*sum{kladné hodnocení od GM>3}
     *     + 4*sum{záporné hodnocení od GM>3}
     *  \par Aktivní vážená karma: Vážená karma, kde hodnotící je GM>3 nebo
     *  byl přihlášený během posledních 90 dní. */
    private function setKarmas ()
    {
      global $db;
      $q = $db -> query ("SELECT k.karma_hodnota AS karma, ac.gmlevel2, ac.last_login
                          FROM ".T_KARMA." k
                          JOIN ".T_ACCOUNTS." ac ON (k.hodnotici = ac.id)
                          WHERE k.hodnoceny = ".Account());

      echo $db -> error;
      while ( $r = $q -> fetch_assoc () )
      {
        $weighted = ($r['gmlevel2'] >= 4) ? 2*$r['karma'] : $r['karma'];
        $active = (strtotime($r['last_login']) < strtotime ("-90 days") && $r['gmlevel2'] < 4)
                ? 0 : $weighted;
        
        if ($r['karma'] >= 0)
        {
          $this->_karma += $r['karma'];
          $this->_weightedKarma += $weighted;
          $this->_activeKarma += $active;
        }
        else
        {
          $this->_karma += 2*$r['karma'];
          $this->_weightedKarma += 2*$weighted;
          $this->_activeKarma += 2*$active;        
        }
      }
    }
  };


  /*
  function GetRaceIcon();
  function GetCharClass();
  function IsGM();
  function IsGhost();
  function GetCharGuild();
  function GetCharLocation();
  function LocationInSilverpine();
  function TranslateMoney();
  function ColorizeMoney();
  function GetCharXP();
  function GetPlayedTime();
  function GetOnlineTime();
  function SuspisiousPlayer();
  function GetCharKarma();
  */
  
  /// Translates name of player to GUID
  /** Used for calling CPlayer class from address line.
   *  @param name Name of character
   *  @return GUID of player
   */
  function GetPlayerGUID($name)
  {
    mysql_select_db(DB_CHARACTERS);
    $c = mysql_fetch_array(mysql_query("SELECT guid FROM characters WHERE name = '$name'"));
    return ($c) ? $c[guid] : -1; 
  }


  /* THIS SHOULD BE INTEGRATED INTO CLASS CPlayer! */

  /// OBSOLETE. Should be replaced by CPlayer::GetRaceIcon(). Still in use.
  function GetRaceIcon ($race, $gender)
  {
    return "http://src.pk-nostalgia.cz/ikonyras/rasa_".$race."_".$gender.".gif";
  }

  /// OBSOLETE. Should be replaced by CPlayer::GetCharacterClassColor(). Still in use.
  function GetCharClass ($class)
  {
    switch ($class) {
      default: $class = NULL;          break;
     	case 1:  $class = "sienna";      break; # warrior
     	case 2:  $class = "deeppink";    break; # paladin
     	case 3:  $class = "green";	     break; # hunter
     	case 4:  $class = "gold";   	   break; # rogue
     	case 5:  $class = "white";    	 break; # priest
     	case 6:  $class = "red";    	   break; # deathknight
     	case 7:  $class = "#0070de";     break; # shaman
     	case 8:  $class = "deepskyblue"; break; # mage
     	case 9:  $class = "#9482c9";     break; # warlock
     	case 11: $class = "orangered";   break; # druid
    }
    return $class;
  }
  
  /// OBSOLETE. Should be replaced by CPlayer::IsGm(). Still in use.
  function IsGM ($flag)
  {
    return ($flag & 16) ? true : false;
  }
  
  function IsGhost ($guid)
  {
    mysql_select_db(DB_CHARACTERS);
    $q = mysql_query("SELECT * FROM character_aura WHERE guid = '$guid' AND spell = '8326'");
    $duch = mysql_num_rows($q);
    return ($duch) ? true : false; 
  }

  function GetCharGuild ($guild)
  {
    switch ($guild) {
      case 1:   return "&lt;Cech mágů&gt;"; break;
      default:  return NULL; break;
    }
  }
  
  /**
   * @return array("Name of Location", AllowedLocation);
   */     
  
  function GetCharLocation ($zone, $x, $y)
  {
    $devs  = array(400, 440, 1377, 2037, 3805);
    switch ($zone) {
      case    0: 
      case 4987: return array("Sen", true);               break;
      case   10:
      case  717: return array("Podzemí", true);           break;
      case   28: return array("Záp. Morové země", true);  break;
      case   33: return array("Stranglethorn", false);    break;
      case   36: return array("Alterak", false);          break;
      case   85: return LocationInTirisfal($zone,$x,$y);  break;
      case  130: return LocationInSilverpine($zone,$x,$y);break;
      case  139: return array("Fenrisova mučírna", false);break;
      case  209: return array("Falkenstein", 13);         break;
      case  267: return array("Hillsbrad", false);        break;
      case  394: return array("dev #394-G", true);        break;
      case  495: return array("dev #495-H", true);        break;
      case  876: return array("GM ostrov", false);        break;
      case 2057: return array("Scholomance", true);       break;
      case 2100: return array("Sen (Maraudon)", true);    break;
      default:   if (in_array($zone, $devs)) 
                 {  return array ("dev $zone", true); break; }
                 else
                 {  return array ("#$zone", false); break; } 
    }
  }
  
  /**
   * @return array ("Name of Location", 11-lordaeron/12-altgrad/13-falkenstein/false-NotAllowed);
   */     
  
  function LocationInSilverpine ($zone, $x, $y)
  {
    $lordaeron = 11;
    $altgrad   = 12;
    if      ($x > -482 && $x < -270 && $y > 1400 && $y < 1700) return array("Pyrewood", $altgrad);
    else if ($x > -215 && $x <    0 && $y >  700 && $y <  900) return array("Ambermill", $altgrad);
    else if ($x >  800 && $x < 1000 && $y >  550 && $y <  800) return array("Kaer Fenris", false);
    else if ($x >  330 && $x <  480 && $y > 1790 && $y < 1935) return array("Tábor Ochránců", $lordaeron);
    else if ($x >  970 && $x < 1130 && $y > 1480 && $y < 1630) return array("Mrtvé pole", $lordaeron);
    else if ($x > 1160 && $x < 1360 && $y > 1080 && $y < 1310) return array("Stříbropolí", $lordaeron);
    else if ($x >  360 && $x <  610 && $y > 1470 && $y < 1680) return array("Krchov", $lordaeron);
    else if ($x >  270)                                        return array("Lordaeron", $lordaeron);
    else                                                       return array("Altgrad", $altgrad);
  }
  
  function LocationInTirisfal ($zone, $x, $y)
  {
    if      ($x > 1582 && $x < 2290 && $y > 1139 && $y < 1926) return array("Vandermar", 14);
    else if ($x < 1637)                                        return array("Tábor Krusády", 11);
    else                                                       return array("Tirisfal", false);
  }

  function TranslateMoney ($coppers)
  {
    $golds   = intval ($coppers / 10000);
    $silvers = intval (($coppers - ($golds * 10000)) / 100);
    $coppers = $coppers - ($golds * 10000) - ($silvers * 100);
  //  return $golds."g ".$silvers."s ".$coppers."c";
    
          /*
    $goldy = intval($penize / 10000);
    $silvery = intval(($penize - ($goldy * 10000))/100);
    $coppery = $penize - ($goldy * 10000) - ($silvery * 100);
    $obnos = $goldy."g ".$silvery."s ".$coppery."c";     */
    return sprintf ("%dg %02ds %'02dc", $golds, $silvers, $coppers);
    //return $obnos;
  }
  
  function ColorizeMoney ($money)
  {
    if      ($money <  10000) return "inherit";
    else if ($money <  30000) return "#ff7";
    else if ($money <  50000) return "#ff0";
    else if ($money < 100000) return "#f77";
    else                      return "#f00";
  }
  
  function GetCharXP ($guid, $type)
  { 
    $type = ($type == "bxp") ? 40753 : 44990;
    mysql_select_db(DB_CHARACTERS);
    $q = mysql_query("SELECT a.data FROM `item_instance` a 
                                    INNER JOIN `character_inventory` b ON (a.guid = b.item) 
                                    WHERE (b.guid = $guid AND b.item_template = $type);");
    while($c = mysql_fetch_array($q))
    {
      $data = explode(" ", $c["data"]);
      $count = $data[14];
    }
    if ($count) 
      return sprintf ("%03d", $count);
    else
      return '000';
  }
  
  function GetPlayedTime ($time)
  {
    if      ($time <  3600) return array(round($time/60), "#ff7", "min");
    else if ($time < 84600) return array(round($time/3600), "#ffd", "hod");
    else                    return array(round($time/86400), "inherit", "dní");
  }
  
  function GetOnlineTime ($login_time)
  {
    $online_time = time() - $login_time;
    if   ($online_time > 3600) return round($online_time/3600)." hod";
    else                       return round($online_time/60)." min";
  }
  
  function SuspiciousPlayer ($played, $money)
  {
    return ($played < 10800 && $money > 8000) ? true : false;
  }

  function GetCharKarma ($accid)
  {
    mysql_select_db(DB_CHARACTERS);
    $karma = mysql_fetch_array(mysql_query("SELECT SUM(karma_hodnota) AS karma FROM karma WHERE hodnoceny = $accid"));
    $karma = $karma[karma];
    if      ($karma == '')             return array("0", 0);
    else if ($karma > 0 && $karma < 8) return array($karma, 0);
    else if ($karma < 0)               return array($karma, -1);
    else                               return array($karma, 1);
  }
  
  function GetCharNewKarma ($accid)
  {
    mysql_select_db(DB_CHARACTERS);
    $q = mysql_query("SELECT k.karma_hodnota AS karma, ac.gmlevel2
                      FROM karma k
                      JOIN nostalgia_realmd.account_prophet ac ON (k.hodnotici = ac.id)
                      WHERE k.hodnoceny = $accid");
    while ($c = mysql_fetch_array($q))
    {
      if ($c[gmlevel2] >= 4)
        $c[karma] = 2*$c[karma];
      
      if ($c[karma] >= 0)
        $karma += $c[karma];
      else
        $karma = $karma + (2*$c[karma]);
    }
    return array($karma, 0);
  }

  function GetCharActiveKarma ($accid)
  {
    mysql_select_db(DB_CHARACTERS);
    $q = mysql_query("SELECT k.karma_hodnota AS karma, ac.gmlevel2, ac.last_login
                      FROM karma k
                      JOIN nostalgia_realmd.account_prophet ac ON (k.hodnotici = ac.id)
                      WHERE k.hodnoceny = $accid");
    while ($c = mysql_fetch_array($q))
    {
      if (strtotime($c[last_login]) < strtotime("-90 days") && $c[gmlevel2] < 4)
          continue;
      if ($c[gmlevel2] >= 4)
        $c[karma] = 2*$c[karma];

      if ($c[karma] >= 0)
        $karma += $c[karma];
      else
        $karma = $karma + (2*$c[karma]);
    }
    return array($karma, 0);
  }






?>
