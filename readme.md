**Unofficial version of BP-Privacy**

Updated to work for BuddyPress 1.7.  Should work for v1.6.x as well.

***

**Changelog**
* Add messaging privacy capability missing in 1.0-RC1
* Add WP Toolbar support
* Use WP pages for privacy pages (privacy policy, maintenance mode, landing page for non-logged-in users)
* Major code clean up

***

**Audit by r-a-y**

_Activity_
* Activity filtering is done *after* the activity loop is generated, which isn't great
     * Example: If 15 activity items are supposed to be shown and BP-Privacy filters 12 of these items from view, only 3 items will be displayed.  This also means that activity pagination will be messed up.
     * Filtering should be done at the DB query level, but this could be complicated (there's a reason why Facebook does not show a sitewide feed!)
* Plugins can hook into BP-Privacy's activity filtering by registering their activity actions with BuddyPress.  Jeff is pretty awesome.
     * Example: See how BuddyPress does this for the activity component here: https://buddypress.trac.wordpress.org/browser/tags/1.7-rc1/bp-activity/bp-activity-functions.php#L834
* One area to watch out for is how the 'item_id' is generated for activity actions.  Read what Jeff has to say about this in the PDF manual (section 3d).  Basically, activity actions are converted into a numeric ID to fit into BP-Privacy's DB schema.  Need to look into this a little more if we're to filter activities at the DB query level.

_UI_
* When filtering by "These Users Only", this shows a list of all available members on the site.  This is a privacy catch-22!
     * Preferably, we should use an AJAX autocomplete box.
     * Perhaps this filtering option isn't even needed as using either the "Friends" or "Members of These Groups" option should be sufficient.