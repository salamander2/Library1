# User Documentation

There are 4 access levels. Each level is able to perform all of the tasks of lower levels.
* **admin**  The admin user is the only one who can add users and reset staff passwords. This user can also change the message/announcement displayed on the main page.
* **staff**  Staff have full control over patron records, checkin/out, and bibliographic records.
* **patron** Patrons can log on and check their own information, see what books they have out, and place holds.
* **public** Public access catalogs can only search the bibliographic data.

# Organization of Data

* The Patron file has all of the patron data in it.
* The LibraryCard is keyed to the patron file. A patron can have multiple library cards (e.g. if some are lost)
* The Bib file has the title records for books.
* The Holdings file has the data for each copy of the book. It is linked to the Bib record. One Bib record will have many holdings normally.
The Holdings file records the cost and the status of the book. It records the libary card of the previous patron.
If the book is out, then it records the LIBRARY CARD barcode (not the Patron ID). It will also record checkout date and due date.

Most of these files also have a creation timestamp recorded.

-------------
