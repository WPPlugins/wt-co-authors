=== WT Co-authors ===
Tags: authors, links, plugin, posts, post
Requires at least: 2.8
Tested up to: 2.9
Stable tag: trunk

Using WordPress Custom Fields, and without editing any template files, lets you credit all authors on collaboration posts. (Recommended for team blogs). Now with a comfortable editing box, simply enter the usernames separated by commas.

== Description ==

Displays authors of a collaboration post in place where only one is shown by default. Uses custom fields (key=<code>coauthor</code>;value=user's username; one per user). Note: If it doesn't automatically display the co-authors you must manually add  `if(function_exists(wt_the_coauthors_link)): wt_the_coauthors_link(); endif;` into your theme (in PHP `<?php ?>` brackets, of course).

== Installation ==

1. Upload `wt-coathors.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. If your theme does not have `the_author()` template tag, manually add `if(function_exists(wt_the_coauthors_link)): wt_the_coauthors_link(); endif;` where you want it to appear (in PHP `<?php ?>` brackets, of course)
1. Finish!

== Changelog ==

= 2.0.1 =
* Bug fix

= 2.0 =
* Editors. Now you can credit them as well.
* Comfortable editing box on the 'Add post' page
* More power, because I never stop learning new tricks and techniques of coding

= 1.8 =
* Little filter enhancement, ensuring more compability

= 1.5 =
* Added manual template tags
* Now feeds display co-authors too
* Better linking: if the co-author has not published his own post yet (and thus has no author archieves), it is linking to his homepage
* Big function hack mistake fixed, no more `author_link` filtering
* Prepared for internationalization (translations)

= 1.1 =
* Handling of more than two co-authors fixed

= 1.0 =
* First release