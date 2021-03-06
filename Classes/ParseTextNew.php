<?php

class ParseText {
	
	public function __construct($xml_file, $sortField = 'interactions') {
		$this->xml = simplexml_load_file('xml/' . $xml_file . '.xml');
    
		$this->sortField = $sortField;
		$this->title = $this->xml->TITLE;
		$this->characters = array();
		$this->lines = array();
		$this->conversations = array();
		$this->conLines = array();
		$this->play = array();

		$this->totalSpeeches = 0;
		$this->totalLines = 0;
		
		$this->createCharacterArray();
		$this->createConversationArray();

	}

	public function assign_roles($readers) {
		$totalCharacters = count($this->characters);
		$characterKeys = array_keys($this->characters);
		$maxLines = ($this->totalLines / $readers) * 1.1;
		$readerLines = array();
		// assign characters with most interactions to separate readers
		for ($i = 0; $i < $readers; $i++) {
			$newRole = $characterKeys[$i];
			$roles[$i][0] = $newRole;
			$readerLines['reader' . $i] = $this->characters[$newRole]['lines']; 
		}

		// assign remaining characters
		asort($readerLines);

//		$lastReader = $readers - 1;
		
		for ($i = $readers; $i < $totalCharacters; $i++) {
			$notAssigned = true;
			$newRole = $characterKeys[$i];

			// start with last reader, move up towards first one
//			for ($j = $lastReader; $j >= 0; $j--) {
		
			// start with fewest lines, move up
			foreach ($readerLines as $key => $value) {
				$thisReader = substr($key,6);
				if ($notAssigned) {
					$noConflict = true;

					// check other roles assigned to this reader
					foreach ($roles[$thisReader] as $role) {
						// if this reader has a conflict, move to next reader
						if ($this->conversations[$newRole][$role]) {
							$noConflict = false;
							break;
						}
					}
					
					if ($noConflict) {
						$roles[$thisReader][] = $newRole;
						$readerLines[$key] += $this->characters[$newRole]['lines'];
						$notAssigned = false;
						break;
					}
				}
			}
			
/*			if ($notAssigned) {
				for ($j = $readers - 1; $j >=0; $j--) {
					if ($notAssigned) {
						$noConflict = true;
	
						// check other roles assigned to this reader
						foreach ($roles[$j] as $role) {
		
							// if this reader has a conflict, move to next reader
							if ($this->conversations[$newRole][$role]) {
								$noConflict = false;
								break;
							}
						}
						
						if ($noConflict) {
							$roles[$j][] = $newRole;
							$notAssigned = false;
							break;
						}
					}	
				} */
				if ($notAssigned) {
					$roles[$readers + 1][] = $newRole;
				}
/*			} */
			
			asort($readerLines);
/*			$lastReaderLines = 0;
			foreach ($roles[$lastReader] as $role) {
				$lastReaderLines += $this->characters[$role]['lines'];
			}
			
			if ($lastReaderLines > $maxLines) {
				$lastReader--;
			}

 */ 
 		}
	
		return $roles;
	}

	private function createCharacterArray() {
		
		$act_number = 1;

		foreach ($this->xml->ACT as $act) {
			$this->play['ACT ' . $act_number] = array();
			$scene_number = 1;
			
			foreach ($act->SCENE as $scene) {
				$this->play['ACT ' . $act_number][] = 'SCENE ' . $scene_number;
				$speech_number = 0;

				foreach ($scene->SPEECH as $speech) {
					$speaker = strtoupper($speech->SPEAKER);
					$line_count = count($speech->LINE);

					if (!array_key_exists($speaker,$this->characters)) {
						$this->characters[$speaker]['speeches'] = 0;
						$this->characters[$speaker]['lines'] = 0;
						$this->characters[$speaker]['interactions'] = 0;
						$this->characters[$speaker]['PLAY']['ACT ' . $act_number]['SCENE ' . $scene_number] = 0;
					}
					
					$this->lines['ACT ' . $act_number]['SCENE ' . $scene_number][$speech_number]['Speaker'] = $speaker;
					$this->lines['ACT ' . $act_number]['SCENE ' . $scene_number][$speech_number]['Lines'] = $line_count;
					
					$this->characters[$speaker]['speeches']++;
					$this->characters[$speaker]['PLAY']['ACT ' . $act_number]['SCENE ' . $scene_number] += $line_count;
					$this->characters[$speaker]['lines'] += $line_count;
		
					$speech_number++;
				}
				$scene_number++;
			}
			$act_number++;
		}

	}

	private function createConversationArray() {
		foreach ($this->characters as $char1key => $char1value) {
		
			foreach ($this->characters as $char2key => $char2value) {
				$this->conversations[$char1key][$char2key] = 0;
				$this->conLines[$char1key][$char2key] = 0;
			}
		}

		foreach ($this->lines as $act) {
		
			foreach ($act as $scene) {
		
				$this->totalSpeeches += count($scene);
		
				$currentSpeaker = "";
				$previousSpeaker = "";
				foreach ($scene as $speech) {
					$this->totalLines += $speech['Lines'];
		
					$currentSpeaker = $speech['Speaker'];
					if ($previousSpeaker !== "") {
							$this->conversations[$currentSpeaker][$previousSpeaker]++;
							$this->conversations[$previousSpeaker][$currentSpeaker]++;
		
							$this->conLines[$currentSpeaker][$previousSpeaker] += $speech['Lines'];
							$this->conLines[$previousSpeaker][$currentSpeaker] += $speech['Lines'];
					} 
					$previousSpeaker = $currentSpeaker;
				}
			}
		}
		
		foreach ($this->conversations as $speaker => $interactions) {
			foreach ($interactions as $key => $value) {
				if ($value > 0) {
					$this->characters[$speaker]['interactions']++;
				}
			} 
		}
		
		$this->characters = $this->subval_sort($this->characters,$this->sortField);
		

	}

	private function subval_sort($a,$subkey) {
		foreach($a as $k=>$v) {
			$b[$k] = strtolower($v[$subkey]);
		}
		arsort($b);
		foreach($b as $key=>$val) {
		    $c[$key] = $a[$key];
		}
		return $c;
	}
	
};

?>
