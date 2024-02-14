
<?php

//////////////////////////////////////////////
//////////////////////////////////////////////
//////////////////////////////////////////////
// ALL THINGS TO CHANGE BELOW:

# New domain:
wfLoadExtension( 'SemanticMediaWiki' ); # Don't change
enableSemantics( 'fina.knowledge.wiki' );  #Change to new FINA domain
$wgServer = 'https://fina-v.knowledge.wiki'; #Change to new FINA domain

# Emails:
$wgSMTP = [
    'host'     => 'mail.knowledge.wiki', #Change
    'IDHost'   => 'mail.knowledge.wiki', #Change
    'port'     => 25, #Change?
    'auth'     => true, // Chane? Should we use SMTP authentication? (true or false)
    'username' => 'USERNAME', #Change
    'password' => 'PASSWORD' #Change
];
$wgEmergencyContact = 'support@knowledge.wiki'; #Change
$wgPasswordSender = 'support@knowledge.wiki'; #Change

# Database:
$wgDBtype = 'mysql'; #Probably don't change
$wgDBserver = 'localhost'; #Maybe change, depends on where the database host is

$wgDBuser = getenv( 'MYSQL_USER' ) ?: 'USERNAME'; #Change
$wgDBpassword = getenv( 'MYSQL_PASSWORD' ) ?: 'PASSWORD'; #Change

$wgDBname = 'fina_vanilla'; #Change to DB name
$wgDBprefix = "fw"; #Probably don't change
$wgPingback = false; # Don't change
$wgShellLocale = "C.UTF-8"; #Probably don't change

if (!defined('MW_DB') && in_array($_SERVER['REMOTE_ADDR'],
   [
       $_SERVER['SERVER_ADDR'],
       // $_SERVER['HTTP_X_FORWARDED_FOR'], # for reverse proxies; disabled for now
       '127.0.0.1', #Maybe change
       'localhost' #Maybe change
    ])) {
   $wgGroupPermissions['*']['read'] = true; #Don't change
   $wgGroupPermissions['*']['edit'] = true; #Don't change
   $wgGroupPermissions['*']['writeapi'] = true; #Don't change
}

# Elasticsearch:
$wgSearchType = 'CirrusSearch'; # No need to change this
$wgCirrusSearchServers = [
        [
                "port" => 9201, # Probably change to 9200
                "host" => "localhost" #Change to ElasticSearch host if not localhost
        ]
];
$wgCirrusSearchIndexBaseName = $wgDBname; # No need to change this, if no special index name preferences

# Others:
$wgImageMagickConvertCommand = '/usr/bin/convert'; #Maybe change

$wgDiff3 = "/usr/bin/diff3"; #Probably don't change

$wgMatomoAnalyticsServerURL = "https://matomo.knowledge.wiki/"; #Change
$wgMatomoAnalyticsGlobalID = "7"; #Probably change
$wgMatomoAnalyticsDisableCookie = "true"; #Probably change
$wgMatomoAnalyticsDisableJS = "true"; #Probably change

// hCaptcha
$wgHCaptchaSiteKey = getenv( 'HCAPTCHA_SITE_KEY' ) ?: '';
$wgHCaptchaSecretKey = getenv( 'HCAPTCHA_SECRET_KEY' ) ?: '';

// END OF THINGS TO CHANGE.
//////////////////////////////////////////////
//////////////////////////////////////////////
//////////////////////////////////////////////

ini_set('memory_limit', '10240M');

//$common_dir = '/var/vps-wiki-config';

// Basic shared info
$wgLanguageCode = 'en'; # can be changed for the wiki

// URL paths
$wgScriptPath = '';
//$wgArticlePath = '/wiki/$1';
$wgUsePathInfo = true;
$wgResourceBasePath = $wgScriptPath;

// Files
$wgEnableUploads = true;
$wgUseImageMagick = true;

