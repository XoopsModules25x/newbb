<?php
// $Id: main.php 62 2012-08-17 10:15:26Z alfred $
if (defined('MAIN_DEFINED')) return;
define('MAIN_DEFINED', true);

define('_MD_ERROR', 'Fehler');
define('_MD_SELFORUM', 'Forum wählen');
define('_MD_THIS_FILE_WAS_ATTACHED_TO_THIS_POST', 'Angehängte Datei:');
define('_MD_ALLOWED_EXTENSIONS', 'Erlaubte Endungen');
define('_MD_MAX_FILESIZE', 'Maximale Dateigröße');
define('_MD_ATTACHMENT', 'Datei anhängen');
define('_MD_FILESIZE', 'Größe');
define('_MD_HITS', 'Hits');
define('_MD_GROUPS', 'Gruppen:');
define('_MD_DEL_ONE', 'Nur diesen Beitrag löschen');
define('_MD_DEL_RELATED', 'Alle Beiträge zu diesen Thema löschen');
define('_MD_MARK_ALL_FORUMS', 'Alle Foren markieren als');
define('_MD_MARK_ALL_TOPICS', 'Alle Themen markieren als');
define('_MD_MARK_UNREAD', 'ungelesen');
define('_MD_MARK_READ', 'gelesen');
define('_MD_ALL_FORUM_MARKED', 'Alle Foren markiert als');
define('_MD_ALL_TOPIC_MARKED', 'Alle Themen markiert als');
define('_MD_BOARD_DISCLAIMER', 'Forum Beschreibung');

//index.php
define('_MD_ADMINCP', 'Admin Bereich');
define('_MD_FORUM', 'Forum');
define('_MD_WELCOME', '%s - Forum');
define('_MD_TOPICS', 'Themen');
define('_MD_POSTS', 'Beiträge');
define("_MD_DIGESTS", "Zusammenfassung");
define('_MD_LASTPOST', 'Letzter Beitrag');
define('_MD_MODERATOR', 'Moderator(en)');
define('_MD_NEWPOSTS', 'Neue Beiträge');
define('_MD_NONEWPOSTS', 'Keine neuen Beiträge');
define('_MD_PRIVATEFORUM', 'Inaktives Forum');
define('_MD_BY', 'von'); // Posted by
define('_MD_TOSTART', 'Um Beiträge zu lesen, einen Forenbereich auswählen.');
define('_MD_TOTALTOPICSC', 'Themen insgesamt: ');
define('_MD_TOTALPOSTSC', 'Beiträge insgesamt: ');
define('_MD_TOTALUSER', 'Benutzer insgesamt: ');
define('_MD_TIMENOW', 'Aktuell: %s');
define('_MD_USER_LASTVISIT', 'Letzter Besuch: %s');
define('_MD_USER_LASTPOST', 'eigener letzter Beitrag: %s');
define('_MD_USER_NOLASTPOST', 'bisher keine eigenen Postings');
define('_MD_USER_TOPICS', 'eigene Themen: %s');
define('_MD_USER_POSTS', 'eigene Postings: %s');
define('_MD_USER_DIGESTS', 'eigene Digests: %s');
define('_MD_VIEW_NEWPOSTS', 'Anzeige neue Postings');
define('_MD_ADVSEARCH', 'Erweiterte Suche');
define('_MD_POSTEDON', 'Geschrieben: ');
define('_MD_SUBJECT', 'Thema: ');
define('_MD_INACTIVEFORUM_NEWPOSTS', 'Inaktives Forum mit neuen Beiträgen');
define('_MD_INACTIVEFORUM_NONEWPOSTS', 'Inaktives Forum ohne neue Beiträge');
define('_MD_SUBFORUMS', 'Unterforen');
define('_MD_MAINFORUMOPT', 'Hauptoptionen');
define("_MD_PENDING_POSTS_FOR_AUTH", "Auf Freigabe wartende Beiträge:");
define('_MD_TODAYTOPICSC', 'Heutige Themen: ');
define('_MD_TODAYPOSTSC', 'Heutige Postings: ');
define('_MD_TOTALDIGESTSC', 'Zusammenfassung: ');

//page_header.php
define('_MD_MODERATEDBY', 'Moderiert von');
define('_MD_SEARCH', 'Im Forum suchen');
define('_MD_FORUMINDEX', 'Forenübersicht');
define('_MD_POSTNEW', 'Neuen Beitrag schreiben');
define('_MD_REGTOPOST', 'Bitte erst registrieren. Danach ist es möglich Beiträge zu schreiben.');

//search.php
define('_MD_SEARCHALLFORUMS', 'Suche in allen Foren');
define('_MD_FORUMC', 'Forum:');
define('_MD_AUTHORC', 'Autor:');
define('_MD_SORTBY', 'Sortiert nach:');
define('_MD_DATE', 'Datum');
define('_MD_TOPIC', 'Thema');
define('_MD_POST2', 'Post');
define('_MD_USERNAME', 'Benutzername');
define('_MD_BODY', 'Beitragstext');
define('_MD_SINCE', 'Seit');

