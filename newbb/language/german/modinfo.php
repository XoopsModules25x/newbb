<?php
// $Id: modinfo.php 62 2012-08-17 10:15:26Z alfred $
// Thanks Tom (http://www.wf-projects.com), for correcting the Engligh language package

if (defined('NEWBB_MODINFO_DEFINED')) return;
define('NEWBB_MODINO_DEFINED', true);

// Module Info

// The name of this module
define("_MI_NEWBB_NAME", "Forum");

// A brief description of this module
define("_MI_NEWBB_DESC", "Forenmodul für Xoops");

// Names of blocks for this module (Not all module has blocks)
define("_MI_NEWBB_BLOCK_TOPIC_POST", "Aktuelle Beiträge im Forum");
define("_MI_NEWBB_BLOCK_TOPIC", "Zuletzt beantwortete Themen");
define("_MI_NEWBB_BLOCK_POST", "Aktuelle Postings im Forum");
define("_MI_NEWBB_BLOCK_AUTHOR", "Autoren");
define("_MI_NEWBB_BLOCK_TAG_CLOUD", "Tag Wolke");
define("_MI_NEWBB_BLOCK_TAG_TOP", "Top Tags");

// Names of admin menu items
define("_MI_NEWBB_ADMENU_INDEX", "Index");
define("_MI_NEWBB_ADMENU_CATEGORY", "Kategorien");
define("_MI_NEWBB_ADMENU_FORUM", "Foren");
define("_MI_NEWBB_ADMENU_PERMISSION", "Berechtigungen");
define("_MI_NEWBB_ADMENU_BLOCK", "Blöcke");
define("_MI_NEWBB_ADMENU_ORDER", "Sortierung");
define("_MI_NEWBB_ADMENU_SYNC", "Sychronisieren");
define("_MI_NEWBB_ADMENU_PRUNE", "Aufräumen");
define("_MI_NEWBB_ADMENU_REPORT", "Meldungen");
define("_MI_NEWBB_ADMENU_DIGEST", "Zusammenfassungen");
define("_MI_NEWBB_ADMENU_VOTE", "Abstimmungen");
define("_MI_NEWBB_ADMENU_TYPE", "Thementyp");
define("_MI_NEWBB_ADMENU_ABOUT", "Über");

