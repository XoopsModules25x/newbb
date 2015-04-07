<?php
// $Id: admin.php 62 2012-08-17 10:15:26Z alfred $

if (defined('NEWBB_ADMIN_DEFINED')) return;
else define('NEWBB_ADMIN_DEFINED', true);

//%%%%%%	File Name  index.php   	%%%%%
define("_AM_NEWBB_FORUMCONF", "Forumkonfiguration");
define("_AM_NEWBB_ADDAFORUM", "Forum hinzufügen");
define("_AM_NEWBB_SYNCFORUM", "Synchronisieren");
define("_AM_NEWBB_REORDERFORUM", "Neu ordnen");
define("_AM_NEWBB_FORUM_MANAGER", "Foren");
define("_AM_NEWBB_PRUNE_TITLE", "Aufräumen");
define("_AM_NEWBB_CATADMIN", "Kategorien");
define("_AM_NEWBB_GENERALSET", "Moduleinstellungen");
define("_AM_NEWBB_MODULEADMIN", "Moduladministration:");
define("_AM_NEWBB_HELP", "Hilfe");
define("_AM_NEWBB_ABOUT", "Über");
define("_AM_NEWBB_BOARDSUMMARY", "Forenstatistik");
define("_AM_NEWBB_PENDING_POSTS_FOR_AUTH", "Noch freizugebende Beiträge");
define("_AM_NEWBB_POSTID", "Beitrags-ID");
define("_AM_NEWBB_POSTDATE", "Beitragsdatum");
define("_AM_NEWBB_POSTER", "Autor");
define("_AM_NEWBB_TOPICS", "Themen");
define("_AM_NEWBB_SHORTSUMMARY", "Forenzusammenfassung");
define("_AM_NEWBB_TOTALPOSTS", "Beiträge gesamt");
define("_AM_NEWBB_TOTALTOPICS", "Themen gesamt");
define("_AM_NEWBB_TOTALVIEWS", "Aufrufe gesamt");
define("_AM_NEWBB_BLOCKS", "Blöcke");
define("_AM_NEWBB_SUBJECT", "Betreff");
define("_AM_NEWBB_APPROVE", "Beitrag freigeben");
define("_AM_NEWBB_APPROVETEXT", "Inhalt dieses Beitrags");
define("_AM_NEWBB_POSTAPPROVED", "Beitrag wurde freigegeben");
define("_AM_NEWBB_POSTNOTAPPROVED", "Beitrag wurde NICHT freigegeben");
define("_AM_NEWBB_POSTSAVED", "Beitrag wurde gespeichert");
define("_AM_NEWBB_POSTNOTSAVED", "Beitrag wurde NICHT gespeichert");
define("_AM_NEWBB_TOPICAPPROVED", "Thema wurde freigegeben");
define("_AM_NEWBB_TOPICNOTAPPROVED", "Thema wurde nicht freigegeben");
define("_AM_NEWBB_TOPICID", "Themen-ID");
define("_AM_NEWBB_ORPHAN_TOPICS_FOR_AUTH", "Freigabe von nicht freigegebenen Beiträgen");
define('_AM_NEWBB_DEL_ONE', 'Nur diesen Beitrag löschen');
define('_AM_NEWBB_POSTSDELETED', 'Ausgewählter Beitrag wurde gelöscht.');
define('_AM_NEWBB_NOAPPROVEPOST', 'Zur Zeit gibt es keine Beiträge, die auf Freigabe warten.');
define('_AM_NEWBB_SUBJECTC', 'Betreff:');
define('_AM_NEWBB_MESSAGEICON', 'Beitragssymbol:');
define('_AM_NEWBB_MESSAGEC', 'Beitrag:');
define('_AM_NEWBB_CANCELPOST', 'Beitrag abbrechen');
define('_AM_NEWBB_GOTOMOD', 'Gehe zum Modul');
define('_AM_NEWBB_PREFERENCES', 'Modulvoreinstellungen');
define('_AM_NEWBB_POLLMODULE', 'Umfragemodul');
define('_AM_NEWBB_POLL_OK', 'einsatzbereit');
define('_AM_NEWBB_GDLIB1', 'GD1-Library:');
define('_AM_NEWBB_GDLIB2', 'GD2-Library:');
define('_AM_NEWBB_AUTODETECTED', 'Automatisch ermittelt: ');
define('_AM_NEWBB_AVAILABLE', 'verfügbar');
define('_AM_NEWBB_NOTAVAILABLE', '<span style="color:red">nicht verfügbar</span>');
define('_AM_NEWBB_NOTWRITABLE', '<span style="color:red">nicht beschreibbar</span>');
define('_AM_NEWBB_IMAGEMAGICK', 'ImageMagick');
define('_AM_NEWBB_IMAGEMAGICK_NOTSET', 'nicht bereit');
define('_AM_NEWBB_ATTACHPATH', 'Pfad zum Speicherort der Dateianhänge');
define('_AM_NEWBB_THUMBPATH', 'Pfad für beigefügte Bildvorschauen');
//define('_AM_NEWBB_RSSPATH','Pfad für RSS-Dateien');
define('_AM_NEWBB_REPORT', 'Gemeldete Beiträge');
define('_AM_NEWBB_REPORT_PENDING', 'Meldung in Bearbeitung');
define('_AM_NEWBB_REPORT_PROCESSED', 'Bearbeitete Meldung');
define('_AM_NEWBB_CREATETHEDIR', 'Anlegen');
define('_AM_NEWBB_SETMPERM', 'Berechtigung setzen');
define('_AM_NEWBB_DIRCREATED', 'Das Verzeichnis wurde angelegt');
define('_AM_NEWBB_DIRNOTCREATED', 'Das Verzeichnis konnte nicht angelegt werden');
define('_AM_NEWBB_PERMSET', 'Die Berechtigung wurde gesetzt');
define('_AM_NEWBB_PERMNOTSET', 'Die Berechtigung konnte nicht gesetzt werden');
define('_AM_NEWBB_DIGEST', 'Digest-Benachrichtigungen');
define('_AM_NEWBB_DIGEST_PAST', '<span style="color:red">Sollte vor %d Minuten abgeschickt worden sein.</span>');
define('_AM_NEWBB_DIGEST_NEXT', 'Soll in %d Minuten abgeschickt werden');
define('_AM_NEWBB_DIGEST_ARCHIVE', 'Digest-Archiv');
define('_AM_NEWBB_DIGEST_SENT', 'Digest verarbeitet.');
define('_AM_NEWBB_DIGEST_FAILED', 'Digest nicht verarbeitet.');