//viewforum.php
define('_MD_REPLIES', 'Antworten');
define('_MD_POSTER', 'Autor');
define('_MD_VIEWS', 'Gelesen');
define('_MD_MORETHAN', 'Neue Beiträge [Populär]');
define('_MD_MORETHAN2', 'Keine neuen Beiträge [Populär]');
define('_MD_TOPICSHASATT', 'Thema hat Anhänge');
define('_MD_TOPICHASPOLL', 'Thema hat eine Umfrage');
define('_MD_TOPICLOCKED', 'Thema geschlossen');
define('_MD_LEGEND', 'Legende');
define('_MD_NEXTPAGE', 'Nächste Seite');
define('_MD_SORTEDBY', 'Sortiert nach');
define('_MD_TOPICTITLE', 'Thementitel');
define('_MD_NUMBERREPLIES', 'Anzahl der Antworten');
define('_MD_TOPICPOSTER', 'Themenautor');
define('_MD_TOPICTIME', 'Veröffentlicht');
define('_MD_LASTPOSTTIME', 'Letzter Beitrag');
define('_MD_ASCENDING', 'Aufsteigende Reihenfolge');
define('_MD_DESCENDING', 'Absteigende Reihenfolge');
define('_MD_FROMLASTHOURS', 'Innerhalb der letzten %s Stunden');
define('_MD_FROMLASTDAYS', 'In den letzten %s Tagen');
define('_MD_THELASTYEAR', 'Im letzten Jahr');
define('_MD_BEGINNING', 'Seit Eröffnung des Forums');
define('_MD_SEARCHTHISFORUM', 'Durchsuche dieses Forum');
define('_MD_TOPIC_SUBJECTC', 'Themenpräfix:');
define('_MD_RATINGS', 'Bewertungen');
define("_MD_CAN_ACCESS", "<b>Erlaubt</b>, dieses Forum zu betreten.<br />");
define("_MD_CANNOT_ACCESS", "<b>Nicht erlaubt</b>, dieses Forum zu betreten.<br />");
define("_MD_CAN_POST", "<b>Erlaubt</b>, ein neues Thema zu erstellen.<br />");
define("_MD_CANNOT_POST", "<b>Nicht erlaubt</b>, ein neues Thema zu erstellen.<br />");
define("_MD_CAN_VIEW", "<b>Erlaubt</b>, Themen anzuschauen.<br />");
define("_MD_CANNOT_VIEW", "<b>Nicht erlaubt</b>, Themen anzuschauen.<br />");
define("_MD_CAN_REPLY", "<b>Erlaubt</b>, auf Beiträge zu antworten.<br />");
define("_MD_CANNOT_REPLY", "<b>Nicht erlaubt</b>, auf Beiträge zu antworten.<br />");
define("_MD_CAN_EDIT", "<b>Erlaubt</b>, Beiträge zu editieren.<br />");
define("_MD_CANNOT_EDIT", "<b>Nicht erlaubt</b>, Beiträge zu editieren.<br />");
define("_MD_CAN_DELETE", "<b>Erlaubt</b>, Beiträge zu löschen.<br />");
define("_MD_CANNOT_DELETE", "<b>Nicht erlaubt</b>, Beiträge zu löschen.<br />");
define("_MD_CAN_ADDPOLL", "<b>Erlaubt</b>, neue Umfragen zu erstellen.<br />");
define("_MD_CANNOT_ADDPOLL", "<b>Nicht erlaubt</b>, Umfragen zu erstellen.<br />");
define("_MD_CAN_VOTE", "<b>Erlaubt</b>, in Umfragen abzustimmen.<br />");
define("_MD_CANNOT_VOTE", "<b>Nicht erlaubt</b>, in Umfragen abzustimmen.<br />");
define("_MD_CAN_ATTACH", "<b>Erlaubt</b>, Dateien hoch zu laden.<br />");
define("_MD_CANNOT_ATTACH", "<b>Nicht erlaubt</b>, Dateien hoch zu laden.<br />");
define("_MD_CAN_NOAPPROVE", "<b>Erlaubt</b>, Beiträge ohne Prüfung zu schreiben.<br />");
define("_MD_CANNOT_NOAPPROVE", "<b>Nicht erlaubt</b>, Beiträge ohne Prüfung zu schreiben.<br />");
define("_MD_CAN_TYPE", "<strong>Erlaubt</strong>, Thementyp zu setzen.<br />");
define("_MD_CANNOT_TYPE", "<strong>Nicht erlaubt</strong>, Thementyp zu setzen.<br />");
define("_MD_CAN_HTML", "<strong>Erlaubt</strong>, HTML zu benutzen.<br />");
define("_MD_CANNOT_HTML", "<strong>Nicht erlaubt</strong>, HTML zu benutzen.<br />");
define("_MD_CAN_UPLOAD", "<strong>Erlaubt</strong>, Dateien hochzuladen.<br />");
define("_MD_CANNOT_UPLOAD", "<strong>Nicht erlaubt</strong>, Dateien hochzuladen.<br />");
define("_MD_CAN_SIGNATURE", "<strong>Erlaubt</strong>, Signatur zu benutzen.<br />");
define("_MD_CANNOT_SIGNATURE", "<strong>Nicht erlaubt</strong>, Signatur zu benutzen.<br />");
define("_MD_IMTOPICS", "Wichtige Themen");
define("_MD_NOTIMTOPICS", "Forumthemen");
define('_MD_FORUMOPTION', 'Forumoptionen');
define('_MD_VAUP', 'Zeige alle unbeantworteten Beiträge');
define('_MD_VANP', 'Zeige alle neuen Beiträge');
define('_MD_UNREPLIED', 'unbeantwortete Themen');
define('_MD_UNREAD', 'ungelesene Themen');
define('_MD_ALL', 'alle Themen');
define('_MD_ALLPOSTS', 'alle Beiträge');
define('_MD_VIEW', 'Zeige');

