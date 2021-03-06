<?php
/** flatLand! : zewo
 * /classes/extendeds/elements/elements.php
 */

namespace Zewo\Extendeds\Elements;

class Elements extends \Zewo\ORM\Elements\Elements {

	public function __construct( $sTargetClass, $sQuery, $bFromCache = true ) {
		return parent::__construct( $sTargetClass, $sQuery, $bFromCache );
	} // __construct

	public function assign( $sName ) {
		\Zewo\Zewo::getInstance()->tpl->assignByRef( $sName, $this );
		return $this;
	} // assign

	public function trace() {
		\Zewo\Zewo::getInstance()->utils->trace( $this );
	} // trace

} // class::Elements
