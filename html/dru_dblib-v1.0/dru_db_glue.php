<?php
/*
 * dru_db_glue.php
 *
 * Routines and support functions as well as stubs for code compatibility
 * glue for borrowed libraries like the mySQL database library from Drupal
 *
 */

// KBI 130903 KBI created

function variable_get( $name, $options = 0, $x1 = NULL, $x2 = NULL ) {
	return false;
}


function drupal_init_language() {
	return true;
}

function drupal_maintenance_theme() {
	return true;
}

function drupal_set_header($header = NULL) {
	return true;
}

function drupal_set_title($title = NULL) {
	return true;
}

function theme() {
	return true;
}


/**
 * Encode special characters in a plain-text string for display as HTML.
 *
 * Also validates strings as UTF-8 to prevent cross site scripting attacks on
 * Internet Explorer 6.
 *
 * @param $text
 *   The text to be checked or processed.
 * @return
 *   An HTML safe version of $text, or an empty string if $text is not
 *   valid UTF-8.
 *
 * @see drupal_validate_utf8().
 */
function check_plain($text) {
  static $php525;

  if (!isset($php525)) {
    $php525 = version_compare(PHP_VERSION, '5.2.5', '>=');
  }
  // We duplicate the preg_match() to validate strings as UTF-8 from
  // drupal_validate_utf8() here. This avoids the overhead of an additional
  // function call, since check_plain() may be called hundreds of times during
  // a request. For PHP 5.2.5+, this check for valid UTF-8 should be handled
  // internally by PHP in htmlspecialchars().
  // @see http://www.php.net/releases/5_2_5.php
  // @todo remove this when support for either IE6 or PHP < 5.2.5 is dropped.

  if ($php525) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
  }
  return (preg_match('/^./us', $text) == 1) ? htmlspecialchars($text, ENT_QUOTES, 'UTF-8') : '';
}


?>