// admin_forum_manager.php
define("_AM_NEWBB_NAME", "Name");
define("_AM_NEWBB_CREATEFORUM", "Forum anlegen");
define("_AM_NEWBB_EDIT", "Bearbeiten");
define("_AM_NEWBB_CLEAR", "Leeren");
define("_AM_NEWBB_DELETE", "Löschen");
define("_AM_NEWBB_ADD", "Hinzufügen");
define("_AM_NEWBB_MOVE", "Verschieben");
define("_AM_NEWBB_ORDER", "Sortieren");
define("_AM_NEWBB_TWDAFAP", "Hinweis: Dieser Vorgang wird das Forum und alle enthaltenen Beiträge löschen.<br /><br />WARNUNG: Sicher, dass das Forum gelöscht werden soll?");
define("_AM_NEWBB_FORUMREMOVED", "Forum gelöscht.");
define("_AM_NEWBB_CREATENEWFORUM", "Ein neues Forum erstellen.");
define("_AM_NEWBB_EDITTHISFORUM", "Forum bearbeiten:");
define("_AM_NEWBB_SET_FORUMORDER", "Forumsposition angeben:");
define("_AM_NEWBB_ALLOWPOLLS", "Umfragen zulassen?");
define("_AM_NEWBB_ATTACHMENT_SIZE", "Max. Größe in KB:");
define("_AM_NEWBB_ALLOWED_EXTENSIONS", "Zugelassene Erweiterungen:<span style='font-size: xx-small; font-weight: normal; display: block;'>'*' bedeutet keine Einschränkungen.<br /> Erweiterungen werden durch '|' getrennt.</span>");
define("_AM_NEWBB_ALLOW_ATTACHMENTS", "Anhänge zulassen?");
define("_AM_NEWBB_ALLOWHTML", "HTML zulassen?");
define("_AM_NEWBB_YES", "Ja");
define("_AM_NEWBB_NO", "Nein");
define("_AM_NEWBB_ALLOWSIGNATURES", "Signaturen zulassen?");
define("_AM_NEWBB_HOTTOPICTHRESHOLD", "Schwellenwert für 'Heisse Themen':");
//define("_AM_NEWBB_POSTPERPAGE","Posts per Page:<span style='font-size: xx-small; font-weight: normal; display: block;'>(This is the number of posts<br /> per topic that will be<br /> displayed per page.)</span>");
//define("_AM_NEWBB_TOPICPERFORUM","Topics per Forum:<span style='font-size: xx-small; font-weight: normal; display: block;'>(This is the number of topics<br /> per forum that will be<br /> displayed per page.)</span>");
//define("_AM_NEWBB_SHOWNAME","Replace user's name with real name:");
//define("_AM_NEWBB_SHOWICONSPANEL","Show icons panel:");
//define("_AM_NEWBB_SHOWSMILIESPANEL","Show smilies panel:");
define("_AM_NEWBB_MODERATOR_REMOVE", "Derzeitige Moderatoren entfernen");
define("_AM_NEWBB_MODERATOR_ADD", "Moderator(en) hinzufügen");

