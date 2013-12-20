<?php

class FileList {
	
	public function getDirectoryList() {
		$list = scandir('xml');
		$plays = array();

		foreach ($list as $item) {
			if (strpos($item,'xml')) {
				$xml = simplexml_load_file('xml/' . $item);
				
				$plays[substr($item,0,-4)] = (string) $xml->TITLE;
			}
		}
		
		asort($plays);
		
		return $plays;
	}
}
?>