//viewtopic.php
define('_MD_AUTHOR', 'Autor');
define('_MD_LOCKTOPIC', 'Thema schliessen');
define('_MD_UNLOCKTOPIC', 'Thema öffnen');
define('_MD_UNSTICKYTOPIC', 'Thema entpinnen');
define('_MD_STICKYTOPIC', 'Thema pinnen');
define('_MD_DIGESTTOPIC', 'Thema als Zusammenfassung');
define('_MD_UNDIGESTTOPIC', 'Thema nicht als Zusammenfassung');
define('_MD_MERGETOPIC', 'Thema verbinden');
define('_MD_MOVETOPIC', 'Thema verschieben');
define('_MD_DELETETOPIC', 'Thema löschen');
define('_MD_TOP', 'Nach oben');
define('_MD_BOTTOM', 'Nach unten');
define('_MD_PREVTOPIC', 'Vorheriges Thema');
define('_MD_NEXTTOPIC', 'Nächstes Thema');
define('_MD_GROUP', 'Gruppe:');
define('_MD_QUICKREPLY', 'Schnellantwort');
define('_MD_POSTREPLY', 'Antworte');
define('_MD_PRINTTOPICS', 'Thema drucken');
define('_MD_PRINT', 'Drucke');
define('_MD_REPORT', 'Melden');
define('_MD_PM', 'PN');
define('_MD_EMAIL', 'Email');
define('_MD_WWW', 'www');
define('_MD_AIM', 'AIM');
define('_MD_YIM', 'YIM');
define('_MD_MSNM', 'MSN');
define('_MD_ICQ', 'ICQ');
define('_MD_PRINT_TOPIC_LINK', 'URL für dieses Thema');
define('_MD_ADDTOLIST', 'Zur Kontaktliste hinzufügen');
define('_MD_TOPICOPT', 'Themenoptionen');
define('_MD_VIEWMODE', 'Anzeigemodus');
define('_MD_NEWEST', 'Aktuelleste zuerst');
define('_MD_OLDEST', 'Älteste zuerst');
define('_MD_FORUMSEARCH', 'Forum durchsuchen');
define('_MD_RATED', 'Bewertung:');
define('_MD_RATE', 'Themenbewertung');
define('_MD_RATEDESC', 'Dieses Thema bewerten');
define('_MD_RATING', 'Jetzt bewerten');
define('_MD_RATE1', 'Sehr schlecht');
define('_MD_RATE2', 'Schlecht');
define('_MD_RATE3', 'Mittel');
define('_MD_RATE4', 'Gut');
define('_MD_RATE5', 'Sehr gut');
define('_MD_TOPICOPTION', 'Themenoptionen');
define('_MD_KARMA_REQUIREMENT', 'Das persönliche Karma %s ist nicht ausreichend für das benötigte Karma von %s. <br /> Bitte später nochmals versuchen.');
define('_MD_REPLY_REQUIREMENT', 'Um diesen Beitrag lesen zu können, bitte einloggen und eine Antwort erstellen.');
define('_MD_TOPICOPTIONADMIN', 'Thema Admin Optionen');
define('_MD_POLLOPTIONADMIN', 'Umfrage Admin Optionen');
define('_MD_EDITPOLL', 'Umfrage bearbeiten');
define('_MD_DELETEPOLL', 'Umfrage löschen');
define('_MD_RESTARTPOLL', 'Umfrage neu starten');
define('_MD_ADDPOLL', 'Umfrage hinzufügen');
define('_MD_QUICKREPLY_EMPTY', 'Eingabebereich für die Schnellantwort');
define('_MD_LEVEL', 'Level:');
define('_MD_HP', 'HP:');
define('_MD_MP', 'MP:');
define('_MD_EXP', 'EXP:');
define('_MD_BROWSING', 'Leser in diesem Thema:');
define('_MD_POSTTONEWS', 'Diesen Beitrag als News veröffentlichen');
define('_MD_EXCEEDTHREADVIEW', 'Anzahl der Beiträge übersteigt das Maximum für diese Ansicht, <br />bitte zur flachen Ansicht wechseln.');