// admin_cat_manager.php
define("_AM_NEWBB_SETCATEGORYORDER", "Kategorieposition angeben:");
define("_AM_NEWBB_ACTIVE", "Aktiv");
define("_AM_NEWBB_INACTIVE", "Inaktiv");
define("_AM_NEWBB_STATE", "Status:");
define("_AM_NEWBB_CATEGORYDESC", "Kategoriebeschreibung:");
define("_AM_NEWBB_SHOWDESC", "Beschreibung anzeigen?");
define("_AM_NEWBB_IMAGE", "Image:");
//define("_AM_NEWBB_SPONSORIMAGE","Sponsor Image:");
define("_AM_NEWBB_SPONSORLINK", "Sponsorlink:");
define("_AM_NEWBB_DELCAT", "Kategorie löschen");
define("_AM_NEWBB_WAYSYWTDTTAL", "Hinweis: Dieser Vorgang löscht NICHT die Themen in dieser Kategorie, dies geschieht im 'Forum bearbeiten'-Bereich.<br /><br />WARNUNG: Sicher, dass diese Kategorie gelöscht werden soll?");

//%%%%%%        File Name  admin_forums.php           %%%%%
define("_AM_NEWBB_FORUMNAME", "Forumname:");
define("_AM_NEWBB_FORUMDESCRIPTION", "Forumbeschreibung:");
define("_AM_NEWBB_MODERATOR", "Moderator(en):");
define("_AM_NEWBB_REMOVE", "Entfernen");
define("_AM_NEWBB_CATEGORY", "Kategorie:");
define("_AM_NEWBB_DATABASEERROR", "Datenbankfehler");
define("_AM_NEWBB_CATEGORYUPDATED", "Kategorie aktualisiert.");
define("_AM_NEWBB_EDITCATEGORY", "Kategorie bearbeiten:");
define("_AM_NEWBB_CATEGORYTITLE", "Kategorietitel:");
define("_AM_NEWBB_CATEGORYCREATED", "Kategorie hinzugefügt.");
define("_AM_NEWBB_CREATENEWCATEGORY", "Neue Kategorie hinzufügen");
define("_AM_NEWBB_FORUMCREATED", "Forum hinzugefügt.");
define("_AM_NEWBB_ACCESSLEVEL", "Allgemeiner Zugriffslevel:");
define("_AM_NEWBB_CATEGORY1", "Kategorie");
define("_AM_NEWBB_FORUMUPDATE", "Forumseinstellungen aktualisiert");
define("_AM_NEWBB_FORUM_ERROR", "FEHLER: Fehler in den Forumseinstellungen");
define("_AM_NEWBB_CLICKBELOWSYNC", "Ein Klick auf die untenstehende Schaltfläche synchronisiert Ihre Foren und Themenbereiche mit dem tatsächlichen Datenbankbestand. Diese Funktion immer dann benutzen, wenn Ungereimtheiten in der Darstellung der Themen- und Forenlisten feststellen.");
define("_AM_NEWBB_SYNCHING", "Forum-, Index- und Themenbereiche werden synchronisiert (Dies kann einen Augenblick dauern)");
define("_AM_NEWBB_CATEGORYDELETED", "Kategorie gelöscht.");
define("_AM_NEWBB_MOVE2CAT", "In folgende Kategorie verschieben:");
define("_AM_NEWBB_MAKE_SUBFORUM_OF", "Unterforum von:");
define("_AM_NEWBB_MSG_FORUM_MOVED", "Forum verschoben!");
define("_AM_NEWBB_MSG_ERR_FORUM_MOVED", "Verschieben des Forums fehlgeschlagen.");
define("_AM_NEWBB_SELECT", "< Auswählen >");
define("_AM_NEWBB_MOVETHISFORUM", "Dieses Forum verschieben.");
define("_AM_NEWBB_MERGE", "Zusammenfügen");
define("_AM_NEWBB_MERGETHISFORUM", "Dieses Forum zusammenfügen");
define("_AM_NEWBB_MERGETO_FORUM", "Dieses Forum zusammenfügen mit:");
define("_AM_NEWBB_MSG_FORUM_MERGED", "Forum zusammengefügt");
define("_AM_NEWBB_MSG_ERR_FORUM_MERGED", "Zusammenfügen des Forums fehlgeschlagen");

