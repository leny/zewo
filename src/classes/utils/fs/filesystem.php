<?php
/** flatLand! : zewo
 * /classes/utils/fs/filesystem.php
 */

namespace Zewo\Utils\FS;

abstract class FileSystem {

	public static function factory( $mData ) {
		if( is_array( $mData ) ) {
			if( strpos( $mData['type'], 'image' ) !== false ) {
				return new \Zewo\Utils\FS\Image( $mData );
			} else
				return new \Zewo\Utils\FS\File( $mData );
		} else {
			if( file_exists( $mData ) ) {
				if( strpos( mime_content_type( $mData ), 'image' ) !== false ) {
					return new \Zewo\Utils\FS\Image( $mData );
				} else
					return new \Zewo\Utils\FS\File( $mData );
			} else
				return new \Zewo\Utils\FS\File( $mData );
		}
	} // factory

} // class::FileSystem