//config options
define("_MI_DO_DEBUG", "Debug Modus");
define("_MI_DO_DEBUG_DESC", "Zeigt eventuell vorhandene Fehlermeldungen");
define("_MI_DO_REWRITE", "SEO-URL einschalten");
define("_MI_DO_REWRITE_DESC", "schreibt die URL um, benötigt mod_rewrite und eine .htaccess siehe readme.htaccess");
define("_MI_IMG_SET", "Bildset");
define("_MI_IMG_SET_DESC", "Hier das zu verwendende Bildset auswählen");
define("_MI_THEMESET", "Themeset");
define("_MI_THEMESET_DESC", "Modulweit, Auswahl '" . _NONE . "' seitenspezifisches Theme auswählen");
define("_MI_DIR_ATTACHMENT", "Physischer Pfad für die Dateianhänge.");
define("_MI_DIR_ATTACHMENT_DESC", "Der physische Pfad muss nur vom Hauptverzeichnis aus angegeben werden, nicht davor. Wenn z. B. die Dateianhänge in www.ihredomain.de/uploads/newbb hochgeladen werden sollen, müsste der einzugebende Pfad '/uploads/newbb' sein. Auf jeden Fall den abschließenden '/' (slash) weglassen. Das Verzeichnis für die Bildvorschauen wäre dann '/uploads/newbb/thumbs'");
define("_MI_PATH_MAGICK", "Pfad zu ImageMagick");
define("_MI_PATH_MAGICK_DESC", "Normalerweise ist dies '/usr/bin/X11'. Diese Angabe weg lassen (leer), wenn ImageMagick nicht installiert ist oder die Autoerkennung aktiviert werden soll.");
define("_MI_SUBFORUM_DISPLAY", "Anzeigemodus der Subforen auf der Indexseite");
define("_MI_SUBFORUM_DISPLAY_DESC", "");
define("_MI_SUBFORUM_EXPAND", "Ausgeklappt");
define("_MI_SUBFORUM_COLLAPSE", "Zusammengeklappt");
define("_MI_SUBFORUM_HIDDEN", "Versteckt");
define("_MI_POST_EXCERPT", "Betragsanreisser auf der Forumsseite");
define("_MI_POST_EXCERPT_DESC", "Länge des angerissenen Beitrags bei Mausberührung. 0 für kein Anreisser.");
define("_MI_PATH_NETPBM", "Pfad zu Netpbm");
define("_MI_PATH_NETPBM_DESC", "Normalerweise ist dies '/usr/bin'. Diese Angabe weg lassen (leer), wenn Netpbm nicht installiert ist oder die Autoerkennung aktiviert werden soll.");
define("_MI_IMAGELIB", "Grafikbibliothek auswählen");
define("_MI_IMAGELIB_DESC", "Legt fest womit die thumbnails erzeugt werden. Auf AUTO stellen für automatische Auswahl.");
define("_MI_MAX_IMG_WIDTH", "Maximale Breite eines Bildes");
define("_MI_MAX_IMG_WIDTH_DESC", "Setzt die maximal erlaubte Breite eines hochgeladenen Bildes.");
define("_MI_MAX_IMG_HEIGHT", "Maximale Höhe eines Bildes");
define("_MI_MAX_IMG_HEIGHT_DESC", "Setzt die maximale erlaubte Höhe eines hochgeladenen Bildes.");
define("_MI_MAX_IMAGE_WIDTH", "Maximale Breite eines Bildes für die Erzeugung eines thumbnail");
define("_MI_MAX_IMAGE_WIDTH_DESC", "Setzt die maximale Breite eines hochgeladenen Bildes, aus dem ein thumbnail erzeugt wird.");
define("_MI_MAX_IMAGE_HEIGHT", "Maximale Höhe eines Bildes für die Erzeugung eines thumbnail");
define("_MI_MAX_IMAGE_HEIGHT_DESC", "Setzt die maxilmale Höhe eines hochgeladenen Bildes, aus dem ein thumbnail erzeugt wird.");
define("_MI_SHOW_DIS", "Ausschlussklausel anzeigen bei");
define("_MI_DISCLAIMER", "Ausschlussklausel");
define("_MI_DISCLAIMER_DESC", "Hier die Ausschlussklausel eingeben, die bei der oben gewählten Option angezeigt werden soll.");
define("_MI_DISCLAIMER_TEXT", "Das Forum enthält eine Menge hilfreicher Informationen. <br /><br />Um die Anzahl der doppelten Einträge gering zu halten, ist es wünschenswert das vorher die Forumsuche verwendet wird bevor eine Frage gestellt wird.");
define("_MI_NONE", "Keinem");
define("_MI_POST", "Beitrag verfassen");
define("_MI_REPLY", "Antworten");
define("_MI_OP_BOTH", "Beiden");
define("_MI_WOL_ENABLE", "Anzeige des Online Status aktivieren");
define("_MI_WOL_ENABLE_DESC", "Aktiviert den 'Wer ist online' Block unterhalb der Forenübersicht und den einzelnen Foren");
define("_MI_NULL", "ausschalten");
define("_MI_TEXT", "Text");
define("_MI_GRAPHIC", "Grafisch");
define("_MI_USERLEVEL", "HP/MP/EXP Level Mod  einschalten");
define("_MI_USERLEVEL_DESC", "<strong>HP</strong> = Anzahl der durchschnittlichen Beiträge pro Tag.<br /><strong>MP</strong> = Verhältnis zwischen Registrierungsdatum und der Anzahl der Beiträge in der dieser Zeit.<br /><strong>EXP</strong> = Dieser Wert erhöht sich bei jedem Beitrag im Forum. Werden 100% erreicht wird, steigt der Erfahrungslevel um eins und die Anzeige wird wieder zurückgesetzt.");
define("_MI_RSS_ENABLE", "RSS-Feed aktivieren");
define("_MI_RSS_ENABLE_DESC", "Aktiviert RSS-Feed. Bitte die unten angegebenen Optionen ändern um die maximale Anzahl der Einträge und die maximale Länge der Beschreibung anzugeben.");
define("_MI_RSS_MAX_ITEMS", "Max. Einträge");
define("_MI_RSS_MAX_DESCRIPTION", "Max. Länge der Beschreibung");
define("_MI_RSS_UTF8", "RSS-Codierung mit UTF-8");
define("_MI_RSS_UTF8_DESCRIPTION", "'UTF-8' wird verwendet, wenn diese Option ausgewählt wird. Sonst wird " . _CHARSET . " verwendet.");
define("_MI_RSS_CACHETIME", "RSS-Feed Cachezeit");
define("_MI_RSS_CACHETIME_DESCRIPTION", "Cachezeit bis der RSS-Feed neu generiert wird, Angabe in Minuten.");
define("_MI_MEDIA_ENABLE", "Media Features aktivieren");
define("_MI_MEDIA_ENABLE_DESC", "Angehängte Bilder direkt im Beitrag anzeigen.");
define("_MI_USERBAR_ENABLE", "Erweitertes Benutzermenü aktivieren");
define("_MI_USERBAR_ENABLE_DESC", "Zeigt ein erweitertes Benutzermenü an, mit Angabe von: Profil, PN, ICQ, MSN, usw.");
define("_MI_GROUPBAR_ENABLE", "Benutzergruppen anzeigen");
define("_MI_GROUPBAR_ENABLE_DESC", "Zeigt im Beitrag die Gruppen an, denen der Benutzer angehört.");
define("_MI_RATING_ENABLE", "Bewertungsfunktion aktivieren");
define("_MI_RATING_ENABLE_DESC", "Erlaubt es Beiträge zu bewerten");
define("_MI_VIEWMODE", "Anzeigemodus für Beiträge");
define("_MI_VIEWMODE_DESC", "Durch diese Einstellung wird die generelle Einstellung des Anzeigemodus, im Forumthemenbereich,  überschrieben. 'Kein' auswählen, um diese Funktion nicht zu nutzen.");
define("_MI_COMPACT", "Kompakt");
define("_MI_MENUMODE", "Vorgabe der Menüanzeige");
define("_MI_MENUMODE_DESC", "'Auswahl' - per pulldown auswählen<br />'hover' - hover Menüauswahl (evtl. wird der IE langsam)<br />'klick' - benötigt Javascript");
define("_MI_REPORTMOD_ENABLE", "Beitrag melden");
define("_MI_REPORTMOD_ENABLE_DESC", "Benutzer können Beiträge an die Moderatoren melden. Diese können dann darauf reagieren (z. B. den Beitrag ändern oder löschen)");
define("_MI_SHOW_JUMPBOX", "'Gehe zu' Box aktivieren");
define("_MI_JUMPBOXDESC", "Wenn aktiviert, wird im Forum eine Combo Box angezeigt, die es dem Benutzer erlaubt, von Forum zu Forum zu springen.");
define("_MI_SHOW_PERMISSIONTABLE", "Tabelle mit Befugnissen anzeigen");
define("_MI_SHOW_PERMISSIONTABLE_DESC", "Wenn ja ausgewählt wird, wird eine Liste mit den Befugnissen des Benutzers angezeigt.");
define("_MI_EMAIL_DIGEST", "Beitragsübersicht per E-Mail");
define("_MI_EMAIL_DIGEST_DESC", "Zeitspanne festlegen, nach der eine Beitragsübersicht per E-Mail verschickt wird.");
define("_MI_NEWBB_EMAIL_NONE", "Keine E-Mail");
define("_MI_NEWBB_EMAIL_DAILY", "Täglich");
define("_MI_NEWBB_EMAIL_WEEKLY", "Wöchentlich");
define("_MI_SHOW_IP", "IP-Adresse des Benutzers anzeigen");
define("_MI_SHOW_IP_DESC", "Wenn ja ausgewählt wird, wird den Moderatoren die IP-Adresse des Verfassers angezeigt.");
define("_MI_ENABLE_KARMA", "'Karmavoraussetzungen' aktivieren");
define("_MI_ENABLE_KARMA_DESC", "Dies ermöglicht es Benutzern eine 'Karmavoraussetzung' für andere Benutzer festzulegen, damit diese ihren/seinen Beitrag lesen können.");
define("_MI_KARMA_OPTIONS", "Karmaeinstellungen für Beiträge");
define("_MI_KARMA_OPTIONS_DESC", "", " als Trennzeichen für mehrere Optionen.");
define("_MI_SINCE_OPTIONS", "'Seit' Optionen für 'Ansicht' und 'Suche'");
define("_MI_SINCE_OPTIONS_DESC", "Positive Werte für Tage und negative Werte für Stunden. ',' als Trennzeichen für mehrere Optionen.");
define("_MI_SINCE_DEFAULT", "'Seit' Vorgabewert");
define("_MI_SINCE_DEFAULT_DESC", "Vorgabewert, wenn nicht anders vom Benutzer gewählt.");
define("_MI_MODERATOR_HTML", "HTML Tags für Moderatoren erlauben");
define("_MI_MODERATOR_HTML_DESC", "Diese Einstellung erlaubt es Moderatoren in den Betreffzeilen Ihrer Beiträge HTML zu verwenden");
define("_MI_USER_ANONYMOUS", "Registrierten Benutzern das Verfassen anonymer Beiträge erlauben?");
define("_MI_USER_ANONYMOUS_DESC", "Diese Einstellung erlaubt es registrierten Benutzern, Beiträge anonym zu erfassen.");
define("_MI_ANONYMOUS_PRE", "Präfix für anonyme Benutzer");
define("_MI_ANONYMOUS_PRE_DESC", "Der hier angegebene Präfix wird anonymen Benutzernamen vorangestellt.");
define("_MI_REQUIRE_REPLY", "Zwingend erforderliche Antwort um einen Beitrag lesen zu können");
define("_MI_REQUIRE_REPLY_DESC", "Diese Option zwingt den Leser eine Antwort zu schreiben bevor er den originalen Beitrag lesen kann");
define("_MI_EDIT_TIMELIMIT", "Zeitlimit, in dem ein Beitrag geändert werden kann");
define("_MI_EDIT_TIMELIMIT_DESC", "Legt ein Zeitintervall fest, in dem ein Benutzer seinen Beitrag ändern kann. Angabe in Minuten, 0 für keine Beschränkung.");
define("_MI_DELETE_TIMELIMIT", "Zeitlimit, in dem ein Beitrag gelöscht werden kann");
define("_MI_DELETE_TIMELIMIT_DESC", "Legt ein Zeitintervall fest, in dem ein Benutzer seinen Beitrag löschen kann. Angabe in Minuten, 0 für keine Beschränkung.");
define("_MI_POST_TIMELIMIT", "Zeitlimit, bis ein weiterer Beitrag geschrieben werden kann");
define("_MI_POST_TIMELIMIT_DESC", "Legt ein Zeitintervall fest, das verstreichen muss, bis ein Benutzer einen weiteren neuen Beitrag schreiben kann. Angabe in Sekunden, 0 für keine Beschränkung.");
define("_MI_RECORDEDIT_TIMELIMIT", "Zeitlimit, in dem ein Beitrag ohne 'Geändert Info' geändert werden kann");
define("_MI_RECORDEDIT_TIMELIMIT_DESC", "Legt ein Zeitintervall fest, in dem ein Benutzer seinen Beitrag ändern kann ohne das diese Änderung mit entsprechender Info angezeigt wird. Angabe in Minuten, 0 für keine Beschränkung.");
define("_MI_SHOW_REALNAME", "Echten Namen anzeigen");
define("_MI_SHOW_REALNAME_DESC", "Ersetzt den Usernamen durch den Namen, der im Profil angegeben ist.");
define("_MI_CACHE_ENABLE", "Cache aktivieren");
define("_MI_CACHE_ENABLE_DESC", "Speichert einige Zwischenergebnisse innerhalb der Sitzung, um Abfragen zu sparen");
define("_MI_QUICKREPLY_ENABLE", "Schnelle Antwort aktivieren");
define("_MI_QUICKREPLY_ENABLE_DESC", "Diese Einstellung aktiviert ein Formular, mit dem Antworten direkt erstellt werden können.");
define("_MI_POSTSPERPAGE", "Beiträge pro Seite");
define("_MI_POSTSPERPAGE_DESC", "Maximale Anzahl Beiträge, die pro Seite angezeigt werden sollen.");
define("_MI_POSTSFORTHREAD", "Maximale Anzahl der Beiträge in der Diskussions Ansicht");
define("_MI_POSTSFORTHREAD_DESC", "Die flache Ansicht wird benutzt wenn die Anzahl höher ist.");
define("_MI_TOPICSPERPAGE", "Themen pro Seite");
define("_MI_TOPICSPERPAGE_DESC", "Maximale Anzahl Themen, die pro Seite angezeigt werden sollen.");
define("_MI_IMG_TYPE", "Bildtyp");
define("_MI_IMG_TYPE_DESC", "Dateityp für die Schaltflächen im Forum festlegen.<br />- png: für schnelle Server;<br />- gif: für normale Geschwindigkeit;<br />- auto: gif für Internet Explorer und png für andere Browser");
define("_MI_PNGFORIE_ENABLE", "'PNG-Hack' aktivieren");
define("_MI_PNGFORIE_ENABLE_DESC", "Dieser Hack erlaubt es dem Internet Explorer, das png Transparenzattribut zu verwenden.");
define("_MI_FORM_OPTIONS", "Formularoptionen");
define("_MI_FORM_OPTIONS_DESC", "Formularoptionen, welche die User beim Beitrag schreiben/ändern/antworten auswählen können.");
define("_MI_FORM_COMPACT", "Kompakt");
define("_MI_FORM_DHTML", "DHTML");
define("_MI_MAGICK", "ImageMagick");
define("_MI_NETPBM", "Netpbm");
define("_MI_GD1", "GD1 Grafikbibliothek");
define("_MI_GD2", "GD2 Grafikbibliothek");
define("_MI_AUTO", "Automatisch");
define("_MI_WELCOMEFORUM", "Forum um neue Mitglieder zu begrüssen");
define("_MI_WELCOMEFORUM_DESC", "Ein Beitrag wird veröffentlicht, mit einem link zum Profil, wenn ein neues Mitglied das Forum zu ersten mal besucht.");
define("_MI_PERMCHECK_ONDISPLAY", "Zugriff überprüfen");
define("_MI_PERMCHECK_ONDISPLAY_DESC", "Zugriff überprüfen für Änderungen auf der Anzeigeseite");
define("_MI_USERMODERATE", "Beitragsmoderation einschalten");
define("_MI_USERMODERATE_DESC", "die Beiträge müssen durch Moderatoren/Administratoren freigegeben werden");

