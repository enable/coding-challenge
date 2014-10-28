<?php

use Phinx\Migration\AbstractMigration;

class CreateImagesTables extends AbstractMigration
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
        $this->execute("CREATE TABLE `coreImageCategory` (
  `ImageCategoryID` int(11) NOT NULL AUTO_INCREMENT,
  `Category` varchar(255) NOT NULL,`SortOrder` int(11) DEFAULT '0',
  `xDateAdded` datetime NOT NULL,`xLastUpdate` datetime NOT NULL,
  PRIMARY KEY (`ImageCategoryID`),
  UNIQUE KEY `Category` (`Category`),KEY `SortOrder` (`SortOrder`),
  KEY `xDateAdded` (`xDateAdded`),KEY `xLastUpdate` (`xLastUpdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $this->execute("INSERT INTO `coreImageCategory` (`ImageCategoryID`, `Category`, `SortOrder`, `xDateAdded`, `xLastUpdate`) VALUES
(1,	'General',	0,	'0000-00-00 00:00:00',	'0000-00-00 00:00:00'),(2,	'Gallery',	0,	'0000-00-00 00:00:00',	'0000-00-00 00:00:00');");

        $this->execute("CREATE TABLE `coreImage` (
  `ImageID` int(11) NOT NULL AUTO_INCREMENT,
  `FileTypeID` int(11) NOT NULL,`ImageCategoryID` int(11) DEFAULT NULL,
  `Description` varchar(255) NOT NULL,`Width` int(11) NOT NULL,
  `Height` int(11) NOT NULL,`Filename` varchar(255) DEFAULT NULL,
  `xDateAdded` datetime NOT NULL,`xLastUpdate` datetime NOT NULL,
  PRIMARY KEY (`ImageID`),KEY `FileTypeID` (`FileTypeID`),
  KEY `Description` (`Description`),KEY `xDateAdded` (`xDateAdded`),
  KEY `xLastUpdate` (`xLastUpdate`),KEY `ImageFKImageCategory` (`ImageCategoryID`),
  CONSTRAINT `ImageFKFileType` FOREIGN KEY (`FileTypeID`)
  REFERENCES `coreFileType` (`FileTypeID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ImageFKImageCategory` FOREIGN KEY (`ImageCategoryID`)
  REFERENCES `coreImageCategory` (`ImageCategoryID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable("coreImage");
        $this->dropTable("coreImageCategory");

    }
}