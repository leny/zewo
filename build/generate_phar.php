<?php
/** flatLand! : zewo
 * build/generate_phar.php : generate phar script
 */

// must be executed with php cli

define( 'TARGET', __DIR__ . '/../bin/zewo.phar' );
define( 'SOURCES', __DIR__ . '/../src/' );

if( file_exists( TARGET ) )
	unlink( TARGET );

$oPhar = new Phar( TARGET, 0, 'zewo.phar' );
$oPhar->buildFromDirectory( SOURCES , '/\.php$/' );
$oPhar->setStub( $oPhar->createDefaultStub( 'zewo.php', 'zewo.php' ) );
$oPhar->stopBuffering();

if( file_exists( TARGET ) )
	die( "L'archive zewo.phar a bien été générée.\n" );
else
	die( "L'archive n'a pas pu être générée.\n" );