//%%%%%%        File Name  admin_forum_reorder.php           %%%%%
define("_AM_NEWBB_REORDERID", "ID");
define("_AM_NEWBB_REORDERTITLE", "Titel");
define("_AM_NEWBB_REORDERWEIGHT", "Position");
define("_AM_NEWBB_SETFORUMORDER", "Forumssortierung bearbeiten");
define("_AM_NEWBB_BOARDREORDER", "Das Forum wurde nach Ihren Angaben umsortiert");

// admin_permission.php
define("_AM_NEWBB_PERMISSIONS_TO_THIS_FORUM", "Themenberechtigungen für dieses Forum");
define("_AM_NEWBB_CAT_ACCESS", "Kategorieberechtigung");
define("_AM_NEWBB_CAN_ACCESS", "Hat Zugriff auf diese Foren");
define("_AM_NEWBB_CAN_VIEW", "Kann lesen");
define("_AM_NEWBB_CAN_POST", "Kann neue Themen starten");
define("_AM_NEWBB_CAN_REPLY", "Kann antworten");
define("_AM_NEWBB_CAN_EDIT", "Kann bearbeiten");
define("_AM_NEWBB_CAN_DELETE", "Kann löschen");
define("_AM_NEWBB_CAN_ADDPOLL", "Kann Umfrage starten");
define("_AM_NEWBB_CAN_VOTE", "Kann abstimmen");
define("_AM_NEWBB_CAN_ATTACH", "Kann Datei hinzufügen");
define("_AM_NEWBB_CAN_NOAPPROVE", "Kann ohne Freigabe schreiben");
define("_AM_NEWBB_CAN_TYPE", "Kann Thementyp festlegen");
define("_AM_NEWBB_CAN_HTML", "Kann HTML nutzen");
define("_AM_NEWBB_CAN_SIGNATURE", "Kann Signatur benutzen");
define("_AM_NEWBB_ACTION", "Aktion");
define("_AM_NEWBB_PERM_TEMPLATE", "Standard Berechtigungsvorlage");
define("_AM_NEWBB_PERM_TEMPLATE_DESC", "Diese kann einem Forum zugewiesen werden");
define("_AM_NEWBB_PERM_FORUMS", "Foren auswählen");
define("_AM_NEWBB_PERM_TEMPLATE_CREATED", "Berechtigungsvorlage wurde angelegt");
define("_AM_NEWBB_PERM_TEMPLATE_ERROR", "Ein Fehler ist beim erstellen der Berechtigungsvorlage aufgetreten.");
define("_AM_NEWBB_PERM_TEMPLATEAPP", "Berechtigungsvorlage zuweisen");
define("_AM_NEWBB_PERM_TEMPLATE_APPLIED", "Standard Berechtigungsvorlage wurde zugewiesen.");
define("_AM_NEWBB_PERM_ACTION", "Berechtigungsaktionen");
define("_AM_NEWBB_PERM_SETBYGROUP", "Berechtigung je Gruppe festlegen");