$wgFileExtensions = [...$wgFileExtensions, 'png', 'jpg', 'jpeg', 'docx', 'doc', 'pptx', 'ppt', 'xlsx', 'xls', 'pdf', 'svg', 'ico', 'txt'];
$wgAllowJavaUploads = true;

// Emails
$wgEnableEmail = true;
$wgEnableUserEmail = true;

$wgEnotifUserTalk = false; # can be changed for the wiki
$wgEnotifWatchlist = false; # can be changed for the wiki
$wgEmailAuthentication = true;

# $wgSMTP set in PrivateSettings.php

// Caches
$wgMainCacheType = CACHE_ACCEL;
$wgSessionCacheType = CACHE_DB; # so sessions aren't lost
$wgMemCachedServers = []; # can be enabled for the wiki

// Performance optimizations
$wgDisableCounters = true;
$wgMiserMode = true;

$wgUseGzip = true;
$wgEnableSidebarCache = true; # should be disabled if HideSidebar is installed on the wiki
$wgJobRunRate = 0;

$wgRevisionCacheExpiry = 3 * 24 * 3600;
$wgParserCacheExpireTime = 14 * 24 * 3600;

// Timezone
$wgLocaltimezone = 'UTC'; # can be changed for the wiki

// Skin
$wgDefaultSkin = 'vector';

wfLoadSkin( 'Vector' );
wfLoadSkin( 'MonoBook' );
wfLoadSkin( 'Timeless' );

if ( !defined( 'MEDIAWIKI' ) ) {
        exit;
}
//$wgMaxArticleSize = 2048;
$scigReferenceListCacheType = CACHE_NONE; //FIX Semantic Cite
$wgCacheEpoch = 20220901150130;

//$wgReadOnly = 'This wiki is currently in read-only mode for maintenance purposes.';

$wgSitename = 'Fina Wiki';
$wgMetaNamespace = "FINA";
$wgLanugageCode = "en";
$wgSecretKey = "da5bfc1128bbf222b83d2affdad246e41ee0e775413cbd76f54132b5bb3516b7";
$wgRightsPage = ""; # Set to the title of a wiki page that describes your license/copyright
$wgRightsUrl = "";
$wgRightsText = "";
$wgRightsIcon = "";


$wgScriptPath = "";
//$wgResourceBasePath = $wgScriptPath;
$wgArticlePath = "/$1";
$wgUsePathInfo = true;
$wgScriptExtension = ".php";

$wgJobRunRate = 0;

$wgUploadPath = "$wgScriptPath/img_auth.php";
$wgUploadDirectory = "images";

$wgLogo = "fina-logo.png";
$wgFavicon = "favicon.png";
$wgEnableUploads = true;
$wgUseImageMagick = false;
$wgTmpDirectory = __DIR__ . "/images/temp";

/// General settings
$wgEnableEmail = true;
$wgEnableUserEmail = true; # UPO

$wgEnotifUserTalk = true; # UPO
$wgEnotifWatchlist = true; # UPO
$wgEmailAuthentication = true;

//Uploads
$wgEnableUploads = true;
$wgUseImageMagick = false;

//Group permissions
// Implicit group for all visitors
$wgGroupPermissions['*'    ]['createaccount']   = false;
$wgGroupPermissions['*'    ]['read']            = true;
$wgGroupPermissions['*'    ]['edit']            = false;
$wgGroupPermissions['*'    ]['createpage']      = false;
$wgGroupPermissions['*'    ]['createtalk']      = false;
$wgGroupPermissions['*'    ]['writeapi']        = false;

$wgFileExtensions = array('jpg','jpeg','png','gif','doc','docx','xls','xlsx','ppt','pptx','pdf','odt','zip');
//$wgCheckFileExtensions = true;
$wgAllowJavaUploads = true;
$wgVerifyMimeType = false;
$wgPingback = false;

//Error handling
$wgShowExceptionDetails = true;

