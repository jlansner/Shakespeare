<?php

class FileList {
	
	public function getDirectoryList() {
		$list = scandir('xml_folgers');
		$plays = array();

		foreach ($list as $item) {
			if (strpos($item,'xml')) {
				/*$xml = simplexml_load_file('xml_folgers/' . $item);
                $xml->registerXPathNamespace('tei', 'http://www.tei-c.org/ns/1.0');

				$plays[substr($item,0,-4)] = (string) mb_convert_encoding($xml->teiHeader->fileDesc->titleStmt->title,'auto'); */

				$file = new SplFileObject('xml_folgers/' . $item);
				if (!$file->eof()) {
			    	$file->seek(6);  // this only works because XML files have been standardized
				    $title = $file->current();
				}
				$plays[substr($item,0,-4)] = substr($title,7,-9);
			}
		}
		
		asort($plays);
		return $plays;
	}
}
?>