<?php
// Used only inside the production image, before Composer resolves dependencies.
// --no-dev alone still resolves require-dev when a lock file is missing.
foreach ( array_slice( $argv, 1 ) as $directory ) {
    if ( is_file( "$directory/composer.lock" ) ) {
        continue;
    }
    $path = "$directory/composer.json";
    // Decode as objects so empty JSON objects remain objects when written back.
    $package = json_decode( file_get_contents( $path ), false, 512, JSON_THROW_ON_ERROR );
    unset( $package->{'require-dev'}, $package->{'autoload-dev'} );
    if ( file_put_contents( $path, json_encode( $package,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR ) . "\n" ) === false ) {
        throw new RuntimeException( "Cannot write $path" );
    }
}
