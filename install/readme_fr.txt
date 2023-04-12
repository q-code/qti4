==============================
UPGRADE vers QuickTicket v2.5
==============================

Pour passer de la version 2.x à 2.5, vous pouvez procéder à une installation standard (voir ci-après).

  Remarque #1
  Il est recommandé de faire une sauvegarde du fichier /bin/config.php
  (au cas où vous ne vous souvenez plus des paramètres de connexion vers votre base de donnée).
  
  Remarque #2
  Si votre application autorisait les photos (avatars) et les documents (upload),
  il est recommandé de préserver les répertoires /avatar/ et /upload/
  Les autres fichiers et répertoires peuvent être effacés.

==============================
INSTALLATION de QuickTicket v2.5
==============================

AVANT de commencer l'installation, assurez-vous que vous connaissez :
- Le type de base de donnée que vous utilisez (MySQL, SQLserver, PostgreSQL, SQLite, Firebird, Oracle ou DB2).
- Le nom de l'hote de votre base de donnée (le nom du serveur de base de donnée, souvent "localhost").
- Le nom de votre base de donnée (où QuickTicket peut installer ses tables).
- Le nom d'utilisateur pour cette base de donnée (ayant le droit de créer des tables).
- Le mot de passe de celui-ci.


1. Envoyez l'application sur votre espace web
---------------------------------------------
Vous devez simplement envoyer (ftp) tous les fichiers et repertoires sur votre espace web (par exemple dans un répertoire /quickticket/).
Si vous aviez une version précédente, veillez à ne PAS effacer les répertoires /avatar/ et /upload/.


2. Définir les permissions
--------------------------
Cette étape est très importante ! Sans elle, le programme d'installation ne pourra pas s'exécuter et votre base de donnée ne pourra être configurée.

Changer les permissions sur le fichier /bin/config.php afin qu'il soit inscriptible (chmod 777)
Changer les permissions sur les répertoires /avatar/ et /upload/ (et sous-répertoire) afin qu'ils soient inscriptibles (chmod 777)


3. Lancer l'installation
------------------------
Depuis votre navigateur internet, démarrez le script d'installation : install/install.php
(ex: Tappez l'url http://www.votresiteweb.com/quickticket/install/install.php)
Ce script va vous demander les informations sur votre base de donnée et y créer les tables nécessaires à l'application.


4. Nettoyage
------------
Lorsque les étapes précédentes sont terminées, vous pouvez effacer le répertoire /install/ et changer les permissions de /bin/config.php en lecture seule.


HISTORIQUE DES VERSIONS
=======================
2.5    : Security and profile improvement. Requires php 5.x
2.4    : Includes inspections as a new type of ticket
2.3    : Improve ticket management and calendar
2.1    : Improve tags and statistics
2.0    : Allow using free/proposed categories to classify tickets (also to search and to produce specific statistics)
1.9.0.3: Improve memory usage and allow exporting statistics to csv
1.9    : Allows multiple translation for section, status and index
1.8    : Improvement in the search tools and in the statistics
1.7.0.1: Bug fix pour la sécurité des profils et les statistiques avec une db oracle
1.7    : Version supportant le module Map
1.6    : Version supportant Oracle
