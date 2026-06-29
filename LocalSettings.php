<?php
# =====================================================
# FINA MediaWiki Configuration (Cleaned & Structured)
# =====================================================

# -----------------------------------------------------
# BASIC CONFIG
# -----------------------------------------------------

$wgServer = getenv('PUBLIC_URL') ?: 'https://fina.oeaw.ac.at';
$wgCanonicalServer = $wgServer;

$wgSitename = 'Fina Wiki';
$wgMetaNamespace = "FINA";
$wgLanguageCode = 'en';

$wgScriptPath = "";
$wgArticlePath = "/$1";
$wgUsePathInfo = false;

$wgScriptExtension = ".php";

# Branding
$wgLogo = "fina-logo.png";
$wgFavicon = "favicon.png";

# -----------------------------------------------------
# DATABASE
# -----------------------------------------------------
$wgDBtype = 'mysql';
$wgDBserver = getenv('MYSQL_SERVER');
$wgDBname = getenv('MYSQL_DB');
$wgDBuser = getenv('MYSQL_USER');
$wgDBpassword = getenv('MYSQL_PASSWORD');
$wgDBprefix = "fw";

$wgDBTableOptions = "ENGINE=InnoDB, DEFAULT CHARSET=binary";

# -----------------------------------------------------
# UPLOADS
# -----------------------------------------------------
$wgEnableUploads = true;
$wgUploadDirectory = "images";
$wgUploadPath = "$wgScriptPath/img_auth.php";

$wgFileExtensions = [
    'jpg','jpeg','png','gif','doc','docx','xls','xlsx',
    'ppt','pptx','pdf','odt','zip','svg','txt'
];

$wgAllowJavaUploads = true;
$wgVerifyMimeType = false;

# -----------------------------------------------------
# EMAIL CONFIG
# -----------------------------------------------------
$wgEnableEmail = true;
$wgEnableUserEmail = true;
$wgEmailAuthentication = true;

$wgSMTP = [
    'host'     => getenv('MAIL_SMTP_HOST'),
    'IDHost'   => getenv('MAIL_SMTP_HOST'),
    'port'     => 25,
    'auth'     => true,
    'username' => getenv('MAIL_SMTP_USER'),
    'password' => getenv('MAIL_SMTP_PASSWORD')
];

$wgEmergencyContact = getenv('EMERGENCY_CONTACT');
$wgPasswordSender   = getenv('EMERGENCY_CONTACT');

# -----------------------------------------------------
# PERFORMANCE & CACHE
# -----------------------------------------------------
$wgMainCacheType = CACHE_ACCEL;
$wgSessionCacheType = CACHE_DB;
$wgMemCachedServers = [];

$wgDisableCounters = true;
$wgMiserMode = true;
$wgJobRunRate = 0;

$wgUseGzip = true;
$wgEnableSidebarCache = true;

$wgRevisionCacheExpiry = 3 * 24 * 3600;
$wgParserCacheExpireTime = 14 * 24 * 3600;

# -----------------------------------------------------
# SEARCH (OpenSearch / Cirrus)
# -----------------------------------------------------
// wfLoadExtension('Elastica');
// wfLoadExtension('CirrusSearch');
// wfLoadExtension('AdvancedSearch');

$wgSearchType = 'CirrusSearch';

$wgCirrusSearchServers = [
    [
        "host" => getenv('OPENSEARCH_SERVER'),
        "port" => 9200
    ]
];

$wgCirrusSearchIndexBaseName = 'fina';

# -----------------------------------------------------
# CORE EXTENSIONS
# -----------------------------------------------------
wfLoadExtension('ParserFunctions');
$wgPFEnableStringFunctions = true;

wfLoadExtension('Scribunto');
$wgScribuntoDefaultEngine = 'luasandbox';

wfLoadExtension('Cite');
wfLoadExtension('CategoryTree');
wfLoadExtension('TemplateData');
wfLoadExtension('WikiEditor');

# -----------------------------------------------------
# SEMANTIC MEDIAWIKI CORE
# -----------------------------------------------------
wfLoadExtension('SemanticMediaWiki');
wfLoadExtension('SemanticResultFormats');


# --------------------------------------------------
# SEMANTIC STACK (order matters!)
# --------------------------------------------------

// wfLoadExtension('Validator');       // MUST be first
require_once "$IP/extensions/Validator/Validator.php";
// wfLoadExtension('ParamProcessor'); // dependency of Validator

wfLoadExtension('Maps');
$egMapsDefaultService = 'leaflet';

// wfLoadExtension('SemanticCompoundQueries');
// wfLoadExtension('SemanticExtraSpecialProperties');
// wfLoadExtension('SemanticMetaTags');
// wfLoadExtension('SemanticGlossary');
// wfLoadExtension('SemanticDrilldown');
// wfLoadExtension('SemanticCite');
wfLoadExtension('PageForms');


# SMW config
/*
$smwgParserFeatures =
    SMW_PARSER_STRICT |
    SMW_PARSER_INL_ERROR |
    SMW_PARSER_HID_CATS |
    SMW_PARSER_UNSTRIP |
    SMW_PARSER_LINV;
*/

$smwgQMaxLimit = 20000;
$smwgQMaxInlineLimit = 20000;
$smwgQSubcategoryDepth = 10;

$smwgEnableUpdateJobs = true;


$smwgPageSpecialProperties = ['_MDAT', '_CDAT', '_NEWP', '_LEDT'];

# -----------------------------------------------------
# ADDITIONAL EXTENSIONS (UI & UX)
# -----------------------------------------------------
// wfLoadExtension('Popups');
$wgPopupsTextExtractsIntroOnly = false;

// wfLoadExtension('MatomoAnalytics');

wfLoadExtension('Widgets');
// wfLoadExtension('HeaderTabs');

// wfLoadExtension('NativeSvgHandler');
// wfLoadExtension('LinkTarget');
wfLoadExtension('TemplateStyles');

# -----------------------------------------------------
# SKINS
# -----------------------------------------------------
// wfLoadSkin('Vector');
wfLoadSkin('MonoBook');
// wfLoadSkin('Timeless');

$wgDefaultSkin = 'MonoBook';

# -----------------------------------------------------
# NAMESPACES (custom)
# -----------------------------------------------------
define("NS_FINA", 3000);
define("NS_FINA_TALK", 3001);

$wgExtraNamespaces[NS_FINA] = "FINA";
$wgExtraNamespaces[NS_FINA_TALK] = "FINA_talk";

# Enable semantic links in namespace
$smwgNamespacesWithSemanticLinks[NS_FINA] = true;
$smwgNamespacesWithSemanticLinks[NS_FINA_TALK] = true;

# -----------------------------------------------------
# PERMISSIONS
# -----------------------------------------------------
$wgGroupPermissions['*']['createaccount'] = false;
$wgGroupPermissions['*']['read'] = true;
$wgGroupPermissions['*']['edit'] = false;

# -----------------------------------------------------
# FILE / SYSTEM
# -----------------------------------------------------
$wgImageMagickConvertCommand = '/usr/bin/convert';
$wgShellLocale = "C.UTF-8";

# -----------------------------------------------------
# SECURITY & DEBUG
# -----------------------------------------------------
$wgShowExceptionDetails = true;

# -----------------------------------------------------
# FINAL SMW INIT
# -----------------------------------------------------

$smwHost = parse_url(
    $wgServer ?: 'https://fina.oeaw.ac.at',
    PHP_URL_HOST
);

if ( function_exists( 'enableSemantics' ) ) {
    enableSemantics( $smwHost );
}