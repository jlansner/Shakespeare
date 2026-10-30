<?php

class FileList {
	
	public function getDirectoryList() {
		$list = scandir( "xml_folgers" );
		$plays = array();

		foreach ( $list as $item ) {
			if ( strpos( $item, "xml" ) ) {
				$file = new SplFileObject( "xml_folgers/{$item}" );
				if ( !$file->eof() ) {
			    	$file->seek( 6 );  // this works because the XML files have been standardized
				    $title = $file->current();
				}
				$abbr = explode( ".", $item )[ 0 ];
				$title = strip_tags( $title );

				$plays[ $abbr ] = $title;
			}
		}
		
		asort( $plays );
		return $plays;
	}
}
?>