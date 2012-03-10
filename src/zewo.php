<?php
/** flatLand! : zewo
 * /zewo.php : main entry point
 */

namespace Zewo;

// TODO : loading all classes
include( __DIR__ . '/tools/singleton.php' );

include( __DIR__ . '/classes/utils/utils.php' );
include( __DIR__ . '/classes/utils/convertor.php' );
include( __DIR__ . '/classes/utils/globals.php' );

include( __DIR__ . '/classes/config/config.php' );

include( __DIR__ . '/classes/db/db.php' );

include( __DIR__ . '/classes/routing/router.php' );
include( __DIR__ . '/classes/routing/route.php' );
include( __DIR__ . '/classes/routing/error_route.php' );

include( __DIR__ . '/classes/zewo.php' );

// TODO : init shits and stuffs
static $zewo;
$zewo = Zewo::getInstance();

// TODO : init static $zewo variable
