<?php
// Html config
if ( !defined('QT_HTML_CHAR') ) define ('QT_HTML_CHAR', 'utf-8');
if ( !defined('QT_HTML_DIR') ) define ('QT_HTML_DIR', 'ltr');
if ( !defined('QT_HTML_LANG') ) define ('QT_HTML_LANG', 'fr');

/**
 * TRANSLATION RULES
 * Use capital on first lettre, the script changes to lower case if required.
 * The index cannot contain the [.] point character. Plural forms are definied by adding '+' to the index
 * The doublequote ["] is forbidden
 * To include a single quote use escape [\']
 * Use html entities for accent characters. You can use plain accent characters if your are sure that this file is utf-8 encoded
 * Note: If you need to re-use a word in lowercase inside an other definition, you can use strtolower($L['Word'])
 */

// TOP LEVEL VOCABULARY
// Use the top level vocabulary to give the most appropriate name for the topics (object items) managed by this application.
// e.g. Ticket, Incident, Subject, Thread, Request...
$L['Domain']='Domaine';  $L['Domain+']='Domaines';
$L['Section']='Section'; $L['Section+']='Sections';
$L['Item']='Ticket';     $L['Item+']='Tickets';
$L['item']='ticket';     $L['item+']='tickets'; // lowercase because re-used in language definition
$L['Reply']='Réponse';   $L['Reply+']='Réponses';
$L['reply']='réponse';   $L['reply+']='réponses';
$L['News']='News';       $L['News+']='News'; // In other languages: News=One news, Newss=Several news
$L['news']='news';       $L['news+']='news';
$L['Message']='Message'; $L['Message+']='Messages';
$L['Inspection']='Inspection'; $L['Inspection+']='Inspections';
$L['Forward']='Transfers'; $L['Forward+']='Transfers';

// Controls
$L['Y']='Oui';
$L['N']='Non';
$L['And']='Et';
$L['Or']='Ou';
$L['Ok']='Ok';
$L['Cancel']='Annuler';
$L['Save']='Sauver';
$L['Exit']='Exit'; $L['Reset']='Effacer';

// Errors
include 'app_error.php'; // includes roles

// Menu
$L['Administration']='Administration';
$L['About']='À propos';
$L['Legal']='Notices légales';
$L['Login']='Connexion';
$L['Logout']='Déconnexion';
$L['Memberlist']='Membres';
$L['Profile']='Profil';
$L['Register']='S\'enregistrer';
$L['Search']='Chercher';
$L['Help']='Aide';

// User and role
$L['User']='Utilisateur'; $L['User+']='Utilisateurs';
$L['Status']='Statut'; $L['Status+']='Statuts';
$L['Hidden']='Caché'; $L['Hidden+']='Cachés';
$L['Actor']='Acteur';
$L['Author']='Auteur';
$L['Deleted_by']='Effacé par';
$L['Handled_by']='Gérés par';
$L['Modified_by']='Modifié par';
$L['Notified_user']='Utilisateur notifié';
$L['Notify_also']='Notifier aussi';
$L['Role']='Rôle';
$L['Top_participants']='Top participants';
$L['Username']='Nom d\'utilisateur';

// Common

