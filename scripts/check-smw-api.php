<?php
// Exercise SMW's real registration hook against a minimal API registry.
// No MediaWiki startup or database connection is required for this check.
class ApiModuleManager {
    public $modules = [];
    public function addModules( array $modules, $group ) {
        if ( $group === 'action' ) {
            $this->modules = array_merge( $this->modules, $modules );
        }
    }
}
$source = $argv[1];
require "$source/src/MediaWiki/HookListener.php";
require "$source/src/OptionsAwareTrait.php";
require "$source/src/MediaWiki/Hooks/ApiModuleManager.php";
$hook = new class extends \SMW\MediaWiki\Hooks\ApiModuleManager {
    public function getOption( $key, $default = null ) {
        return $key === 'SMW_EXTENSION_LOADED' ? true : $default;
    }
};
$registry = new ApiModuleManager();
$hook->process( $registry );
if ( isset( $registry->modules['smwtask'] ) ||
    !isset( $registry->modules['ask'], $registry->modules['askargs'], $registry->modules['smwbrowse'] ) ) {
    throw new RuntimeException( 'Unexpected SMW API module registration.' );
}
echo "SMW API check passed: smwtask absent; query/browse modules retained.\n";
