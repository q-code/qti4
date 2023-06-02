<?php
// common
$L['Content']='Content';
$L['Display']='Display';
$L['Down']='Down';
$L['Download']='Download';
$L['File']='File';
$L['Format']='Format';
$L['Info']='Info';
$L['In_section']='In section';
$L['Properties']='Properties';
$L['Prune']='Prune';
$L['Seconds']='Seconds';
$L['Translations']='Translations';
$L['Up']='Up';
$L['Upload']='Upload';
$L['Version']='Version';

// Admin Menu
$L['Board_info']='Info';
$L['Board_status']='Board status';
$L['Board_stats']='Board statistics';
$L['Board_security']='Security';
$L['Board_general']='Site & contact';
$L['Board_region']='Regional';
$L['Board_layout']='Layout & skin';
$L['Board_content']='Content management';
$L['Move_items']='Move tickets';
$L['Board_modules']='Modules management';
$L['Authority']='Authority';
$L['Internal']='Internal';
$L['Online_services']='Online services';
$L['Module']='Module';
$L['Module_status']='Module status';
$L['Module_detected_names']='A scan for possible names returns';
$L['Containing_sections']='Containing the sectons';
$L['Move_sections_to']='Move the sections to';

$L['Domain_add']='New domain';
$L['Domain_del']='Delete domain';
$L['Domain_upd']='Update domain';
$L['Section_add']='New section';
$L['Section_del']='Delete section';
$L['Section_upd']='Update section';
$L['Member_add']='New member';

// Section status

$L['Off_line']='Off-line';
$L['On_line']='On-line';
$L['Db_disk_space']='Database disk space';

// General settings

$L['User_interface']='User interface';
$L['Site_name']='Board name';
$L['H_Site_name']='Name of the site (browser title).';
$L['Site_url']='Board url';
$L['H_Site_url']='With http:// (and without index.php)';
$L['Name_of_index']='Name of the index';
$L['H_Name_of_index']='Name of the section index.';
$L['Show_banner']='Banner style';
$L['Show_banner0']='Hidden';
$L['Show_banner1']='Visible (menu after)';
$L['Show_banner2']='Visible (menu inside)';
$L['H_Show_banner']='Show the logo banner on top of the page.';
$L['Site_contact']='Board (or admin) contact';
$L['Adm_e_mail']='E-mail';
$L['H_Admin_e_mail']='Send during the registration process.';
$L['Adm_name']='Name';
$L['Adm_addr']='Address';
$L['Email_settings']='Email settings';
$L['Use_smtp']='Use external SMTP server';
$L['H_Use_smtp']='If you want to send mail via a named server instead of the local mail function.';
$L['Public_access_level']='Public access level';
$L['Visitors_can']='Unregistered user can';
$L['Pal'][0]='0) View nothing';
$L['Pal'][1]='1) View the section index';
$L['Pal'][2]='2) View the ticket list';
$L['Pal'][3]='3) View tickets and replies';
$L['Pal'][4]='4) View the memberlist';
$L['Pal'][5]='5) Search the messages';
$L['Pal'][6]='6) Post replies';
$L['Pal'][7]='7) Create tickets';
$L['H_Visitors_can']='Default level is 5. (Level 6 and 7 are recommended for Intranet only)';

// Layout and skin

$L['Skin']='Skin';
$L['Board_skin']='Board skin';
$L['H_Board_skin']='Stylesheet to apply.';
$L['Layout']='Layout';
$L['Show_welcome']='Show welcome message';
$L['H_Show_welcome']='Displays the welcome message at the top of the page.';
$L['While_unlogged']='When signed out';
$L['Show_legend']='Show footer info';
$L['H_Show_legend']='Displays the icon legends and general board information at the bottom of the page.';
$L['Show_quick_reply']='Show quick reply form';
$L['H_Show_quick_reply']='Show quick reply form at the end of the message page.';
$L['By_section']='Set by section';
$L['Items_per_section_page']='Show section tickets';
$L['Replies_per_item_page']='Show ticket replies';
$L['H_Items_per_section_page']='Number of tickets listed';
$L['H_Replies_per_item_page']='Number of replies listed';
$L['Your_website']='Link to your website';
$L['Add_home']='Add in the menu bar';
$L['H_Add_home']='Add your website in the menu bar.';
$L['Home_website_name']='Website button name';
$L['H_Home_website_name']='Label of the button (e.g. Home).';
$L['Home_website_url']='Your website url';
$L['Use_|_add_attributes']='Use "|" after the url to add anchor attributes.';
$L['Display_options']='Display options';
$L['Item_firstline']='Show message first line';
$L['H_Item_firstline']='In ticket list, show titles with the message first line';
$L['Show_Back_button']='Show Back button';
$L['H_Show_Back_button']='Displays a back button.';
$L['Show_news_on_top']='Show news on top';
$L['H_Show_news_on_top']='Display news before other '.$L['item+'].' (until news is closed).';
$L['if_not_closed']='not applicable to news with status "closed"';
$L['Show_views_count']='Show views count';
$L['H_Show_views_count']='Displays views statistics in the ticket page.';
$L['Show_calendar']='Allow calendar';
$L['H_Show_calendar']='Allow to view the calendar';
$L['Show_statistics']='Allow statistics';
$L['H_Show_statistics']='Allow to view the statistics';

