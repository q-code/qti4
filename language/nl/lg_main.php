<?php
// Html config
if ( !defined('QT_HTML_CHAR') ) define ('QT_HTML_CHAR', 'utf-8');
if ( !defined('QT_HTML_DIR') ) define ('QT_HTML_DIR', 'ltr');
if ( !defined('QT_HTML_LANG') ) define ('QT_HTML_LANG', 'en');

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
// Use the top level vocabulary to give the most appropriate name for the topics (object items)
// managed by this application: Ticket, Incident, Subject, Thread, Request...
$L['Domain']='Domein';   $L['Domain+']='Domeinen';
$L['Section']='Sectie';  $L['Section+']='Secties';
$L['Item']='Ticket';     $L['Item+']='Tickets';
$L['item']='ticket';     $L['item+']='tickets'; // lowercase, because frequently used
$L['Reply']='Antwoord';  $L['Reply+']='Antwoorden';
$L['reply']='antwoord';  $L['reply+']='antwoorden';
$L['News']='Nieuws';     $L['News+']='Nieuws'; //News=One news, Newss=Several news
$L['news']='nieuws';     $L['news+']='nieuws';
$L['Message']='Bericht'; $L['Message+']='Berichten';
$L['Inspection']='Inspection'; $L['Inspection+']='Inspections';
$L['Forward']='Verstuurd bericht'; $L['Forward+']='Verstuurd berichten';

// Controls
$L['Y']='Ja';
$L['N']='Nee';
$L['And']='En';
$L['Or']='Of';
$L['Ok']='Ok';
$L['Save']='Opslaan';
$L['Cancel']='Annuleren';
$L['Exit']='Uitrit';

// Errors
include 'app_error.php'; // includes roles

// Menu
$L['Administration']='Administratie';
$L['Help']='Hulp';
$L['About']='Over';
$L['Legal']='Privacybeleid';
$L['Login']='Inloggen';
$L['Logout']='Uitloggen';
$L['Memberlist']='Gebruikerslijst';
$L['Profile']='Profiel';
$L['Register']='Registreer';
$L['Search']='Zoeken';

// TOP LEVEL VOCABULARY
// Use the top level vocabulary to give the most appropriate name for the topics (object items) managed by this application.
// e.g. Ticket, Incident, Subject, Thread, Request...

$L['User']='Gebruiker';  $L['User+']='Gebruikers';
$L['Status']='Statuut';  $L['Status+']='Statuten';


// User and role
$L['Actor']='Acteur';
$L['Author']='Auteur';
$L['Deleted_by']='Geschrapt door';
$L['Handled_by']='Behandeld door';
$L['Modified_by']='Bewerkt door';
$L['Notified_user']='Gebruiker op de hoogte';
$L['Notify_also']='Op de hoogte brengen';
$L['Role']='Rang';
$L['Top_participants']='Top deelnemers';
$L['Username']='Gebruikersnaam';