// admin_forum_prune.php
define("_AM_NEWBB_PRUNE_RESULTS_TITLE", "Ergebnisse aufräumen");
define("_AM_NEWBB_PRUNE_RESULTS_TOPICS", "Aufgeräumte Themen");
define("_AM_NEWBB_PRUNE_RESULTS_POSTS", "Aufgeräumte Beiträge");
define("_AM_NEWBB_PRUNE_RESULTS_FORUMS", "Aufgeräumte Foren");
define("_AM_NEWBB_PRUNE_STORE", "In diesem Forum speichern anstatt die Beiträge zu löschen:");
define("_AM_NEWBB_PRUNE_ARCHIVE", "Kopien der Beiträge im Archiv sichern");
define("_AM_NEWBB_PRUNE_FORUMSELERROR", "Fehler, kein aufzuräumendes Forum angegeben.");
define("_AM_NEWBB_PRUNE_DAYS", "Entferne Themen ohne Beiträge seit:");
define("_AM_NEWBB_PRUNE_FORUMS", "Foren zum Aufräumen");
define("_AM_NEWBB_PRUNE_STICKY", "Sticky-Themen behalten");
define("_AM_NEWBB_PRUNE_DIGEST", "Zusammenfassungen behalten");
define("_AM_NEWBB_PRUNE_LOCK", "Geschlossene Themen behalten");
define("_AM_NEWBB_PRUNE_HOT", "Themen behalten die mehr als diese Anzahl Antworten haben:");
define("_AM_NEWBB_PRUNE_SUBMIT", "OK");
define("_AM_NEWBB_PRUNE_RESET", "Zurücksetzen");
define("_AM_NEWBB_PRUNE_YES", "Ja");
define("_AM_NEWBB_PRUNE_NO", "Nein");
define("_AM_NEWBB_PRUNE_WEEK", "Eine Woche");
define("_AM_NEWBB_PRUNE_2WEEKS", "Zwei Wochen");
define("_AM_NEWBB_PRUNE_MONTH", "Ein Monat");
define("_AM_NEWBB_PRUNE_2MONTH", "Zwei Monate");
define("_AM_NEWBB_PRUNE_4MONTH", "Vier Monate");
define("_AM_NEWBB_PRUNE_YEAR", "Ein Jahr");
define("_AM_NEWBB_PRUNE_2YEARS", "2 Jahre");

