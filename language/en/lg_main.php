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
// Use the top level vocabulary to give the most appropriate name for the topics (object items) managed by this application.
// e.g. Ticket, Incident, Subject, Thread, Request...
$L['Domain']='Domain';   $L['Domain+']='Domains';
$L['Section']='Section'; $L['Section+']='Sections';
$L['Item']='Ticket';     $L['Item+']='Tickets';
$L['item']='ticket';     $L['item+']='tickets'; // lowercase because re-used in language definition
$L['Reply']='Reply';     $L['Reply+']='Replies';
$L['reply']='reply';     $L['reply+']='replies';
$L['News']='News';       $L['News+']='News'; // In other languages: News=One news, News+=Several news
$L['news']='news';       $L['news+']='news';
$L['Message']='Message'; $L['Message+']='Messages';
$L['Inspection']='Inspection'; $L['Inspection+']='Inspections';
$L['Forward']='Forward'; $L['Forward+']='Forwards';

// Controls
$L['Y']='Yes';
$L['N']='No';
$L['And']='And';
$L['Or']='Or';
$L['Ok']='Ok';
$L['Save']='Save';
$L['Cancel']='Cancel';
$L['Exit']='Exit';
$L['Reset']='Reset';

// Errors
include 'app_error.php'; // includes roles

// Menu
$L['Administration']='Administration';
$L['Help']='Help';
$L['About']='About'; $L['Legal']='Legal notices';
$L['Login']='Sign in';
$L['Logout']='Sign out';
$L['Memberlist']='Memberlist';
$L['Profile']='Profile';
$L['Register']='Register';
$L['Search']='Search';
$L['User']='User'; $L['User+']='Users';
$L['Status']='Status'; $L['Status+']='Statuses';
$L['Hidden']='Hidden'; $L['Hidden+']='Hidden';

// User and role
$L['Actor']='Actor';
$L['Author']='Author';
$L['Deleted_by']='Deleted by';
$L['Handled_by']='Handled by';
$L['Modified_by']='Modified by';
$L['Notified_user']='Notified user';
$L['Notify_also']='Notify also';
$L['Role']='Role';
$L['Top_participants']='Top participants';
$L['Username']='Username';

// Common
$L['Action']='Action';
$L['Add']='Add';
$L['Add_user']='New user';
$L['All']='All';
$L['Assign_to']='Assign to';
$L['Attachment']='Attachment'; $L['Attachment+']='Attachments';
$L['Avatar']='Photo';
$L['By']='By';
$L['Birthday']='Date of birth';
$L['Birthdays_calendar']='Birthdays calendar';
$L['Change']='Change';
$L['Change_name']='Change username';
$L['Change_status']='Change status...';
$L['Change_type']='Change type...';
$L['Changed']='Changed';
$L['Charts']='Charts';
$L['Close']='Close';
$L['Closed']='Closed';
$L['Column']='Column';
$L['Commands']='Commands';
$L['Contact']='Contact';
$L['Containing']='Containing';
$L['Continue']='Continue';
$L['Coord']='Coordinates';
$L['Coord_latlon']='(lat,lon)';
$L['Created']='Created';
$L['Csv']='Export'; $L['H_Csv']='Download to spreadsheet';
$L['Date']='Date';
$L['Date+']='Dates';
$L['Day']='Day';
$L['Day+']='Days';
$L['Default']='Default'; $L['Use_default']='Use default';
$L['Delete']='Delete';
$L['Delete_tags']='Delete (click a word or type * to delete all)';
$L['Destination']='Destination';
$L['Details']='Details';
$L['Disable']='Disable';
$L['Display_at']='Display at date';
$L['Drop_attachment']='Drop attachment';
$L['Edit']='Edit';
$L['Email']='E-mail'; $L['No_Email']='No e-mail';
$L['First']='First';
$L['Goodbye']='You are disconnected... Goodbye';
$L['Goto']='Jump to';
$L['H_Website']='Url of your website (with http://)';
$L['H_Wisheddate']='desired delivery date';
$L['Help']='Help';
$L['I_wrote']='I wrote';
$L['Information']='Information';
$L['Items_per_month']='Tickets per month';
$L['Items_per_month_cumul']='Cumulative tickets per month';
$L['Joined']='Joined';
$L['Last']='Last';
$L['latlon']='(lat,lon)';
$L['Legend']='Legend';
$L['Location']='Location';
$L['Maximum']='Maximum';
$L['Me']='Me';
$L['Message_deleted']='Message deleted...';
$L['Minimum']='Minimum';
$L['Missing']='Missing information';
$L['Modified']='Modified';
$L['Month']='Month';
$L['More']='More';
$L['Move']='Move';
$L['Name']='Name';
$L['None']='None';
$L['Notification']='Notification';
$L['Opened']='Opened';
$L['Options']='Options';
$L['or']='or'; // lowercase
$L['Other']='Other'; $L['Other+']='Others';
$L['Page']='Page';   $L['Page+']='Pages';
$L['Parameters']='Parameters';
$L['Password']='Password';
$L['Percent']='Percent';
$L['Phone']='Phone';
$L['Picture']='Picture';
$L['Picture+']='Pictures';
$L['Prefix']='Prefix';
$L['Preview']='Preview';
$L['Privacy']='Privacy';
$L['Reason']='Reason';
$L['Ref']='Ref.';
$L['Remove']='Remove';
$L['Result']='Result';
$L['Result+']='Results';
$L['Save']='Save';
$L['Score']='Score';
$L['Seconds']='Seconds';
$L['Security']='Security';
$L['Send']='Send';
$L['Send_on_behalf']='On behalf of';
$L['Settings']='Settings';
$L['Show']='Show';
$L['Signature']='Signature';
$L['Statistics']='Statistics';
$L['Tag']='Category';
$L['Tag+']='Categories';
$L['Time']='Time';
$L['Title']='Title';
$L['Total']='Total';
$L['Type']='Type';
$L['Unknown']='Unknown';
$L['Unchanged']='Unchanged';
$L['Update']='Update';
$L['Used_tags']='Used '.strtolower($L['Tag+']);
$L['Views']='Views';
$L['Website']='Website'; $L['No_Website']='No website';
$L['Welcome']='Welcome';
$L['Welcome_not']='I\'m not %s !';
$L['Welcome_to']='We welcome a new member, ';
$L['Wisheddate']='Wished date';
$L['Year']='Year'; $L['Years']='Years';
$L['yyyy-mm-dd']='yyyy-mm-dd';

