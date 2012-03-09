<?php
/** flatLand! - zewo
 * /tools/singleton.php
 */

namespace Zewo\Tools;

class Singleton {

	final public static function getInstance() {
		$sClass = get_called_class();
		if( !isset( self::$_aInstances[ $sClass ] ) )
			self::$_aInstances[ $sClass ] = new $sClass;
		return self::$_aInstances[ $sClass ];
	} // getInstance

	final public function __clone() {
		trigger_error( "Le clonage d'un singleton n'est pas autorisé.", E_USER_ERROR );
	} // __clone

	protected function __construct() {} // __construct

	private static $_aInstances = array();

} // class::Singleton
