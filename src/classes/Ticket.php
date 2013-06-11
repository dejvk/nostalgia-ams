<?php


/// Reprezentuje jeden požadavek na technickou podporu.
class Ticket
{
  private $_text;
  private $_response;
  private $_change;
  private $_name;
  private $_username;
  private $_lastlogin;


  /// Konstruktor. Vytvoří instanci podle parametrů. Určeno pro hromadné načítání.
  /** \param $text,$resp Text ticketu a odpovědi.
   *  \param $change Poslední změna ticketu.
   *  \param $name,$username,$login Informace o postavě a hráči. */
  public function __construct ( $text, $resp, $change,
                                $name, $username, $login )
  {
    $this->_text      = $text;
    $this->_response  = $resp;
    $this->_change    = $change;
    $this->_name      = $name;
    $this->_username  = $username;
    $this->_lastlogin = $login;
  }


  /// Vypíše ticket jako jeden řádek.
  /** \todo Rozšířit výpis jména o další informace o hráči. */
  public function DrawRow ()
  {
    ?>
      <tr>
        <td class="character-name"><?=$this->_name;?>
        <td class="ticket-body"><?=$this->BuildText();?>
        <td class="ticket-change"><?=$this->Time();?>
      </tr>
    <?php
  }


  /// Vytvoří tělo ticketu sloučením s odpovědí.
  /** \return Dva bloky textu s textem ticketu a odpovědí, existuje-li. */
  private function BuildText ()
  {
    $ret  = "<div class=\"ticket-text\">".nl2br($this->_text)."</div>";
    if ($this->_response)
      $ret .= "<div class=\"ticket-response\">".nl2br($this->_response)."</div>";
    return $ret;
  }


  /// Překlad času do relativní a evropské absolutní podoby.
  private function Time ()
  {
    $time = round ((time() - strtotime($this->_change)) / 60 / 60 / 24 , 1);
    if ($time > 10)
      $warn = " style=\"color: #ff0; text-decoration: blink;\"";
    echo "<div $warn>před " . round ((time() - strtotime($this->_change)) / 60 / 60 / 24 , 1) . " dny</div>";
    echo "<div style=\"font-size: 90%\">".date("d.n.Y G:i", strtotime($this->_change))."</div>";
  }


};





  /// Reprezentuje kontejner na tickety s možností výpisu.
  class TicketList
  {
    private $_new;
    private $_responded;


    /// Konstruktor.
    /** Načte tickety z databáze a uloží je do kontejnerů na nové a zodpovězené.
     *  \throw Exception Nedostatečná oprávnění. */
    public function __construct ()
    {
      if (Rights() < SEC::MIN_TICKETS)
        throw new Exception ("Nemáte dostatečné oprávnění.");

      global $db;
      $q = $db -> query ("SELECT t.ticket_text, t.response_text, t.ticket_lastchange,
                                 c.name, r.username, r.last_login
                          FROM ".T_CHARACTERS." c
                          JOIN ".T_TICKETS." t ON c.guid = t.guid
                          JOIN ".T_ACCOUNTS." r ON c.account = r.id");

      $i = $j = 0;
      while ( $r = $q -> fetch_assoc() )
      {
        if ( ! $r['response_text'] )
          $this->_new [ $i++ ] = new Ticket ($r['ticket_text'], null, $r['ticket_lastchange'], $r['name'],
                                             $r['username'], $r['last_login']);
        else
          $this->_responded [ $j++ ] = new Ticket ($r['ticket_text'], $r['response_text'], $r['ticket_lastchange'],
                                                   $r['name'], $r['username'], $r['last_login']);
      }

      $q -> free ();
    }


    /// Vypíše seznam ticketů, pokud má přihlášený uživatel dostatečná oprávnění.
    public function Draw ()
    {
      ?>

        <table id="ticketlist">
        <tr>
          <th class="ticket-player">Hráč
          <th class="ticket-body">Ticket
          <th class="ticket-change">Změna
        </tr>

      <?php

        if (SEC::Check(SEC::MIN_TICKETS))
          foreach ($this->_new as $r)
            $r -> DrawRow ();

        if (SEC::Check(SEC::MIN_TICKETS_CLOSED))
          foreach ($this->_responded as $r)
            $r -> DrawRow ();

      ?>

        </table>

      <?php
    }

  };


?>
