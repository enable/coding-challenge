<?php

use Phinx\Migration\AbstractMigration;

class CreateTables extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    public function change()
    {
    }
    */
    
    /**
     * Migrate Up.
     */
    public function up()
    {

        $this->execute("CREATE TABLE `coreMimeType` (
  `MimeTypeID` int(11) NOT NULL AUTO_INCREMENT,`MimeType` varchar(50) NOT NULL,
  `xDateAdded` datetime NOT NULL,`xLastUpdate` datetime NOT NULL,
  PRIMARY KEY (`MimeTypeID`),KEY `MimeType` (`MimeType`),
  KEY `xDateAdded` (`xDateAdded`),KEY `xLastUpdate` (`xLastUpdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $this->execute("INSERT INTO `coreMimeType` (`MimeTypeID`, `MimeType`, `xDateAdded`, `xLastUpdate`) VALUES
(1,	'image/jpeg',	'0000-00-00 00:00:00',	'0000-00-00 00:00:00'),(2,	'image/gif',	'0000-00-00 00:00:00',	'0000-00-00 00:00:00'),
(3,	'image/png',	'0000-00-00 00:00:00',	'0000-00-00 00:00:00'),(4,	'video/x-flv',	'0000-00-00 00:00:00',	'0000-00-00 00:00:00');");

        $this->execute("CREATE TABLE `coreFileType` (
  `FileTypeID` int(11) NOT NULL AUTO_INCREMENT,
  `MimeTypeID` int(11) NOT NULL,`Description` varchar(255) NOT NULL,
  `Extension` varchar(10) NOT NULL,`Type` enum('Image','Movie') DEFAULT 'Image',
  `xDateAdded` datetime NOT NULL,`xLastUpdate` datetime NOT NULL,
  PRIMARY KEY (`FileTypeID`),KEY `MimeTypeID` (`MimeTypeID`),
  KEY `Description` (`Description`),KEY `Type` (`Type`),
  KEY `xDateAdded` (`xDateAdded`),KEY `xLastUpdate` (`xLastUpdate`),
  CONSTRAINT `FileTypeFKMimeType` FOREIGN KEY (`MimeTypeID`) REFERENCES `coreMimeType` (`MimeTypeID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $this->execute("INSERT INTO `coreFileType` (`FileTypeID`, `MimeTypeID`, `Description`, `Extension`, `Type`, `xDateAdded`, `xLastUpdate`) VALUES
(1,	1,	'JPEG Image',	'jpg',	'Image',	'0000-00-00 00:00:00',	'0000-00-00 00:00:00'),(2,	2,	'GIF Image',	'gif',	'Image',	'0000-00-00 00:00:00',	'0000-00-00 00:00:00'),
(3,	3,	'PNG Image',	'png',	'Image',	'0000-00-00 00:00:00',	'0000-00-00 00:00:00'),(4,	4,	'Flash Movie',	'flv',	'Movie',	'0000-00-00 00:00:00',	'0000-00-00 00:00:00');");

        $this->execute("CREATE TABLE `coreLanguage` (
  `LanguageID` int(11) NOT NULL AUTO_INCREMENT,`LanguageCode` varchar(3) NOT NULL,
  `Language` varchar(255) NOT NULL,`SortOrder` int(11) DEFAULT '0',
  `Active` tinyint(1) DEFAULT '0',`xDateAdded` datetime NOT NULL,
  `xLastUpdate` datetime NOT NULL,`MainLanguage` int(1) DEFAULT NULL,
  PRIMARY KEY (`LanguageID`),UNIQUE KEY `LanguageCode` (`LanguageCode`),
  KEY `Language` (`Language`),KEY `SortOrder` (`SortOrder`),
  KEY `xDateAdded` (`xDateAdded`),KEY `xLastUpdate` (`xLastUpdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $this->execute("INSERT INTO `coreLanguage` (`LanguageID`, `LanguageCode`, `Language`, `SortOrder`, `Active`, `xDateAdded`, `xLastUpdate`, `MainLanguage`) VALUES
(1,	'en',	'English',	0,	1,	'0000-00-00 00:00:00',	'0000-00-00 00:00:00',	1),(2,	'es',	'Spanish',	0,	1,	'0000-00-00 00:00:00',	'0000-00-00 00:00:00',	1),
(3,	'hu',	'Hungarian',	3,	1,	'0000-00-00 00:00:00',	'0000-00-00 00:00:00',	NULL),(4,	'cz',	'Czech',	0,	1,	'0000-00-00 00:00:00',	'0000-00-00 00:00:00',	0),
(5,	'ro',	'Romanian',	0,	1,	'0000-00-00 00:00:00',	'0000-00-00 00:00:00',	0),(6,	'pl',	'Poland',	0,	1,	'0000-00-00 00:00:00',	'0000-00-00 00:00:00',	0),
(7,	'ru',	'Russian',	0,	1,	'0000-00-00 00:00:00',	'0000-00-00 00:00:00',	0),(8,	'it',	'Italiano',	0,	1,	'2013-08-07 19:02:28',	'2013-08-07 19:02:28',	NULL),
(9,	'pt',	'Portuguese',	0,	1,	'2013-12-04 14:44:40',	'2013-12-04 14:44:40',	1);");

        $this->execute("CREATE TABLE `coreVersion` (
  `VersionID` int(11) NOT NULL AUTO_INCREMENT,
  `Module` varchar(50) NOT NULL,`Version` double(5,2) NOT NULL,
  `xDateAdded` datetime NOT NULL,`xLastUpdate` datetime NOT NULL,
  PRIMARY KEY (`VersionID`),KEY `Module` (`Module`),
  KEY `xDateAdded` (`xDateAdded`),KEY `xLastUpdate` (`xLastUpdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $this->execute("INSERT INTO `coreVersion` (`VersionID`, `Module`, `Version`, `xDateAdded`, `xLastUpdate`) VALUES
(1,	'core',	2.05,	'0000-00-00 00:00:00',	'2013-03-25 17:00:51'),(2,	'cms_core',	2.08,	'0000-00-00 00:00:00',	'2013-04-03 15:54:18'),
(1000,	'ecancer_video',	2.20,	'0000-00-00 00:00:00',	'2013-06-17 18:13:56'),(1001,	'ecancer_news',	2.27,	'0000-00-00 00:00:00',	'2013-07-02 17:49:41'),
(1002,	'ecancer_journal',	2.23,	'0000-00-00 00:00:00',	'2013-06-12 16:44:02'),(1003,	'ecancer_conference',	2.09,	'0000-00-00 00:00:00',	'2013-08-01 18:05:09'),
(1004,	'ecancer_institute',	2.07,	'0000-00-00 00:00:00',	'2013-02-22 17:29:41'),(1005,	'ecancer_career',	2.06,	'0000-00-00 00:00:00',	'2013-02-22 17:29:41'),
(1006,	'ecancer_education',	2.12,	'0000-00-00 00:00:00',	'2013-06-04 11:47:42');");

    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable("coreLanguage");
        $this->dropTable("coreFileType");
        $this->dropTable("coreMimeType");
        $this->dropTable("coreVersion");
    }
}