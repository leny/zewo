<?php
/** flatLand! : zewo
 * /classes/utils/utils.php
 */

namespace Zewo\Utils;

class Utils extends \Zewo\Tools\Singleton {

	public function __get( $sName ) {
		switch( $sName ) {
			case 'convertor':
				return \Zewo\Utils\Convertor::getInstance();
				break;
			case 'global':
			case 'globals':
				return \Zewo\Utils\Globals::getInstance();
				break;
		}
	} // __get

	public function trace() {
		$aAttributes = func_get_args();
		if( !is_array( $aAttributes ) )
			die( 'nothing to debug' );
		$aBacktrace = debug_backtrace();
		$aBacktrace = reset( $aBacktrace );
		echo '<div style="border: 1px solid #ffc266; background: #ffffcc; padding: 2px 5px; margin: 5px; font-size: 11px; font-family: Verdana;">';
			echo '<strong style="color: #ff944c;"><small>trace: ' . str_replace( $_SERVER[ 'DOCUMENT_ROOT' ], '', $aBacktrace[ 'file' ] ) . ' ln. ' . $aBacktrace[ 'line' ] . '</small></strong>';
		for( $i=0; $i<count( $aAttributes ); $i++ ) {
			if( $i > 0 )
				echo '<hr style="height: 1px; border: 0; background: #ffd699" />';
			echo '<pre>';
				var_dump( $aAttributes[ $i ] );
			echo '</pre>';
		}
		echo '</div>';
	} // trace

	public function load( $mPaths ) {
		$aFiles = ( is_array( $mPaths ) && sizeof( $mPaths ) ) ? $mPaths : glob( $mPaths );
		foreach( $aFiles as $sFilePath )
			include( $sFilePath );
	} // load

	public function genUID() {
		return substr( md5( uniqid() ), 0, 8 );
	} // genUID

	public function now( $iDecalage = 0 ) {
		return time() + $iDecalage;
	} // now

	public function datetime( $iDecalage = 0 ) {
		return strftime( '%Y-%m-%d %H:%M:%S', time() + $iDecalage );
	} // datetime

	public function compareDate( $sDate ) {
		return strtotime( $sDate ) <= time();
	} // compareDate

	public function getAge( $sDate ) {
		$iBirth = strtotime( $date );
		$iYear = date( 'Y', $birth );
		$iMonth = date( 'm', $birth );
		$iDay = date( 'd', $birth );
		$iAge = date( 'Y' ) - $iYear;
		if( date( 'm' ) - $iMonth < 0 ) $iAge--;
		else if( date( 'm' ) - $iMonth == 0 && date( 'd' ) - $iDay < 0 ) $iAge--;
			return $iAge;
	} // getAge

	public function rssDate($iTime=null) {
		return date('r', ($iTime) ? strtotime($iTime) : time());
	} // rssDate

	public function HUSize( $iSizeInOctet ) {
		$iSizeInKo = $iSizeInOctet / 1024;
		if( round( $iSizeInKo, 1 ) > 1024*1024 ) {
			$iSizeInGo = $iSizeInKo / 1024 / 1024;
			return round( $iSizeInGo, 2) ."Go";
		} elseif( round( $iSizeInKo, 1 ) > 1024 ) {
			$iSizeInMo = $iSizeInKo / 1024;
			return round( $iSizeInMo, 1 )."Mo";
		} elseif( $iSizeInOctet > 1024 ) {
			return round( $iSizeInKo )."ko";
		} else
			return round( $iSizeInOctet )."o";
	} // HUSize

	public function download( $sFilePath, $sContent = null ) {
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Expires: 0' );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Pragma: no-cache' );
		if( file_exists( $sFilePath ) ) {
			$iSize = filesize( $sFilepath );
			header( 'Content-Type: application/force-download; name="' . basename( $sFilepath ) . '"' );
			header( 'Content-Length: ' . $iSize );
			header( 'Content-Disposition: attachment; filename="' . basename( $sFilepath ) . '"' );
			readfile( $sFilepath );
		} else {
			header( 'Content-Type: application/force-download; name="' . $sFilePath . '"' );
			header( 'Content-Length: ' . strlen( $sContent ) );
			header( 'Content-Disposition: attachment; filename="' . $sFilename . '"' );
			echo $sContent;
		}
		die();
	} // download

	public function folder_size( $sFolderPath ) {
		if( !file_exists( $sFolderPath ) )
			return 0;
		if( is_file( $sFolderPath ) )
			return filesize( $sFolderPath );
		$iComputedSize = 0;
		foreach( glob( $sFolderPath . '*' ) as $sFilePath)
			$iComputedSize += folder_size( $sFilePath );
		return $iComputedSize;
	} // folder_size

	function array_keys_exists( $aNeedle, $aHaystack ) {
		if( !is_array( $aHaystack ) )
			return false;
		foreach( $aNeedle as $sKey )
			if( !array_key_exists( $sKey, $aHaystack ) )
				return false;
		return true;
	} // array_has_keys


} // class::Utils
