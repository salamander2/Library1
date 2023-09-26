## USERS

```
mysql> describe users;
+-----------+--------------+------+-----+-------------------+-------+
| Field     | Type         | Null | Key | Default           | Extra |
+-----------+--------------+------+-----+-------------------+-------+
| username  | varchar(30)  | NO   | PRI | NULL              |       |
| fullname  | varchar(50)  | NO   |     | ---               |       |
| password  | varchar(255) | NO   |     | NULL              |       |
| lastLogin | timestamp    | NO   |     | CURRENT_TIMESTAMP |       |
+-----------+--------------+------+-----+-------------------+-------+
4 rows in set (0.00 sec)
```

```
CREATE TABLE `users` (
  `username` varchar(30) NOT NULL,
  `fullname` varchar(50) NOT NULL DEFAULT '---',
  `password` varchar(255) NOT NULL,
  `lastLogin` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
```

## PATRON

```
CREATE TABLE `patron` (
 `id` int unsigned AUTO_INCREMENT NOT NULL,
 `firstname` varchar(30) NOT NULL,
 `lastname` varchar(30) NOT NULL,
 `address` varchar(255) NOT NULL,
 `city` varchar(100) NOT NULL,
 `prov` varchar(2) NOT NULL,
 `phone` varchar(20) DEFAULT NULL,
 `email` varchar(50) DEFAULT NULL,
 `birthdate` date NOT NULL,
 `createDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`id`) 
) ENGINE=InnoDB;
```

```
describe patron;
+------------+--------------+------+-----+-------------------+-------------------+
| Field      | Type         | Null | Key | Default           | Extra             |
+------------+--------------+------+-----+-------------------+-------------------+
| id         | int unsigned | NO   | PRI | NULL              | auto_increment    |
| firstname  | varchar(30)  | NO   |     | NULL              |                   |
| lastname   | varchar(30)  | NO   |     | NULL              |                   |
| address    | varchar(255) | NO   |     | NULL              |                   |
| city       | varchar(100) | NO   |     | NULL              |                   |
| prov       | varchar(2)   | NO   |     | NULL              |                   |
| phone      | varchar(20)  | YES  |     | NULL              |                   |
| email      | varchar(50)  | YES  |     | NULL              |                   |
| birthdate  | date         | NO   |     | NULL              |                   |
| createDate | timestamp    | NO   |     | CURRENT_TIMESTAMP | DEFAULT_GENERATED |
+------------+--------------+------+-----+-------------------+-------------------+
10 rows in set (0.02 sec)
```

## BIB

```
CREATE TABLE `bib`(
`id` int unsigned AUTO_INCREMENT NOT NULL,
`title` varchar(50) NOT NULL,
`author` varchar(50) NOT NULL,
`pub_date` int NOT NULL,
`ISBN` int NOT NULL,
`call_number` varchar(15) DEFAULT NULL,
`subjects` varchar(100) DEFAULT NULL,
`time_stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(`id`) 
)ENGINE=InnoDB;
```

```describe bib;
+-------------+--------------+------+-----+-------------------+-------------------+
| Field       | Type         | Null | Key | Default           | Extra             |
+-------------+--------------+------+-----+-------------------+-------------------+
| id          | int unsigned | NO   | PRI | NULL              | auto_increment    |
| title       | varchar(150) | NO   |     | NULL              |                   |
| author      | varchar(50)  | NO   |     | NULL              |                   |
| pub_date    | int          | NO   |     | NULL              |                   |
| ISBN        | int          | NO   |     | NULL              |                   |
| call_number | varchar(15)  | YES  |     | NULL              |                   |
| subjects    | varchar(200) | YES  |     | NULL              |                   |
| time_stamp  | timestamp    | NO   |     | CURRENT_TIMESTAMP | DEFAULT_GENERATED |
+-------------+--------------+------+-----+-------------------+-------------------+
8 rows in set (0.00 sec)
```

## STATUS

```
mysql> describe status;
+-------------+--------------+------+-----+---------+-------+
| Field       | Type         | Null | Key | Default | Extra |
+-------------+--------------+------+-----+---------+-------+
| code        | varchar(20)  | NO   | PRI | NULL    |       |
| description | varchar(255) | NO   |     | NULL    |       |
+-------------+--------------+------+-----+---------+-------+
2 rows in set (0.00 sec)
```

```
> SELECT * FROM status;
+---------+-------------------------+
| code    | description             |
+---------+-------------------------+
| CR      | Claimed Returned        |
| DAMAGED | Damaged (is in library) |
| DISCARD | To be discarded         |
| IN      | In                      |
| LOST    | Lost                    |
| OUT     | Out                     |
| REPAIR  | Is being repaired       |
+---------+-------------------------+
7 rows in set (0.00 sec)
```

## LIBRARY CARD

```
CREATE TABLE `libraryCard` (
  `barcode` int unsigned NOT NULL,
  `patronId` int unsigned NOT NULL,
  `status` enum('VALID','LOST','EXPIRED') NOT NULL,
  `expiryDate` date DEFAULT NULL,
  PRIMARY KEY (`barcode`),
  KEY `patron_link` (`patronId`),
  CONSTRAINT `patron_link` FOREIGN KEY (`patronId`) REFERENCES `patron` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
```

**Trigger** to add expiry date if none is supplied 

```
delimiter //
CREATE TRIGGER set_expiryDate BEFORE INSERT ON libraryCard FOR EACH ROW
BEGIN
  IF NEW.expiryDate IS NULL THEN
     SET NEW.expiryDate = CURDATE() + INTERVAL 1 YEAR;
  END IF;
END;//
delimiter ;
```




```
> describe libraryCard
+------------+--------------------------------+------+-----+---------+-------+
| Field      | Type                           | Null | Key | Default | Extra |
+------------+--------------------------------+------+-----+---------+-------+
| barcode    | int unsigned                   | NO   | PRI | NULL    |       |
| patronId   | int unsigned                   | NO   | MUL | NULL    |       |
| status     | enum('VALID','LOST','EXPIRED') | NO   |     | NULL    |       |
| expiryDate | date                           | YES  |     | NULL    |       |
+------------+--------------------------------+------+-----+---------+-------+
4 rows in set (0.00 sec)
```

## HOLDINGS

`from "SHOW CREATE TABLE holdings;"`

```
CREATE TABLE `holdings` (
  `barcode` int unsigned NOT NULL,
  `bibID` int unsigned NOT NULL,
  `patronID` int unsigned DEFAULT NULL,
  `cost` int unsigned NOT NULL COMMENT 'in cents',
  `status` varchar(20) NOT NULL,
  `ckoDate` date DEFAULT NULL,
  `dueDate` date DEFAULT NULL,
  `prevPatron` int unsigned DEFAULT NULL,
  `createDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`barcode`),
  KEY `patronID_link` (`patronID`),
  KEY `bibID_link` (`bibID`),
  KEY `status_link` (`status`),
  KEY `prevPatron_link` (`prevPatron`),
  CONSTRAINT `bibID_link` FOREIGN KEY (`bibID`) REFERENCES `bib` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `patronID_link` FOREIGN KEY (`patronID`) REFERENCES `patron` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `prevPatron_link` FOREIGN KEY (`prevPatron`) REFERENCES `patron` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `status_link` FOREIGN KEY (`status`) REFERENCES `status` (`code`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci |
```

```
> DESCRIBE holdings;
+------------+--------------+------+-----+-------------------+-------------------+
| Field      | Type         | Null | Key | Default           | Extra             |
+------------+--------------+------+-----+-------------------+-------------------+
| barcode    | int unsigned | NO   | PRI | NULL              |                   |
| bibID      | int unsigned | NO   | MUL | NULL              |                   |
| patronID   | int unsigned | YES  | MUL | NULL              |                   |
| cost       | int unsigned | NO   |     | NULL              |                   |
| status     | varchar(20)  | NO   | MUL | NULL              |                   |
| ckoDate    | date         | YES  |     | NULL              |                   |
| dueDate    | date         | YES  |     | NULL              |                   |
| prevPatron | int unsigned | YES  | MUL | NULL              |                   |
| createDate | timestamp    | NO   |     | CURRENT_TIMESTAMP | DEFAULT_GENERATED |
+------------+--------------+------+-----+-------------------+-------------------+
9 rows in set (0.01 sec)
```
