<?php
// Html config
if ( !defined('QT_HTML_CHAR') ) define ('QT_HTML_CHAR', 'utf-8');
if ( !defined('QT_HTML_DIR') ) define ('QT_HTML_DIR', 'ltr');
if ( !defined('QT_HTML_LANG') ) define ('QT_HTML_LANG', 'es');

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
$L['Domain']='Dominio';  $L['Domain+']='Dominios';
$L['Section']='Sección'; $L['Section+']='Secciones';
$L['Item']='Ticket';     $L['Item+']='Tickets';
$L['item']='ticket';     $L['item+']='tickets'; // lowercase because re-used in language definition
$L['Reply']='Respuesta'; $L['Reply+']='Respuestas';
$L['reply']='respuesta'; $L['reply+']='respuestas';
$L['News']='News';       $L['News+']='News'; // In other languages: News=One news, Newss=Several news
$L['news']='news';       $L['news+']='news';
$L['Message']='Mensaje'; $L['Message+']='Mensajes';
$L['Inspection']='Inspección'; $L['Inspection+']='Inspecciones';
$L['Forward']='Enviar'; $L['Forward+']='Envios';

// Controls
$L['Y']='Sí';
$L['N']='No';
$L['And']='Y';
$L['Or']='O';
$L['Ok']='Ok';
$L['Cancel']='Cancelar';
$L['Save']='Guardar';
$L['Exit']='Salida';

// Errors
include 'app_error.php'; // includes roles

// Menu
$L['Administration']='Adminstración';
$L['Help']='Ayuda';
$L['Login']='Acceder';
$L['Logout']='Desconectar';
$L['Memberlist']='Lista de miembros';
$L['Privacy']='Privado';
$L['Profile']='Perfil';
$L['Register']='Registrar';
$L['Search']='Buscar';
$L['User']='Usuario';    $L['User+']='Usuarios';
$L['Status']='Estado';   $L['Status+']='Estados';
$L['Hidden']='Ocultar';  $L['Hidden+']='Ocultos';

// User and role
$L['Actor']='Actor';
$L['Author']='Autor';
$L['Deleted_by']='Borrado por';
$L['Handled_by']='Manejado por';
$L['Modified_by']='Modificado por';
$L['Notified_user']='Notificación usuario';
$L['Notify_also']='Notificar también';
$L['Role']='Role';
$L['Top_participants']='Top participantes';
$L['Username']='Nombre de usuario';

// Common

$L['Action']='Acción';
$L['Add']='Añadir';
$L['Add_user']='Nuevo usuario';
$L['All']='Todo';
$L['Assign_to']='Asignar a';
$L['Attachment']='Adjunto'; $L['Attachment+']='Adjuntos';
$L['Avatar']='Fotografía';
$L['By']='Por';
$L['Birthday']='Fecha de nacimiento';
$L['Birthdays_calendar']='Calendario de cumpleaños';
$L['Change']='Cambio';
$L['Change_name']='Cambie el nombre de usuario';
$L['Change_status']='Estado del cambio...';
$L['Change_type']='Tipo de cambio...';
$L['Changed']='Cambiado';
$L['Charts']='Gráficos';
$L['Close']='Cerrar';
$L['Closed']='Cerrado';
$L['Column']='Columna';
$L['Contact']='Contacta';
$L['Containing']='Contenido';
$L['Continue']='Seguir';
$L['Coord']='Datos';
$L['Created']='Creado';
$L['Csv']='Export'; $L['H_Csv']='Abrase en hoja de balance';
$L['Date']='Fecha';
$L['Date+']='Fechas';
$L['Day']='Día';
$L['Day+']='Días';
$L['Default']='Por defecto'; $L['Use_default']='Configuración predeterminada';
$L['Delete']='Borrar';
$L['Delete_tags']='Borrar (haga clic en una palabra o tipo * para eliminar todos)';
$L['Destination']='Destino';
$L['Details']='Detalles';
$L['Disable']='Desactivar';
$L['Display_at']='Mostrar la fecha';
$L['Drop_attachment']='Adjuntar archivo';
$L['Edit']='Editar';
$L['Email']='E-mail'; $L['No_Email']='No e-mail';
$L['First']='Primero';
$L['Goodbye']='Estás desconectado... Adios';
$L['Goto']='Saltar a';
$L['H_Website']='Url de tu sitio web (con http://)';
$L['H_Wisheddate']='fecha de entrega deseada';

