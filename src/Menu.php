<?php


class CMenu
{
  private $m_Items;     ///< Array of hyperlinks
  private $m_ItemsCnt;  ///< Number of items in menu
  
  public function __construct ()
  {
    $this->m_ItemsCnt = 0;
  }

  /**
   * Add item to menu. Items are stored as hypertext link. First item is handled like a homepage!
   * @param $name Name of item as it should be displayed in menu. May contain anything, but you should avoid tag <a>. Required parameter.
   * @param $address Target URL of the link. May be both relative or absolute. Required parameter.
   **/
  public function AddItem ($name, $address)
  {
    $this->m_Items[$this->m_ItemsCnt++] = "<a href=\"$address\">$name</a>";
  }


  /**
   * Prints out table with horizontal menu. Table cells are same width calculated from number of items.
   * Table layout can be customized via class ".navigation" or via custom class added as parameter.
   * Function compare actual URL with URL of the link and tag menu item which is actual with class ".active-tab". All other tab should have tag ".inactive-tab".
   * First item is handled as homepage, so if no parameter is found in the address, first item will be highlighted.
   * @param $css_class Optional parameter which is used as table class. May be used for deeper customization.
   */
  public function PrintMenu($css_class = null)
  {
    echo "<table class=\"navigation $css_class\">\n";
    echo "<tr>\n";
    for ($i = 0; $i < $this->m_ItemsCnt; $i++)
    {
      $active_tab = (strpos($this->m_Items[$i], $_GET[tab])) ? "active-tab" : ((!$i && !$_GET[tab]) ? "active-tab" : "inactive-tab");
      echo "  <td width=\"".(100/$this->m_ItemsCnt)."%\" class=\"$active_tab\">".$this->m_Items[$i]."</td>\n";
    }
    echo "</tr>\n";
    echo "</table>\n";
  }


  /**
   * \brief Prints out vertical menu as unordered list.
   * This type of menu does not highlight actual page.
   * \param $css_class Optional parameter used as list's class.
   */
  public function PrintVerticalMenu ($css_class = null)
  {
    ?>
    <ul class="sidebar-menu <?php echo $css_class; ?>">
      <?php
        for ($i = 0; $i < $this->m_ItemsCnt; $i++)
          echo "<li>".$this->m_Items[$i]."\n";
      ?>
    </ul>
    <?php
  }
};


?>