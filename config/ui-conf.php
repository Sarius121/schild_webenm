<?php

/**
 * ########
 * # MENU #
 * ########
 */

/**
 * enable button in menu to manually save the grade file
 * (the grade file is automatically saved after 20 edits, logout and new login)
 */
define("ENABLE_MANUAL_SAVING", true);

/**
 * enable button in menu to create and restore a backup
 */
define("ENABLE_LOCAL_BACKUPS", true);

/**
 * #############
 * # DATA TABS #
 * #############
 */

/**
 * configure which optional tabs should be displayed
 */
define("SHOW_CLASS_TEACHER_TAB", true);
define("SHOW_EXAMS_TAB", true);

/**
 * #########
 * # LOGIN #
 * #########
 */

/**
 * text shown above the login form
 */
define("LOGIN_PROMPT", "Melden Sie sich mit Ihrem webENM-Account an.");
define("UNSAVED_CHANGES_EXTRA_HINT", "Wenn Sie die Änderungen wiederherstellen, führt das aber dazu, dass alle Änderungen,
        die Sie seit dem Bearbeiten direkt oder indirekt auf IServ durchgeführt haben, verloren gehen.");

/**
 * allow webENM to automatically save all changes from previous, not properly closed sessions when logging in
 */
define("RESTORE_AUTO_CONSENT", false);

?>