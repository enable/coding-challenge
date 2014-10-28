<?php

use Phinx\Migration\AbstractMigration;

class VideoFixtures extends AbstractMigration
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
        $this->execute("INSERT INTO `coreImage` (`ImageID`,`FileTypeID`,`ImageCategoryID`,`Description`,`Width`,`Height`,`Filename`,`xDateAdded`,`xLastUpdate`) ".
            "VALUES(137,1, null,'EBCC 6: Conference highlights from Europa Donna - 12.jpg',134,100, null,'2012-12-13 16:29:26','2012-12-13 16:29:26'),
(138,1, null,'Highlights and headlines anticipated at the joint ECCO 15 - 34th ESMO Multidisciplinary Congress - 14.jpg',134,100, null,'2012-12-13 16:29:26','2012-12-13 16:29:26'),
(139,1, null,'Cetuximab and docetaxal for squamous cell cancer of the head and neck, from ASCO 2008 - 16.jpg',134,100, null,'2012-12-13 16:29:27','2012-12-13 16:29:27'),
(140,1, null,'Cetuximab for metastatic colorectal cancer (mCRC), from ASCO 2008 - 17.jpg',134,100, null,'2012-12-13 16:29:28','2012-12-13 16:29:28'),
(141,1, null,'Cetuximab for NSCLC, from ASCO 2008 - 18.jpg',134,100, null,'2012-12-13 16:29:28','2012-12-13 16:29:28'),
(142,1, null,'Cetuximab for NSCLC, from ASCO 2008 - 19.jpg',134,100, null,'2012-12-13 16:29:29','2012-12-13 16:29:29'),
(143,1, null,'Results of the new Cetuximab studies in mCRC, from ASCO 2008 - 20.jpg',134,100, null,'2012-12-13 16:29:29','2012-12-13 16:29:29'),
(144,1, null,'Tailored Therapy - Increase Efficacy of Cetuximab - 21.jpg',134,100, null,'2012-12-13 16:29:30','2012-12-13 16:29:30'),
(145,1, null,'Princess Margaret Hospital and University of Toronto, Canada - 22.jpg',134,100, null,'2012-12-13 16:29:30','2012-12-13 16:29:30'),
(146,1, null,'Princess Margaret Hospital and University of Toronto, Canada - 23.jpg',134,100, null,'2012-12-13 16:29:31','2012-12-13 16:29:31'),
(167,1, null,'Princess Margaret Hospital and University of Toronto, Canada - 24.jpg',134,100, null,'2012-12-14 00:44:07','2012-12-14 00:44:07'),
(168,1, null,'The Institute of Cancer Research, Sutton, UK - 25.jpg',134,100, null,'2012-12-14 00:44:07','2012-12-14 00:44:07'),
(169,1, null,'The Institute of Cancer Research, Sutton, UK - 26.jpg',134,100, null,'2012-12-14 00:44:08','2012-12-14 00:44:08'),
(170,1, null,'Early breast cancer and non-chemo treatment - 27.jpg',134,100, null,'2012-12-14 00:44:08','2012-12-14 00:44:08'),
(171,1, null,'Director, Division of Breast Surgery, IEO - 28.jpg',134,100, null,'2012-12-14 00:44:09','2012-12-14 00:44:09')");

        $this->execute("INSERT INTO `siteVideo` (`VideoID`,`PublicationDate`,`VideoTypeID`,`StreamUKID`,`Featured`,`ConferenceFeature`,`Width`,".
"`Height`,`ClubPoints`,`Active`,`Hidden`,`PeerReviewed`,`VideoSponsorID`,`ProjectID`,`IOSHeading`,`UseMobilePlayer`,".
"`UseHtml5`,`ExternalVideo`,`Feedback`,`PPT`,`RegionID`,`RatingOverride`,`zRating`,`zViews`,`zViewsBias`,`zFeedback`,`xDateAdded`,".
"`xLastUpdate`) VALUES".
"(12,'2008-11-27 00:00:00',0,'0_nrkh7izn',0,0,null,null,0,1,0,0,0,null,null,1,1,null,1,null,3,null,3.0,13268,0,0,'2008-11-27 00:00:00','2014-10-23 06:34:54'),".
"(14,'2008-11-27 00:00:00',0,'0_ja4c3tu7',0,0,null,null,0,1,0,0,0,null,null,1,1,null,1,null,3,null,3.0,10649,0,0,'2008-11-27 00:00:00','2014-10-20 10:29:28'),".
"(16,'2008-11-27 00:00:00',0,'0_zuv2g2ic',0,0,null,null,0,1,0,0,0,null,null,1,1,null,1,null,3,null,3.0,13416,0,0,'2008-11-27 00:00:00','2014-10-21 20:15:55'),".
"(17,'2008-11-27 00:00:00',0,'0_e87ax98a',0,0,null,null,0,1,0,0,0,null,null,1,1,null,1,null,3,null,3.0,12816,0,0,'2008-11-27 00:00:00','2014-10-22 01:01:26'),".
"(18,'2008-11-27 00:00:00',0,'0_ymij19jl',0,0,null,null,0,1,0,0,0,null,null,1,1,null,1,null,3,null,3.0,12348,0,0,'2008-11-27 00:00:00','2014-10-23 10:49:29'),".
"(19,'2008-11-27 00:00:00',0,'0_ueljcuwd',0,0,null,null,0,1,0,0,0,null,null,1,1,null,1,null,3,null,3.0,11762,0,0,'2008-11-27 00:00:00','2014-10-22 01:01:27'),".
"(20,'2008-11-28 00:00:00',0,'0_zet9hiuu',0,0,null,null,0,1,0,0,0,null,null,1,1,null,1,null,3,null,3.0,11505,0,0,'2008-11-28 00:00:00','2014-10-21 21:05:38'),".
"(21,'2008-11-28 01:01:00',0,'0_lt7lum14',0,0,null,null,0,1,0,0,0,null,null,1,1,null,1,null,3,null,4.0,12470,0,0,'2008-11-28 01:01:00','2014-10-17 19:16:50'),".
"(22,'2008-11-28 00:00:00',0,'0_8o20zv6n',0,0,null,null,0,1,0,0,0,null,null,1,1,null,1,null,3,null,3.0,12262,0,0,'2008-11-28 00:00:00','2014-10-22 01:05:19'),".
"(23,'2008-11-28 00:00:00',0,'0_okzvamda',0,0,null,null,0,1,0,0,0,null,null,1,1,null,1,null,3,null,3.0,12675,0,0,'2008-11-28 00:00:00','2014-10-21 12:56:52'),".
"(24,'2008-11-28 00:00:00',0,'0_uzquzjw6',0,0,null,null,0,1,0,0,0,null,null,1,1,null,1,null,3,null,3.0,12244,0,0,'2008-11-28 00:00:00','2014-10-21 15:33:49'),".
"(25,'2008-11-28 00:00:00',0,'0_ngkxvdau',0,0,null,null,0,1,0,0,0,null,null,1,1,null,1,null,3,null,4.0,11664,0,0,'2008-11-28 00:00:00','2014-10-22 03:02:42'),".
"(26,'2008-11-28 00:00:00',0,'0_gaya7jm8',0,0,null,null,0,1,0,0,0,null,null,1,1,null,1,null,3,null,3.0,12830,0,0,'2008-11-28 00:00:00','2014-10-20 16:10:00'),".
"(27,'2008-11-28 00:00:00',0,'0_tmxudkbq',0,0,null,null,0,1,0,0,0,null,null,1,1,null,1,null,3,null,3.0,982,0,0,'2008-11-28 00:00:00','2014-10-21 13:09:07'),".
"(28,'2008-11-28 00:00:00',0,'0_fk0csxrh',0,0,null,null,0,1,0,0,0,null,null,1,1,null,1,null,3,null,3.0,1496,0,0,'2008-11-28 00:00:00','2014-10-22 10:54:43')");

        $this->execute("INSERT INTO `siteVideoToImage` (`VideoToImageID`,`VideoID`,`ImageID`,`SortOrder`,`EditorPick`,`xDateAdded`,`xLastUpdate`) ".
"VALUES(5181,12,137,0,0,'2013-02-14 00:59:15','2013-02-14 00:59:15'),
(5182,14,138,0,0,'2013-02-14 00:59:16','2013-02-14 00:59:16'),(5183,16,139,0,0,'2013-02-14 00:59:17','2013-02-14 00:59:17'),
(5184,17,140,0,0,'2013-02-14 00:59:18','2013-02-14 00:59:18'),(5185,18,141,0,0,'2013-02-14 00:59:19','2013-02-14 00:59:19'),
(5186,19,142,0,0,'2013-02-14 00:59:20','2013-02-14 00:59:20'),(5187,20,143,0,0,'2013-02-14 00:59:21','2013-02-14 00:59:21'),
(5188,21,144,0,0,'2013-02-14 00:59:22','2013-02-14 00:59:22'),(5189,22,145,0,0,'2013-02-14 00:59:23','2013-02-14 00:59:23'),
(5190,23,146,0,0,'2013-02-14 00:59:23','2013-02-14 00:59:23'),(5191,24,167,0,0,'2013-02-14 00:59:33','2013-02-14 00:59:33'),
(5192,25,168,0,0,'2013-02-14 00:59:34','2013-02-14 00:59:34'),(5193,26,169,0,0,'2013-02-14 00:59:34','2013-02-14 00:59:34'),
(5194,27,170,0,0,'2013-02-14 00:59:36','2013-02-14 00:59:36'),(5195,28,171,0,0,'2013-02-14 00:59:37','2013-02-14 00:59:37')");

        $this->execute("INSERT INTO `siteVideo_lang` (`Video_langID`,`VideoID`,`LanguageID`,`Title`,`Summary`,".
        "`Speaker`,`Description`,`Transcript`,`ForeignTranscript`,`VideoSponsorText`,`Biography`,".
        "`Context`,`PageTitle`,`PageMetaDescription`,`PageMetaKeywords`,`zTags`,`xDateAdded`,`xLastUpdate`,`ChineseTranscript`) ".
        "VALUES".
"(4329,12,1,'EBCC 6: Conference highlights from Europa Donna',null,'Susan Knox - Executive Director of Europa Donna',".
"'Susan Knox, Executive Director of Europa Donna a Europe-wide coalition of affiliated groups of women that facilitates the exchange and spread of pertinent information concerning breast cancer through the different cultures it represents.<br/><br/>".
"Ms. Knox explains the importance of the EBCC conference both to their work with clinicians and as a way of informing their advocates of the advances being made in breast cancer services. <br/><br/>".
"Discrepancies between the access to expensive new treatments among Eurpoean countries is a concern and Europa Dona are trying to minimise this and guarantee that all European countries offer the same services, recommended by EU guidelines.',".
"null,null,null,null,null,null,null,null,'breast aware breast cancer cancer treatment cultures Europa Donna Europe European Knox more than lip service October',".
"'2008-11-27 00:00:00','2008-11-27 00:00:00',''),".
"(4330,14,1,'Highlights and headlines anticipated at the joint ECCO 15 - 34th ESMO Multidisciplinary Congress',null,'Exclusive Preview',".
"null,null,null,null,null,null,null,null,null,'Baselga Ciardiello ECCO Eggermont ESMO McVie multidisciplinary phase 0 Twelves',".
"'2008-11-27 00:00:00','2008-11-27 00:00:00',''),".
"(4331,16,1,'Cetuximab and docetaxal for squamous cell cancer of the head and neck, from ASCO 2008',null,".
"'Professor Jan B. Vermorken',null,null,null,null,null,null,null,null,null,".
"'ASCO Bokemeyer bowel cetuximab colorectal erbitux Merck Serono targeted therapy',".
"'2008-11-27 00:00:00','2008-11-27 00:00:00',''),".
"(4332,17,1,'Cetuximab for metastatic colorectal cancer (mCRC), from ASCO 2008',null,".
"'Professor Carsten Bokemeyer',null,null,null,null,null,null,null,null,null,".
"'ASCO Bokemeyer bowel cetuximab colorectal erbitux Merck Serono targeted therapy',".
"'2008-11-27 00:00:00','2008-11-27 00:00:00',''),".
"(4333,18,1,'Cetuximab for NSCLC, from ASCO 2008',null,'Professor Nicholas Thatcher',".
"null,null,null,null,null,null,null,null,null,".
"'ASCO cetuximab erbitux lung Merck Serono targeted therapy Thatcher',".
"'2008-11-27 00:00:00','2008-11-27 00:00:00',''),".
"(4334,19,1,'Cetuximab for NSCLC, from ASCO 2008',null,'Professor Robert Pirker',".
"null,null,null,null,null,null,null,null,null,'ASCO cetuximab erbitux lung Merck Serono Pirker targeted therapy',".
"'2008-11-27 00:00:00','2008-11-27 00:00:00',''),".
"(4335,20,1,'Results of the new Cetuximab studies in mCRC, from ASCO 2008',null,'Professor Eric Van Cutsem',".
"null,null,null,null,null,null,null,null,null,'bowel cetuximab colorectal erbitux Merck Serono targeted therapy Van Cutsem',".
"'2008-11-28 00:00:00','2008-11-28 00:00:00',''),".
"(4336,21,1,'Tailored Therapy - Increase Efficacy of Cetuximab',null,'Erbitux and the KRAS Mutation','<p>Tailored Therapy - Increase Efficacy of Cetuximab</p>',".
"null,null,null,null,null,null,null,null,'cetuximab efficacy erbitux KRAS Merck Serono targeted therapy',".
"'2008-11-28 01:01:00','2008-11-28 01:01:00',''),".
"(4337,22,1,'Princess Margaret Hospital and University of Toronto, Canada',null,'Prof. Ian Tannock',".
"'Patupilone in patients with metastatic hormone refractory prostate cancer who have progressed after docetaxel, from ESMO 2008',".
"null,null,null,null,null,null,null,null,'Docetaxel EPO906 epothilone B ESMO hormone Patupilone Sanofi Aventis. Prostate Tannock Taxotere',".
"'2008-11-28 00:00:00','2008-11-28 00:00:00',''),".
"(4338,23,1,'Princess Margaret Hospital and University of Toronto, Canada',null,'Prof. Ian Tannock',".
"'Sunitinib vs. interferon-alfa as first-line treatment for metastatic renal cancer, from ESMO 2008',".
"null,null,null,null,null,null,null,null,".
"'biological therapy ESMO immunotherapy interferon alfa Pfizer Roferon-A Sunitinib sutent Tannock',".
"'2008-11-28 00:00:00','2008-11-28 00:00:00',''),".
"(4339,24,1,'Princess Margaret Hospital and University of Toronto, Canada',null,'Prof. Ian Tannock',".
"'Blood pressure and efficacy in patients with metastatic renal cell carcinoma receiving axitinib, from ESMO 2008',".
"null,null,null,null,null,null,null,null,'AG013736 axitinib blood pressure ESMO kidney renal small molecule tyrosine kinase inhibitor Tannock urology',".
"'2008-11-28 00:00:00','2008-11-28 00:00:00',''),".
"(4340,25,1,'The Institute of Cancer Research, Sutton, UK',null,'Prof. Alan Horwich',".
"'Sexual function after treatment for testicular cancer, from ESMO 2008',".
"null,null,null,null,null,null,null,null,'ESMO Horwich impotence sexual function testicular urology',".
"'2008-11-28 00:00:00','2008-11-28 00:00:00',''),".
"(4341,26,1,'The Institute of Cancer Research, Sutton, UK',null,'Prof. Alan Horwich',".
"'Incidence of contralateral germ cell tumours, from ESMO 2008',".
"null,null,null,null,null,null,null,null,'contralateral ESMO germ cell Horwich testicular',".
"'2008-11-28 00:00:00','2008-11-28 00:00:00',''),".
"(4342,27,1,'Early breast cancer and non-chemo treatment',null,'Professor Alan Coates',".
"'From the Milan Breast Cancer Conference.',".
"null,null,null,null,null,null,null,null,".
"'clinical trials Coates early breast cancer European Institute Her2 herceptin hormone receptor IEO Milan Breast Cancer Conference tamoxifen trastuzumab triple negative',".
"'2008-11-28 00:00:00','2008-11-28 00:00:00',''),".
"(4343,28,1,'Director, Division of Breast Surgery, IEO',null,".
"'Dr Alberto Luini','Radioguided Localisation (ROLL) of breast lesions. From the Milan Breast Cancer Conference',".
"null,null,null,null,null,null,null,null,".
"'European Institute IEO Luini Milan Breast Cancer Conference radioguided ROLL',".
"'2008-11-28 00:00:00','2008-11-28 00:00:00','');");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("DELETE FROM `coreImage`;");
        $this->execute("DELETE FROM `siteVideo_lang`;");
        $this->execute("DELETE FROM `siteVideoToImage`;");
        $this->execute("DELETE FROM `siteVideo`;");

    }
}