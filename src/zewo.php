<?php
/** flatLand! : zewo
 * /zewo.php : main entry point
 */

namespace Zewo;

include( __DIR__ . '/tools/singleton.php' );
include( __DIR__ . '/tools/cache/cache.php' );
include( __DIR__ . '/tools/cache/apc.php' );
include( __DIR__ . '/tools/cached.php' );

include( __DIR__ . '/classes/utils/utils.php' );
include( __DIR__ . '/classes/utils/convertor.php' );
include( __DIR__ . '/classes/utils/globals.php' );

include( __DIR__ . '/classes/config/config.php' );

include( __DIR__ . '/classes/db/db.php' );

include( __DIR__ . '/classes/orm/structure/table.php' );
include( __DIR__ . '/classes/orm/structure/column.php' );
include( __DIR__ . '/classes/orm/elements/element.php' );
include( __DIR__ . '/classes/orm/elements/elements.php' );
include( __DIR__ . '/classes/orm/orm.php' );

include( __DIR__ . '/classes/template/template.php' );
include( __DIR__ . '/classes/template/templating.php' );

include( __DIR__ . '/classes/routing/router.php' );
include( __DIR__ . '/classes/routing/route.php' );
include( __DIR__ . '/classes/routing/error_route.php' );

include( __DIR__ . '/classes/zewo.php' );

static $zewo;
$zewo = Zewo::getInstance();
