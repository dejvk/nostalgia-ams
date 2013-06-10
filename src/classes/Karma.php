<?php

  /// Reprezentuje jeden důvod karmy.
  class Karma
  {
    private $_giver;
    private $_receiver;
    private $_value;
    private $_reason;
    private $_valid;
    private $_time;
    

    /// Konstruktor.
    /** \param int $value Hodnota karmy.
     *  \param string $reason Důvod karmy.
     *  \param bool $validity Platnost karmy. */
    public function __construct ($value, $reason, $validity)
    {
      $this->_value  = $value;
      $this->_reason = $reason;
      $this->_valid  = $validity;
    }
    
    
    /// Vypíše karmu jako zformátovaný řádek tabulky.
    public function DrawRow ()
    {
      ?>
        
        <tr>
          <?php echo $this->getValue(); ?>
          <?php echo $this->getReason(); ?>
        </tr>
        
      <?php
    }
    
    
    /// Zvýrazní zápornou hodnotu karmy.
    /** \return Zformátovaná buňka tabulky s hodnotou karmy. */
    private function getValue ()
    {
      if ($this->_value >= 0)
        return "<td class=\"karmalist-value\">$this->_value</td>";
      else
        return "<td class=\"karmalist-value karmalist-value-negative\">$this->_value</td>";
    }
    

    /// Zvýrazní nevyhovující důvod karmy.
    /** \return Zformátovaná buňka tabulky s důvodem karmy. */
    private function getReason ()
    {
      if ($this->_valid == 1)
        return "<td class=\"karmalist-reason\">$this->_reason</td>";
      else
        return "<td class=\"karmalist-reason karmalist-reason-invalid\">$this->_reason</td>";
    }    
    
  };
    
    
  
  /// Reprezentuje kontejner na karmy s možností vypsat jejich seznam.
  /** Rozlišuje rozdané a přijaté karmy a udržuje je odděleně. */
  class KarmaList
  {
    private $_given;
    private $_received;
    
    
    /// Konstruktor.
    /** Načte z databáze všechny karmy týkající se přihlášeného uživatele.
     *  \throw Exception Nepřihlášený uživatel. */
    public function __construct ()
    {
      if (!$_SESSION["account_id"])
        throw new Exception ("Nepřihlášený uživatel");
      
      global $db;
      $q = $db -> query ("SELECT karma_hodnota, duvod, vyhovuje, hodnoceny
                          FROM ".T_KARMA."
                          WHERE hodnoceny = ".$_SESSION["account_id"]."
                             OR hodnotici = ".$_SESSION["account_id"]);
      
      $i = $j = 0;
      while ( $r = $q -> fetch_assoc() )
      {
        if ($r["hodnoceny"] == $_SESSION["account_id"])
          $this->_received [ $i++ ] = new Karma ($r["karma_hodnota"],
                                                 $r["duvod"],
                                                 $r["vyhovuje"]);
        else
          $this->_given [ $j++ ] = new Karma ($r["karma_hodnota"],
                                              $r["duvod"],
                                              $r["vyhovuje"]);
      }
      
      $q -> free ();
    }
    
    
    /// Vypíše formátovaný seznam všech uložených karem jako dvě tabulky.
    /** Jedna tabulka obsahuje přijatou karmu, druhá rozdanou. */
    public function Draw ()
    {
      ?>
      
      <table id="karmalist">
      <tr>
        <th class="karmalist-value">Hodn.
        <th class="karmalist-reason">Důvody obdržené karmy
      </tr>
      
      <?php
      
      foreach ($this->_received as $r)
      {
        $r -> DrawRow ();
      }
      
      ?>
      </table>
      
      
      <table id="karmalist">
      <tr>
        <th class="karmalist-value">Hodn.
        <th class="karmalist-reason">Důvody rozdané karmy
      </tr>
      
      <?php
      
      foreach ($this->_given as $r)
      {
        $r -> DrawRow ();
      }
      
      ?>
      
      </table>
      
      <?php
    }

  };


?>
