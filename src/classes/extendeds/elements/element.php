<?php
/** flatLand! : zewo
 * /classes/extendeds/elements/element.php
 */

namespace Zewo\Extendeds\Elements;

abstract class Element extends \Zewo\ORM\Elements\Element {

	public function __construct( $sTable, $mQuery ) {
		return parent::__construct( $sTable, $mQuery );
	} // __construct

	public static function restore( $sKey = null ) {
		if( is_null( $sKey ) )
			return false && trigger_error( "Tentative de récupération d'un object [" . get_called_class() . "] sans clé.", E_USER_WARNING );
		return \Zewo\Zewo::getInstance()->globals->session( $sKey ) ? unserialize( base64_decode( \Zewo\Zewo::getInstance()->globals->session( $sKey ) ) ) : false && trigger_error( "L'object [" . get_called_class() . "] stocké sous le nom '".$sKey."' n'existe pas.", E_USER_NOTICE);
	} // restore

	public function store( $sKey ) {
		if( is_null( $sKey ) )
			return false && trigger_error( "Tentative de stockage d'un object [" . get_called_class() . "] sans clé.", E_USER_NOTICE );
		\Zewo\Zewo::getInstance()->globals->session( $sKey, base64_encode( serialize( $this ) ) );
	} // store

	public function assign( $sName ) {
		\Zewo\Zewo::getInstance()->tpl->assignByRef( $sName, $this );
		return $this;
	} // assign

	public static function get( $sQuery ) {
		return new \Zewo\Extendeds\Elements\Elements( get_called_class(), $sQuery );
	} // get

} // class::Element
