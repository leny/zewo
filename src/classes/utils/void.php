<?php
/** flatLand! : zewo
 * /classes/utils/void.php
 */

namespace Zewo\Utils;

class Void {

	public function __get( $sName ) {
		return new Void();
	} // __get

	public function __set( $sName, $mValue ) {
		throw new \RuntimeException( "Can't set a property to Void class !" );
	} // __set

	public function __isset( $sName ) {
		return false;
	} // __isset

	public function __unset( $sName ) {
		throw new \RuntimeException( "Can't unset a property to Void class !" );
	} // __unset

	public function __call( $sName, $aArguments ) {
		return null;
	} // __call

	public function __toString() {
		return '';
	} // __toString

	public function __invoke() {
		return new Void();
	} // __invoke

	public function __construct() {
		return null;
	} // __construct

	public static function __callStatic( $sName, $aArguments ) {
		return new Void();
	} // __callStatic

} // class::Void