// SSE

$L['Requery_status']='Requery status';
$L['Origin']='Origin';
$L['Address_http']='should start with %1$s or %2$s';
$L['External_server']='External server';
$L['SSE_1']='To enable SSE set a requery delay value (recommended 10 seconds). Use 0 to disable SSE.';
$L['SSE_2']='Origin is a security control required to reject messages coming from other servers. It\'s possible to enter here several origins (space separated). If the server script (qti_srv_sse.php) is on the same server as the other pages, it must be your board url (http://www.yourdomain.com).<br><br>To identify the correct origin, put temporarily http://x here, then check the javascript consol log on the index page. The origin will be reported after 10 seconds.';
$L['SSE_3']='Number of recent tickets that can be added on top of the section list. When more tickets arrive, the oldest is replaced (recommended 2)';
$L['SSE_4']='This is possible only if memcache and [ext] directory are on an other server.';

// Sections

$L['Description']='Description';
$L['In_domain']='In domain';
$L['Section_status'][0]='Actif';
$L['Section_status'][1]='Frozen';
$L['Section_type'][0]='Visible (public)';
$L['Section_type'][1]='Hidden';
$L['Section_type'][2]='Visible (private)';

$L['Default_items_order']='Default subjects order';
$L['Lastpost_date']='Date of the last post';
$L['Ref_number']='Reference number';

$L['Specific_fields']='Specific fields';
$L['Specific_image']='Specific image';
$L['Show_item_notify']='Other notifed user';
$L['H_Show_item_notify']='Allow an other user to be notified';
$L['Item_title'][0]='No';
$L['Item_title'][1]='Yes (optional)';
$L['Item_title'][2]='Yes (mandatory)';
$L['Show_item_title']='Ticket title';
$L['H_Show_item_title']='When missing, uses ticket text';
$L['Item_notify'][0]='No';
$L['Item_notify'][1]='Yes (optional)';
$L['Item_notify'][2]='Yes (mandatory)';
$L['Item_notify'][3]='Yes (mandatory and user by default)';
$L['Item_no_notify']='As notification is disabled, "'.$L['Show_item_notify'].'" must remain disabled.';
$L['Show_item_wisheddate']='Wished date';
$L['H_Show_item_wisheddate']='Request a wished delivery date';
$L['Item_wisheddate'][0]='No';
$L['Item_wisheddate'][1]='Yes (optional)';
$L['Item_wisheddate'][2]='Yes (mandatory)';

$L['By_user']='By user';
$L['H_Notify']='Allow e-mail notification';
$L['Show_item_id']='Ticket reference';
$L['H_Show_item_id']='uses php <a href="http://www.php.net/manual/en/function.sprintf.php" target="_blank">formats</a> (e.g. T-%03s). Let empty to hide this column.';
$L['Infofield']='Show in last column';
$L['Item_order']='Ticket order';
$L['Item_prefix']='Ticket prefix';
$L['H_Item_prefix']='Image used as ticket prefix';
$L['Item_prefix_demo']='Prefix demo';
$L['Section_name_and_desc']='Section name<br>and<br>description';
$L['Status_name_and_desc']='Status name<br>and<br>description';
$L['Is_locked_by_the_layout_skin']='Is locked by the layout & skin settings';
$L['Reorder_domains']='Reorder domains<br>(drag and drop to reorder)';

// Topics
$L['H_Items_delete']='Tickets, News and replies will be deleted !';
$L['H_Items_prune']='Unreplied tickets older than %s days will be deleted !';
$L['Unreplied']='Unreplied';
  $L['Unreplied_item']='Unreplied ticket';
  $L['Unreplied_items']='Unreplied tickets';
  $L['H_Unreplied']='Unreplied tickets older than';
