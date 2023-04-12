<?php
/**
 * @var array $L
 */
// Prefix icons can be use to label the tickets.
// Each section can have a distinct set of icons.
// To create a set of icons, create a series here after and upload the corresponding GIF images in the /skin/ directory
// A serie begin with a letter (a,b,...) and contains members noted "_01" to "_09".
// The member '00' is used to definie 'none', meaning no icon displayed (and has no corresponding icon file).

// SERIE A: EMOTICONS
$L['PrefixSerie']['a']='a) Emoticone'; // name of the serie
$L['PrefixIcon']['a01']='Content';
$L['PrefixIcon']['a02']='Triste';
$L['PrefixIcon']['a03']='Hoops';
$L['PrefixIcon']['a04']='Clin d\'oeil';
$L['PrefixIcon']['a05']='F&acirc;ché';
$L['PrefixIcon']['a06']='Surpris';
$L['PrefixIcon']['a07']='Question';
$L['PrefixIcon']['a08']='Important';
$L['PrefixIcon']['a09']='Idée';

// SERIE B: TECHNICAL PHASES
$L['PrefixSerie']['b']='b) Phase technique'; // name of the serie
$L['PrefixIcon']['b01']='A';
$L['PrefixIcon']['b02']='AA';
$L['PrefixIcon']['b03']='AAA';
$L['PrefixIcon']['b04']='X';
$L['PrefixIcon']['b05']='XX';
$L['PrefixIcon']['b06']='XXX';

// SERIE C: USUAL
$L['PrefixSerie']['c']='c) Usuel'; // name of the serie
$L['PrefixIcon']['c01']='Mineur';
$L['PrefixIcon']['c02']='Majeur';
$L['PrefixIcon']['c03']='Critique';

// SERIE D: Stars
$L['PrefixSerie']['d']='d) Etoiles'; // name of the serie
$L['PrefixIcon']['d01']='Aucune étoile';
$L['PrefixIcon']['d02']='1 étoile';
$L['PrefixIcon']['d03']='2 étoiles';
$L['PrefixIcon']['d04']='3 étoiles';

// If you add a new serie here:
// don't forget to update this file in all languages
// don't forget to upload the corresponding .gif images in all skins

// SYSTEM ICONS (do not change)
$L['Ico_section_0_0']='Section publique (active)';
$L['Ico_section_0_1']='Section publique (inactive)';
$L['Ico_section_1_0']='Section cachée (active)';
$L['Ico_section_1_1']='Section cachée (inactive)';
$L['Ico_section_2_0']='Section privée (active)';
$L['Ico_section_2_1']='Section privée (inactive)';

$L['View_n']='Vue normale';
$L['View_c']='Vue compacte';
$L['View_p']='Imprimer';
$L['View_f_c']='Vue calendrier';
$L['View_f_n']='Vue tableau';

$L['Ico_user_p']='Utilisateur';
$L['Ico_user_pZ']='Utilisateur (pas de profil)';
$L['Ico_user_w']='Voir le site web';
$L['Ico_user_wZ']='pas de site web';
$L['Ico_user_e']='Envoyer un e-mail';
$L['Ico_user_eZ']='pas d\'e-mail';

$L['Ico_item_t']='Ticket';
$L['Ico_item_tZ']='Ticket fermé';
$L['Ico_item_a']='News';
$L['Ico_item_aZ']='News closed';
$L['Ico_item_i']='Inspection';
$L['Ico_item_iZ']='Inspection fermé';

$L['Ico_post_p']='Message';
$L['Ico_post_r']='Réponse';
$L['Ico_post_f']='Forward';
$L['Ico_post_d']='Message effacé';

$L['Bbc']['bold']='Gras';
$L['Bbc']['italic']='Italic';
$L['Bbc']['under']='Souligné';
$L['Bbc']['bullet']='Bullet';
$L['Bbc']['quote']='Citer';
$L['Bbc']['code']='Code';
$L['Bbc']['url']='Url';
$L['Bbc']['mail']='E-mail';
$L['Bbc']['image']='Image (avec @ pour afficher l\'image attachée)';
$L['Bbc']['Quotation']='Citation';
$L['Bbc']['Quotation_from']='Citation de';