// RMV-NOTIFY
// Notification event descriptions and mail templates

define("_MI_NEWBB_THREAD_NOTIFY", "Diskussion");
define("_MI_NEWBB_THREAD_NOTIFYDSC", "Benachrichtigungsoptionen die die aktuelle Diskussion betreffen.");
define("_MI_NEWBB_FORUM_NOTIFY", "Forum");
define("_MI_NEWBB_FORUM_NOTIFYDSC", "Benachrichtigungsoptionen die das aktuelle Forum betreffen.");
define("_MI_NEWBB_GLOBAL_NOTIFY", "Allgemein");
define("_MI_NEWBB_GLOBAL_NOTIFYDSC", "Allgemeine Forum-Benachrichtigungsoptionen.");
define("_MI_NEWBB_THREAD_NEWPOST_NOTIFY", "Neuer Beitrag");
define("_MI_NEWBB_THREAD_NEWPOST_NOTIFYCAP", "Benachrichtigen bei neuen Beiträgen in der aktuellen Diskussion.");
define("_MI_NEWBB_THREAD_NEWPOST_NOTIFYDSC", "Benachrichtigung bei neuen Beiträgen in der aktuellen Diskussion.");
define("_MI_NEWBB_THREAD_NEWPOST_NOTIFYSBJ", "[{X_SITENAME}] {X_MODULE} Automatische Benachrichtigung: Neuer Beitrag in Diskussion");
define("_MI_NEWBB_FORUM_NEWTHREAD_NOTIFY", "Neues Thema");
define("_MI_NEWBB_FORUM_NEWTHREAD_NOTIFYCAP", "Benachrichtigen bei neuen Themen im aktuellen Forum.");
define("_MI_NEWBB_FORUM_NEWTHREAD_NOTIFYDSC", "Benachrichtigung bei neuen Themen im aktuellen Forum.");
define("_MI_NEWBB_FORUM_NEWTHREAD_NOTIFYSBJ", "[{X_SITENAME}] {X_MODULE} Automatische Benachrichtigung: Neues Thema in Forum");
define("_MI_NEWBB_GLOBAL_NEWFORUM_NOTIFY", "Neues Forum");
define("_MI_NEWBB_GLOBAL_NEWFORUM_NOTIFYCAP", "Benachrichtigen wenn ein neues Forum angelegt worden ist.");
define("_MI_NEWBB_GLOBAL_NEWFORUM_NOTIFYDSC", "Benachrichtigung wenn ein neues Forum angelegt worden ist.");
define("_MI_NEWBB_GLOBAL_NEWFORUM_NOTIFYSBJ", "[{X_SITENAME}] {X_MODULE} Automatische Benachrichtigung: Neues Forum");
define("_MI_NEWBB_GLOBAL_NEWPOST_NOTIFY", "Neuer Beitrag");
define("_MI_NEWBB_GLOBAL_NEWPOST_NOTIFYCAP", "Benachrichtigen bei neuen Beiträgen.");
define("_MI_NEWBB_GLOBAL_NEWPOST_NOTIFYDSC", "Benachrichtigung bei neuen Beiträgen.");
define("_MI_NEWBB_GLOBAL_NEWPOST_NOTIFYSBJ", "[{X_SITENAME}] {X_MODULE} Automatische Benachrichtigung: Neuer Beitrag");
define("_MI_NEWBB_FORUM_NEWPOST_NOTIFY", "Neuer Beitrag");
define("_MI_NEWBB_FORUM_NEWPOST_NOTIFYCAP", "Benachrichtigen bei neuen Beiträgen im aktuellen Forum.");
define("_MI_NEWBB_FORUM_NEWPOST_NOTIFYDSC", "Benachrichtigung bei neuen Beiträgen im aktuellen Forum.");
define("_MI_NEWBB_FORUM_NEWPOST_NOTIFYSBJ", "[{X_SITENAME}] {X_MODULE} Automatische Benachrichtigung: Neuer Beitrag im aktuellen Forum");
define("_MI_NEWBB_GLOBAL_NEWFULLPOST_NOTIFY", "Neuer Beitrag (Kompletter Text)");
define("_MI_NEWBB_GLOBAL_NEWFULLPOST_NOTIFYCAP", "Benachrichtigen bei neuen Beiträgen (Kompletten Text in Benachrichtigung anzeigen).");
define("_MI_NEWBB_GLOBAL_NEWFULLPOST_NOTIFYDSC", "Benachrichtigung bei neuen Beiträgen (Kompletten Text in Benachrichtigung anzeigen).");
define("_MI_NEWBB_GLOBAL_NEWFULLPOST_NOTIFYSBJ", "[{X_SITENAME}] {X_MODULE} Automatische Benachrichtigung: Neuer Beitrag (Kompletter Text)");
define("_MI_NEWBB_GLOBAL_DIGEST_NOTIFY", "Zusammenfassung");
define("_MI_NEWBB_GLOBAL_DIGEST_NOTIFYCAP", "Benachrichtigen bei Zusammenfassungen.");
define("_MI_NEWBB_GLOBAL_DIGEST_NOTIFYDSC", "Zusammenfassungen empfangen.");
define("_MI_NEWBB_GLOBAL_DIGEST_NOTIFYSBJ", "[{X_SITENAME}] {X_MODULE} Automatische Benachrichtigung: Zusammenfassung veröffentlicht");