//forumform.inc
define('_MD_QUOTE', 'Zitat');
define('_MD_VIEW_REQUIRE', 'Anforderungen zeigen');
define('_MD_REQUIRE_KARMA', 'Karma');
define('_MD_REQUIRE_REPLY', 'Antworten');
define('_MD_REQUIRE_NULL', 'Keine Anforderungen');
define("_MD_SELECT_FORMTYPE", "Bevorzugten Editor wählen");
define("_MD_FORM_COMPACT", "Kompakt");
define("_MD_FORM_DHTML", "DHTML");

// ERROR messages
define('_MD_ERRORFORUM', 'Fehler, kein Forum ausgewählt!');
define('_MD_ERRORPOST', 'Fehler, kein Beitrag ausgewählt!');
define('_MD_NORIGHTTOVIEW', 'Keine Rechte um dieses Thema zu lesen.');
define('_MD_NORIGHTTOPOST', 'Keine Rechte um in diesem Forum zu schreiben.');
define('_MD_NORIGHTTOEDIT', 'Keine Rechte um in diesem Forum zu editieren.');
define('_MD_NORIGHTTOREPLY', 'Keine Rechte um in diesem Forum zu antworten.');
define('_MD_NORIGHTTOACCESS', 'Keine Rechte dieses Forum zu betreten.');
define('_MD_ERRORTOPIC', 'Fehler, kein Thema ausgewählt!');
define('_MD_ERRORCONNECT', 'Fehler, konnte Datenbank nicht erreichen.');
define('_MD_ERROREXIST', 'Fehler, das Forum welches ausgewählt wurde existiert nicht. Bitte zurück und erneut versuchen.');
define('_MD_ERROROCCURED', 'Ein Fehler ist aufgetreten.');
define('_MD_COULDNOTQUERY', 'Konnte Datenbank nicht erreichen.');
define('_MD_FORUMNOEXIST', 'Fehler, das Forum oder Thema welches ausgewählt wurde existiert nicht. Bitte zurück und erneut versuchen.');
define('_MD_USERNOEXIST', 'Dieser Benutzer existiert nicht. Bitte zurück und erneut versuchen.');
define('_MD_COULDNOTREMOVE', 'Fehler, der Beitrag konnte nicht in der Datenbank gelöscht werden!');
define('_MD_COULDNOTREMOVETXT', 'Fehler, konnte den Beitragstext nicht löschen!');
define('_MD_TIMEISUP', 'Das vorgegebene Zeitlimit zum ändern eines Beitrags wurde überschritten.');
define('_MD_TIMEISUPDEL', 'Das vorgegebene Zeitlimit zum löschen eines Beitrags wurde überschritten.');

//reply.php
define('_MD_ON', 'am'); //Posted on
define('_MD_USERWROTE', '%s schrieb:'); // %s is username
define('_MD_RE', 'Aw');

//post.php
define('_MD_EDITNOTALLOWED', 'Nicht erlaubt, diesen Beitrag zu editieren.');
define('_MD_EDITEDBY', 'Bearbeitet von ');
define('_MD_ANONNOTALLOWED', 'Gästen ist es nicht gestattet Beiträge zu veröffentlichen. Bitte registrieren, um sich aktiv in diesem Thema zu beteiligen.');
define('_MD_THANKSSUBMIT', 'Danke für den Beitrag.');
define('_MD_REPLYPOSTED', 'Eine Antwort auf einen Beitrag wurde verfasst.');
define('_MD_HELLO', 'Hallo %s,');
define('_MD_URRECEIVING', 'Diese E-Mail wurde gesendet, weil eine Antwort auf dem %s Forum zu einem Beitrag verfasst wurde.'); // %s is your site name
define('_MD_CLICKBELOW', 'Der Beitrag befindet sich unter folgender URL:');
define('_MD_WAITFORAPPROVAL', 'Danke für den Beitrag. Der Beitrag wird geprüft, bev|| exitser veröffentlicht wird.');
define('_MD_POSTING_LIMITED', 'In diesem Forum ist eine Spamsperre aktiviert, bitte in %d Sekunden erneut versuchen.');

