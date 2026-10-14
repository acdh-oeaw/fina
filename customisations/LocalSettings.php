<?php
// Shared production/local FINA configuration. Secrets are supplied at runtime.
if (!defined('MEDIAWIKI')) {
    exit;
}
foreach (['MYSQL_SERVER', 'MYSQL_DB', 'MYSQL_USER', 'MYSQL_PASSWORD', 'MW_SECRET_KEY'] as $key) {
    if (!getenv($key)) {
        throw new RuntimeException("Missing environment setting: $key");
    }
}
$finaLocal = getenv('FINA_ENV') === 'local';
$wgServer = getenv('PUBLIC_URL') ?: 'https://fina.oeaw.ac.at';
$wgCanonicalServer = $wgServer;
$wgSitename = $finaLocal ? 'FINA — 1.43 upgrade test' : 'Fina Wiki';
$wgMetaNamespace = 'FINA';
$wgLanguageCode = 'en';
$wgScriptPath = '';
$wgArticlePath = '/$1';
$wgUsePathInfo = true;
// Preserve the existing FINA key unless deployment explicitly supplies an override.
$wgSecretKey = getenv('MW_SECRET_KEY');
$wgDBtype = 'mysql';
$wgDBserver = getenv('MYSQL_SERVER');
$wgDBname = getenv('MYSQL_DB');
$wgDBuser = getenv('MYSQL_USER');
$wgDBpassword = getenv('MYSQL_PASSWORD');
$wgDBprefix = 'fw';
$wgDBTableOptions = 'ENGINE=InnoDB, DEFAULT CHARSET=binary';
$wgEnableEmail = !$finaLocal && (bool)getenv('MAIL_SMTP_HOST');
$wgEnableUserEmail = $wgEnableEmail;
$wgEmailAuthentication = $wgEnableEmail;
$wgEnotifUserTalk = !$finaLocal;
$wgEnotifWatchlist = !$finaLocal;
if ($wgEnableEmail) {
    $wgSMTP = [
        'host' => getenv('MAIL_SMTP_HOST'), 'IDHost' => getenv('MAIL_SMTP_HOST'),
        'port' => 25, 'auth' => true,
        'username' => getenv('MAIL_SMTP_USER'), 'password' => getenv('MAIL_SMTP_PASSWORD'),
    ];
    $wgEmergencyContact = getenv('EMERGENCY_CONTACT');
    $wgPasswordSender = $wgEmergencyContact;
}
$wgPingback = false;
$wgJobRunRate = 0;
$wgMainCacheType = CACHE_ACCEL;
$wgSessionCacheType = CACHE_DB;
$wgCacheDirectory = '/tmp/mw-cache';
$wgLocalisationCacheConf['store'] = 'file';
// Invalidate parser caches older than the configured upgrade cutoff.
$wgCacheEpoch = '20261002200000';
$wgMiserMode = true;
$wgParserCacheExpireTime = 14 * 24 * 3600;
$wgRevisionCacheExpiry = 3 * 24 * 3600;
$wgCategoryCollation = 'numeric';
$wgMaxArticleSize = 4096;
$wgEnableUploads = true;
$wgUploadDirectory = "$IP/images";
$wgUploadPath = "$wgScriptPath/img_auth.php";
// Parsing writes UUID state here; it must not depend on the uploads volume.
$wgTmpDirectory = '/tmp/mw-tmp';
$wgUseImageMagick = false;
$wgFileExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'svg', 'txt', 'ico', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'zip'];
$wgShowExceptionDetails = $finaLocal;
$wgShowDBErrorBacktrace = $finaLocal;
ini_set('display_errors', $finaLocal ? '1' : '0');
$wgLocaltimezone = 'UTC';
$wgGroupPermissions['*']['read'] = true;
foreach (['edit', 'createaccount', 'createpage', 'createtalk', 'writeapi'] as $right) {
    $wgGroupPermissions['*'][$right] = false;
}
// Page Forms uses the write API when a signed-in editor saves a form.
$wgGroupPermissions['user']['writeapi'] = true;
// FINA is the project namespace (4), where About/Imprint/privacy pages live.
// Do not shadow it with an empty custom namespace numbered 3000.
$wgNamespacesToBeSearchedDefault[NS_PROJECT] = true;

foreach ([
    'ParserFunctions', 'Scribunto', 'Cite', 'CategoryTree', 'TemplateData',
    'WikiEditor', 'TemplateStyles', 'CiteThisPage', 'CodeEditor', 'ConfirmEdit',
    'Gadgets', 'ImageMap', 'InputBox', 'Interwiki', 'Nuke', 'OATHAuth',
    'PageImages', 'PdfHandler', 'Poem', 'ReplaceText', 'SecureLinkFixer',
    'SyntaxHighlight_GeSHi', 'TextExtracts',
    'SemanticMediaWiki', 'SemanticResultFormats', 'Maps', 'PageForms',
    'Widgets', 'NativeSvgHandler', 'LinkTarget', 'DataTransfer', 'AdminLinks',
    'RSS', 'MyVariables', 'Variables', 'UrlGetParameters', 'UserFunctions',
    'RightFunctions', 'Mpdf', 'CookieWarning', 'Popups', 'Lockdown',
    'CodeMirror', 'Lingo', 'ModernTimeline', 'Bootstrap',
    'ExternalData', 'TitleIcon', 'SemanticDrilldown', 'SemanticCite', 'Mermaid', 'Network',
] as $extension) {
    wfLoadExtension($extension);
}
$wgPFEnableStringFunctions = true;
$wgPFStringLengthLimit = 10000;
$wgScribuntoDefaultEngine = 'luasandbox';
$smwgQMaxLimit = 20000;
$smwgQMaxInlineLimit = 20000;
$smwgQMaxSize = 32;
$smwgQSubcategoryDepth = 10;
$smwgEntityCollation = 'numeric';
$smwgPageSpecialProperties = ['_MDAT', '_CDAT', '_NEWP', '_LEDT'];
$smwgNamespacesWithSemanticLinks[NS_PROJECT] = true;
$smwgParserFeatures = ['strict', 'inline-errors', 'hidden-categories', 'unstrip', 'links-in-values'];
$srfgFormats[] = 'incoming';
$srfgMapProvider = 'OpenStreetMap.Mapnik';
$egMapsDefaultService = 'leaflet';
$egMapsEnableCategory = false;
$wgPageFormsRenameEditTabs = true;
$wgGroupPermissions['*']['viewedittab'] = false;
$wgGroupPermissions['sysop']['viewedittab'] = true;
$wgPageFormsLinkAllRedLinksToForms = false;
$wgPageFormsRedLinksCheckOnlyLocalProps = true;
$wgPageFormsSimpleUpload = true;
$wgWidgetsCompileDir = '/tmp/widgets';
wfLoadSkin('Vector');
wfLoadSkin('chameleon');
$wgDefaultSkin = 'vector';
require __DIR__ . '/FinaSettings.php';