// Footer
$wgFooterIcons['poweredby']['knowledge.wiki'] = array(
        "src" => "/KnowledgeWiki.png",
        "url" => "https://www.knowledge.wiki",
        "alt" => "a Knowledge.wiki project",
        "height" => "31",
        "width" => "100",
);

unset ($wgFooterIcons['poweredby']['mediawiki']);

$wgRightsUrl = "https://creativecommons.org/share-your-work/public-domain/cc0/";
$wgRightsText = "CC0";
$wgRightsIcon = "https://licensebuttons.net/p/zero/1.0/88x31.png";

#Caching
$wgCachePages = true;
$wgEnableSidebarCache = true;
/// CirrusSearch
wfLoadExtension( 'Elastica' );
wfLoadExtension( 'CirrusSearch' );
wfLoadExtension( 'AdvancedSearch' );


// SMW
$smwgParserFeatures = SMW_PARSER_STRICT | SMW_PARSER_INL_ERROR | SMW_PARSER_HID_CATS | SMW_PARSER_UNSTRIP | SMW_PARSER_LINV;
// $smwgQEqualitySupport = SMW_EQ_NONE;  // always interpret redirects as equality in queries
$smwgPageSpecialProperties = array( '_MDAT', '_CDAT', '_NEWP', '_LEDT');
$smwgQMaxInlineLimit = 20000;
$smwgQMaxLimit = 20000;
$smwgCompactLinkSupport = false;
$wgMaxArticleSize = 4096;
$smwgQSubcategoryDepth = 10;
$wgCategoryCollation = 'numeric';
$smwgEntityCollation = 'numeric';

// VisualEditor
////wfLoadExtension( 'VisualEditor' );
$wgDefaultUserOptions['visualeditor-enable'] = 0;
$wgVisualEditorEnableDiffPage = true;
$wgVisualEditorEnableVisualSectionEditing = true;
$wgVisualEditorAvailableNamespaces['Help'] = true;



wfLoadExtension( 'Kma');

/// Skins
wfLoadExtension( 'Bootstrap');
wfLoadSkin( 'Kma' );
$wgDefaultSkin='kma';
$egChameleonLayoutFile= 'skins/Kma/SITE/FINA-layout.xml';
$egChameleonThemeFile = 'skins/Kma/SITE/f_variables.scss';
$egChameleonExternalStyleModules = ['skins/Kma/SITE/f_bootswatch.scss' => 'afterMain',];
$egChameleonEnableVisualEditor = true;
$egChameleonEnableExternalLinkIcons = false;
$egChameleonExternalStyleVariables = [
    'body-bg' => '#ffffff',
    'font-family-base' => "Roboto,sans-serif",
    'headings-color' => '#0047bb', #ÖAW
    'headings-font-family' => "Alegreya,serif",
    'dark' => '#5c6784',
    'light' => '#f5f5f5', #f6f6f6
    'primary' => '#00122f', #ÖAW  0047bb
    'secondary' => '#9eaeb5',
    'info' => '#0355ad', #MediaWiki Buttons
    'danger' => '#910a00',
    'warning' => '#e39f00',
    'success' => '#009e4c',
    'cmln-navbar-logo-height' => '1.0rem',
    'cmln-navbar-bg-color' => "light",
    'cmln-search-bar-btn-color' => "light",
    'navbar-light-color' => "#0047bb", #ÖAW
    'navbar-light-hover-color' => "#0099ff",
    'navbar-light-active-color' => "#0355ad",
    'navbar-padding-y' => '1.0rem',
    'dropdown-link-hover-bg' => '#0355ad', #should be light
    'dropdown-link-hover-color' => "#f5f5f5",
    'dropdown-link-active-color' => "#ffffff",
    'dropdown-link-active-bg' => "#0355ad",
    'dropdown-link-color' => '#0355ad',
    'font-size-base' => '1.0rem',
    'h1-font-size' => '1.8rem',
    'h2-font-size' => '1.6rem',
    'h3-font-size' => '1.4rem',
    'h4-font-size' => '1.2rem',
    'h5-font-size' => '1.2rem',
    'cmln-link-formats' => "(new: ('color': #f60000, 'hover-color': #6e0000), stub: #0099ff #007aff
#10345a underline, extiw: #1b599b none #10345a underline, external: #044eae #ff0000 #2d125d underline)",

    'jumbotron-bg' => '#f0f0f0',
    'card-cap-bg' => '#f0f0f0',
    'card-spacer-y' => '0.5rem',
    'card-spacer-x' => '1rem',


    'table-cell-padding-sm' => '0.2rem',

];


