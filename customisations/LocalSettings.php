<?php
# =====================================================
# FINA MediaWiki Configuration
# =====================================================

# -----------------------------------------------------
# DEBUG (disable in production)
# -----------------------------------------------------
# error_reporting( -1 );
# ini_set( 'display_errors', 1 );
# $wgShowExceptionDetails = true;
# $wgShowDBErrorBacktrace = true;

# -----------------------------------------------------
# BASIC CONFIG
# -----------------------------------------------------

if ( !defined( 'MEDIAWIKI' ) ) {
    exit;
}

$wgServer = getenv('PUBLIC_URL') ?: 'https://fina.oeaw.ac.at';
$wgCanonicalServer = $wgServer;

$wgSitename = 'Fina Wiki';
$wgMetaNamespace = "FINA";
$wgLanguageCode = 'en';

$wgScriptPath = "";
$wgArticlePath = "/$1";
$wgUsePathInfo = true;
$wgScriptExtension = ".php";
$wgResourceBasePath = $wgScriptPath;

$wgSecretKey = getenv('MW_SECRET_KEY') ?: "da5bfc1128bbf222b83d2affdad246e41ee0e775413cbd76f54132b5bb3516b7";

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

$wgPingback = false;

# -----------------------------------------------------
# UPLOADS
# -----------------------------------------------------

$wgEnableUploads = true;
$wgUploadDirectory = "images";
$wgUploadPath = "$wgScriptPath/img_auth.php";
$wgUseImageMagick = false;
$wgTmpDirectory = __DIR__ . "/images/temp";

$wgFileExtensions = [
    'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx', 'xls', 'xlsx',
    'ppt', 'pptx', 'pdf', 'odt', 'zip', 'svg', 'txt', 'ico'
];

$wgAllowJavaUploads = true;
$wgVerifyMimeType = false;

# -----------------------------------------------------
# EMAIL
# -----------------------------------------------------

$wgEnableEmail = true;
$wgEnableUserEmail = true;
$wgEmailAuthentication = true;

$wgEnotifUserTalk = true;
$wgEnotifWatchlist = true;