$L['Items_moved']='Tickets moved';
$L['Delete_closed']='Delete closed tickets only';
$L['Move_closed']='Move closed tickets only';

// Users
$L['Users_FM']='False users';
$L['H_Users_FM']='Users without post';
$L['Users_SM']='Sleeping users';
$L['H_Users_SM']='Users without post since 1 year.';
$L['Users_CH']='Children';
$L['H_Users_CH']='Users under 13 years old';
$L['Users_SC']='Sleeping children';
$L['H_Users_SC']='Parent/tutor agreement not yet received.';
$L['Users_import_csv']='Import users from csv file';
$L['Users_export_csv']='Export to csv file';
$L['Separator']='Separator';
$L['First_line']='First line';
$L['Skip_first_line']='Skip first line';

// Security
$L['Registration']='Registration';
$L['Size_rules']='Size rules';
$L['Security_rules']='Security rules';
$L['Reg_mode']='Registration mode';
$L['Reg_direct']='Online (direct)';
$L['Reg_email']='Online (with e-mail checking)';
$L['Reg_backoffice']='Back-office request';
$L['Reg_security']='Registration security';
$L['H_Reg_security']='Image code requires GD library. reCaptcha services require a Google API key.';
$L['Internal']='Internal';
$L['Online service']='Online service';
$L['Text_code']='Text code';
$L['Image_code']='Image code';
$L['Max_items_per_section']='Maximum tickets';
$L['H_Max_items_per_section']='Automatic section closure when limit is reached.';
$L['Max_replies_per_items']='Maximum replies';
$L['H_Max_replies_per_items']='Automatic ticket closure when limit is reached (not for '.$L['Inspection+'].')';
$L['Message_size']='Message size';
$L['Max_char_per_post']='Maximum char. per post';
$L['H_Max_char_per_post']='Warning message when limit is reached.';
$L['Max_line_per_post']='Maximum lines per post';
$L['H_Max_line_per_post']='Warning message when limit is reached.';
$L['Max_post_per_user']='Maximum posts per user';
$L['Posts_delay']='Delay between posts';
$L['H_Posts_delay']='Protection against hacking (recommended 10).';
$L['H_hacking_day']='Protection against hacking (recommended 100). This is not applicable to staff members.';
$L['Java_mail']='Protect e-mail';
$L['H_java_mail']='Addresses are protected by a javascript (protection against Spam-Crawlers/Bots)';
$L['Allow_picture']='Allow user\'s photo (avatar)';
$L['Allow_bbc']='Allow bbcode';
$L['Jpg_only']='jpg only';
$L['Gif_jpg_png']='gif, jpg, png';
$L['Allow_upload']='Allow document upload';
$L['H_Allow_upload']='Allow document upload and maximum size';
$L['Allow_tags']='Allow '.strtolower($L['Tag+']).' edit';
$L['H_Allow_tags']='The '.strtolower($L['Tag+']).' cannot be changed anymore when the ticket is closed (status "Z"). Allowing document or category edit to Visitor is not recommended (only in case of intranet usage).';
$L['Member_edit_own_items']='Member, for his own tickets';
$L['Member_edit_any_items']='Member, for any tickets';

// Update domain
$L['H_Hidden']='Hidden section is for staff members only';
$L['H_Closed']='Only staff members can post in closed section';
$L['Classification']='Classification';
$L['Definition']='Definition';

// Update status
$L['H_Status_move']='Tickets with this status become';
$L['Status_background']='Background colour';
$L['H_Status_background']='Html colour or # for transparent';
$L['Icon']='Icon';
$L['Show_z']='Show last status';
$L['H_Show_z']='Tickets with status "%s" remain visible in the lists';
$L['H_Status_notify']='Type the user id(s) separated by a comma (type S for all '.L('role_Ms').', A for all '.L('role_As').')';


// Categories
$L['Proposed_tags']='Proposed '.strtolower($L['Tag+']);
$L['Find_item_tag']='Search for '.$L['item+'].' having this '.strtolower($L['Tag']);
$L['Find_used_tags']='Show used '.strtolower($L['Tag+']);
$L['Common_all_sections']='Common to all sections';

// Errors
$L['E_missing_http']='The url must start with http:// or https://';
$L['E_no_tag']='No '.strtolower($L['Tag']);
$L['E_no_translation']='If translation does not exist, the application shows: ';
$L['E_listsize']='The number of items is not the same here.';
$L['E_pixels_max']='Pixels maximum';