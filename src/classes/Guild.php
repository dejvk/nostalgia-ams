<?php

/// Reprezentuje cech.
/** \todo Administrační rozhraní pro cechmistra.*/
class Guild
{
  private $_uid;
  private $_name;


  /// Konstruktor
  public function __construct ( $uid )
  {
    $this->_uid = $uid;
  }



  /** Vypíše všechny členy cechu včetně jejich hodností. */
  public function Draw ()
  {
    global $db;
    $q = $db -> query ("SELECT ch.`name` AS c_name, che.`name` AS ce_name,
                          cr.`name` AS rank, c.`name` AS guild
                        FROM ".T_GUILD_MEMBERS." cc
                        JOIN ".T_GUILD_RANKS." cr
                          ON cc.id_spolku = cr.id_cechu AND cr.id_ranku = cc.rank
                        JOIN ".T_CHARACTERS." ch
                          ON cc.guid = ch.guid
                        LEFT JOIN ".T_CHRREGISTER." che
                          ON ch.guid = che.guid
                        JOIN ".T_GUILDS." c
                          ON c.id = cc.id_spolku
                        WHERE c.id = $this->_uid
                        ORDER BY cr.id_ranku");

    if ( ! $q -> num_rows )
      return;

    ?>

      <table id="guildmemberlist">
        <tr>
          <th>Jméno
          <th>Hodnost
          <th>Další informace
        </tr>

    <?php

    while ( $r = $q -> fetch_assoc () )
    {
      ?>

        <tr>
          <td class="guild-char character-name">
            <a href="?mode=character&character=<?=$r['c_name'];?>">
              <?=$r['ce_name'] ? $r['ce_name'] : $r['c_name'];?>
            </a>
          <td class="guild-rank">
            <?=$r['rank'];?>
          <td>

      <?php
    }

    ?>

      </table>

    <?php

    $q -> free ();
  }


  /** Vrátí jméno cechu podle zadaného ID.
   *  \param $id ID cechu.
   *  \return Jméno cechu.*/
  static function GetNameById ( $id )
  {
    global $db;
    $r = $db->query ("SELECT name
                      FROM cechy
                      WHERE id = $id");
    $ret = $r->fetch_assoc();
    return $ret['name'];
  }


  /** Vypíše seznam cechů s možností si otevřít podrobnější informace.
   *  \todo Umožnit skryté cechy, které se nezobrazí. */
  static function DrawListOfGuilds ()
  {
    global $db;
    $q = $db -> query ("SELECT g.id, g.name AS g_name, ch.name AS c_name,
                          che.name AS ce_name
                        FROM ".T_GUILDS." g
                        LEFT JOIN ".T_GUILD_MEMBERS." gm
                          ON g.id = gm.id_spolku AND gm.rank = 0
                        LEFT JOIN ".T_CHARACTERS." ch
                          ON gm.guid = ch.guid
                        LEFT JOIN ".T_CHRREGISTER." che
                          ON gm.guid = che.guid
                        ORDER BY g.name");

    if ( ! $q -> num_rows )
      return;

    ?>
      <table id="guildlist">
        <tr>
          <th>Název cechu
          <th>Vůdce
          <th>Další informace
        </tr>

    <?php

    while ( $r = $q -> fetch_assoc () )
    {
      ?>

        <tr>
          <td class="guild-name">
            <a href="?mode=guilds&guild=<?=$r['id'];?>">
              <?=$r['g_name'];?>
            </a>
          <td class="guild-master">
            <a href="?mode=character&character=<?=$r['c_name'];?>">
              <?=$r['ce_name'] ? $r['ce_name'] : $r['c_name'];?>
            </a>
          <td>
        </tr>

      <?php
    }

    ?>

      </table>

    <?php

    $q -> free ();
  }

};

?>
