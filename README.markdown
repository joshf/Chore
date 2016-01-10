Chore Readme
========================

Chore is a simple app to help you remember things. The app offers the ability to assign categories, due dates and different priorities to your items.

Features:
---------

* Assign categories
* Works well on mobile devices due to a responsive layout
* Add due dates
* Overdue or items due today are highlighted
* Sorting
* Search items
* Add items via an API

Donations:
------------

If you like Chore and appreciate my hard work a [donation](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=UYWJXFX6M4ADW) (no matter how small) would be appreciated. I code in my spare time and make no money formally from my scripts.

Screenshots:
------------

Coming soon

Releases:
------------

Releases of Chore can be found on the the [releases page](https://github.com/joshf/Chore/releases).

Installation:
-------------

1. Create a new database using your web hosts control panel (for instructions on how to do this please contact your web host)
2. Download and unzip Chore-xxxx.zip
3. Upload the Chore folder to your server via FTP or your hosts control panel
4. Open up http://yoursite.com/Chore/install in your browser and enter your database/user details
5. Delete the "install" folder from your server
6. Login to Chore using the username and password you set during the install process
7. Add your items
8. Chore should now be set up

Usage:
------

To add new items click the "+" icon in the right hand corner.

To edit items click the name of the item and you will be able to edit the different fields by double clicking.

To mark an item as done click the tick icon on the right of each items

Different filters can be applied using the dropdown selector near the top. You can also search items from here


Updating:
---------

1. Before performing an update please make sure you backup your database
2. Download your config.php file (in the Chore folder) via FTP or your hosts control panel
3. Delete the Chore folder off your server
4. Download the latest version of Chore from [here](https://github.com/joshf/Chore/releases)
5. Unzip the file
6. Upload the unzipped Chore folder to your server via FTP or your hosts control panel
7. Upload your config.php file into the Chore folder
4. Open up http://yoursite.com/Chore/install/upgrade.php in your browser and the upgrade process will start
9. You should now have the latest version of Chore

N.B: The upgrade will only upgrade from the previous version of Chore (e.g 0.1 to 0.2), it cannot be used to upgrade from a historic version.

Removal:
--------

To remove Chore, simply delete the Chore folder from your server and delete the "items" and "users" tables from your database. Then delete the "Chore" folder via FTP or your hosts control panel. 

Support:
-------------

For help and support post an issue on [GitHub](https://github.com/joshf/Chore/issues).

Contributing:
-------------

Feel free to fork and make any changes you want to Chore. If you want them to be added to master then send a pull request via GitHub.