//forumform.inc
define('_MD_NAMEMAIL', 'Name:');
define('_MD_LOGOUT', 'Abmelden');
define('_MD_REGISTER', 'Registrieren');
define('_MD_SUBJECTC', 'Titel:');
define('_MD_MESSAGEICON', 'Beitragssymbol:');
define('_MD_MESSAGEC', 'Beitragstext:');
define('_MD_ALLOWEDHTML', 'Erlaubte HTML-Tags:');
define('_MD_OPTIONS', 'Optionen:');
define('_MD_POSTANONLY', 'Anonym veröffentlichen');
define('_MD_DOSMILEY', 'Smilies aktivieren');
define('_MD_DOXCODE', 'XOOPS-Code aktivieren');
define('_MD_DOBR', 'Zeilenvorschub einschalten (Ausschalten, wenn HTML eingeschaltet ist)');
define('_MD_DOHTML', 'HTML aktivieren');
define('_MD_NEWPOSTNOTIFY', 'Benachrichtigen bei neuen Beiträgen in diesem Thema');
define('_MD_ATTACHSIG', 'Signatur anhängen');
define('_MD_POST', 'Veröffentlichen');
define('_MD_SUBMIT', 'Abschicken');
define('_MD_CANCELPOST', 'Veröffentlichung abbrechen');
define('_MD_REMOVE', 'Löschen');
define('_MD_UPLOAD', 'Hochladen');

// forumuserpost.php
define('_MD_ADD', 'Hinzufügen');
define('_MD_REPLY', 'Antworten');

// topicmanager.php
define('_MD_VIEWTHETOPIC', 'Thema anschauen');
define('_MD_RETURNTOTHEFORUM', 'Zurück zum Forum');
define('_MD_RETURNFORUMINDEX', 'Zurück zum Forenindex');
define('_MD_ERROR_BACK', 'Fehler, bitte zurück und erneut versuchen.');
define('_MD_GOTONEWFORUM', 'Anschauen des aktualisierten Themas');
define('_MD_TOPICDELETE', 'Thema wurde gelöscht.');
define('_MD_TOPICMERGE', 'Thema wurde verbunden.');
define('_MD_TOPICMOVE', 'Thema wurde verschoben');
define('_MD_TOPICLOCK', 'Thema wurde geschlossen');
define('_MD_TOPICUNLOCK', 'Thema wurde geöffnet');
define('_MD_TOPICSTICKY', 'Thema wurde gepinnt');
define('_MD_TOPICUNSTICKY', 'Thema wurde entpinnt');
define('_MD_TOPICDIGEST', 'Thema wurde zusammengefasst');
define('_MD_TOPICUNDIGEST', 'Zusammenfassung des Themas wurde aufgehoben.');
define('_MD_DELETE', 'Löschen');
define('_MD_MOVE', 'Verschieben');
define('_MD_MERGE', 'Verbinden');
define('_MD_LOCK', 'Sperren');
define('_MD_UNLOCK', 'Entsperren');
define('_MD_STICKY', 'Pinnen');
define('_MD_UNSTICKY', 'Entpinnen');
define('_MD_DIGEST', 'Zusammenfassung');
define('_MD_UNDIGEST', 'Zusammenfassung aufheben');
define('_MD_DESC_DELETE', 'Wirklich das Thema und alle damit verbundenen Beiträge <b>endgültig löschen?</b>');
define('_MD_DESC_MOVE', 'Sicher, das Thema und alle damit verbundenen Beiträge in das ausgewählte Forum zu verschieben.');
define('_MD_DESC_MERGE', 'Sicher, das Thema und alle damit verbundenen Beiträge mit dem ausgewählten Thema zu verbinden.<br /><strong>Die ID des Zielthemas muss kleiner sein, als die des zu verbindenden Themas</strong>.');
define('_MD_DESC_LOCK', 'Sicher, das ausgewählte Thema zu sperren? Es kann es zu einem späteren Zeitpunkt wieder entsperrt werden.');
define('_MD_DESC_UNLOCK', 'Sicher, das ausgewählte Thema zu entsperren? Es kann zu einem späteren Zeitpunkt wieder gesperrt werden.');
define('_MD_DESC_STICKY', 'Sicher, das ausgewählte Thema zu pinnen? Dies kann zu einem späteren Zeitpunkt wieder aufgehoben werden.');
define('_MD_DESC_UNSTICKY', 'Sicher, die Markierung (gepinnt) des ausgewählten Themas wieder aufzugeheben? Es kann zu einem späteren Zeitpunkt wieder markiert (gepinnt) werden.');
define('_MD_DESC_DIGEST', 'Sicher, das ausgewählte Thema zusammenzufassen? Die Zusammenfassung kann zu einem späteren Zeitpunkt wieder aufgehoben werden.');
define('_MD_DESC_UNDIGEST', 'Sicher, die Zusammenfassung des ausgewählten Themas wieder aufzugeheben? Das Thema kann zu einem späteren Zeitpunkt wieder zusammengefasst werden.');
define('_MD_MERGETOPICTO', 'Verbinde Thema mit:');
define('_MD_MOVETOPICTO', 'Verschiebe Thema nach:');
define('_MD_NOFORUMINDB', 'Es ist kein Forum in der Datenbank vorhanden.');

