ABOUT
-----

This is an small extension for Zen Cart to allow queries against fulltext indexes 
on the products_description table.

In layman words, this sorts the results of the catalog searches by RELEVANCE,
which I think is quite an improvement for the final user,
specially when searching shops with thousands of items which usually return
search results of many hundreds of items that are pretty unhelpful if 
sorted alphabetically, by price, etc with no relevance sorting.

This contribution was coded by AJ Justo, http://www.ajweb.eu, but the original idea 
was found in this post:
http://www.zen-cart.com/forum/showthread.php?t=131747&page=1
by madhouse (Rob).

This software was tested on three different servers
and on three different Zen Cart installations,
one of them a clean 1.3.9h install,  another a highly customized one
and a third with some 25,000 products, which needed a couple of minutes
to complete the installation (index creation) but otherwise run fine. 


HOW DOES IT WORK
----------------

If you do a search for "Olivia" this will be look up on the name and description 
fields and a logarithm will rank each found result dedending on the times "Olivia" appears and on its 
position in the full string.

So, you will no longer have an article appearing at the beginning of the results although the search 
term (Olivia) was only found one time in the description of the article, and an article named "Olivia" 
at the same time positioned on the third page of results. 

Once you install and activate the contribution, all the searches on the CATALOG site 
will return results sorted by relevance by default. The user will still have the option 
to re-sort the results by name, price, etc. if you had that option active.

You can activate and deactivate this funtionality on the admin site, plus also re-install, check it and uninstall it.


INSTALLATION
------------

NOTE: This needs a MySQL database to work (although it could be changed for other types). Check this first.
If you don't know what DB you are using you almost certainly are using MySQL.

1. Backup your DB. This contribution doesn't make any important change on the DB, 
but it is always a VERY good idea to backup your DB before you make any change to it.

2. Have you got your backup files at hand and checked?

3. Upload the files in /admin to your correspondent folders inside your admin folder. 

4. Upload the files in the /catalog folder to your correspondent folders inside your catalog folder.

5. Go to your admin site and to Catalog > Good Search Zen.

6. Click on Install, and then on Active if neccessary.


UNINSTALLATION
--------------

1. Backup your DB

2. Go to your admin site, then to Catalog > Good Search Zen

3. Click on Uninstall. This will clean the database changes.

4. You will also be given a list of the files you need to remove.


LICENCE
---------

Read the licence for details, but basically you can do whatever you want
with this code as long as you keep the copyright notices. 