$L['Action']='Action';
$L['Add']='Ajouter';
$L['Add_user']='Nouvel utilisateur';
$L['All']='Tous';
$L['and']='et'; // lowercase
$L['Assign_to']='Assigner à';
$L['Attachment']='Document'; $L['Attachment+']='Documents';
$L['Avatar']='Photo';
$L['By']='Par';
$L['Birthday']='Date de naissance';
$L['Birthdays_calendar']='Calendrier des anniversaires';
$L['Change']='Changer';
$L['Change_name']='Changer l\'identifiant';
$L['Change_status']='Changer le statut...';
$L['Change_type']='Changer le type...';
$L['Changed']='Changé';
$L['Charts']='Graphiques';
$L['Close']='Fermer';
$L['Closed']='Fermé';
$L['Column']='Colonne';
$L['Commands']='Commandes';
$L['Contact']='Contacte';
$L['Containing']='Contenant';
$L['Continue']='Continuer';
$L['Coord']='Coordonnées';
$L['Created']='Créé';
$L['Csv']='Export'; $L['H_Csv']='Ouvrir dans un tableur';
$L['Date']='Date';
$L['Date+']='Dates';
$L['Day']='Jour';
$L['Day+']='Jours';
$L['Default']='Défaut'; $L['Use_default']='Utiliser le défaut';
$L['Delete']='Effacer';
$L['Delete_tags']='Effacer (clickez un mot ou tappez * pour tout effacer)';
$L['Destination']='Destination';
$L['Details']='Détails';
$L['Disable']='Désactiver';
$L['Display_at']='Afficher à la date';
$L['Drop_attachment']='Effacer le document';
$L['Edit']='Editer';
$L['Email']='E-mail'; $L['No_Email']='Pad d\'e-mail';
$L['First']='Première';
$L['Goodbye']='Vous êtes déconnecté... Au revoir';
$L['Goto']='Atteindre';
$L['H_Website']='Url avec http://';
$L['H_Wisheddate']='date de livraison souhaitée';
$L['Help']='Aide';
$L['I_wrote']='J\'ai écrit';
$L['Information']='Information';
$L['Items_per_month']='Tickets par mois';
$L['Items_per_month_cumul']='Cumul des tickets par mois';
$L['Joined']='Depuis';
$L['Last']='Dernière';
$L['latlon']='(lat,lon)';
$L['Legend']='Légende';
$L['Location']='Localisation';
$L['Maximum']='Maximum';
$L['Me']='Moi';
$L['Message_deleted']='Message effacé...';
$L['Minimum']='Minimum';
$L['Missing']='Un champ obligatoire est vide';
$L['Modified']='Modifié';
$L['Month']='Mois';
$L['More']='Plus';
$L['Move']='Déplacer';
$L['Name']='Nom';
$L['None']='Aucun';
$L['Notification']='Notification';
$L['Opened']='Ouvert';
$L['Options']='Options';
$L['or']='ou'; // lowercase
$L['Other']='Autre'; $L['Other+']='Autres';
$L['Page']='Page';   $L['Page+']='Pages';
$L['Parameters']='Parametres';
$L['Password']='Mot de passe';
$L['Percent']='Pourcent';
$L['Phone']='Téléphone';
$L['Picture']='Photo';
$L['Picture+']='Photos';
$L['Prefix']='Préfixe';
$L['Preview']='Aperçu';
$L['Privacy']='Vie privée';
$L['Reason']='Raison';
$L['Ref']='Ref.';
$L['Remove']='Enlever';
$L['Result']='Résultat';
$L['Result+']='Résultats';
$L['Save']='Sauver';
$L['Score']='Score';
$L['Seconds']='Secondes';
$L['Security']='Sécurité';
$L['Send']='Envoyer';
$L['Send_on_behalf']='Au nom de';
$L['Settings']='Paramètres';
$L['Show']='Afficher';
$L['Signature']='Signature';
$L['Statistics']='Statistiques';
$L['Tag']='Catégorie';
$L['Tag+']='Catégories';
$L['Time']='Heure';
$L['Title']='Titre';
$L['Total']='Total';
$L['Type']='Type';
$L['Unknown']='Inconnu';
$L['Unchanged']='Inchangé';
$L['Update']='Mettre à jour';
$L['Used_tags']=$L['Tag+'].' utilisées';
$L['Views']='Vues';
$L['Website']='Site web'; $L['No_Website']='Aucun site web';
$L['Welcome']='Bienvenue';
$L['Welcome_not']='Je ne suis pas %s !';
$L['Welcome_to']='Bienvenue à un nouveau membre, ';
$L['Wisheddate']='Date demandée';
$L['Year']='Année'; $L['Years']='Années';
$L['yyyy-mm-dd']='aaaa-mm-jj';

// Section

