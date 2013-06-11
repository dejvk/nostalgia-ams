<?php

/// Reprezentuje cech. Nehotové.
/** \warning Třída není hotová.
 *  \todo Výpis členů cechu.
 *  \todo Administrační rozhraní pro cechmistra.*/
class Guild
{
  private $_uid;
  private $_name;


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
};
