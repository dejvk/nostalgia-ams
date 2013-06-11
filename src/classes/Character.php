<?php

  session_start();

  /// Reprezentuje jednu postavu
  class Character
  {
    private $_guid;
    private $_name;
    private $_age;
    private $_desc;
    private $_race;
    private $_class;
    private $_played;


    /** Konstruktor.
     *  Vytvoří objekt postavy ze zadané arraye.
     *  Pokud není array předána, pokusí se importovat z databáze na základě
     *  <tt>GET["character"]</tt>.
     *  \throws Exception Nebyl nalezen žádný vstupní parametr.
     *  \param $arr Array obsahující informace o postavě
     *  [ GUID, Jméno, (Datum narození, Rok narození), Popis,
     *    Rasa, Povolání, Odehraný čas ] */
    public function __construct ( $arr = null )
    {
      if ( $arr )
        $this->ImportFromArray ( $arr );
      else if ( $_GET["character"] )
        $this->ImportByName ( $_GET["character"] );
      else throw Exception ("Neznámá postava.");
    }


    /** Konstruktor z arraye.
     *  \param $arr Array obsahující informace o postavě
     *  [ GUID, Jméno, (Datum narození, Rok narození), Popis,
     *    Rasa, Povolání, Odehraný čas ] */
    private function ImportFromArray ( $arr )
    {
      $this->_guid        = $arr["guid"];
      $this->_name        = $arr["name"];
      $this->SetAge ( $arr["dob"] );
      $this->_desc        = $arr["desc"];
      $this->_race        = $arr["race"];
      $this->_class       = $arr["class"];
      $this->_played      = $arr["played"];
    }


    /** Konstruktor podle jména.
     *  \param $name Jméno postavy */
    private function ImportByName ( $name )
    {
      global $db;
      $q = $db -> query ("SELECT chr.guid, chrext.name AS name_ext, chr.name,
                                 chr.race, chr.class, chr.totaltime,
                                 chrext.description AS descr, chrext.birth_date,
                                 chrext.birth_year
                          FROM ".T_CHARACTERS." chr
                          LEFT JOIN ".T_CHRREGISTER." chrext
                            ON (chr.guid = chrext.guid)
                          WHERE chr.name = '$name'
                          ORDER BY chr.name");

      if (!$q)
        throw new Exception ($db->error);

      $r = $q -> fetch_assoc ();

      $arr = [ "guid"   => $r["guid"],
               "name"   => ($r["name_ext"] ? $r["name_ext"] : $r["name"]),
               "dob"    =>
                 [ "date" => $r["birth_date"],
                   "year" => $r["birth_year"] ],
               "desc"   => $r["descr"],
               "race"   => $r["race"],
               "class"  => $r["class"],
               "played" => $r["totaltime"] ];

      $this->ImportFromArray ( $arr );
    }


    /** Vypíše stručné informace o postavě jako řádek tabulky. */
    public function DrawRow ()
    {
      ?>
        <tr>
          <td class="character-name characterlist-name"><?php echo $this->_name; ?>
          <td class="characterlist-age"><?php echo $this->_age; ?>
          <td class="characterlist-desc">
            <?php 
              if (strlen($this->_desc) > 75) 
                echo mb_substr($this->_desc, 0, 75, "UTF-8")."&hellip;";
              else
                echo $this->_desc; ?>
          <td class="characterlist-edit not-implemented">[profil]
        </tr>
      <?php
    }


    /** Vypíše kompletní profil postavy.
     *  \todo Ověření pravomocí na zobrazení
     *  \todo Zobrazení obrázku postavy
     *  \todo Výpis cechů */
    public function DrawProfile ()
    {
      ?>

        <h2>Databáze postav</h2>
        <div id="characterprofile-wrapper">
        <div id="characterprofile">

          <div class="characterportrait-big"> </div>
          <div class="character-name"><?=$this->_name; ?></div>
          <div class="guild-name"><?=$this->GetGuildNames();?></div>
          <div class="dob"><?=$this->_age?$this->_age." let":false;?></div>
          <div class="character-desc"><?=$this->_desc; ?></div>

        </div>
        </div>

      <?
    }


    /// Nastaví věk postavy podle zadaného data narození.
    /** \param dob Array [ date => Datum narození, year => Rok narození ]
     *  \attention Ve skutečnosti se věk počítá jen podle roku, nikoliv dne.
     *  \todo Počítat věk podle celého data. */
    private function SetAge ( $dob )
    {
      if ($dob["year"])
        $this->_age = date("Y", mktime()) - 1393 - $dob["year"];
    }


    /** Získá všechny cechy, ve kterých je postava registrovaná a vypíše je.
     *  \return Seznam všech cechů postavy včetně hodností. */
    private function GetGuildNames ()
    {
      global $db;
      $q = $db -> query ("SELECT c.`name` AS guild, cr.`name` AS rank
                          FROM ".T_GUILD_MEMBERS." cc
                          JOIN ".T_GUILDS." c
                           ON c.id = cc.id_spolku
                          JOIN ".T_GUILD_RANKS." cr
                           ON cr.id_cechu = c.id AND cr.id_ranku = cc.rank
                          WHERE cc.guid = $this->_guid");

      $ret = null;
      while ( $r = $q -> fetch_assoc () )
      {
        $ret .= $r['rank']." &lt;".$r['guild']."&gt;<br>";
      }
      return $ret;
    }
  };

  /// Reprezentuje seznam postav na účtu přihlášeného uživatele.
  /** \todo Umožnit reprezentaci libovolného seznamu postav. */
  class CharacterList
  {
    private $_characters;


    /** Konstruktor.
     *  Importuje z databáze všechny postavy přihlášeného uživatele a uloží
     *  do pole.
     *  \throw Exception V případě nepřihlášeného uživatele.
     *  \throw Exception V případě selhání připojení k databázi.
     */
    public function __construct ()
    {
      if (!$_SESSION['account_id'])
        throw new Exception ("Nepřihlášený uživatel");

      global $db;
      $q = $db -> query ("SELECT chr.guid, chrext.name AS name_ext, chr.name, chr.race, chr.class, chr.totaltime,
                                 chrext.description AS descr, chrext.birth_date, chrext.birth_year
                          FROM ".T_CHARACTERS." chr
                          LEFT JOIN ".T_CHRREGISTER." chrext ON (chr.guid = chrext.guid)
                          WHERE chr.account = ".$_SESSION['account_id']."
                          ORDER BY chr.name");
      
      if (!$q)
        throw new Exception ($db->error);
        

      $i = 0;
      while ( $r = $q -> fetch_assoc () )
      {
        $name = $r['name_ext'] ? $r['name_ext'] : $r['name'];
        $this->_characters [ $i++ ] = new Character ( [ "guid" => $r['guid'],
                                                        "name" => $name,
                                                        "dob" => [ "date" => $r['birth_date'],
                                                                   "year" => $r['birth_year']],
                                                        "desc" => $r['descr'],
                                                        "race" => $r['race'],
                                                        "class" => $r['class'],
                                                        "played" => $r['totaltime'] ] );
      }
      

      $q -> free ();
    }


    /** Vypíše seznam postav jako tabulku. */
    public function Draw ()
    {
      ?>

        <table id="characterlist">
          <tr>
            <th class="characterlist-name">Jméno
            <th class="characterlist-age">Věk
            <th class="characterlist-desc">Popis
            <th class="characterlist-edit">
          </tr>

      <?php
      
      foreach ($this->_characters AS $r)
      {
        $r -> DrawRow ();
      }

      ?>
        </table>
      <?php
    }
  };
