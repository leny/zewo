<?php
/** flatLand! : zewo
 * /classes/utils/convertor.php
 */

namespace Zewo\Utils;

class Convertor extends \Zewo\Tools\Singleton {

	public function fromDB( $mValue, \Zewo\ORM\Structure\Column $oColumn ) {
		if( ( is_null( $mValue ) || empty( $mValue ) ) && $oColumn->isNullable() )
			return null;
		switch( $oColumn->type ) {
			case 'date':
			case 'datetime':
			case 'time':
			case 'timestamp':
				return new DateTime( $mValue );
				break;

			case 'float':
			case 'double':
			case 'precision':
				return floatval( $mValue );
				break;

			case 'set':
				return explode( ',', $mValue );
				break;

			case 'boolean':
				return true && $mValue;
				break;

			case 'tinyint':
			case 'smallint':
			case 'mediumint':
			case 'bigint':
			case 'int':
				return intval( $mValue );
				break;

			default:
				return stripslashes( $this->decode( $mValue ) );
				break;
		}
	} // fromDB

	public function toDB( $mValue, \Zewo\ORM\Structure\Column $oColumn, $bForCheck = false ) {
		if( ( is_null( $mValue ) || empty( $mValue ) ) && $oColumn->isNullable() )
			return 'NULL';
		if( $bForCheck && is_null( $mValue ) )
			return null;
		switch( $oColumn->type ) {
			case 'float':
			case 'double':
			case 'precision':
				$iNewValue = $oColumn->isSigned() ? floatval( $mValue ) : abs( floatval( $mValue ) );
				return $iNewValue;
				break;

			case 'boolean':
				return intval( $mValue && true );
				break;

			case 'tinyint':
			case 'smallint':
			case 'mediumint':
			case 'bigint':
			case 'int':
				$iNewValue = !$oColumn->isSigned() ? intval($mValue) : abs(intval($mValue));
				return $iNewValue;
				break;

			case 'time':
			case 'date':
			case 'datetime':
			case 'timestamp':
				return "'" . $mValue->format('Y-m-d H:i:s') . "'";
				break;

			case 'set':
				return implode(',', $mValue);
				break;

			case 'enum':
				if( in_array( $mValue, $oColumn->possibleValues ) )
					return "'" . addslashes( $this->encode( strval( $mValue ) ) ) . "'";
				else {
					trigger_error('The value "' . $mValue . '" is not an allowed value for the ' . $oColumn->table . '.' . $oColumn->name . ' enum column !', E_USER_WARNING);
				}
				break;

			default:
				return "'" . addslashes( $this->encode( strval( $mValue ) ) ) . "'";
				break;
		}
	} // toDB

	public function fromClassNameToTableName( $sClassName ) {
		return strtolower( preg_replace( '~(?<=\\w)([A-Z])~', '_$1', $sClassName ) );
	} // fromClassNameToDBName

	public function fromTableNameToClassName( $sTableName ) {
		return str_replace( " ", "", ucwords( strtr( $sTableName, "_-", "  " ) ) );
	} // fromTableNameToClassName

	public function encode( $sStr = null ) {
		return htmlentities($sStr, ENT_QUOTES, 'utf-8');
	} // encode

	public function decode( $sStr = null ) {
		return html_entity_decode($sStr, ENT_QUOTES, 'utf-8');
	} // decode

	public function unencode( $sStr = '' ) {
		$aEntities = array('&aacute;','&agrave;','&acirc;','&auml;','&eacute;','&egrave;','&ecirc;','&euml;','&iacute;','&igrave;','&icirc;','&iuml;','&oacute;','&ograve;','&ocirc;','&ouml;','&uacute;','&ugrave;','&ucirc;','&uuml;','&yacute;','&ygrave;','&ycirc;','&yuml;',);
		$aNoEntities = array('a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','u','u','u','u','y','y','y','y',);
		return str_replace($aEntities, $aNoEntities, $sStr);
	} // unencode

	public function no_accent( $sStr = '' ) {
		$aReplace = array( "À"=>"A","Á"=>"A","Â"=>"A","Ã"=>"A","Ä"=>"A","Å"=>"A","Ç"=>"C","Ð"=>"D","È"=>"E","É"=>"E","Ê"=>"E","Ë"=>"E","Ì"=>"I","Í"=>"I","Î"=>"I","Ï"=>"I","Ñ"=>"N","Ò"=>"O","Ó"=>"O","Ô"=>"O","Õ"=>"O","Ö"=>"O","Ø"=>"O","Š"=>"S","Ù"=>"U","Ú"=>"U","Û"=>"U","Ü"=>"U","Ý"=>"Y","Ž"=>"Z","à"=>"a","á"=>"a","â"=>"a","ã"=>"a","ä"=>"a","å"=>"a","ç"=>"c","è"=>"e","é"=>"e","ê"=>"e","ë"=>"e","ì"=>"i","í"=>"i","î"=>"i","ï"=>"i","ñ"=>"n","ð"=>"o","ò"=>"o","ó"=>"o","ô"=>"o","õ"=>"o","ö"=>"o","ø"=>"o","š"=>"s","ù"=>"u","ú"=>"u","û"=>"u","ü"=>"u","ý"=>"y","ÿ"=>"y","ž"=>"z","Æ"=>"Ae","æ"=>"ae","Œ"=>"Oe","œ"=>"oe","ß"=>"ss","Ä"=>"Ae","ä"=>"ae","Ö"=>"Oe","ö"=>"oe","Ü"=>"Ue","ü"=>"ue" );
		return strtr( $sStr, $aReplace );
	} // no_accent

	public function urlify( $sStr ) {
		if( function_exists( "mb_strtolower" ) )
			$sStr = mb_strtolower( $this->no_accent( $this->decode( $sStr ) ), 'UTF-8' );
		else
			$sStr = strtolower( $this->no_accent( $this->decode( $sStr ) ) );
		$sStr = preg_replace( "#[^0-9a-zA-Z]#is", "-", $sStr );
		$sStrTmp = str_replace( "--", "-", $sStr );
		while ( $sStr != $sStrTmp ) {
			$sStrTmp = $sStr;
			$sStr = str_replace( "--", "-", $sStr );
		}
		$sStr = trim( $sStr, "-" );
		return $sStr;
	} // urlify

	public function br2nl( $sStr ) {
		return preg_replace( '/\<br(\s*)?\/?\>/i', "\n", $sStr );
	} // br2nl

} // class::Convertor
