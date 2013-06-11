<?php


  /// Reprezentuje registr postav s vyplněnými RP daty.
  /** \deprecated Bude sloučeno s třídou CharacterList. */
  class CharacterSheetRegister
  {
    private $_characters;


    /// Konstruktor.
    /** Importuje všechny postavy s vyplněnými RP daty z databáze.*/
    public function __construct ()
    {
      global $db;
      $q = $db -> query ("SELECT *
                           FROM ".T_CHRREGISTER."
                           ORDER BY name");

      $i = 0;
      while ( $r = $q -> fetch_assoc () )
      {
        $this->_characters[$i++] = new CharacterSheet ( $r );
      }

      $q -> free ();
    }


    /// Vypíše všechny postavy jako tabulku.
    public function Draw ()
    {
      ?>

        <table id="chrsheetreg">
        <tr>
          <th class="chrsheetrow-name">Jméno</th>
          <th class="chrsheetrow-desc">Popis</th>
        </tr>

      <?php
        foreach ($this->_characters AS $chr)
          $chr -> DrawRow ();
      ?>
        </table>
      <?php
    }
  };




  /// Reprezentuje jeden vyplněný profil postavy.
  /** \deprecated Bude sloučeno s třídou Character. */
  class CharacterSheet
  {
    private $_guid;
    private $_name;
    private $_birthDate;
    private $_birthMonth;
    private $_birthYear;
    private $_class;
    private $_desc;


    /// Konstruktor. Načte data z arraye.
    /** \param arr Array obsahující data o postavě. [ guid, name, birth_date,
     *  birth_year, profession, description ]. */
    public function __construct ( $arr )
    {
      $this->_guid      = $arr ['guid'];
      $this->_name      = $arr ['name'];
      $this->_makeDate ( $arr ['birth_date'], $arr ['birth_year'] );
      $this->_class     = $arr ['profession'];
      $this->_desc      = $arr ['description'];
    }


    /// Vypíše profil postavy
    /** \todo Zcela nezpracované.
     *  \warning Konflikt s Character::DrawProfile(). */
    public function DrawSingle () {}


    /// Vypíše stručný záznam postavy jako řádek tabulky.
    public function DrawRow ()
    {
      ?>
        <tr>
          <td class="chrsheetrow-name character-name"><?php echo $this->_name; //." (".(date("Y", mktime()) - 1393 - $this->_birthYear).")"; ?>
          <td class="chrsheetrow-desc">
              <?php
                if ( strlen ($this->_desc) >= 100 )
                  echo mb_substr($this->_desc, 0, 100, "UTF-8") . "&hellip;";
                else
                  echo $this->_desc;
              ?>
        </tr>
      <?php
    }


    /// Nastaví rok narození objektu.
    /** \param date Den a měsíc narození.
     *  \param year Rok narození. */
    private function _makeDate ( $date, $year )
    {
      $this->_birthYear = $year;
    }

  };

?>