$L['I_wrote']='Escribí';
$L['Information']='Información';
$L['Items_per_month']='Tickets por mes';
$L['Items_per_month_cumul']='Tickets acumulados por mes';
$L['Joined']='Entrar';
$L['Last']='Último';
$L['latlon']='(lat,lon)';
$L['Legend']='Leyenda';
$L['Location']='Situación';
$L['Maximum']='Máximo';
$L['Me']='Mí';
$L['Message_deleted']='Mensaje borrado...';
$L['Minimum']='Mínimo';
$L['Missing']='El campo de destino está vacío';
$L['Modified']='Modificado';
$L['Month']='Mes';
$L['More']='Más';
$L['Move']='Mover';
$L['Name']='Nombre';
$L['None']='Nada';
$L['Notification']='Notificación';
$L['Open']='Abrir';
$L['Options']='Opciones';
$L['Other']='Otro'; $L['Other+']='Otros';
$L['Page']='página';
$L['Page+']='páginas';
$L['Parameters']='Parámetro';
$L['Password']='Contraseña';
$L['Percent']='Por ciento';
$L['Phone']='Llamar';
$L['Picture']='Foto';
$L['Picture+']='Fotos';
$L['Prefix']='Emoticón';
$L['Preview']='Vista previa';
$L['Reason']='Motivo';
$L['Ref']='Ref.';
$L['Remove']='Quitar';
$L['Result']='Resultado';
$L['Result+']='Resultados';
$L['Score']='Cuenta';
$L['Seconds']='Segundos';
$L['Security']='Seguridad';
$L['Send']='Enviar';
$L['Send_on_behalf']='A nombre de';
$L['Settings']='Ajustes';
$L['Show']='Mostrar';
$L['Signature']='Firma';
$L['Statistics']='Estadísticas';
$L['Tag']='Categoría';
$L['Tag+']='Categorías';
$L['Time']='Hora';
$L['Title']='Título';
$L['Total']='Total';
$L['Type']='Tipo';
$L['Update']='Actualizar';
$L['Unchanged']='sin alterar';
$L['Unknown']='Desconocido';
$L['Used_tags']=$L['Tag+'].' usadas';
$L['Views']='Views';
$L['Website']='Website'; $L['No_Website']='No website';
$L['Welcome']='Bienvenido';
$L['Welcome_not']='No soy %s !';
$L['Welcome_to']='Damos la bienvenida a un nuevo miembro, ';
$L['Wisheddate']='Fecha deseada';
$L['Year']='Año'; $L['Years']='Años';
$L['yyyy-mm-dd']='aaaa-mm-dd';