// About.php constants
define('_AM_NEWBB_AUTHOR_INFO', "Autoreninformation");
define('_AM_NEWBB_AUTHOR_NAME', "Autor");
define('_AM_NEWBB_AUTHOR_WEBSITE', "Webseite des Autors");
define('_AM_NEWBB_AUTHOR_EMAIL', "E-Mail des Autors");
define('_AM_NEWBB_AUTHOR_CREDITS', "Credits");
define('_AM_NEWBB_MODULE_INFO', "Modulentwicklungsinformation");
define('_AM_NEWBB_MODULE_STATUS', "Status");
define('_AM_NEWBB_MODULE_DEMO', "Demo Website");
define('_AM_NEWBB_MODULE_SUPPORT', "Offizielle Supportwebsite");
define('_AM_NEWBB_MODULE_BUG', "Einen Modulfehler melden");
define('_AM_NEWBB_MODULE_FEATURE', "Einen Vorschlag für zukünftige Erweiterung des Moduls machen");
define('_AM_NEWBB_MODULE_DISCLAIMER', "Disclaimer");
define('_AM_NEWBB_AUTHOR_WORD', "Bemerkungen des Entwicklers");
define('_AM_NEWBB_BY', 'Von');
define('_AM_NEWBB_AUTHOR_WORD_EXTRA', "Zusätzliche Worde des Modul Autors");

// admin_report.php
define("_AM_NEWBB_REPORTADMIN", "Gemeldete Beiträge");
define("_AM_NEWBB_PROCESSEDREPORT", "Bearbeitete Meldungen zeigen");
define("_AM_NEWBB_PROCESSREPORT", "Meldungen bearbeiten");
define("_AM_NEWBB_REPORTTITLE", "Meldungstitel");
define("_AM_NEWBB_REPORTEXTRA", "Extra");
define("_AM_NEWBB_REPORTPOST", "Gemeldeter Beitrag");
define("_AM_NEWBB_REPORTTEXT", "Meldungstext");
define("_AM_NEWBB_REPORTMEMO", "Memo bearbeiten");

// admin_report.php
define("_AM_NEWBB_DIGESTADMIN", "Digest Manager");
define("_AM_NEWBB_DIGESTCONTENT", "Digest Inhalte");

// admin_votedata.php
define("_AM_NEWBB_VOTE_RATINGINFOMATION", "Abstimmungsinformationen");
define("_AM_NEWBB_VOTE_TOTALVOTES", "Gesamtzahl Abstimmungen: ");
define("_AM_NEWBB_VOTE_REGUSERVOTES", "Abstimmungen registrierter User: %s");
define("_AM_NEWBB_VOTE_ANONUSERVOTES", "Abstimmungen nicht registrierter User: %s");
define("_AM_NEWBB_VOTE_USER", "Benutzer");
define("_AM_NEWBB_VOTE_IP", "IP-Adresse");
define("_AM_NEWBB_VOTE_USERAVG", "Durchschnittliche Bewertung");
define("_AM_NEWBB_VOTE_TOTALRATE", "Gesamtzahl Bewertungen");
define("_AM_NEWBB_VOTE_DATE", "Eingereicht");
define("_AM_NEWBB_VOTE_RATING", "Bewertung");
define("_AM_NEWBB_VOTE_NOREGVOTES", "Keine Abstimmung durch registrierte User möglich.");
define("_AM_NEWBB_VOTE_NOUNREGVOTES", "Keine Abstimmung durch nicht registrierte User möglich.");
define("_AM_NEWBB_VOTEDELETED", "Abstimmungsdaten gelöscht.");
define("_AM_NEWBB_VOTE_ID", "ID");
define("_AM_NEWBB_VOTE_FILETITLE", "Thementitel");
define("_AM_NEWBB_VOTE_DISPLAYVOTES", "Abstimmungsinformationen");
define("_AM_NEWBB_VOTE_NOVOTES", "Keine Abstimmungen vorhanden");
define("_AM_NEWBB_VOTE_DELETE", "Abstimmungsdaten löschen?");
define("_AM_NEWBB_VOTE_DELETEDSC", "<b>Löscht</b> die ausgewählten Abstimmungsdaten aus der Datenbank.");

