<?php

use Phinx\Migration\AbstractMigration;

class CreateVideosTables extends AbstractMigration
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
        $this->execute("CREATE TABLE `siteVideo` (
  `VideoID` int(11) NOT NULL AUTO_INCREMENT,`PublicationDate` datetime DEFAULT NULL,
  `VideoTypeID` int(11) NOT NULL,`StreamUKID` varchar(50) DEFAULT NULL,
  `Featured` tinyint(1) DEFAULT '0',`ConferenceFeature` tinyint(1) DEFAULT '0',
  `Width` int(11) DEFAULT NULL,`Height` int(11) DEFAULT NULL,
  `ClubPoints` int(11) DEFAULT '0',`Active` tinyint(1) DEFAULT '0',
  `Hidden` tinyint(1) DEFAULT '0',`PeerReviewed` tinyint(1) DEFAULT '0',
  `VideoSponsorID` int(11) DEFAULT NULL,`ProjectID` varchar(20) DEFAULT NULL,
  `IOSHeading` varchar(255) DEFAULT NULL,`UseMobilePlayer` tinyint(1) DEFAULT '1',
  `UseHtml5` tinyint(1) DEFAULT '1',`ExternalVideo` varchar(255) DEFAULT NULL,
  `Feedback` int(11) NOT NULL DEFAULT '1',`PPT` varchar(255) DEFAULT NULL,
  `RegionID` int(11) DEFAULT NULL,`RatingOverride` int(1) DEFAULT NULL,
  `zRating` double(3,1) DEFAULT NULL,`zViews` int(11) DEFAULT '0',
  `zViewsBias` int(11) NOT NULL DEFAULT '0',`zFeedback` int(11) DEFAULT '0',
  `xDateAdded` datetime NOT NULL,`xLastUpdate` datetime NOT NULL,
  PRIMARY KEY (`VideoID`),
  KEY `VideoTypeID` (`VideoTypeID`),KEY `Featured` (`Featured`),KEY `Active` (`Active`),
  KEY `Hidden` (`Hidden`),KEY `VideoSponsorID` (`VideoSponsorID`),KEY `zRating` (`zRating`),
  KEY `zViews` (`zViews`),KEY `zFeedback` (`zFeedback`),KEY `xDateAdded` (`xDateAdded`),
  KEY `xLastUpdate` (`xLastUpdate`),KEY `RegionID` (`RegionID`),KEY `PublicationDate` (`PublicationDate`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $this->execute("CREATE TABLE `siteVideoToImage` (
  `VideoToImageID` int(11) NOT NULL AUTO_INCREMENT,
  `VideoID` int(11) NOT NULL,`ImageID` int(11) NOT NULL,
  `SortOrder` int(11) DEFAULT '0',`EditorPick` tinyint(1) DEFAULT '0',
  `xDateAdded` datetime NOT NULL,`xLastUpdate` datetime NOT NULL,
  PRIMARY KEY (`VideoToImageID`),KEY `ImageID` (`ImageID`),KEY `SortOrder` (`SortOrder`),
  KEY `xDateAdded` (`xDateAdded`),KEY `xLastUpdate` (`xLastUpdate`),KEY `EditorPick` (`EditorPick`),
  KEY `VideoID` (`VideoID`),
  CONSTRAINT `siteVideoToImageFKImage` FOREIGN KEY (`ImageID`) REFERENCES `coreImage` (`ImageID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `siteVideoToImageFKVideo` FOREIGN KEY (`VideoID`) REFERENCES `siteVideo` (`VideoID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $this->execute("CREATE TABLE `siteVideo_lang` (
  `Video_langID` int(11) NOT NULL AUTO_INCREMENT,
  `VideoID` int(11) NOT NULL,`LanguageID` int(11) NOT NULL,
  `Title` varchar(255) NOT NULL,`Summary` varchar(255) DEFAULT NULL,
  `Speaker` varchar(255) DEFAULT NULL,`Description` text,
  `Transcript` text,`ForeignTranscript` text,
  `VideoSponsorText` varchar(255) DEFAULT NULL,`Biography` text,
  `Context` text,`PageTitle` varchar(255) DEFAULT NULL,
  `PageMetaDescription` varchar(255) DEFAULT NULL,`PageMetaKeywords` varchar(255) DEFAULT NULL,
  `zTags` varchar(255) DEFAULT NULL,`xDateAdded` datetime NOT NULL,
  `xLastUpdate` datetime NOT NULL,`ChineseTranscript` text NOT NULL,
  PRIMARY KEY (`Video_langID`),
  UNIQUE KEY `VideoIDLanguageID` (`VideoID`,`LanguageID`),KEY `LanguageID` (`LanguageID`),
  KEY `Title` (`Title`),KEY `zTags` (`zTags`),KEY `xDateAdded` (`xDateAdded`),
  KEY `xLastUpdate` (`xLastUpdate`),KEY `Summary` (`Summary`),KEY `Speaker` (`Speaker`),
  CONSTRAINT `siteVideo_langFKLanguage` FOREIGN KEY (`LanguageID`) REFERENCES `coreLanguage` (`LanguageID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `siteVideo_langFKsiteVideo` FOREIGN KEY (`VideoID`) REFERENCES `siteVideo` (`VideoID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable("siteVideo_lang");
        $this->dropTable("siteVideoToImage");
        $this->dropTable("siteVideo");
    }
}