# Enabled skins.
# The following skins were automatically enabled:
wfLoadSkin( 'MonoBook' );
wfLoadSkin( 'Timeless' );
wfLoadSkin( 'Vector' );
wfLoadSkin( 'Tweeki' );

/// Extensions MyWikis
wfLoadExtension( 'CategoryTree' );
wfLoadExtension( 'Cite' );
wfLoadExtension( 'CiteThisPage' );
wfLoadExtension( 'CodeEditor' );
wfLoadExtension( 'ConfirmEdit' );
wfLoadExtension( 'Gadgets' );
wfLoadExtension( 'ImageMap' );
wfLoadExtension( 'InputBox' );
wfLoadExtension( 'Interwiki' );
wfLoadExtension( 'LocalisationUpdate' );
wfLoadExtension( 'Nuke' );
wfLoadExtension( 'OATHAuth' );
wfLoadExtension( 'PageImages' );
wfLoadExtension( 'ParserFunctions' );
$wgPFEnableStringFunctions = true;
$wgPFStringLengthLimit = 10000;
wfLoadExtension( 'PdfHandler' );
wfLoadExtension( 'Poem' );
wfLoadExtension( 'Renameuser' );
wfLoadExtension( 'ReplaceText' );
wfLoadExtension( 'Scribunto' );
wfLoadExtension( 'SecureLinkFixer' );
wfLoadExtension( 'SyntaxHighlight_GeSHi' );
wfLoadExtension( 'TemplateData' );
wfLoadExtension( 'TextExtracts' );
wfLoadExtension( 'WikiEditor' );

$wgPFEnableStringFunctions = true;
$wgScribuntoDefaultEngine = 'luasandbox';

# Semantic Result Formats via Composer
# More info: https://semantic-mediawiki.org/wiki/Semantic_Result_Formats
$srfgFormats[] = 'incoming';
$srfgMapProvider='BasemapAT.basemap';
wfLoadExtension( 'SemanticResultFormats' );
$srfgMapProvider='OpenStreetMap.Mapnik';


# Modern Timeline
# More info: https://github.com/ProfessionalWiki/ModernTimeline
wfLoadExtension( 'ModernTimeline' );

#Network
wfLoadExtension( 'Network' );

# Maps COMPOSER
wfLoadExtension( 'Maps' );
$egMapsEnableCategory = false;
//require_once __DIR__ . '/../../w/extensions/Maps/DefaultSettings.php';
$egMapsDefaultService = 'leaflet';


#Mermaid
# More info: https://github.com/SemanticMediaWiki/Mermaid
wfLoadExtension( 'Mermaid' );
$mermaidgDefaultTheme = 'forest';

# Semantic Compound Queries via Composer
# More info: https://www.mediawiki.org/wiki/Extension:Semantic_Compound_Queries
wfLoadExtension( 'SemanticCompoundQueries' );

# Semantic Extra Special Properties
# More info: https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties
wfLoadExtension( 'SemanticExtraSpecialProperties' );
$sespgEnabledPropertyList = [
        '_CUSER',
	'_PAGEID',
        '_REVID',
	'_NSNAME',
        '_SUBP',
        '_EXIFDATA',
];
$sespgUseFixedTables = true;
$wgDisableCounters = false;

