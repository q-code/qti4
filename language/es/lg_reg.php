<?php

// Is is recommended to always use capital on first letter in the translation
// software change to lower case if necessary.

$L['Agree']='He leído, y acepto respetar estas reglas.';
$L['Proceed']='Puede registrarse';
$L['Rules_not_agreed']='No está de acuerdo con las reglas de participación en el foro. El procedimiento de registro no puede continuar sin su consentimiento.';

// registration
$L['User_del']='Borrar usuario';
$L['Not_your_account']='Este no es tu cuenta.';
$L['Choose_name']='Elige un nombre de usuario';
$L['Choose_password']='Elige una contraseña';
$L['Old_password']='Antigua';
$L['New_password']='Nueva';
$L['Confirm_password']='Confirma';
$L['Password_updated']='Contraseña cambiada';
$L['Password_by_mail']='La contraseña le será enviada a su dirección de e-mail.';
$L['Your_mail']='Tu e-mail';
$L['Parent_mail']='Padre/tutor e-mail';
$L['Reset_pwd']='Reset contraseña';
$L['Reset_pwd_help']='La aplicación enviará por e-mail una nueva contraseña de usuario.';
$L['Type_code']='Escriba el código que ve con.';
$L['Unregister']='Darse de baja';
$L['H_Unregister']='<p>Usted parará el tener de acceso a este uso como miembro. Su perfil será suprimido y su cuenta será no más visible en el memberlist. Sus mensajes seguirán siendo visibles. Si otros usuarios intentan tener acceso a su perfil, consiguieron el perfil del "Visitor".</p><p>Incorpore su contraseáa para confirmar...</p>';

// login and profile

$L['Remember']='Recordarme';
$L['Forgotten_pwd']='Contraseña olvidada';
$L['Change_password']='Cambiar contraseña';
$L['Change_picture']='Cambiar fotografía';
$L['Picture_thumbnail'] = 'La imagen cargada es demasiado grande.<br>Para definir su imagen, dibujar un cuadrado en la imagen grande.';
$L['Delete_picture']='Borrar fotografía';
$L['Change_signature']='Cambiar firma';
$L['Change_role']='Cambiar rol';
$L['W_Somebody_else']='Precaución ... Usted está corrigiendo el perfil del alguien diferente';

$L['H_no_signature']='Su firma se exhibe en la parte inferior de sus mensajes. Si usted no quiere la firma, excepto un texto vacío aquí.';
$L['Is_banned']='Está prohibida';
$L['Is_banned_nomore']='<h2Bienvenido de nuevo...</h2><p>Su cuenta ha sido reabierto.<br>Vuelva a intentar iniciar sesión ahora...</p>';
$L['Since']='desde';
$L['Retry_tomorrow']='Intente otra vez mañana o entre en contacto con al Administrador.';

// Secret question

$L['Secret_question']='Pregunta secreta';
$L['H_Secret_question']='Esta pregunta será hecha si usted olvida su contraseña.';
$L['Update_secret_question']='Su perfil debe ser actualizado... Para mejorar seguridad, le solicitamos definir su propio "Pregunta secreta". Esta pregunta será hecha si usted olvida su contraseña.';
$L['Secret_q']['What is the name of your first pet?']='&iquest;Cuál es el nombre de su primer animal doméstico?';
$L['Secret_q']['What is your favorite character?']='&iquest;Cuál es su carácter preferido?';
$L['Secret_q']['What is your favorite book?']='&iquest;Cuál es su libro preferido?';
$L['Secret_q']['What is your favorite color?']='&iquest;Cuál es su color preferido?';
$L['Secret_q']['What street did you grow up on?']='&iquest;Qué calle usted creció encendido?';

// Error

$L['E_pixels_max']='Pixels maximum';
$L['E_min_4_char']='Caracteres del mínimo 4';
$L['E_pwd_char']='La contraseña contiene el carácter inválido.';
$L['reCAPTCHA_failed']='reCAPTCHA ha fallado. Si no eres un robot, vuelve a cargar la página y vuelve a intentarlo.';

// Help!!!

$L['Reg_help']='<p>Complete por favor esta página para terminar su registro.</p>
<p>Username and password must be at least 4 characters without tags or trailing spaces.</p>
<p>E-mail address will be used to send you a new password if you forgot it. It is visible for registrered members only. To make it invisible, change your privacy settings in your profile.</p>
<p>If you are visually impaired or cannot otherwise read the security code please contact the <a href="mailto:'.$_SESSION[QT]['admin_email'].'">Administrator</a> for help.</p>';
$L['Reg_mail']='Usted recibirá un email pronto incluyendo una contraseña temporal.<br><br>A le invitan que abra una sesión y corrija su perfil para definir su propia contraseña.';
$L['Reg_pass']='Password reset.<br><br>If you have forgotten your password, please enter your username. We will send you a single-use access password key that will allow you to select a new password.';
$L['Reg_pass_reset']='Podemos enviarle una nueva contraseña si usted puede contestar a su pregunta secreta.';