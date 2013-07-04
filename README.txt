	Installation

First, establish the correct place in the moodle code 'tree' for the plugin.

Advance course search can be found in 

Administration->course->Advance Course search

Download the zip file

Upload or copy it to your Moodle server

Unzip it in the right place for the plugin type (or follow add-on instructions /admin/tool. )

In your Moodle site (as admin) go to Settings > Site administration > Notifications (you should, for most plugin types, get a message saying the add-on is installed)

Test the add-on

Note: The add-on may contain language files. They'll be found by your Moodle automatically. These language strings can be customized using the standard Settings > Site administration > Language editing interface.
If you have problems...

Check the file permissions. The web server needs to be able to read the plugin files. If the the rest of Moodle works then try to make the plugin permissions and ownership match.

Did you definitely unzip or install the add-on in the correct place?
Because Moodle scans plugin folders for new plugins you cannot have any other files or folders there. Make sure you deleted the zip file and don't try to rename (for example) an old version of the plugin to some other name - it will break.
Make sure the directory name for the plugin is correct. All the names have to match. If you change the name then it won't work.

Turn on Debugging - any error messages?

Ask in the appropriate forum in Using Moodle. Make sure you describe your system (including versions of MySQL, PHP etc.), what you tried and what happened. Copy and paste error messages exactly. Provide the link to the version of the add-on you downloaded (some have very similar names).

for more information please look over the moodle documentation:-

http://docs.moodle.org/23/en/Installing_plugins