// Section
$L['New_item']='Nuevo '.$L['item'];
$L['Goto_message']='Ver el &Uacute;ltimo mensaje';
$L['Allow_emails']='Permitir el envío de e-mail';
$L['Change_actor']='Cambiar actor';
$L['Close_item']='Cerrar el ticket';
$L['Close_my_item']='Cerrar my ticket';
$L['Closed_item']=$L['Item'].' cerrado';
$L['Closed_item+']=$L['Item+'].' cerradas';
$L['Edit_start']='Comience a corregir';
$L['Edit_stop']='Pare el corregir';
$L['First_message']='Primer mensaje';
$L['Insert_forward_reply']='Añadir información hacia adelante en las respuestas';
$L['Item_closed']='Cerrado ticket';
$L['Item_closed_hide']='Ocultar tickets cerrados';
$L['Item_closed_show']='Mostrar tickets cerrados';
$L['Item_forwarded']='Ticket que han sido enviados a %s.';
$L['Item_handled']='Ticket manejado';
$L['Item_insp_hide']='Ocultar inspecciones';
$L['Item_insp_show']='Mostar inspecciones';
$L['Item_news_hide']='Ocultar noticias';
$L['Item_news_show']='Mostar noticias';
$L['Item_show_all']='Mostrar todas las secciones en una';
$L['Item_show_this']='Mostrar sólo esta sección';
$L['Items_deleted']='Borrar tickets';
$L['Items_handled']='Tickets manejados';
$L['Last_message']='&Uacute;ltimo mensaje';
$L['Move_follow']='Renumerar el destino de la siguiente dirección';
$L['Move_keep']='Mantener la fuente de la referencia';
$L['Move_reset']='Resetear la referencia a cero';
$L['Move_to']='Mover hacia';
$L['My_last_item']='Mi último ticket';
$L['My_preferences']='Mis preferencias';
$L['News_on_top']='Noticias abiertas primero';;
$L['Post_reply']='Responder';
$L['Previous_replies']='Respuestas anteriores';
$L['Quick_reply']='Respuesta rápida';
$L['Quote']='Cuota';
$L['Unreplied']='Sin respuesta';
$L['Unreplied_news']='Noticias sin respuesta';
$L['Unreplied_def']='Temas abiertos sin respuesta desde hace %s días o más';
$L['You_reply']='Sa respuesta';
$L['Showhide_legend']='Mostrar/minimizar información y leyenda';
$L['Only_your_items']='En esta sección, solo se pueden mostrar sus propios '.$L['item+'].'.';

// Stats
$L['General_site']='Sitio general';
$L['Board_start_date']='Tabón de la fecha de inicio';

// Search
$L['Advanced_search']='Búsqueda avanzada';
$L['All_my_items']='Todos mis tickets';
$L['All_news']='Todas noticias';
$L['Any_status']='Cualquier';
$L['Any_time']='Cualquier';
$L['At_least_0']='Con o sin respuesta';
$L['At_least_1']='Al menos una respuesta';
$L['At_least_2']='Al menos 2 restpuestas';
$L['At_least_3']='Al menos 3 restpuestas';
$L['Number_or_keyword']='Tipo de ticket de número referencia o de clave';
$L['H_Reference']='(Tipo sólo en la parte numérica)';
$L['Multiple_input']='Puede indicar varias palabras separadas por un %1$s (ej.: c1%1$sc2 busca los ticket " c1" o " c2").';
$L['In_all_sections']='En todas las secciones';
$L['In_title_only']='En el título sólo';
$L['Keywords']='Clave(s)';
$L['Only_in_section']='Sólo en la sección';
$L['Recent_items']='Tickets recientes';
$L['Search_by_date']='Buscar por fecha';
$L['Search_by_key']='Buscar por clave(s)';
$L['Search_by_ref']='Buscar por n&Uacute;mero de referencia';
$L['Search_by_status']='Buscar por estatus';
$L['Search_by_tag']='Buscar por categoría';
$L['Search_by_words']='Buscar cada palabra separadamente';
$L['Search_criteria']='Criterios de búsqueda';
$L['Search_exact_words']='Buscar exactamente las palabras';
$L['Search_option']='Opciones de la búsqueda';
$L['Show_only_tag']='Categorías en esta lista <small>(haga clic para filtrar)</small>';
$L['Tag_only']='solamente los categoría'; // must be in lowercase
$L['This_month']='Este mes';
$L['This_week']='Esta semana';
$L['This_year']='Este año';
$L['Too_many_keys']='Demasiadas claves';
$L['With_tag']= 'Categoría';
$L['Between_date']='Entre fecha';

// Search result
$L['Search_result']='Búsqueda';
$L['Search_results']=$L['Item+'];
$L['Search_results_actor']=$L['Item+'].' manejado por %s';
$L['Search_results_keyword']=$L['Message+'].' que contengan %s';
$L['Search_results_last']=$L['item+'].' el último semana';
$L['Search_results_news']=$L['News'];
$L['Search_results_insp']=$L['Inspection+'];
$L['Search_results_ref']=$L['item+'].' con claves %2$s';
$L['Search_results_tags']=$L['item+'].' con categoría %s';
$L['Search_results_user']=$L['Item+'].' emitidos por los %s';
$L['Search_results_user_m']=$L['Message+'].' emitidos por los %s';
$L['Search_results_date']=$L['item+'].' entre %1$s y %2$s';
$L['Username_starting']='Nombre de usuario que comienza con';
$L['other_char']='número o símbolo';