$wgSMTP = [
    'host'     => getenv('MAIL_SMTP_HOST'),
    'IDHost'   => getenv('MAIL_SMTP_HOST'),
    'port'     => 25,
    'auth'     => true,
    'username' => getenv('MAIL_SMTP_USER'),
    'password' => getenv('MAIL_SMTP_PASSWORD'),
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
$wgCachePages = true;
$wgEnableSidebarCache = true;

$wgRevisionCacheExpiry = 3 * 24 * 3600;
$wgParserCacheExpireTime = 14 * 24 * 3600;

$wgMaxArticleSize = 4096;
$wgCategoryCollation = 'numeric';

# -----------------------------------------------------
# SEARCH
# -----------------------------------------------------

# CirrusSearch disabled until Elastica/CirrusSearch extensions are added
# wfLoadExtension( 'Elastica' );
# wfLoadExtension( 'CirrusSearch' );
# wfLoadExtension( 'AdvancedSearch' );
# $wgSearchType = 'CirrusSearch';

# $wgCirrusSearchServers = [
#     [
#         "host" => getenv('OPENSEARCH_SERVER'),
#         "port" => 9200,
#     ]
# ];
# $wgCirrusSearchIndexBaseName = 'fina';

# -----------------------------------------------------
# NAMESPACES (must be before extensions that use them)
# -----------------------------------------------------

define("NS_FINA", 3000);
define("NS_FINA_TALK", 3001);

$wgExtraNamespaces[NS_FINA] = "FINA";
$wgExtraNamespaces[NS_FINA_TALK] = "FINA_talk";

$wgNamespacesToBeSearchedDefault[NS_FINA] = true;
$wgNamespacesToBeSearchedDefault[NS_FINA_TALK] = false;

# -----------------------------------------------------
# CORE EXTENSIONS
# -----------------------------------------------------

wfLoadExtension( 'ParserFunctions' );
$wgPFEnableStringFunctions = true;
$wgPFStringLengthLimit = 10000;

wfLoadExtension( 'Scribunto' );
$wgScribuntoDefaultEngine = 'luastandalone';

wfLoadExtension( 'Cite' );
wfLoadExtension( 'CategoryTree' );
wfLoadExtension( 'TemplateData' );
wfLoadExtension( 'WikiEditor' );
wfLoadExtension( 'TemplateStyles' );

# -----------------------------------------------------
# SEMANTIC MEDIAWIKI
# -----------------------------------------------------

wfLoadExtension( 'SemanticMediaWiki' );

$smwHost = parse_url(
    $wgServer ?: 'https://fina.oeaw.ac.at',
    PHP_URL_HOST
);

if ( function_exists( 'enableSemantics' ) ) {
    enableSemantics( $smwHost );
}

# SMW config
$smwgQMaxLimit = 20000;
$smwgQMaxInlineLimit = 20000;
$smwgQMaxSize = 32;
$smwgQSubcategoryDepth = 10;
$smwgEnableUpdateJobs = true;
$smwgCompactLinkSupport = false;
$smwgEntityCollation = 'numeric';

$smwgPageSpecialProperties = [ '_MDAT', '_CDAT', '_NEWP', '_LEDT' ];

$smwgNamespacesWithSemanticLinks[NS_FINA] = true;
$smwgNamespacesWithSemanticLinks[NS_FINA_TALK] = true;

$smwgParserFeatures =
    SMW_PARSER_STRICT |
    SMW_PARSER_INL_ERROR |
    SMW_PARSER_HID_CATS |
    SMW_PARSER_UNSTRIP |
    SMW_PARSER_LINV;

# -----------------------------------------------------
# SEMANTIC RESULT FORMATS
# -----------------------------------------------------

wfLoadExtension( 'SemanticResultFormats' );

$srfgFormats[] = 'incoming';
$srfgMapProvider = 'OpenStreetMap.Mapnik';

# -----------------------------------------------------
# MAPS & VALIDATOR
# -----------------------------------------------------

require_once "$IP/extensions/Validator/Validator.php";

wfLoadExtension( 'Maps' );
$egMapsDefaultService = 'leaflet';
$egMapsEnableCategory = false;

# -----------------------------------------------------
# PAGE FORMS
# -----------------------------------------------------

wfLoadExtension( 'PageForms' );
$wgPageFormsRenameEditTabs = true;
$sfgRenameEditTabs = true;
$wgPageFormsLinkAllRedLinksToForms = false;
$wgPageFormsRedLinksCheckOnlyLocalProps = true;
$wgPageFormsSimpleUpload = true;
$wgGroupPermissions['*']['viewedittab'] = false;
$wgGroupPermissions['sysop']['viewedittab'] = true;

# -----------------------------------------------------
# WIDGETS
# -----------------------------------------------------

wfLoadExtension( 'Widgets' );

# -----------------------------------------------------
# SKINS
# -----------------------------------------------------

wfLoadExtension( 'Bootstrap' );
wfLoadExtension( 'Kma' );

wfLoadSkin( 'Kma' );
wfLoadSkin( 'Vector' );

$wgDefaultSkin = 'kma';

$egChameleonLayoutFile = 'skins/Kma/SITE/FINA-layout.xml';
$egChameleonThemeFile = 'skins/Kma/SITE/f_variables.scss';

$egChameleonExternalStyleModules = [
    'skins/Kma/SITE/f_bootswatch.scss' => 'afterMain',
];

$egChameleonEnableVisualEditor = true;
$egChameleonEnableExternalLinkIcons = false;

$egChameleonExternalStyleVariables = [
    'body-bg'                     => '#ffffff',
    'font-family-base'            => "Roboto,sans-serif",
    'headings-color'              => '#0047bb',
    'headings-font-family'        => "Alegreya,serif",
    'dark'                        => '#5c6784',
    'light'                       => '#f5f5f5',
    'primary'                     => '#00122f',
    'secondary'                   => '#9eaeb5',
    'info'                        => '#0355ad',
    'danger'                      => '#910a00',
    'warning'                     => '#e39f00',
    'success'                     => '#009e4c',
    'cmln-navbar-logo-height'     => '1.0rem',
    'cmln-navbar-bg-color'        => 'light',
    'cmln-search-bar-btn-color'   => 'light',
    'navbar-light-color'          => '#0047bb',
    'navbar-light-hover-color'    => '#0099ff',
    'navbar-light-active-color'   => '#0355ad',
    'navbar-padding-y'            => '1.0rem',
    'dropdown-link-hover-bg'      => '#0355ad',
    'dropdown-link-hover-color'   => '#f5f5f5',
    'dropdown-link-active-color'  => '#ffffff',
    'dropdown-link-active-bg'     => '#0355ad',
    'dropdown-link-color'         => '#0355ad',
    'font-size-base'              => '1.0rem',
    'h1-font-size'                => '1.8rem',
    'h2-font-size'                => '1.6rem',
    'h3-font-size'                => '1.4rem',
    'h4-font-size'                => '1.2rem',
    'h5-font-size'                => '1.2rem',
    'jumbotron-bg'                => '#f0f0f0',
    'card-cap-bg'                 => '#f0f0f0',
    'card-spacer-y'               => '0.5rem',
    'card-spacer-x'               => '1rem',
    'table-cell-padding-sm'       => '0.2rem',
    'cmln-link-formats'           => "(new: ('color': #f60000, 'hover-color': #6e0000), stub: #0099ff #007aff #10345a underline, extiw: #1b599b none #10345a underline, external: #044eae #ff0000 #2d125d underline)",
];

# -----------------------------------------------------
# PERMISSIONS
# -----------------------------------------------------

$wgGroupPermissions['*']['createaccount'] = false;
$wgGroupPermissions['*']['read']          = true;
$wgGroupPermissions['*']['edit']          = false;
$wgGroupPermissions['*']['createpage']    = false;
$wgGroupPermissions['*']['createtalk']    = false;
$wgGroupPermissions['*']['writeapi']      = false;

# -----------------------------------------------------
# FOOTER
# -----------------------------------------------------

$wgFooterIcons['poweredby']['knowledge.wiki'] = [
    "src"    => "/KnowledgeWiki.png",
    "url"    => "https://fina.oeaw.ac.at",
    "alt"    => "a Knowledge.wiki project",
    "height" => "31",
    "width"  => "100",
];

unset( $wgFooterIcons['poweredby']['mediawiki'] );

$wgRightsUrl  = "https://creativecommons.org/share-your-work/public-domain/cc0/";
$wgRightsText = "CC0";
$wgRightsIcon = "https://licensebuttons.net/p/zero/1.0/88x31.png";

# -----------------------------------------------------
# SYSTEM
# -----------------------------------------------------

$wgImageMagickConvertCommand = '/usr/bin/convert';
$wgShellLocale = "C.UTF-8";
$wgLocaltimezone = 'UTC';

# -----------------------------------------------------
# ERROR HANDLING (production)
# -----------------------------------------------------

$wgShowExceptionDetails = false;
$wgDevelopmentWarnings = false;

error_reporting( E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED );
ini_set( 'display_errors', 0 );
