# moodle-mod_literature (alpha)


#### A literature plugin for moodle

This plugin adds basic bibliographic support to moodle and helps users to manage literature for their courses.

It enables users to:

+ search literature in multiple searchsources
+ maintain lists of literature
+ publish literature to a course
+ import/export literature in multiple formats


### Installation

First you have to install the basic literature plugin.

<pre>cd $MOODLE_ROOT
git clone git@github.com:streldev/moodle-mod_literature.git mod/literature
</pre>

To fully integrate the literature plugin in your moodle instance, you should now install an additional local plugin. It integrates the literature plugin in the navigation tree.

<pre>cd $MOODLE_ROOT
git clone git@github.com:streldev/moodle-local_litnav.git local/litnav
</pre>

Thats it. Now login with your admin account and install the plugins. The literatuer plugin comes with some bundled sub plugins for search and export/import. They get installed along with the main 'core'. For further details see section *Sub-Plugins*.


### Configuration

Congrats. You should now have an instance of the literature plugin up and running.
To search for literature and get some nice book covers shown, you have to complete some basic configuration first.

#### Add searchsources

First you should add a searchsource. Login with your admin account and look for the **Literature** entry in the main navigation. Search for the entry **Manage Sources** and click on it. Now select **Add** in the section *Actions* and press **OK**. A dropdown menue with all installed types of sources is what you get. Select the type of source you want to add. Each type needs some configuration.

###### SRU

+ **Name:** The name of the source. Gets displayed to the users.
+ **Server:** The SRU-Server followed by the DB. Click on the tooltip for an example.


###### OPAC XML

+ **Name:** The name of the source. Gets displayed to the users.
+ **Server:** The OPAC-Server with the XML-Interface. Click on the tooltip for an example.


#### Configure the google enricher (optional)

The google enricher brings some beauty to the search results. It looks for a nice and shiny cover and adds it to each result. To activate this feature you have to naviagte to the plugins settings page via the *Site administration*. Generate a [Google API key](https://code.google.com/apis/console) for google books and insert it into the *Google API Key* field on the settings page. After you inserted the key just activate the *Google Enricher* checkbox and you are done.


### Support

You just found a bug? You need some help? You wanna help?

Just open a new issue, send a pull request or use the wiki on [GitHub](https://github.com/streldev/moodle-mod_literature). Any help is very much appreciated :-)

### Documentation

A detailed documentation for developers is in progress and will be published soon [here](https://github.com/streldev/moodle-mod_literature/wiki). If you don't want to or can't wait, just contact me or open an issue.