// Inspection
$L['I_aggregation']='Método de la agregación';
$L['I_closed']='Inspección cerrada';
$L['I_level']='Nivel de respuesta';
$L['I_r_bad']='Malo';
$L['I_r_good']='Bueno';
$L['I_r_high']='Arriba';
$L['I_r_low']='Bajo';
$L['I_r_medium']='Medio';
$L['I_r_no']='No';
$L['I_r_veryhigh']='Muy arriba';
$L['I_r_verylow']='Muy bajo';
$L['I_r_yes']='Sí';
$L['I_running']='Inspección funcionando';
$L['I_v_first']='Primer valor';
$L['I_v_last']='Valor pasado';
$L['I_v_max']='Máximo';
$L['I_v_mean']='Valor medio';
$L['I_v_min']='Mínimo';
$L['Use_star_to_delete_all']='Tipo * para eliminar todos';

// Privacy
$L['Privacy_visible_0']='Información no es visible';
$L['Privacy_visible_1']='Información es visible para los miembros';
$L['Privacy_visible_2']='Información es visible para los visitantes';

// Restrictions
$L['R_login_register']='El acceso está restringido solo a las/los miembros.<br><br>Inicie sesión o continúe con el registro para convertirse en miembro.';
$L['R_member']='El acceso está restringido solo a las/los miembros.';
$L['R_staff']='El acceso está restringido solo a moderadores.';
$L['R_security']='La configuración de seguridad no permite el uso de esta función.';
$L['No_attachment_preview']='Archivo adjunto no visible en la vista previa';

// Success
$L['S_registration']='Registro correcto...';
$L['S_update']='Modificación correcta...';
$L['S_delete']='Borrado correcto...';
$L['S_insert']='Creación correcta...';
$L['S_save']='Guardado correctamente...';
$L['S_message_saved']='Mensaje guardado...<br>Muchas gracias';

// Dates
$L['Items_other_section_are_gayout']=$L['Item+'].' de otra sección están atenuados';
$L['dateMMM']=array(1=>'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
$L['dateMM'] =array(1=>'Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic');
$L['dateM']  =array(1=>'E','F','M','A','M','J','J','A','S','O','N','D');
$L['dateDDD']=array(1=>'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo');
$L['dateDD'] =array(1=>'Lun','Mar','Mie','Jue','Vie','Sab','Dom');
$L['dateD']  =array(1=>'L','M','X','J','V','S','D');
$L['dateSQL']=array(
  'January'  => 'Enero',
  'February' => 'Febrero',
  'March'    => 'Marzo',
  'April'    => 'Abril',
  'May'      => 'Mayo',
  'June'     => 'Junio',
  'July'     => 'Julio',
  'August'   => 'Agosto',
  'September'=> 'Septiembre',
  'October'  => 'Octubre',
  'November' => 'Noviembre',
  'December' => 'Diciembre',
  'Monday'   => 'Lunes',
  'Tuesday'  => 'Martes',
  'Wednesday'=> 'Miércoles',
  'Thursday' => 'Jueves',
  'Friday'   => 'Viernes',
  'Saturday' => 'Sábado',
  'Sunday'   => 'Domingo',
  'Today'=>'Hoy',
  'Yesterday'=> 'Ayer',
  'Jan'=>'Ene',
  'Feb'=>'Feb',
  'Mar'=>'Mar',
  'Apr'=>'Abr',
  'May'=>'May',
  'Jun'=>'Jun',
  'Jul'=>'Jul',
  'Aug'=>'Ago',
  'Sep'=>'Sep',
  'Oct'=>'Oct',
  'Nov'=>'Nov',
  'Dec'=>'Dic',
  'Mon'=>'Lun',
  'Tue'=>'Mar',
  'Wed'=>'Mie',
  'Thu'=>'Jue',
  'Fri'=>'Vie',
  'Sat'=>'Sab',
  'Sun'=>'Dom');