<?php

// To add a new language, add a subfolder (iso code) with the translations in /language/,
// then add the corresponding key-value pair here to create the menu.

const LANGUAGES = array(
'en' => 'EN English',
'es' => 'ES Español',
'fr' => 'FR Français',
'nl' => 'NL Nederlands'
);

// The key (iso code) must be the /language/ subfolder.
// The value is used to display the menu, where the first part (space separated) is the menu label and the rest is the help tips)

// Attention: Remove the comma after the last entry.

// Display order in the menu is just the order of the entries in this array.
// Using html entities for accent characters is recommended.
// Even if you don't use translations, you must have at least: const LANGUAGES = array('en' => 'EN English');