# SemanticMetaTags
# More info: https://www.semantic-mediawiki.org/wiki/Extension:Semantic_Meta_Tags
wfLoadExtension( 'SemanticMetaTags' );
$GLOBALS['smtgTagsProperties'] = [
    // Standard meta tags
        'keywords' => [
        'Numismatic keyword', 'Keyword'
    ],
        'title' => 'Pagename',
        'description' => 'Grand document',
        'author' => 'Author',
    // Summary card tag
        'twitter:title' => 'Pagename',
        'twitter:description' => 'Category',
        'twitter:image' => 'Image URL',
    // Open Graph protocol supported tag
       'og:title' => 'Pagename',
       'og:image' => 'Image URL',
];
$GLOBALS['smtgMetaPropertyPrefixes'] = [
    // Open Graph prefixes
    'og:',
    'fb:'
];


# Lingo und Semantic Glossary via Composer
wfLoadExtension( 'Lingo' );
wfLoadExtension( 'SemanticGlossary' );
$wgexLingoCacheType = CACHE_NONE;

// Add custom namespaces. No "-" allowed in names.
define("NS_GLOSSARY", 190);
define("NS_GLOSSARY_TALK", 191);

$wgExtraNamespaces[NS_GLOSSARY] = "Glossary";
$wgExtraNamespaces[NS_GLOSSARY_TALK] = "Glossary_talk";
$wgexLingoPage = 'Glossary';
$smwgNamespacesWithSemanticLinks[NS_GLOSSARY] = true;

define("NS_FINA", 3000);
define("NS_FINA_TALK", 3000);
$wgExtraNamespaces[NS_FINA] = "FINA";
$wgExtraNamespaces[NS_FINA_TALK] = "FINA_talk";
$smwgNamespacesWithSemanticLinks[NS_FINA] = true;
$smwgNamespacesWithSemanticLinks[NS_FINA_TALK] = true;
$wgNamespacesToBeSearchedDefault[NS_FINA] = true;
$wgNamespacesToBeSearchedDefault[NS_FINA_TALK] = false;

# Semantic Drilldown
# More info: https://www.mediawiki.org/wiki/Extension:Semantic_Drilldown
wfLoadExtension( 'SemanticDrilldown' );
$sdgShowCategoriesAsTabs = true;
$sdgNumResultsPerPage = 500;
$sdgMinValuesForComboBox=50;
$sdgHideCategoriesByDefault = true;

# SemanticCite
# More info: https://github.com/SemanticMediaWiki/SemanticCite/
wfLoadExtension( 'SemanticCite' );

# Semantic Dependency Updater
# More info: https://www.mediawiki.org/wiki/Extension:SemanticDependencyUpdater
wfLoadExtension( 'SemanticDependencyUpdater' );

# Page Forms
# More info: https://www.mediawiki.org/wiki/Extension:Page_Forms
wfLoadExtension( 'PageForms' );
#wfLoadExtension( 'PageReForms' );
$wgPageFormsRenameEditTabs = true;
$sfgRenameEditTabs = true;
$wgPageFormsLinkAllRedLinksToForms = false;
$wgPageFormsRedLinksCheckOnlyLocalProps = true;
//$wgPageFormsCacheAutocompleteValues = true;
//$wgPageFormsAutocompleteCacheTimeout = 60 * 60 * 24; // 1 day (in seconds)
$wgGroupPermissions['*']['viewedittab'] = false;
$wgGroupPermissions['sysop']['viewedittab'] = true;
$wgPageFormsSimpleUpload = true;

#### INTERFACE

# HeaderTabs
# More info: https://www.mediawiki.org/wiki/Extension:Header_Tabs
wfLoadExtension( 'HeaderTabs' );
$wgHeaderTabsRenderSingleTab = true;
$wgHeaderTabsEditTabLink = false;
#$wgHeaderTabsStyle = 'bare';