$L['New_item']='Nouveau '.$L['item'];
$L['Goto_message']='Voir le dernier message';
$L['Allow_emails']='Permettre l\'envoi des emails';
$L['Change_actor']='Changer d\'acteur';
$L['Close_item']='Fermer le ticket';
$L['Close_my_item']='Je ferme mon ticket';
$L['Closed_item']=$L['Item'].' fermé';
$L['Closed_item+']=$L['Item+'].' fermés';
$L['Edit_start']='Mode édition';
$L['Edit_stop']='Arrêter l\'édition';
$L['First_message']='Premier message';
$L['Insert_forward_reply']='Ajouter l\'info de transfert dans les réponses';
$L['Item_closed']='Ticket fermé';
$L['Item_closed_hide']='Masquer les tickets fermés';
$L['Item_closed_show']='Montrer les tickets fermés';
$L['Item_forwarded']='Ticket a été pris en charge par %s.';
$L['Item_handled']='Ticket géré';
$L['Item_insp_hide']='Masquer les inspections';
$L['Item_insp_show']='Montrer les inspections';
$L['Item_news_hide']='Masquer les news';
$L['Item_news_show']='Montrer les news';
$L['Item_show_all']='Montrer toutes les sections';
$L['Item_show_this']='Montrer cette section uniquement';
$L['Items_deleted']='Tickets effacés';
$L['Items_handled']='Tickets gérés';
$L['Last_message']='Dernier message';
$L['Move_follow']='Renuméroté (suivant la section de destination)';
$L['Move_keep']='Conserver la référence originale';
$L['Move_reset']='Remettre la référence à zéro';
$L['Move_to']='Déplacer vers';
$L['My_last_item']='Mon dernier ticket';
$L['My_preferences']='Mes préférences';
$L['News_on_top']='News actives en premier';
$L['Post_reply']='Répondre';
$L['Previous_replies']='Précédentes réponses';
$L['Quick_reply']='Reponse rapide';
$L['Quote']='Citer';
$L['Unreplied']='Abandonnés';
$L['Unreplied_news']='News abandonnées';
$L['Unreplied_def']='Sujets ouverts et sans réponse depuis plus de %s jours';
$L['You_reply']='J\'ai répondu';
$L['Showhide_legend']='Afficher/réduire les info et légende';
$L['Only_your_items']='Dans cette section, seuls vos propres '.$L['item+'].' peuvent être affichés.';

// Stats
$L['General_site']='Site en géneral';
$L['Board_start_date']='Mise en service';

// Search
$L['Advanced_search']='Recherche avancée';
$L['All_my_items']='Mes tickets';
$L['All_news']='Toutes les news';
$L['Any_status']='Tout statut';
$L['Any_time']='Toute date';
$L['At_least_0']='Avec ou sans réponse';
$L['At_least_1']='Au moins 1 réponse';
$L['At_least_2']='Au moins 2 réponses';
$L['At_least_3']='Au moins 3 réponses';
$L['Number_or_keyword']='Numéro de '.$L['item'].' ou mot clé';
$L['H_Reference']='(entrez seulement la partie numérique)';
$L['Multiple_input']='Vous pouvez indiquer plusieurs mots séparés par %1$s (ex.: t1%1$st2 recherche les tickets contenant "t1" ou "t2").';
$L['In_all_sections']='Dans toutes sections';
$L['In_title_only']='Dans le titre uniquement';
$L['Keywords']='Mot(s) clé';
$L['Only_in_section']='Dans la section';
$L['Recent_items']=$L['Item+'].' récents';
$L['Search_by_date']='Chercher par date';
$L['Search_by_key']='Chercher par mot(s) clé';
$L['Search_by_ref']='Chercher par numéro';
$L['Search_by_status']='Chercher par statut';
$L['Search_by_tag']='Chercher par catégorie';
$L['Search_by_words']='Chercher chaque mot séparément';
$L['Search_criteria']='Critère de recherche';
$L['Search_exact_words']='Chercher les mots ensemble';
$L['Search_option']='Option de recherche';
$L['Show_only_tag']='Catégories dans cette liste <small>(clickez pour filtrer)</small>';
$L['Tag_only']='uniquement de catégorie'; // must be in lowercase
$L['This_month']='Ce mois';
$L['This_week']='Cette semaine';
$L['This_year']='Cette année';
$L['Too_many_keys']='Trop de mots clés';
$L['With_tag']= 'Catégorie';
$L['Between_date']='Entre la date';

//Search result
$L['Search_result']='Résultat de recherche';
$L['Search_results']=$L['Item+'];
$L['Search_results_actor']=$L['Item+'].' gérés par %s';
$L['Search_results_keyword']=$L['Message+'].' contenant %s';
$L['Search_results_last']=$L['item+'].' de cette semaine';
$L['Search_results_news']=$L['News'];
$L['Search_results_insp']=$L['Inspection+'];
$L['Search_results_ref']=$L['item+'].' ayant la réf. %s';
$L['Search_results_tags']=$L['item+'].' de catégorie %s';
$L['Search_results_user']=$L['Item+'].' créés par %s';
$L['Search_results_user_m']=$L['Message+'].' créés par %s';
$L['Search_results_date']=$L['item+'].' entre le %1$s et %2$s';
$L['Username_starting']='Nom d\'utilisateur commençant par';
$L['other_char']='un chiffre ou symbole';