// Common
$L['Action']='Actie';
$L['Add']='Toevoegen';
$L['Add_user']='Nieuw gebruiker';
$L['All']='Alles';
$L['Attachment']='Attachment'; $L['Attachment+']='Attachments';
$L['Avatar']='Foto';
$L['By']='Per';
$L['Change']='Bewerk';
$L['Change_name']='Gebruikersnaam veranderen';
$L['Change_status']='Statuut veranderen...';
$L['Change_type']='Type veranderen...';
$L['Changed']='Bewerkt';
$L['Charts']='Grafieken';
$L['Close']='Afsluiten';
$L['Closed']='Gesloten';
$L['Column']='Kolom';
$L['Contact']='Contact';
$L['Containing']='Bevat';
$L['Continue']='Voortduren';
$L['Coord']='Co&ouml;rdinaten';
$L['Created']='Gemaakt';
$L['Csv']='Export'; $L['H_Csv']='Tonen in spreadsheet';
$L['Date']='Datum';
$L['Date+']='Datum';
$L['Day']='Dag';
$L['Day+']='Dagen';
$L['Default']='Standaard';  $L['Use_default']='Standaardinstelling';
$L['Delete']='Uitwissen';
$L['Delete_tags']='Verwijderen (click een woord of type * om alles te verwijderen)';
$L['Destination']='Bestemming';
$L['Details']='Details';
$L['Disable']='Uit te schakelen';
$L['Display_at']='Tonen op datum van';
$L['Drop_attachment']='Attachment wegnemen';
$L['Edit']='Bewerken';
$L['Email']='E-mail'; $L['No_Email']='Geen e-mail';
$L['Exit']='Uitrit';
$L['First']='Eerste';
$L['Goodbye']='U bent uitgelogd... Tot ziens';
$L['Goto']='Ga naar';
$L['H_Website']='Uw website url (met http://)';
$L['H_Wisheddate']='Gewenste leveringsdatum';
$L['Help']='Hulp';
$L['Hidden']='Verborgen';
$L['I_wrote']='Ik schreef';
$L['Information']='Informatie';
$L['Items_per_month']='Tickets per maand';
$L['Items_per_month_cumul']='Cumul tickets per maand';
$L['Joined']='Geregistreerd op';
$L['Last']='Laatste';
$L['latlon']='(lat,lon)';
$L['Legend']='Legend';
$L['Location']='Woonplaats';
$L['Maximum']='Maximum';
$L['Me']='Mij';
$L['Message_deleted']='Bericht verwijderd...';
$L['Minimum']='Minimum';
$L['Missing']='Verplicht data niet gevonden';
$L['Modified']='Verandered';
$L['Month']='Maand';
$L['More']='Meer';
$L['Move']='Verplaatsen';
$L['Name']='Naam';
$L['None']='Geen';
$L['Notification']='Nota';
$L['Opened']='Geopend';
$L['Options']='Opties';
$L['or']='of'; // lowercase
$L['Other']='Ander'; $L['Other+']='Anderen';
$L['Page']='Pagina';  $L['Page+']='Pagina\'s';
$L['Parameters']='Parameters';
$L['Password']='Wachtwoord';
$L['Percent']='Procent';
$L['Phone']='Telefoon';
$L['Picture']='Beeld';
$L['Prefix']='Voorvoegsel';
$L['Preview']='Voorproef';
$L['Privacy']='Priv&eacute;-leven';
$L['Reason']='Reden';
$L['Ref']='Ref.';
$L['Remove']='Uitwissen';
$L['Result']='Resultaat';
$L['Result+']='Resultaten';
$L['Save']='Saven';
$L['Score']='Score';
$L['Seconds']='Seconden';
$L['Security']='Veiligheid';
$L['Send']='Zenden';
$L['Send_on_behalf']='Zenden namens';
$L['Settings']='Instellingen';
$L['Show']='Tonen';
$L['Signature']='Onderschrift';
$L['Statistics']='Statistieken';
$L['Tag']='Categorie';
$L['Tag+']='Categorie&euml;n';
$L['Time']='Uren';
$L['Title']='Titel';
$L['Total']='Totaal';
$L['Type']='Type';
$L['Unknown']='Onbekend';
$L['Update']='Bijwerken';
$L['Used_tags']='Gebruikte '.strtolower($L['Tag+']);
$L['Views']='Bekeken';
$L['Website']='Website'; $L['No_Website']='Geen website';
$L['Welcome']='Welkom';
$L['Welcome_not']='Ik ben %s niet !';
$L['Welcome_to']='Welkom voor een nieuwe gebruiker, ';
$L['Wisheddate']='Oplevering';
$L['Year']='Jaar'; $L['Years']='Jaren';
$L['yyyy-mm-dd']='jjjj-mm-dd';