# Widgets
# More info: https://www.mediawiki.org/wiki/Extension:Widgets
wfLoadExtension( 'Widgets' );

# TitleIcon
# More info: https://www.mediawiki.org/wiki/Extension:Title_Icon
wfLoadExtension( 'TitleIcon' );
$wgTitleIcon_EnableIconInSearchTitle = true;

#NativeSvgHandler
# More info: https://www.mediawiki.org/wiki/Extension:NativeSvgHandler
wfLoadExtension( 'NativeSvgHandler' );

#Link Target
# More info: https://www.mediawiki.org/wiki/Extension:LinkTarget
wfLoadExtension( 'LinkTarget' );
# $wgLinkTargetParentClasses = ' /*ENTER SOME CLASSES HERE*/ ';

#CookieWarning
wfLoadExtension( 'CookieWarning' );
$wgCookieWarningEnabled = false;

#MPDF
# More info: https://www.mediawiki.org/wiki/Extension:Mpdf
wfLoadExtension( 'Mpdf' );
# Toolbox-Link klappt nicht wegen chameleon-skin
$wgMpdfToolboxLink = false;

#Popups
# More info: https://www.mediawiki.org/wiki/Extension:Popups
wfLoadExtension( 'Popups' );
$wgPopupsTextExtractsIntroOnly = false;
$wgExtractsRemoveClasses = [
                "script",
                "input",
                "style",
                "ul.gallery",
                ".mw-editsection",
                "sup.reference",
                "ol.references",
                ".error",
                ".nomobile",
                ".noprint",
                ".noexcerpt",
                ".sortkey"
              ];

#### HELPERS

# External Data
# More info:
wfLoadExtension( 'ExternalData' );

# Data Transfer
# More info: https://www.mediawiki.org/wiki/Extension:Data_Transfer
wfLoadExtension( 'DataTransfer' );
$wgPhpCli = false;

# DeleteBatch
# More info: https://www.mediawiki.org/wiki/Extension:DeleteBatch
wfLoadExtension( 'DeleteBatch' );

# SimpleBatchUpload
# More info: https://www.mediawiki.org/wiki/Extension:SimpleBatchUpload
wfLoadExtension( 'SimpleBatchUpload' );

# Matomo Analytics
wfLoadExtension( 'MatomoAnalytics' );

#Import users
#https://www.mediawiki.org/wiki/Extension:ImportUsers
wfLoadExtension( 'ImportUsers' );

#Who's Online
#wfLoadExtension( 'WhosOnline' );

#Admin Links
wfLoadExtension( 'AdminLinks' );

#RottenLinks
#More info: https://www.mediawiki.org/wiki/Extension:RottenLinks
wfLoadExtension( 'RottenLinks' );

#RSS
wfLoadExtension( 'RSS' );

#MyVariables
wfLoadExtension( 'MyVariables' );

#Variables
wfLoadExtension( 'Variables' );

#UrlGetParameters
# https://www.mediawiki.org/wiki/Extension:UrlGetParameters
wfLoadExtension( 'UrlGetParameters' );

#RightFunctions
# More info: https://www.mediawiki.org/wiki/Extension:RightFunctions
wfLoadExtension( 'RightFunctions' );

#UserFunctions
# More info: https://www.mediawiki.org/wiki/Extension:UserFunctions
wfLoadExtension( 'UserFunctions' );

# Lockdown
wfLoadExtension( 'Lockdown' );

#CodeMirror
wfLoadExtension( 'CodeMirror' );
$wgDefaultUserOptions['usecodemirror'] = 1;
$wgCodeMirrorEnableBracketMatching = true;
$wgCodeMirrorAccessibilityColors = true;
$wgCodeMirrorLineNumberingNamespaces = null;

wfLoadExtension( 'TemplateStyles' );


$wgPagePropertiesDisableSidebarLink = true;

$smwgQMaxSize = 32;

wfLoadExtension( 'KnowledgeGraph' );
