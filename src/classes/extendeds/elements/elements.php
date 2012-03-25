<?php
/** flatLand! : zewo
 * /classes/extendeds/elements/element.php
 */

namespace Zewo\Extendeds\Elements;

class Elements extends \Zewo\ORM\Elements\Elements {

	public function __construct( $sTargetClass, $sQuery ) {
		return parent::__construct( $sTargetClass, $sQuery );
	} // __construct

	public function assign( $sName ) {
		\Zewo\Zewo::getInstance()->tpl->assignByRef( $sName, $this );
		return $this;
	} // assign

} // class::Elements
