<?php

  /// Represents table of requests
  class CRequestList
  {
    public $m_Requests; ///< Array of requests loaded


    /// Loads actual requests from database
    public function __construct ()
    {
      global $db;
      $q = $db -> query ("SELECT req.uid, chr.name, chr.totaltime, req.item_type, req.item_name, req.item_entry,
                            req.item_desc, chr2.name AS worker, req.material, req.time, req.last_change, req.state, req.supervized_by
                          FROM ".T_UNIQUE_LIST." req
                          JOIN ".T_CHARACTERS." chr ON (req.character = chr.guid)
                          LEFT JOIN ".T_CHARACTERS." chr2 ON (req.worker = chr2.guid)
                          WHERE state != 2 AND NOT (state = 20 AND last_change <= DATE_SUB(NOW(), INTERVAL 7 DAY))
                          ORDER BY req.uid");

      $i = 0;
      while ($r = $q -> fetch_assoc())
      {
        $this->m_Requests[$i++] = new CRequest ($r['uid'], $r['name'], $r['totaltime'], $r['item_type'], $r['item_name'],
                                          $r['item_entry'], $r['item_desc'], $r['worker'], $r['material'], $r['time'],
                                          $r['last_change'], $r['state'], $r['supervized_by']);
      }

      $q -> free ();
    }


    /// Prints out table of requests
    public function Draw ()
    {
      ?>

      <table id="requestlist">
      <tr>
        <th class="character-name">Postava
        <th class="custom-request">Žádost</th>
        <th class="time">Čas
      </tr>

      <?php
      foreach ($this->m_Requests as $request)
      {
        $request -> Draw ();
      }
      ?>

      </table>

      <?php
    }
  };


  /// Represents single request
  class CRequest
  {
    public $m_Uid;
    public $m_ChrName;
    public $m_ChrPlayed;
    public $m_ItemType;
    public $m_ItemName;
    public $m_ItemEntry;
    public $m_ItemDesc;
    public $m_ItemMaterial;
    public $m_Worker;
    public $m_Created;
    public $m_LastChange;
    public $m_State;
    public $m_SupervizedBy;


    /// Load-from-array constructor
    /** Prepares request on given parameters called automatically after mass load from database */
    public function __construct ( $uid, $name, $totaltime, $item_type, $item_name,
                                  $item_entry, $item_desc, $worker, $material, $time,
                                  $last_change, $state, $supervized_by )
    {
      $this->m_Uid          = $uid;
      $this->m_ChrName      = $name;
      $this->m_ChrPlayed    = $totaltime;
      $this->m_ItemType     = ($item_type) ? "spell" : "item";
      $this->m_ItemName     = $item_name;
      $this->m_ItemEntry    = $item_entry;
      $this->m_ItemDesc     = $item_desc;
      $this->m_ItemMaterial = $material;
      $this->m_Worker       = $worker;
      $this->m_Created      = date("d. n.", strtotime($time));
      $this->m_LastChange   = $last_change;
      $this->m_State        = $state;
      $this->m_SupervizedBy = $supervized_by;
    }


    /// Prints out single row to table of requests
    public function Draw ()
    {
      ?>

      <tr>
        <td class="character-name"><?php echo $this->m_ChrName."<div class=\"character-played\">".round(($this->m_ChrPlayed/60/60/24))." dní</div>"; ?>
        <td class="custom-request">
          <div class="custom-name">
            <?php echo $this->State()." <a href=\"http://www.wowhead.com/".$this->m_ItemType."=".$this->m_ItemEntry."\">".$this->m_ItemName."</a>"; ?>
          </div>
          <div class="custom-desc">
            <?php echo $this->m_ItemDesc; ?>
          </div>
          <div class="custom-material">
            <?php echo $this->m_ItemMaterial; ?>
          </div>
          <div class="custom-comments">
            <?php echo $this->Comments(); ?>
          </div>
        <td class="time"><?php echo $this->m_Created; ?>
      </tr>

      <?php
    }


    /// Translates m_State to human readable label
    private function State ()
    {
      switch ($this->m_State)
      {
        case  0: $cls = 'pending';  $text = 'Rozhoduje se'; break;
        case  1: $cls = 'crafted';  $text = 'Dozorováno';  break;
        case  2: $cls = 'closed';   $text = 'Uzavřeno';  break;
        case 10: $cls = 'denied';   $text = 'Zamítnuto'; break;
        case 11: $cls = 'approved'; $text = 'Schváleno'; break;
        case 20: $cls = 'denied';   $text = 'Zamítnuto'; break; ## closed by admin
        case 21: $cls = 'approved'; $text = 'Schváleno'; break; ## closed by admin
        default: $cls = 'closed';   $text = 'Uzavřeno';  break;
      }

      return "<span class=\"label-status-$cls\">$text</span>";
    }


    /// Loads up voters' comments from db and prints it out one-by-one
    private function Comments ()
    {
      global $db;
      $q = $db -> query ("SELECT vote, notes
                            FROM ".T_UNIQUE_VOTES."
                            WHERE request_uid = ".$this->m_Uid." AND notes IS NOT NULL");
      $i = 0;
      while ($r = $q -> fetch_assoc ())
      {
        $val = ($r['vote'] == 1) ? "yes" : "no";
        ?>

          <div class="custom-comment-<?php echo $val; ?>"><?php echo $r['notes']; ?></div>

        <?php
      }

      $q -> free ();
    }

  };

?>