// admin_type_manager.php
define("_AM_NEWBB_TYPE_ADD", "Thementypen hinzufügen");
define("_AM_NEWBB_TYPE_TEMPLATE", "Typentemplate");
define("_AM_NEWBB_TYPE_TEMPLATE_APPLY", "Template auswählen");
define("_AM_NEWBB_TYPE_FORUM", "Typ je Forum");
define("_AM_NEWBB_TYPE_NAME", "Typ Name");
define("_AM_NEWBB_TYPE_COLOR", "Farbe");
define("_AM_NEWBB_TYPE_DESCRIPTION", "Beschreibung");
define("_AM_NEWBB_TYPE_ORDER", "Sortierung");
define("_AM_NEWBB_TYPE_LIST", "Typenliste");
define("_AM_NEWBB_TODEL_TYPE", "Soll der Typ [%s] gelöscht werden?");
define("_AM_NEWBB_TYPE_EDITFORUM_DESC", "Die Daten wurden noch nicht gespeichert!");
define("_AM_NEWBB_TYPE_ORDER_DESC", "Zum aktivieren eines Types muss die Sortierung > 0 sein, zum deaktivieren auf 0 setzen.");

// admin_synchronization.php
define("_AM_NEWBB_SYNC_TYPE_FORUM", "Foren Daten");
define("_AM_NEWBB_SYNC_TYPE_TOPIC", "Themen Daten");
define("_AM_NEWBB_SYNC_TYPE_POST", "Posting Daten");
define("_AM_NEWBB_SYNC_TYPE_USER", "User Daten");
define("_AM_NEWBB_SYNC_TYPE_STATS", "Statistiken");
define("_AM_NEWBB_SYNC_TYPE_MISC", "Sonstiges");
define("_AM_NEWBB_SYNC_ITEMS", "Items for each loop: ");
define("_AM_NEWBB_ALLOW_SUBJECT_PREFIX", "Themen-Präfixe zulassen?");
define("_AM_NEWBB_ALLOW_SUBJECT_PREFIX_DESC", "Dies lässt Präfixe zu, die zur Themenbezeichnung hinzugefügt werden.");
define("_AM_NEWBB_GROUPMOD_TITLE", "Moderatoren per Gruppe hinzufügen");
define("_AM_NEWBB_GROUPMOD_TITLEDESC", "Ermöglicht es, User aus bestimmten Gruppen als Moderatoren einzutragen");
define("_AM_NEWBB_GROUPMOD_ALLFORUMS", "alle Foren");
define("_AM_NEWBB_GROUPMOD_ADDMOD", "Moderatoren wurden erfolgreich eingetragen.");
define("_AM_NEWBB_GROUPMOD_ERRMOD", "Fehler aufgetreten!");

// added in V 4.3
define('_AM_NEWBB_UPLOAD', 'max. Upload je Datei:');
define('_AM_NEWBB_MEMLIMITTOLARGE', 'Achtung! Wert \'memory_limit\' in PHP.INI kleiner als \'post_max_size\'');
define('_AM_NEWBB_MEMLIMITOK', 'Es können Dateien mit max %s hochgeladen werden.');

// irmtfan add messages
define('_AM_NEWBB_REPORTSAVE', "Selected Reports have been processed successfully");
define('_AM_NEWBB_REPORTDELETE', "Selected Reports have been deleted from database successfully");
define('_AM_NEWBB_REPORTNOTSELECT', "Kein Bericht ausgewählt!");
define('_AM_NEWBB_SYNC_TYPE_READ', "Read Data");
define('_AM_NEWBB_DATABASEUPDATED', "Datenbank erfolgreich aktualisiert!");
define('_AM_NEWBB_CAN_PDF', "Can PDF Dateien erstellen");
define('_AM_NEWBB_CAN_PRINT', "Kann Seiten drucken");

