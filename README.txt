INSTALLATION
------------

1. Backup your DB. This contribution doesn't make any important change on the DB, 
but it is always a VERY good idea to backup your DB before you make any change to it.

2. Have you got your backup files at hand and checked?

3. Upload the files in /admin to your correspondent folders inside your admin folder. 

4. Upload the files in the /catalog folder to your correspondent folders inside your catalog folder.

5. Go to your admin site and to Catalog > Good Search Zen.

6. Click on Install, and then on Active if neccessary.


This little contribution works by adding two fulltext indexes 
to the products_description and products_name columns 
on the products_description table.
These indexes are dropped if you uninstall this contribution



UNINSTALLATION
--------------

1. Backup your DB

2. Go to your admin site, then to Catalog > Good Search Zen

3. Click on Uninstall. This will clean the database changes.

4. You will also be given a list of the files you need to remove.
