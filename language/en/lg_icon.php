<?php
/**
 * @var array $L
 */
// Prefix icons can be use to label the tickets.
// Each section can have a distinct set of icons.
// To create a set of icons, create a series here after and upload the corresponding GIF images in the /skin/ directory
// A serie begin with a letter (a,b,...) and contains members noted '01' to '09'.
// The member 00 is used to define none, meaning no icon displayed (and has no corresponding icon file).

// SERIE A: EMOTICONS
$L['PrefixSerie']['a']='a) Emoticon'; // name of the serie
$L['PrefixIcon']['a01']='Happy';
$L['PrefixIcon']['a02']='Sad';
$L['PrefixIcon']['a03']='Hoops';
$L['PrefixIcon']['a04']='Wink';
$L['PrefixIcon']['a05']='Angry';
$L['PrefixIcon']['a06']='Surprise';
$L['PrefixIcon']['a07']='Question';
$L['PrefixIcon']['a08']='Important';
$L['PrefixIcon']['a09']='Idea';

// SERIE B: TECHNICAL PHASES
$L['PrefixSerie']['b']='b) Technical phase'; // name of the serie
$L['PrefixIcon']['b01']='A';
$L['PrefixIcon']['b02']='AA';
$L['PrefixIcon']['b03']='AAA';
$L['PrefixIcon']['b04']='X';
$L['PrefixIcon']['b05']='XX';
$L['PrefixIcon']['b06']='XXX';

// SERIE C: USUAL
$L['PrefixSerie']['c']='c) Usual'; // name of the serie
$L['PrefixIcon']['c01']='Minor';
$L['PrefixIcon']['c02']='Major';
$L['PrefixIcon']['c03']='Critical';

// SERIE D: Stars
$L['PrefixSerie']['d']='d) Stars'; // name of the serie
$L['PrefixIcon']['d01']='No star';
$L['PrefixIcon']['d02']='1 star';
$L['PrefixIcon']['d03']='2 stars';
$L['PrefixIcon']['d04']='3 stars';

// If you add a new serie here:
// don't forget to update this file in all languages
// don't forget to upload the corresponding .gif images in all skins

// SYSTEM ICONS (do not change)
$L['Ico_section_0_0']='Public section (actif)';
$L['Ico_section_0_1']='Public section (frosen)';
$L['Ico_section_1_0']='Hidden section (actif)';
$L['Ico_section_1_1']='Hidden section (frosen)';
$L['Ico_section_2_0']='Private section (actif)';
$L['Ico_section_2_1']='Private section (frosen)';

$L['View_n']='Normal view';
$L['View_c']='Compact view';
$L['View_p']='Print view';
$L['View_f_c']='Calendar view';
$L['View_f_n']='Table view';

$L['Ico_user_p']='User';
$L['Ico_user_pZ']='User (no profile)';
$L['Ico_user_w']='Open website';
$L['Ico_user_wZ']='no website';
$L['Ico_user_e']='Send e-mail';
$L['Ico_user_eZ']='no e-mail';

$L['Ico_item_t']=$L['Item'];
$L['Ico_item_tZ']=$L['Item'].' closed';
$L['Ico_item_a']='News';
$L['Ico_item_aZ']='News closed';
$L['Ico_item_i']='Inspection';
$L['Ico_item_iZ']='Inspection closed';

$L['Ico_post_p']='Message';
$L['Ico_post_r']='Reply message';
$L['Ico_post_f']='Forwarded message';
$L['Ico_post_d']='Deleted message';

$L['Bbc']['bold']='Bold';
$L['Bbc']['italic']='Italic';
$L['Bbc']['under']='Underline';
$L['Bbc']['bullet']='Bullet';
$L['Bbc']['quote']='Quote';
$L['Bbc']['code']='Code';
$L['Bbc']['url']='Url';
$L['Bbc']['mail']='E-mail';
$L['Bbc']['image']='Image (use @ to view attached image)';
$L['Bbc']['Quotation']='Quotation';
$L['Bbc']['Quotation_from']='Quotation from';