// delete.php
define('_MD_DELNOTALLOWED', 'Keine Rechte vorhanden um diesen Beitrag zu löschen.');
define('_MD_AREUSUREDEL', 'Wirklich sicher, dass dieser Beitrag und alle damit verbundenen Antworten gelöscht werden soll?');
define('_MD_POSTSDELETED', 'Ausgewählter Beitrag und die damit verbundenen Antworten wurden erfolgreich gelöscht.');
define('_MD_POSTDELETED', 'Ausgewählter Beitrag wurde gelöscht.');
define('_MD_POSTFIRSTWITHREPLYNODELETED', 'Das Startposting kann nicht gelöscht werden, wenn schon Antworten da sind<br />Löschen Sie dazu das ganze Thema.');

// definitions moved from global.
define('_MD_THREAD', 'Diskussion');
define('_MD_FROM', 'Aus:');
define('_MD_JOINED', 'Registriert seit');
define('_MD_ONLINE', 'Online');
define('_MD_OFFLINE', 'Offline');
define('_MD_FLAT', 'Flach');

// online.php
define('_MD_USERS_ONLINE', 'Besucher online:');
define('_MD_ANONYMOUS_USERS', 'Anonyme(r)');
define('_MD_REGISTERED_USERS', 'Mitglied(er): ');
define('_MD_BROWSING_FORUM', 'Besucher sind im Forum');
define('_MD_TOTAL_ONLINE', 'Insgesamt %d Besucher online.');
define('_MD_ADMINISTRATOR', 'Administrator');
define('_MD_NO_SUCH_FILE', 'Datei existiert nicht!');
//define('_MD_ERROR_UPATEATTACHMENT','Ein Fehler ist beim Aktualisieren der Dateianhänge aufgetreten');

// ratethread.php
define("_MD_CANTVOTEOWN", "Es darf nicht für die eigenen Themen abgestimmt werden..<br />Alle Stimmen werden aufgezeichnet und überprüft.");
define("_MD_VOTEONCE", "Bitte nicht mehrfach für das gleiche Thema abstimmen.");
define("_MD_VOTEAPPRE", "Ihre Bewertung ist willkommen.");
define("_MD_THANKYOU", "Danke, für den Zeitaufwand auf %s abzustimmen."); // %s is your site name
define("_MD_VOTES", "Stimmen");
define("_MD_NOVOTERATE", "Dieses Thema noch nicht bewertet.");

// polls.php
define("_MD_POLL_DBUPDATED", "Datenbank wurde erfolgreich aktualisiert!");
define("_MD_POLL_POLLCONF", "Umfragekonfiguration");
define("_MD_POLL_POLLSLIST", "Umfragenliste");
define("_MD_POLL_AUTHOR", "Aut|| exitser Umfrage");
define("_MD_POLL_DISPLAYBLOCK", "Im Block anzeigen?");
define("_MD_POLL_POLLQUESTION", "Umfragetitel");
define("_MD_POLL_VOTERS", "Insgesamt haben abgestimmt");
define("_MD_POLL_VOTES", "Stimmen insgesamt");
define("_MD_POLL_EXPIRATION", "Ablaufdatum");
define("_MD_POLL_EXPIRED", "Abgelaufen");
define("_MD_POLL_VIEWLOG", "Logs anzeigen");
define("_MD_POLL_CREATNEWPOLL", "Neue Umfrage erstellen");
define("_MD_POLL_POLLDESC", "Umfragebeschreibung");
define("_MD_POLL_DISPLAYORDER", "Position");
define("_MD_POLL_ALLOWMULTI", "Darf mehr als eine Stimme abgegeben werden?");
define("_MD_POLL_NOTIFY", "Den Umfrageautor nach Ablauf der Umfrage benachrichtigen?");
define("_MD_POLL_POLLOPTIONS", "Optionen");
define("_MD_POLL_EDITPOLL", "Umfrage bearbeiten");
define("_MD_POLL_FORMAT", "Format: yyyy-mm-dd hh:mm:ss");
define("_MD_POLL_CURRENTTIME", "Aktuelle Uhrzeit ist %s");
define("_MD_POLL_EXPIREDAT", "Abgelaufen am %s");
define("_MD_POLL_RESTART", "Diese Umfrage neu starten");
define("_MD_POLL_ADDMORE", "Mehr Optionen hinzufügen");
define("_MD_POLL_RUSUREDEL", "Sicher das diese Abstimmung und alle damit verbundenen Kommentare gelöscht werden sollen?");
define("_MD_POLL_RESTARTPOLL", "Umfrage neu starten");
define("_MD_POLL_RESET", "Neustarten der Logs für diese Umfrage?");
define("_MD_POLL_ADDPOLL", "Umfrage hinzufügen");
define("_MD_POLLMODULE_ERROR", "Das Umfrage Modul steht nicht zur Verfügung");