// FOR installation
define("_MI_NEWBB_INSTALL_CAT_TITLE", "Kategorie Test");
define("_MI_NEWBB_INSTALL_CAT_DESC", "Kategorie für Test.");
define("_MI_NEWBB_INSTALL_FORUM_NAME", "Forum Test");
define("_MI_NEWBB_INSTALL_FORUM_DESC", "Forum für Test.");
define("_MI_NEWBB_INSTALL_POST_SUBJECT", "Glückwunsch! Das Forum ist installiert und arbeitet.");
define("_MI_NEWBB_INSTALL_POST_TEXT", "
    Willkommen zu diesem neuen Forum.

    Jetzt Registrieren, Anmelden und mit Themen starten.


    Für Fragen hinsichtlich des NewBB steht die lokale Supportseite oder
    die [url=http://www.simple-xoops.de/]NewBB Modul Support-Seite[/url] zur Verfügung.

    ");
define("_MI_NEWBB_ADMENU_GROUPMOD", "Gruppen Moderation");
define("_MI_SUBJECT_PREFIX", "Einen Präfix dem Themenbetreff hinzufügen");
define("_MI_SUBJECT_PREFIX_DESC", "Setzt einen Präfix, z. B. [Erledigt] an den Anfang des Betreffs. Für mehrere Optionen bitte ein ", " als Trennzeichen verwenden. NONE setzt keinen Präfix.");
define("_MI_SUBJECT_PREFIX_DEFAULT", "<span style='color:#00CC00'>[gelöst]</span>,<span style='color:#00CC00'>[erledigt]</span>,<span style='color:#FF0000'>[Anfrage]</span>,<span style='color:#FF0000'>[bug melden]</span>,<span style='color:#FF0000'>[ungelöst]</span>");
define("_MI_SUBJECT_PREFIX_LEVEL", "Berechtigungen zum nutzen von Präfixen");
define("_MI_SUBJECT_PREFIX_LEVEL_DESC", "Die Gruppe(n) auswählen die Präfixe benutzen dürfen.");
define("_MI_SPL_DISABLE", "Deaktiviert");
define("_MI_SPL_ANYONE", "Jeder");
define("_MI_SPL_MEMBER", "Mitglieder");
define("_MI_SPL_MODERATOR", "Moderatoren");
define("_MI_SPL_ADMIN", "Administratoren");
define("_MI_STATISTIK_ENABLE", "Statistik einschalten");
define("_MI_STATISTIK_ENABLE_DESC", "");

//4.05
define("_MI_SHOW_INFOBOX", "Anzeige der Infobox");
define('_MI_SHOW_INFOBOX_DESC', 'Infobox enthält Informationen über den Benutzer (Beitritt Datum, Anzahl der Beiträge, ...)');
define("_MI_NEWBB_INFOBOX_NONE", "Nein");
define("_MI_NEWBB_INFOBOX_HIDDEN", "Ja, eingeklappt");
define("_MI_NEWBB_INFOBOX_SHOW", "Ja, ausgeklappt");
define("_MI_SHOW_SOCIALLINKS", "Anzeige von Social Links");
define("_MI_SHOW_SOCIALLINKS_DESC", "Anzeige von Links, Buttons zu Facebook, Twitter etc.");

//4.2
define("_MI_PAGENAV_DISPLAY", "Anzeige Navigationsart");
define("_MI_PAGENAV_DISPLAY_DESC", "Zeigt die Seitenzahlen im jeweilgen Modus an");
define("_MI_PAGENAV_ZAHL", "als Zahlen");
define("_MI_PAGENAV_BILD", "als kleine Bilder");
define("_MI_PAGENAV_SELECT", "als Select-Box");
define("_MI_ADVERTISING", "Werbung einblenden");
define("_MI_ADVERTISING_DESC", "zeigt einen Werbeblock nach dem 2. Thread");
define("_MI_USERATTACH_ENABLE", "Anzeige Attachments nur für registrierte User");
define("_MI_USERATTACH_DESC", "zeigt Dateianhänge im Forum erst nach dem Einloggen an");
// 4.3
define('_MI_NEWBB_BLOCK_LIST_TOPIC', "Liste der Themen"); // irmtfan