// Inspection - Category
$L['I_aggregation']='Méthode d\'agrégation';
$L['I_closed']='Inspection fermée';
$L['I_level']='Niveau de réponse';
$L['I_r_bad']='Mauvais';
$L['I_r_good']='Bon';
$L['I_r_high']='Haut';
$L['I_r_low']='Faible';
$L['I_r_medium']='Moyen';
$L['I_r_no']='Non';
$L['I_r_veryhigh']='Très haut';
$L['I_r_verylow']='Très faible';
$L['I_r_yes']='Oui';
$L['I_running']='Inspection en cours';
$L['I_v_first']='Première valeur';
$L['I_v_last']='Dernière valeur';
$L['I_v_max']='Maximum';
$L['I_v_mean']='Valeur moyenne';
$L['I_v_min']='Minimum';
$L['Use_star_to_delete_all']='Utilisez * pour effacer tout';

// Pricacy
$L['Privacy_visible_0']='Donnée masquée';
$L['Privacy_visible_1']='Donnée visible par les membres';
$L['Privacy_visible_2']='Donnée visible par les visiteurs';

// Restrictions
$L['R_login_register']='Accès réservé aux seuls membres...<br><br>Veuillez vous connecter pour pouvoir continuer. Pour devenir membre, utilisez le menu s\'enregistrer.';
$L['R_member']='Accès réservé aux seuls membres.';
$L['R_staff']='Accès réservé aux seuls modérateurs.';
$L['R_security']='Les paramètres de sécurités ne permettent pas d\'utiliser cette fonction.';
$L['No_attachment_preview']='Pièce jointe non visible en prévisualisation';

// Errors
include 'app_error.php'; // includes roles

// Success

$L['S_registration']='Inscription effectué...';
$L['S_update']='Changement effectué...';
$L['S_delete']='Effacement effectué...';
$L['S_insert']='Création terminée...';
$L['S_save']='Sauvegarde réussie...';
$L['S_message_saved']='Message sauvé...<br>Merci';

// Dates
$L['Items_other_section_are_gayout']='Les '. $L['item+'].' des autres sections sont grisés';
$L['dateMMM']=array(1=>'Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Ao&ucirc;t','Septembre','Octobre','Novembre','Décembre');
$L['dateMM'] =array(1=>'Jan','Fev','Mar','Avr','Mai','Juin','Juil','Aout','Sept','Oct','Nov','Dec');
$L['dateM']  =array(1=>'J','F','M','A','M','J','J','A','S','O','N','D');
$L['dateDDD']=array(1=>'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche');
$L['dateDD'] =array(1=>'Mon','Tue','Wed','Thu','Fri','Sat','Sun');
$L['dateD']  =array(1=>'L','M','M','J','V','S','D');
$L['dateSQL']=array(
  'January'  => 'Janvier',
  'February' => 'Février',
  'March'    => 'Mars',
  'April'    => 'Avril',
  'May'      => 'Mai',
  'June'     => 'Juin',
  'July'     => 'Juillet',
  'August'   => 'Aout',
  'September'=> 'Septembre',
  'October'  => 'Octobre',
  'November' => 'Novembre',
  'December' => 'Décembre',
  'Monday'   => 'Lundi',
  'Tuesday'  => 'Mardi',
  'Wednesday'=> 'Mercredi',
  'Thursday' => 'Jeudi',
  'Friday'   => 'Vendredi',
  'Saturday' => 'Samedi',
  'Sunday'   => 'Dimanche',
  'Today'=>'Aujourd\'hui',
  'Yesterday'=>'Hier',
  'Jan'=>'Jan',
  'Feb'=>'Fév',
  'Mar'=>'Mar',
  'Apr'=>'Avr',
  'May'=>'Mai',
  'Jun'=>'Jun',
  'Jul'=>'Jul',
  'Aug'=>'Aout',
  'Sep'=>'Sept',
  'Oct'=>'Oct',
  'Nov'=>'Nov',
  'Dec'=>'Déc',
  'Mon'=>'Lu',
  'Tue'=>'Ma',
  'Wed'=>'Me',
  'Thu'=>'Je',
  'Fri'=>'Ve',
  'Sat'=>'Sa',
  'Sun'=>'Di');