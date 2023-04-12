<?php

$L['Agree']='J\'ai lu ce règlement et j\'accepte de le suivre.';
$L['Proceed']='S\'enregister';
$L['Rules_not_agreed']='Vous n\'avez pas accepté les règles de ce forum.<br>La procédure d\'enregistrement ne peut continuer sans cet accord.';

// registration
$L['User_del']='Effacer l\'utilisateur';
$L['Not_your_account']='Ceci n\'est pas votre compte';
$L['Choose_name']='Choisissez un nom';
$L['Choose_password']='Choisissez un mot de passe';
$L['Old_password']='Ancien';
$L['New_password']='Nouveau';
$L['Confirm_password']='Confirmez';
$L['Password_updated']='Mot de passe modifié';
$L['Password_by_mail']='Un mot de passe temporaire sera envoyé à votre adresse e-mail.';
$L['Your_mail']='Votre e-mail';
$L['Parent_mail']='Parent/tuteur e-mail';
$L['Reset_pwd']='Réinitialiser mot de passe';
$L['Reset_pwd_help']='L\'application va envoyer par e-mail un nouveau mot de passe à l\'utilisateur';
$L['Type_code']='Copiez le code que vous voyez.';
$L['Unregister']='Désinscription';
$L['H_Unregister']='En vous désinscrivant, vous n\'aurez plus accès à cette application en tant que membre.<br>Votre profil sera effacé et ne sera plus visible dans la liste des membres. Vos messages resterons visible.<br>Si des utilisateurs tente d\'accéder à votre profil, ils verront un profil anonyme "Visiteur".<br>Entrez votre mot de passe pour confirmer votre désinscription...';

// login and profile

$L['Remember']='Se souvenir de moi';
$L['Forgotten_pwd']='Mot de passe perdu';

$L['Change_password']='Mot de passe';
$L['Change_picture']='Changer de photo';
$L['Picture_thumbnail'] = 'L\'image est trop grande.<br>Pour définir votre photo, tracez un carré dans la grande image.';
$L['Delete_picture']='Effacer la photo';
$L['Change_signature']='Changer de signature';
$L['Change_role']='Changer de rôle';
$L['W_Somebody_else']='Attention... Vous éditez le profil de quelqu\'un d\'autre';

$L['H_no_signature']='Votre signauture s\'affiche en bas de vos messages. Pour effacer votre signature, sauvez un texte vide ci-après.';
$L['Is_banned']='Est bloqué';
$L['Is_banned_nomore']='<h2>Bienvenue à nouveau...</h2><p>Votre compte est à présent ré-ouvert.<br>Vous pouvez maintenant vous re-connecter...</p>';
$L['Since']='depuis';
$L['Retry_tomorrow']='Ré-essayez demain ou contactez l\'Administrateur du site.';

// Secret question

$L['Secret_question']='Question secrète';
$L['H_Secret_question']='Cette question vous sera posée si vous avez oublié votre mot de passe.';
$L['Update_secret_question']='Votre profil doit être mis à jour...<br><br>Afin d\'améliorer la sécurité, nous vous demandons de définir, votre "Question secrète". Cette question vous sera posée si vous avez oublié votre mot de passe.';
$L['Secret_q']['What is the name of your first pet?']='Quel est le nom de votre premier chien/chat ?';
$L['Secret_q']['What is your favorite character?']='Quel est votre personnage préféré ?';
$L['Secret_q']['What is your favorite book?']='Quel est votre livre préféré ?';
$L['Secret_q']['What is your favorite color?']='Quelle est votre couleur préférée ?';
$L['Secret_q']['What street did you grow up on?']='Dans quelle rue avez-vous grandi ?';

// Error

$L['E_pixels_max']='Pixels maximum';
$L['E_min_4_char']='Minimum 4 caractères';
$L['E_pwd_char']='Le mot de passe contient des caractères non-valides.';
$L['reCAPTCHA_failed']='reCAPTCHA a échoué. Si vous n\êtes pas un robot, rechargez la page et réessayez.';

// Help
$L['Reg_help']='<p>Veuillez remplir ce formulaire afin de compléter votre inscription.</p>
<p>Le nom d\'utilisateur et le mot de passe doivent avoir au moins 4 caractères et être sans espace au début et à la fin.</p>
<p>L\'adresses e-mail sert à vous renvoyer un nouveau mot de passe en cas d\'oubli. Elle n\'est visible  que pour les membres enregistrés. Vous pouvez la rendre invisible dans votre profil.</p>
<p>Si vous êtes malvoyant ou que vous ne voyez pas le code de sécurité, contactez l\'<a href="mailto:'.$_SESSION[QT]['admin_email'].'">Administrateur</a>.</p>';
$L['Reg_mail']='Vous allez recevoir par e-mail un mot de passe temporaire.<br><br>Vous êtes invité à vous connecter et à changer votre mot de passe dans la page Profil.';
$L['Reg_pass']='Réinitialisation du mot de passe.<br><br>Si vous avez oublié votre mot de passe, veuillez entrer votre nom d\utilisateur. Nous vous enverrons un mot de passe temporaire qui vous permettra de vous reconnecter et de définir un nouveau mot de passe.';
$L['Reg_pass_reset']='Nous pouvons vous envoyer un nouveau mot de passe si vous savez répondre à votre question secrète.';