// Section
$L['New_item']='Nieuw '.$L['item'];
$L['Goto_message']='Laatste bericht';
$L['Allow_emails']='Laat het verzenden van e-mails';
$L['Change_actor']='Werknemer';
$L['Close_item']='Sluit dit ticket';
$L['Close_my_item']='Ik sluit mijn ticket';
$L['Edit_start']='Wijziging beginnen';
$L['Edit_stop']='Wijziging stoppen';
$L['First_message']='Eerste bericht';
$L['Insert_forward_reply']='Voeg vooruit info in antwoorden';
$L['Item_closed']='Gesloten ticket';
$L['Item_closed_hide']='Gesloten aanvragen: verborgen';
$L['Item_closed_show']='Gesloten aanvragen: tonen';
$L['Item_forwarded']='Ticket is naar %s gestruurd.';
$L['Item_handled']='Ticket behandeld';
$L['Item_insp_hide']=$L['Inspection+'].': verborgen';
$L['Item_insp_show']=$L['Inspection+'].': tonen';
$L['Item_news_hide']=$L['News'].': verborgen';
$L['Item_news_show']=$L['News'].': tonen';
$L['Item_show_all']='Alle secties verzamelen';
$L['Item_show_this']='Alleen dit sectie tonen';
$L['Items_deleted']='Uitgewist tickets';
$L['Items_handled']='Tickets behandeld';
$L['Last_message']='Laatste bericht';
$L['Move_follow']='Volgt sectienummer';
$L['Move_keep']='Houd origineel nummer';
$L['Move_reset']='Verwijzingen naar 0';
$L['Move_to']='Verplatsen';
$L['My_last_item']='Mijn laatste ticket';
$L['My_preferences']='Mijn voorkeuren';
$L['News_on_top']='actieve nieuws op de top';
$L['Post_reply']='Antwoord';
$L['Previous_replies']='Vorige berichten';
$L['Quick_reply']='Snel antwoord';
$L['Quote']='Quote';
$L['Show_news_on_top']='Nieuws op de top tonen';
$L['Unreplied']='Verloren';
$L['Unreplied_news']='Verloren nieuws';
$L['Unreplied_def']='Berichten zijn open en onbeantwoord voor meer dan %s dagen';
$L['You_reply']='Ik antwoord';
$L['Showhide_legend']='Tonen/verkleinen info en legenda';
$L['Only_your_items']='In dit sectie kunnen, alleen uw  '.$L['item+'].'  worden weergegeven';

// Stats
$L['General_site']='Algemene site';
$L['Board_start_date']='Applicatie begin datum';

// Search
$L['Advanced_search']='Geavanceerd onderzoek';
$L['All_my_items']='Alle mijn '.$L['item+'];
$L['All_news']='Alle mededelingen';
$L['Any_status']='Alle statuut';
$L['Any_time']='Alle tijd';
$L['At_least_0']='Met of zonder antwoord';
$L['At_least_1']='Minstens een antwoord';
$L['At_least_2']='Minstens 2 antwoorden';
$L['At_least_3']='Minstens 2 antwoorden';
$L['Number_or_keyword']='tiketnummer of woord';
$L['H_Reference']='(typ het numerieke deel)';
$L['H_Tag_input']='Met %1$s kunt u verschillende worden invoeren (b.v.: t1%1$st2 betekend tickets met "t1" of "t2").';
$L['In_all_sections']='In alle secties';
$L['In_title_only']='In titel alleen';
$L['Keywords']='Sleutelwoord(en)';
$L['Only_in_section']='Alleen in sectie';
$L['Recent_items']='Recente '.$L['item+'];
$L['Search_by_date']='Zoeken met datum';
$L['Search_by_key']='Zoeken met sleutelwoord(en)';
$L['Search_by_ref']='Zoeken met nummer';
$L['Search_by_status']='Zoeken met statuut';
$L['Search_by_tag']='Zoeken per categorie';
$L['Search_by_words']='Elk woord afzonderlijk zoeken';
$L['Search_criteria']='Onderzoekscriterium';
$L['Search_exact_words']='Worden samen zoeken';
$L['Search_option']='Onderzoeksoptie';
$L['Show_only_tag']='Tonen alleen deze met categorie';
$L['Tag_only']='alleen met categorie'; // must be in lowercase
$L['This_month']='Deze maand';
$L['This_week']='Deze week';
$L['This_year']='Dit jaar';
$L['Too_many_keys']='Te veel sleutelwoorden';
$L['With_tag']= 'Met categorie';
$L['Between_date']='Tussen datum';

//Search restore_include_path
$L['Search_result']='Zoekresultaten';
$L['Search_results']=$L['Item+'];
$L['Search_results_actor']=$L['item+'].' behandeld door %s';
$L['Search_results_keyword']=$L['item+'].' met woord %s';
$L['Search_results_last']='%1s '.$L['item+'].' laaste week';
$L['Search_results_news']=$L['News'];
$L['Search_results_insp']=$L['Inspection+'];
$L['Search_results_ref']=$L['item+'].' met ref. %s';
$L['Search_results_tags']=$L['item+'].' met categorie %s';
$L['Search_results_user']=$L['item+'].' door %s';
$L['Search_results_date']=$L['item+'].' tussen %1$s en %2$s';
$L['Username_starting']='Gebruikersnaam begin met';
$L['other_char']='nummer of symbool';

