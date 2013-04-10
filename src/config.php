<?php

  // Database connection
  define (MYSQL_SERVER, "");
  define (MYSQL_USER, "");
  define (MYSQL_PASS, "");
  
  // Main switch for application modules
  define (M_DEBUGGING, true);
  define (M_LOGIN, false);
  define (M_UNIQUE, false);
  define (M_UNIQUE_RESTRICTIONS, false);
  define (M_MYPROFILE, false);
  define (M_KARMAMGR, false);
  
  // Table names in database.table format
  define (T_AMS_USERS, "");
  define (T_ACCOUNTS, "");
  define (T_CHARACTERS, "");
  define (T_UNIQUE_LIST, "");
  define (T_UNIQUE_VOTES, "");
  define (T_UNIQUE_USERS, "");
  define (T_BANS, "");
  define (T_SUPPORT, "");
  define (T_KARMA, "");
  
  // Restrictions definition for support
  define (R_KARMA, "");
  define (R_PLAYED, "");
  define (R_NOTBANNED, "");
  
  // User roles
  define (U_GUEST, 0);
  define (U_PLAYER, 1);
       //(not used, 2); // reserved
  define (U_PRIVILEGED, 3);
  define (U_MASTER, 4);
  define (U_ADMIN, 5);
  
  // Customize layout
  define (L_STYLE, "default");
  define (L_LOGO, "");
  define (L_LANG, "cs-CZ");
  define (L_SITENAME, "Nostalgia AMS");
  
  
  

?>