//report.php
define("_MD_REPORTED", "Danke für die Meldung dieses Beitrags/Themas! Ein Moderator wird sich in Kürze dieser Meldung annehmen.");
define("_MD_REPORT_ERROR", "Ein Fehler ist aufgetreten beim Versenden der Meldung.");
define("_MD_REPORT_TEXT", "Meldungstext:");
define("_MD_PDF", "Erstelle PDF des Beitrags");
define("_MD_PDF_PAGE", "Seite %s");

//print.php
define("_MD_COMEFROM", "Dieser Beitrag stammt von:");

//viewpost.php
define("_MD_VIEWALLPOSTS", "Alle Beiträge");
define("_MD_VIEWTOPIC", "Thema");
define("_MD_VIEWFORUM", "Forum");
define("_MD_COMPACT", "Kompakt");
define("_MD_MENU_SELECT", "Menüanzeige = Auswahl");
define("_MD_MENU_HOVER", "Menüanzeige = hover");
define("_MD_MENU_CLICK", "Menüanzeige = klick");
define("_MD_WELCOME_SUBJECT", "%s hat das Forum zum ersten mal betreten");
define("_MD_WELCOME_MESSAGE", "Hallo %s,<br />herzlich willkommen im Forum. An dieser Stelle nur eine kleine Anmerkung: Um die Anzahl der doppelten Einträge gering zu halten, ist es wünschenswert das vorher die Forumsuche verwendet wird, bevor eine Frage gestellt wird.<br /><br />");
define("_MD_VIEWNEWPOSTS", "Neue Beiträge zeigen");
define("_MD_INVALID_SUBMIT", "Fehler, bitte eine Kopie des Textes anfertigen und nochmals versuchen.");
define("_MD_ACCOUNT", "Profil");
define("_MD_NAME", "Name");
define("_MD_PASSWORD", "Passwort");
define("_MD_LOGIN", "Login");
define("_MD_APPROVE", "Genehmigen");
define("_MD_RESTORE", "Wiederherstellen");
define("_MD_SPLIT_ONE", "Teilen");
define("_MD_SPLIT_TREE", "zusammen gehörende teilen");
define("_MD_SPLIT_ALL", "Alle teilen");
define("_MD_TYPE_ADMIN", "Moderation");
define("_MD_TYPE_VIEW", "Forensicht");
define("_MD_TYPE_PENDING", "Wartend");
define("_MD_TYPE_DELETED", "Gelöscht");
define("_MD_TYPE_SUSPEND", "Usersperre");
define("_MD_DBUPDATED", "Datenbank Update war erfolgreich!");
define("_MD_SUSPEND_SUBJECT", "Benutzer %s ist für %d Tage gesperrt");
define("_MD_SUSPEND_TEXT", "Benutzer %s ist für %d Tage gesperrt. Grund:<br />[quote]%s[/quote]<br /><br />Die Sperrung ist bis %s gültig");
define("_MD_SUSPEND_UID", "Benutzer ID");
define("_MD_SUSPEND_IP", "IP Segmente (voll oder nur Segmente)");
define("_MD_SUSPEND_DURATION", "Dauer der Sperrung (Tage)");
define("_MD_SUSPEND_DESC", "Grund der Sperrung");
define("_MD_SUSPEND_LIST", "Liste der Sperrung");
define("_MD_SUSPEND_START", "Start");
define("_MD_SUSPEND_EXPIRE", "Ende");
define("_MD_SUSPEND_SCOPE", "Bereich");
define("_MD_SUSPEND_MANAGEMENT", "User Moderation");
define("_MD_SUSPEND_NOACCESS", "Zutritt verboten. Die ID oder IP wurde gesperrt");
define("_MD_NEWBB_TYPE", "Thementyp");
define("_MD_NEWBB_SEENOTGUEST", "<font color=\"red\"><b>Link nur für registrierte User sichtbar</b></font>");
define("_MD_NEWBB_REPORTSUBJECT", "Ein Beitrag wurde gemeldet");
define("_MD_NEWBB_GOTOLASTPOST", "Gehe zum letzten Posting");
define("_MD_EDITEDMSG", "Grund (optional):");
define("_MD_DELEDEDMSG", "Grund des Löschens<br /><small>(Wird ein Grund angegeben wird der User benachrichtigt)</small>:");
define("_MD_DELEDEDMSG_SUBJECT", "Löschung deines Beitrages");
define("_MD_DELEDEDMSG_BODY", "Hallo %s,


deinen Beitrag im Forenthema
%s
wurde durch mich gelöscht
Als Begründung erlaube ich mir folgendes mitzuteilen:



%s

Mit bestem Gruss

%s
-------------------------
Bitte antworte nicht auf diese Nachricht!

%s
%s");
define("_MD_FORUMHOME", "Forenübersicht");
define("_MD_NEWBB_SEEWAITREPORT", "<font color=\"red\">Es wurden <b>%s</b> Beiträge gemeldet</font>");
define('NEWBB_PDF_SUBJECT', 'Titel: ');
define('NEWBB_PDF_TOPIC', 'Beitrag: ');
define('NEWBB_PDF_AUTHOR', 'Autor: ');
define('NEWBB_PDF_DATE', 'Datum: ');
define('NEWBB_PDF_URL', 'Link zum Beitrag: ');
define('_NW_PAGE', 'Seite: ');
define('_AM_NEWBB_NOTOPIC', 'keine Beiträge vorhanden');
define('_MD_NORSS_DATA', 'keine Daten zum Anzeigen');
define('_MD_NEWBB_STATS', 'Statistik');
define("_MD_POSTTIME", "gepostet am");

// 4.2
define("_MD_ADVERTISING_BLOCK", "<br />Hier könnte auch Ihre Werbung stehen!<br />Kontaktieren Sie uns dazu und wir unterbreiten Ihnen ein Angebot.");
define("_MD_ADVERTISING_USER", "Werbung");
define('_MD_SHARE_FACEBOOK', "Facebook");
define('_MD_SHARE_TWITTER', "Twitter");
define('_MD_SHARE_GOOGLEPLUS', "Google Plus");
define('_MD_SHARE_LINKEDIN', "Linkedin");
define('_MD_SHARE_STUMBLEUPON', "Stumbleupon");
define('_MD_SHARE_FRIENDFEED', "FriendFeed");
define('_MD_SHARE_REDDIT', "Reddit");
define('_MD_SHARE_DELICIOUS', "Del.icio.us");
define('_MD_SHARE_DIGG', "Digg");
define('_MD_SHARE_TECHNORATI', "Technorati");
define('_MD_SHARE_MRWONG', "Mr. Wong");

// 4.3
define("_MD_GO", "Los");
define('_MD_NEWBB_SEEUSERDATA', 'Benutzerinformationen');
define('_MD_NEWBB_MAXKB', 'Datei ist zu groß (max. %s Kb möglich).');
define('_MD_NEWBB_UPLOAD_ERRNODEF', 'unbekannter Fehler');
define('_MD_NEWBB_MAXUPLOADFILEINI', 'Die hochgeladene Datei überschreitet die in der Anweisung upload_max_filesize in php.ini festgelegte Größe.');
define('_MD_NEWBB_MAXPIC', 'bei Bildern beträgt die max. Größe %s X %s Pixel.');
define('_MD_NEWBB_SEARCHDISABLED', 'Die Suche ist deaktiviert und kann nicht genutzt werden.');

// irmtfan added messages
define('_MD_NEWBB_HIDEUSERDATA', "Verstecke Benutzerinformationen");
define('_MD_NEWBB_HIDE', "Verstecke");
define('_MD_NEWBB_SEE', "Zeige");
// votepolls.php - irmtfan
define('_MD_POLL_NOOPTION', "Bitte eine Option wählen!!");
define('_MD_SEARCHTOPIC', "Search Topic");
define('_MD_SHOWSEARCH', "Zeige das Egebnis:");
define('_MD_SEARCHPOSTTEXT', "Posts text");
define('_MD_SELECT_STARTLAG', "Start lag of selected text");
define('_MD_SELECT_STARTLAG_DESC', "Select text from X characters before the first keyword");
define('_MD_SELECT_LENGTH', "Length of selected text");
define('_MD_SELECT_HTML', "Strip all html from result?");
define('_MD_SELECT_EXCLUDE', "Exclude these tags:");
define('_MD_SELECT_TAG', "Tag");
define('_MD_CAN_PDF', "Du <strong>kannst</strong> PDF erstellen.<br />");
define('_MD_CANNOT_PDF', "Du <strong>kannst kein</strong> PDF erstellen.<br />");
define('_MD_CAN_PRINT', "Du <strong>kannst</strong> Seiten drucken.<br />");
define('_MD_CANNOT_PRINT', "Du <strong>kannst keine</strong> Seiten drucken.<br />");
define('_MD_NORIGHTTOPDF', "You don\'t have the right to create pdf in this forum.");
define('_MD_NORIGHTTOPRINT', "You don\'t have the right to get print in this forum.");
// irmtfan for new block system
define('_MD_TOPICHASNOTPOLL', "Topic hasnot poll");
define('_MD_VOTED', "Voted topics");
define('_MD_UNVOTED', "Unvoted topics");
define('_MD_VIEWED', "Viewed topics");
define('_MD_UNVIEWED', "Unviewed topics");
define('_MD_REPLIED', "Replied topics");
define('_MD_READ', "Read topics");
define('_MD_POLL_POLL', "Umfrage");
define('_MD_PAGENAV_DISPLAY', "Display of navigation");