// Section
$L['New_item']='Post new '.$L['item'];
$L['Goto_message']='View last message';
$L['Allow_emails']='Allow sending notification emails';
$L['Change_actor']='Change actor';
$L['Close_item']='Close the ticket';
$L['Close_my_item']='I close my ticket';
$L['Closed_item']='Closed '.$L['item'];
$L['Closed_item+']='Closed '.$L['item+'];
$L['Edit_start']='Start editing';
$L['Edit_stop']='Stop editing';
$L['First_message']='First message';
$L['Insert_forward_reply']='Add forward info in replies';
$L['Item_closed']='Ticket closed';
$L['Item_closed_hide']='Hide closed tickets';
$L['Item_closed_show']='Show closed tickets';
$L['Item_forwarded']='Ticket has been forwarded to %s.';
$L['Item_handled']='Ticket handled';
$L['Item_insp_hide']='Hide inspections';
$L['Item_insp_show']='Show inspections';
$L['Item_news_hide']='Hide news';
$L['Item_news_show']='Show news';
$L['Item_show_all']='Show all sections at once';
$L['Item_show_this']='Show this section only';
$L['Items_deleted']='Deleted tickets';
$L['Items_handled']='Tickets handled';
$L['Last_message']='Last message';
$L['Move_follow']='Renumber following the destination section';
$L['Move_keep']='Use same number';
$L['Move_reset']='Reset reference to zero';
$L['Move_to']='Move to';
$L['My_last_item']='My last ticket';
$L['My_preferences']='My preferences';
$L['News_on_top']='Active news on top';
$L['Post_reply']='Reply';
$L['Previous_replies']='Previous replies';
$L['Quick_reply']='Quick reply';
$L['Quote']='Quote';
$L['Unreplied']='Unreplied'; $L['Unreplied_news']='Unreplied news';
$L['Unreplied_def']='Opened subjects without reply since %s days or more';
$L['You_reply']='I replied';
$L['Showhide_legend']='Show/minimize info and legend';
$L['Only_your_items']='In this section, only your '.$L['item+'].' can be displayed.';

// Stats
$L['General_site']='General site';
$L['Board_start_date']='Board start date';

// Search
$L['Advanced_search']='Advanced search';
$L['All_my_items']='All my tickets';
$L['All_news']='All news';
$L['Any_status']='Any status';
$L['Any_time']='Any time';
$L['At_least_0']='With or without reply';
$L['At_least_1']='At least 1 reply';
$L['At_least_2']='At least 2 replies';
$L['At_least_3']='At least 3 replies';
$L['Number_or_keyword']='Ticket number or keyword';
$L['H_Reference']='(type the numeric part only)';
$L['Multiple_input']='You can enter several words separated by %1$s (ex.: t1%1$st2 means tickets containing "t1" or "t2").';
$L['In_all_sections']='In all sections';
$L['In_title_only']='In title only';
$L['Keywords']='Keyword(s)';
$L['Only_in_section']='Only in section';
$L['Recent_items']='Recent '.$L['item+'];
$L['Search_by_date']='Search by date';
$L['Search_by_key']='Search by keyword(s)';
$L['Search_by_ref']='Search reference number';
$L['Search_by_status']='Search by status';
$L['Search_by_tag']='Search by category';
$L['Search_by_words']='Search each word separately';
$L['Search_criteria']='Search criteria';
$L['Search_exact_words']='Search exact words';
$L['Search_option']='Search option';
$L['Show_only_tag']='Categories in this list <small>(click to filter)</small>';
$L['Tag_only']='only category'; // must be in lowercase
$L['This_month']='This month';
$L['This_week']='This week';
$L['This_year']='This year';
$L['Too_many_keys']='Too many keys';
$L['With_tag']='Category';
$L['Between_date']='Between date';

