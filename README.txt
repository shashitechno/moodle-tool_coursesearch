=== Advanced Course Search ===
This plugin integrates moodle Course Search with the Apache Solr search platform. Solr search
can be used as a replacement for existing course search and boasts both extra
features and better performance. Among the extra features is the ability 
of being flexible, case-insensitive, works with non-latin languages, fast, and can sort results by relevance(Score)

The module comes with a schema.xml, solrconfig.xml, and protwords.txt file which
must be used in your Solr installation.


Installation
------------

Prerequisite: Java 5 or higher (a.k.a. 1.5.x).  PHP 5.1.4 or higher.

Step 1:-  ==== Installing Cleantheme that randers the search results ====

1. Download the cleantheme from here (https://github.com/shashirepo/moodle-theme_cleantheme) 

2. Extract the theme folder. and put it under moodle installtion theme directory.

3. If you are already logged in just refreshing the browser should trigger your Moodle
    site to begin the install 'Plugins Check'.
	
4. If not then navigate to Administration > Notifications.

Step 2:-  ==== Installing Admin tool that manage Solr configuration and indexing.

1. Download the admin tool from here (https://github.com/shashirepo/moodle-tool_coursesearch) 

2. Extract the Course Search folder. and put it under moodle installtion /admin/tool directory.

3. If you are already logged in just refreshing the browser should trigger your Moodle
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

Step 4:- Testing With ping to Solr(Checking Config parameters of Solr)

1. Advance Course can be found under :-

Administration->course->Advance Course search(URL:- http://127.0.0.1/MoodleInstalltionURL/admin/tool/coursesearch)

2. You running under localhost than fill:-

Solr Host:- localhost
Solr Port:- 8983 (Defalut port for Solr )
Solr path :- /solr (Configuration directory for solr)

3. Click on "Check Solr instance Setting". if it Shows ping Succesfully(with an success image). Now Click save changes.

4. Now Click on "Load Content" to index all the Courses. After Succesfull index it will come with an success image.

5. Click on "Optimize" to Optimize the exsiting Indexes. And improve solr performance.


Enjoy the Search by going on page (http://127.0.0.1/MoodleInstallationURL/course/search.php)

The plugin is tested properly. But its under development. Please mail me if you found any issue. @Shashikantvaishnaw@gmail.com

Thanks :)
