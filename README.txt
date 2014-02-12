=== Advanced Course Search(Solr integration with moodle course schema) ===

 Introduction
---------------

The plugin is integration of moodle course search with the apache solr search platform.
Solr search can be used as a replacement for existing course search and boasts both
extra features and better performance. Among the extra features is the ability 
of being flexible, case-insensitive, works with non-latin languages, fast, auto complete suggestions,
facting and can sort results by relevance(score).

The module comes with a schema.xml, solrconfig.xml, and protwords.txt file which
must be used in your solr installation.


 Installation
---------------

Prerequisite: Java 5 or higher (a.k.a. 1.5.x), PHP 5.1.4 or higher, moodle 2.5 or higher.

Step 1:-  ==== Installing coursesearch theme that renders the search results / Placing the renderer file ====

Note:- You can try how the search results will look like by going to /course/example.php without installing coursesearch theme or modifying rederers.php. 


Here is two options either you may replace/copy your existing theme renderer with the one found in
search_coursesearch theme(https://github.com/shashirepo/moodle-theme_coursesearch theme). OR you may install coursesearch theme itself.

replacing /copying the renderer file to your theme.

1. Copy renderers.php file from search_coursesearch theme replace it with your theme's renderer file.

2. Standard moodle theme doesn't have any renderer file so you may simply copy this to your
   theme directory.

3. Rename renderer class name according to your theme name.

for example if you are using theme 'clean'.
then rename the class name to 'theme_clean_core_course_renderer'.

OR you may either use search_coursesearch theme. This is also based on clean theme.

1. Download the coursesearch theme from here (https://github.com/shashirepo/moodle-theme_cleantheme) 

2. Extract the theme folder. and put it under moodle installation theme directory.

3. If you are already logged in just refreshing the browser should trigger your Moodle
    site to begin the install 'Plugins Check'.
	
4. If not then navigate to Administration > Notifications.

Step 2:-  === Installing admin tool that manage solr configuration & options to index courses. ===

1. Download the admin tool from here. (https://github.com/shashirepo/moodle-tool_coursesearch) 

2. Extract the Course Search folder. Put it under moodle installation/admin/tool directory.

3. It should be named coursesearch. If you are already logged in just refreshing the browser should trigger your Moodle
    site to begin the install 'Plugins Check'.
	
4. If not then navigate to Administration > Notifications.

Step 3:-  ==== Installing Solr & placing the plugin Schema ===

Download the latest Solr 4.4.0 release from:
http://lucene.apache.org/solr/

Unpack the tarball somewhere not visible to the web (not in your apache docroot
and not inside of your moodle directory).

The Solr download comes with an example application that you can use for
testing, development, and even for smaller production sites. This
application is found at apache-solr-4.4.0/example.

Move apache-solr-4.4.0/example/solr/collection1/conf/schema.xml and rename it to
something like schema.bak. Then move the schema.xml that comes 
with moodle course search admin tool plugin to take its place.

Similarly, move apache-solr-4.4.0/example/solr/collection1/conf/solrconfig.xml and rename
it like solrconfig.bak. Then move the solrconfig.xml that comes with the
moodle course search admin tool plugin to take its place.

Finally, move apache-solr-4.4.0/example/solr/collection1/conf/protwords.txt and rename
it like protwords.bak. Then move the protwords.txt that comes with the
moodle course search admin tool plugin  to take its place.

Make sure that the conf directory includes the following files - the Solr core
may not load if you don't have at least an empty file present:
solrconfig.xml
schema.xml
elevate.xml
mapping-ISOLatin1Accent.txt
protwords.txt
stopwords.txt
synonyms.txt

Now start the solr application by opening a shell, changing directory to
apache-solr-4.4.0/example, and executing the command java -jar start.jar

Test that your solr server is now available by visiting
http://localhost:8983/solr/admin/

Step 4:- === Testing with ping to solr ===

1. Advance Course can be found under :-

Administration->course->Course search settings(URL:- http://127.0.0.1/MoodleInstalltionURL/admin/tool/coursesearch)

2. Give the solr configuration options here:-

Solr Host:- localhost or 127.0.0.1
Solr Port:- 8983 (Default port for Solr )
Solr path :- /solr (Configuration directory for solr)
Solr username :- if you're using authetication with solr. Give your http authetication username here.
Solr password :- Give your solr http authetication password here.

3. Click on "Check Solr instance Setting". if it Shows ping successful(with an success image). Now click save changes.

4. Now click on "Index courses" to index all the courses. After successful indexing. It will come up with a success image.

5. Click on "Optimize" to optimize the existing indexes And improve solr performance.


Enjoy the Search by going on page (http://127.0.0.1/MoodleInstallationURL/course/search.php)

to configure navigation with the course search page goto Site administration -> Front page -> Front page settings.

If you find any issue with plugin. You may either use tracker or github to report the bug(tracker.moodle.org/browse/CONTRIB-4335)

Some usefull links :-

General user documentation :- http://docs.moodle.org/25/en/Course_search
Github wiki :- https://github.com/shashirepo/moodle-tool_coursesearch/wiki
Tracker of the project :- http://tracker.moodle.org/browse/CONTRIB-4335
Weekly reports :- http://shashitechno.wordpress.com/tag/moodle/

Good luck, Thanks.