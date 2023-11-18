# Library Database Project

## User Documentation

This documentation is aimed at library staff, not patrons. Patron access should be simple enough to be self-explanatory.

### Terminology

* "record" and "table" are essentially interchangelable. _A database table has certain fields (columns). Each row in the table is a record._
* Bib record = title record
* Holdings = table that holds the copies of each book (title)
* PURF = patron unresolved file.  This keeps track of all outstanding issues for the patron, eg. fines, validate address, etc. These need to be addressed before the patron can sign out any more materials.
* xxxx.his  the .his files (purf.his  patron.his) are history files, where things go when they are deleted. When a patron wants to cancel his account, his record is moved to the patron.his file.  When the issue in the purf file is resolved, that issue is moved to purf.his.

* Note that for privacy, we do not keep a history of materials that users take out. We only record the current user and the previous one.

### Library Cards
-- see Patron
### Patron
The patron screen allows you to search for a patron by firstname, lastname, phone number or barcode.  
Use birthdate and postal code to distinguish two patrons with the same name.

The patron screen displays name, birthdate and the date that the patron was registered on the system. 
It also shows address information and contact information. Contact information is not mandatory: patrons do not need to have a phone or email address.
A street address is mandatory though.

The bottom part of the screen shows the patron's **library card(s).**  These can have various statuses: active, expired, lost. You can change the status here.
The appropriate buttons will show up (eg. you cannot set a lost card to lost since it's already lost).    
⭐ In particular, cards can be renewed here. The new expiry date is one year from today.
If a patron does not have a card, one can be added here.
