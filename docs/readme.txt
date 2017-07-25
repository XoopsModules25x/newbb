Newbb 4.3 guide

Requirements:
=========================
XOOPS 2.5.x php 5.2 mysql 5.0

To Install
=========================
1- close your website. (recommended) be sure you be logged in.
2- upload the newbb to /modules/newbb (upload the compressed file and decompressed via Cpanel is the best way to insure all files are correctly uploaded)
3- go to your admin -> system -> modules -> install
4- change the default settings to your desired in the module preferences and newbb/include/plugin.php
5- dont forget to open your website again.

To Upgrade from older newbb versions 1.x 2.x 3.0.x
==========================
1- close your website. (highly recommended) be sure you be logged in.
2- get a backup from your old newbb database.(all XOOPSPREFIX_bb_* tables)
3- get a backup from your old newbb/images directory to save your custom old images. Also get a backup from any changes you done in files.
4- IF EXIST get a backup from your old newbb/include/plugin.php
5- delete your old newbb folder located in modules (or rename it to newbb_old)
6- IF EXIST delete old newbb folder (templates) in htdocs/themes/default/modules/newbb AND htdocs/themes/YOUR_THEME/modules/newbb (or rename it to newbb_old)
7- upload the newbb to htdocs/modules/newbb (upload the compressed file and decompressed via Cpanel is the best way to insure all files are correctly uploaded)
8- go to your admin -> system -> modules -> newbb -> upgrade (important: wait until you see the report page)
9- go to system -> maintenance -> clear all caches
10- change the default settings to your desired in the module preferences and newbb/include/plugin.php and IF EXIST based on your old back-upped plugin.php. Set the permissions in newbb -> admin -> permission for all groups one by one. pay attention that webmasters group has all accesses regardless of permissions. using a non webmaster test account is recommended to test the permissions for each group.
11 - dont forget to open your website again.

Image set Full customization
==========================
In newbb 4.3 you can customize all images (like reply, edit, ... buttons and icons) for all themes or each theme one by one without touching the modules/newbb files.
The priority for reading images are as below:
 * IF EXISTS XOOPS_ROOT/themes/YOUR_THEME/modules/newbb/images/, TAKE IT;
 * ELSEIF EXISTS  XOOPS_ROOT/themes/default/modules/newbb/assets/images/, TAKE IT;
 * ELSE TAKE  XOOPS_ROOT/modules/newbb/templates/images/
The above means, if you want to customize images for all of your themes you should not touch the modules/newbb/templates/images/ folder.
Just upload the image set in XOOPS_ROOT/themes/default/modules/newbb/images and all of your themes will read it from default theme.
Then if you want to customize it for another specific theme you can upload the new image set in XOOPS_ROOT/themes/YOUR_THEME/modules/newbb/images
And you can follow the above for the other themes.

FAQ:
==========================
1- How do I set options like HTML and signature behind reply editor box?

for signature:
- In newbb the webmaster can set the permission so that different groups had the ability to use and disable/enable their signatures in different forums behind their posts. In other words, if the user set the attachsig option to No in its profile, the signature is disabled (unchecked) by default and can be enabled in specific posts, otherwise it is enabled (checked) and can be disabled.
- the default is defined in Profile module by webmaster for new registered users. in profile -> admin -> fields -> attachsig -> default -> Yes/No(default)
- registered users can change the above default in their profiles (if they have permission): in edit profile -> Always attach my signature -> Yes/No


for html:
- you can set the permission to allow different groups to use html in different forums.
- the default for allowed groups in allowed forums is hardcoded to enable (check box has always a tick) and user can disable it in each post.

for smilies, xoopscode and br:
- the default is hardcoded to enable (check box has always a tick) and user can disable it in each post.

2-  attachments  are only showed to registered users. where can i change it to show for guests?
in newbb -> admin -> preferences ->  Display attachments only for registered users -> yes(default)/no

3- where can I change the image buttons to text links?
Open newbb/include/plugin.php
change this line:
$customConfig["display_text_links"] = false;
to this:
$customConfig["display_text_links"] = true;

4- where can I change the reply and quick reply default editors to tinymce?
In newbb/include/plugin.php

5- Why i have not a JQuery redirect in some submits in newbb module like reply and new topic?
Please find and remove any newline/space before <?php or after ?> in your whole xoops php files.
Also find and remove any BOM in language utf-8 files in your whole xoops.

6- When i tried "Set permission directly by group" in newbb => admin => permissions i get a message "Sorry, you don't have the permission to access this area".
it is because of max_input_vars php config.
if you go to modules/system/admin in file error_log you can find this error:
 PHP Warning: Unknown: Input variables exceeded 1000. To increase the limit change max_input_vars in php.ini
you should increase max_input_vars like this.
creating a php.ini in the root of your website and enter this:
max_input_vars = 10000
readm more: https://xoops.org/modules/newbb/viewtopic.php?topic_id=75669