// Search result
$L['Search_results']=$L['Item+'];
$L['Search_results_tags']=$L['Item+'].' with tag %s';
$L['Search_results_ref']=$L['Item+'].' with ref. %s';
$L['Search_results_keyword']=$L['Message+'].' containing %s';
$L['Search_results_actor']=$L['Item+'].' handled by %s';
$L['Search_results_user']=$L['Item+'].' issued by %s';
$L['Search_results_user_m']=$L['Message+'].' issued by %s';
$L['Search_results_last']='Recent '.$L['item+'].' (last week)';
$L['Search_results_news']=$L['News'];
$L['Search_results_insp']=$L['Inspection+'];
$L['Search_results_date']=$L['item+'].' between %2$s and %3$s';
$L['Username_starting']='Username starting with';
$L['other_char']='other char';

// Inspection
$L['I_aggregation']='Aggregation method';
$L['I_closed']='Inspection closed';
$L['I_level']='Response level';
$L['I_r_bad']='Bad';
$L['I_r_good']='Good';
$L['I_r_high']='High';
$L['I_r_low']='Low';
$L['I_r_medium']='Medium';
$L['I_r_no']='No';
$L['I_r_veryhigh']='Very high';
$L['I_r_verylow']='Very low';
$L['I_r_yes']='Yes';
$L['I_running']='Inspection running';
$L['I_v_first']='First value';
$L['I_v_last']='Last value';
$L['I_v_max']='Maximum';
$L['I_v_mean']='Mean value';
$L['I_v_min']='Minimum';
$L['Use_star_to_delete_all']='Use * to remove all';

// Privacy
$L['Privacy_visible_0']='Hidden data';
$L['Privacy_visible_1']='Data visible to members';
$L['Privacy_visible_2']='Data visible to visitors';

// Restrictions
$L['R_login_register']='Access is restricted to members only.<br><br>Please log in, or proceed to registration to become member.';
$L['R_member']='Access is restricted to members only.';
$L['R_staff']='Access is restricted to moderators only.';
$L['R_security']='Security settings does not allow using this function.';
$L['No_attachment_preview']='Attachment not available in preview';

// Success
$L['S_registration']='Registration successful...';
$L['S_update']='Update successful...';
$L['S_delete']='Delete completed...';
$L['S_insert']='Creation successful...';
$L['S_save']='Successfully saved...';
$L['S_message_saved']='Message saved...<br>Thank you';

// Dates
$L['Items_other_section_are_gayout']=$L['Item+'].' from an other section are grayed out';
$L['dateMMM']=array(1=>'January','February','March','April','May','June','July','August','September','October','November','December');
$L['dateMM'] =array(1=>'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
$L['dateM']  =array(1=>'J','F','M','A','M','J','J','A','S','O','N','D');
$L['dateDDD']=array(1=>'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
$L['dateDD'] =array(1=>'Mon','Tue','Wed','Thu','Fri','Sat','Sun');
$L['dateD']  =array(1=>'M','T','W','T','F','S','S');
$L['dateSQL']=array(
  'January'  => 'January',
  'February' => 'February',
  'March'    => 'March',
  'April'    => 'April',
  'May'      => 'May',
  'June'     => 'June',
  'July'     => 'July',
  'August'   => 'August',
  'September'=> 'September',
  'October'  => 'October',
  'November' => 'November',
  'December' => 'December',
  'Monday'   => 'Monday',
  'Tuesday'  => 'Tuesday',
  'Wednesday'=> 'Wednesday',
  'Thursday' => 'Thursday',
  'Friday'   => 'Friday',
  'Saturday' => 'Saturday',
  'Sunday'   => 'Sunday',
  'Today'=>'Today',
  'Yesterday'=> 'Yesterday',
  'Jan'=>'Jan',
  'Feb'=>'Feb',
  'Mar'=>'Mar',
  'Apr'=>'Apr',
  'May'=>'May',
  'Jun'=>'Jun',
  'Jul'=>'Jul',
  'Aug'=>'Aug',
  'Sep'=>'Sep',
  'Oct'=>'Oct',
  'Nov'=>'Nov',
  'Dec'=>'Dec',
  'Mon'=>'Mon',
  'Tue'=>'Tue',
  'Wed'=>'Wed',
  'Thu'=>'Thu',
  'Fri'=>'Fri',
  'Sat'=>'Sat',
  'Sun'=>'Sun');