<h1>Goram & Vincent</h1>

<hr/>
<h2>Coding Challenge for Ecancer</h2>

<ul>
    <li><a href="#setting-up">Setting up</a></li>
    <li><a href="#challenge">The Challenge</a></li>
    <li><a href="#code-and-layout">Code &amp; Layout</a></li>
    <li><a href="#bugs">Bugs &amp; Fixes</a></li>
    <li><a href="#delivery">Delivering Your Solution</a></li>
    <li>
        <a href="#tasks">Tasks</a>
        <ul>
            <li><a href="#task-1">Task 1</a></li>
            <li><a href="#task-2">Task 2</a></li>
        </ul>
    </li>
</ul>

<hr>

<h2><a name="setting-up">Setting up</a></h2>

To setup this code base you will need to:
<ol>
    <li>Checkout the <a href="https://github.com/enable/coding-challenge/tree/develop">develop</a> branch</li>
    <li>Install the <a href="http://getcomposer.org">composer</a> dependencies</li>
    <li>Setup your database credentials for migrations
        <ul>
            <li>This should be setup in the <a href="https://github.com/enable/coding-challenge/blob/develop/phinx.yml">phinx.yml</a> file</li>
        </ul>
    </li>
    <li>Setup your database credentials for the app/website
        <ul>
            <li>this will be in <a href="https://github.com/enable/coding-challenge/blob/develop/site/ini/database.ini">site/ini/database.ini</a></li>
        </ul>
    </li>
    <li>
        Ensure write permissions set on <em>site/cache/</em>
        <ul>
            <li>this should be 777 - <em>chmod -R 777 site/cache</em></li>
        </ul>
    </li>
    <li>
        Create your blank/empty database - you can use whatever you like! Try - <a href="http://www.phpmyadmin.net/home_page/index.php">phpMyAdmin</a>.
        <ul>
            <li>the system <em>won't</em> create it for you!</li>
        </ul>
    </li>
    <li>
        Run the migrations
        <ul>
            <li>we use <a href="http://docs.phinx.org/en/latest/commands.html#the-migrate-command">phinx</a> to keep our databases in-sync</li>
            <li>phinx command should be: <em>vendor/bin/phinx migrate -e development</em></li>
        </ul>
    </li>
    <li>
        Setup a virtual host in Apache or MAMP/XAMPP
        <ul>
            <li>you will need <em>AllowOverride All</em> on to allow our <a href="http://httpd.apache.org/docs/2.2/howto/htaccess.html">.htaccess</a> rules to take effect</li>
            <li>you will also need Mod Rewrite enabled in Apache.</li>
            <li>Virtual Host configuration should be:<br>
<pre>
&lt;VirtualHost *:80&gt;
  ServerName gv_challenge.local
  DocumentRoot /SomeWhere/OnYourLaptopOrPc
  &lt;Directory /SomeWhere/OnYourLaptopOrPc&gt;
    AllowOverride All
  &lt;/Directory&gt;
&lt;/VirtualHost&gt;
</pre>
            </li>
        </ul>
    </li>
    <li>
        Add the new domain name in to your system's host file.
        <ul>
            <li>Unix Systems:<br>
                You'll need to <strong>sudo vim /etc/hosts</strong>
                <ul>
                    <li>On linux systems - /etc/hosts</li>
                    <li>On Mac OS X systems - /private/etc/hosts</li>
                </ul>
            </li>
            <li>On Windows system - C:\Windows\system32\etc\drivers\hosts</li>
            <li>There is a <em><a href="http://www.rackspace.com/knowledge_center/article/how-do-i-modify-my-hosts-file">Guide</a></em> here.</li>
        </ul>
    </li>
</ol>

<hr>

<h2><a name="challenge">The Challenge</a></h2>

<p>This challenge is based upon elements of the legacy codebase you will be working on from time to time.</p>

<p>The framework itself is quite big and complicated so we have only ported over a few of the bare essential classes.</p>

<p>We will be looking to see your approach to solving the problems and how confident you are working with the PHP code.</p>

<p>Ideally you should try and work within the constraints of the existing codebase and framework. But we are open-minded to other solutions and libraries that may compliment what we have already.</p>

<hr>

<h2><a name="code-and-layout">The Code & Layout</a></h2>

<p>Majority of the logic should live in the <em>includes/</em> directory.</p>

<p>From the <em>includes/</em> directory you should see:</p>
<ul>
    <li>a <em>core/</em> directory - this contains the core framework</li>
    <li>a <em>site-core/</em> directory - this contains your classes and user classes</li>
</ul>

<p>In the <em>migrations/</em> directory we have loaded in our test data using the <a href="http://docs.phinx.org/en/latest">phinx</a> library. This library can be fetched using <a href="http://getcomposer.org/">composer</a>.</p>

<p>All configuration should live in the <em>site/ini/</em> directory. You will need to add your database credentials to the <em>site/ini/database.</em> file - under the <em>development</em> heading.</p>

<p>The <em>site/</em> directory also contains images and cache folders. The video thumbnails are in the <em>site/cache/development/images/</em> directory. You will need to give write permissions to the <em>site/cache/</em> directory.</p>

<p>All view/template logic should live in the <em>includes/site-core/display/</em> directory. These templates should get called from their controller.</p>

<hr>

<h3><a name="bugs">Bugs</a></h3>

<p>As we said this is a legacy codebase, and we've tried to port across the bare essentials of the codebase.</p>

<p>If you receive lots of debug messages or error messages and you are sure your code is working, you can disable the
custom error handler by setting <em>Debug = 0</em> in <em>site/ini/debug.ini</em>.</p>

<p>If you do fix any bugs please let us know what you've fixed.</p>

<hr>


<h3><a name="delivery">Delivery</a></h3>

<p>We don't mind how we get your code...</p>

<ul>
    <li>If you forked this repository you can send in a <em>pull-request</em> on github.</li>
    <li>If we've sent you this challenge as a Zip file or directly:
        <ul>
            <li>you can send it back to us as a Zip via email.</li>
            <li>you can send us a <a href="http://getdropbox.com">Dropbox</a> link.</li>
        </ul>
    </li>
</ul>

<hr>

<h2><a name="tasks">The Tasks</a></h2>

<h3><a name="task-1">Task 1</a></h3>

<p>Make modifications to the existing Videos homepage.</p>

<strong>Task 1a</strong>

<p>Order the Videos in the table by the PublicationDate column.</p>

<p>See: <em>includes/site-core/classes/controllers/class_controller_videos.php</em></p>

<strong>Task 1b</strong>

<p>Use your own judgement and add more Video data from the database to the HTML table on the Video Homepage.</p>


<h3><a name="task-2">Task 2</a></h3>

<p>Create a new Video Gallery page that displays 5 of the most recent video's thumbnails.</p>

<strong>Tips:</strong><br>

<p>To do this you will need to use the <em>siteVideoToImage</em> link table, this database query is already coded for you.</p>

<p>To create the thumbnail you'll need to look at the <em>clsImage</em> class. You should use the <em>funFetch</em> method.</p>