// Inspection
$L['I_aggregation']='Samenvoeging methode';
$L['I_closed']='Gesloten inspectie';
$L['I_level']='Antworden waarden';
$L['I_r_bad']='Slecht';
$L['I_r_good']='Goed';
$L['I_r_high']='Hoog';
$L['I_r_low']='Laag';
$L['I_r_medium']='Middel';
$L['I_r_no']='Nee';
$L['I_r_veryhigh']='Zeer hoog';
$L['I_r_verylow']='Zeer laag';
$L['I_r_yes']='Ja';
$L['I_running']='Lopende inspectie';
$L['I_v_first']='Eerste waarde';
$L['I_v_last']='Laatste waarde';
$L['I_v_max']='Maximum';
$L['I_v_mean']='Gemiddelde waarde';
$L['I_v_min']='Minimum';
$L['Use_star_to_delete_all']='Typt * om alles te verwijderen';

// Privacy
$L['Privacy_visible_0']='Data onzichtbaar';
$L['Privacy_visible_1']='Data zichtbaar voor leden';
$L['Privacy_visible_2']='Data zichtbaar voor bezoekers';

// Restrictions
$L['R_login_register']='De toegang is beperkt tot slechts leden.<br><br>Gelieve in te loggen, of ga naar Registreerd om lid te worden.';
$L['R_member']='De toegang is beperkt tot slechts leden.';
$L['R_staff']='De toegang is beperkt tot slechts moderatoren.';
$L['R_security']='De veiligheid instellingen laten deze functie geen toe.';
$L['No_attachment_preview']='Bijlage niet zichtbaar in preview';

// Success
$L['S_registration']='Voltooide registratie...';
$L['S_update']='Voltooide update...';
$L['S_delete']='Schrap voltooid...';
$L['S_insert']='Succesvolle verwezenlijking...';
$L['S_save']='Sparen voltooid...';
$L['S_message_saved']='Het bericht wordt bewaard...<br>Dank u';

// Dates
$L['dateMMM']=array(1=>'Januari','Februari','Maart','April','Mei','Juni','Juli','Augustus','Septembre','Oktober','November','December');
$L['dateMM'] =array(1=>'Jan','Feb','Mrt','Apr','Mei','Jun','Jul','Aug','Sep','Okt','Nov','Dec');
$L['dateM']  =array(1=>'J','F','M','A','M','J','J','A','S','O','N','D');
$L['dateDDD']=array(1=>'Maandag','Dinsdag','Woensdag','Donderdag','Vrijdag','Zaterdag','Zondag');
$L['dateDD'] =array(1=>'Ma','Di','Wo','Do','Vr','Za','Zo');
$L['dateD']  =array(1=>'M','D','W','D','V','Z','Z');
$L['dateSQL']=array(
  'January'  => 'januari',
  'February' => 'februari',
  'March'    => 'maart',
  'April'    => 'april',
  'May'      => 'mei',
  'June'     => 'juni',
  'July'     => 'juli',
  'August'   => 'augustus',
  'September'=> 'september',
  'October'  => 'oktober',
  'November' => 'november',
  'December' => 'december',
  'Monday'   => 'maandag',
  'Tuesday'  => 'dinsdag',
  'Wednesday'=> 'woensdag',
  'Thursday' => 'donderdag',
  'Friday'   => 'vrijdag',
  'Saturday' => 'zaterdag',
  'Sunday'   => 'zondag',
  'Today'=>'Vandaag',
  'Yesterday'=>'Gisteren',
  'Jan'=>'jan',
  'Feb'=>'feb',
  'Mar'=>'mrt',
  'Apr'=>'apr',
  'May'=>'mei',
  'Jun'=>'jun',
  'Jul'=>'jul',
  'Aug'=>'aug',
  'Sep'=>'sep',
  'Oct'=>'okt',
  'Nov'=>'nov',
  'Dec'=>'dec',
  'Mon'=>'ma',
  'Tue'=>'di',
  'Wed'=>'wo',
  'Thu'=>'do',
  'Fri'=>'vr',
  'Sat'=>'za',
  'Sun'=>'zo');