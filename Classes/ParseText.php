<?php

class ParseText {
	
	public function __construct($xml_file, $act = null, $readers = null) {
    	$this->xml_file = $xml_file;
		$this->xml = simplexml_load_file('xml/' . $xml_file . '.xml');
	
		$this->sortField = 'interactions';
    	$this->actNumber = null;
		$this->title = $this->xml->TITLE;
		if ($act) {
			$this->actNumber = str_split($act);
			sort($this->actNumber);
			if (count($this->actNumber) == 1) {
				$this->title .= ' - Act ' . $this->actNumber[0];
			} else {
				$this->title .= ' - Acts ' . implode(", ",$this->actNumber);
			}
		}
		$this->characters = array();
		$this->lines = array();
		$this->conversations = array();
		$this->conLines = array();
		$this->play = array();

		$this->totalSpeeches = 0;
		$this->totalLines = 0;

		$this->readers = $readers;
		$this->acts = count($this->xml->ACT);
    
		$this->createCharacterArray();
		$this->createConversationArray();
		

	}

	public function assign_roles($readers) {
		$this->readers = $readers;
		$totalCharacters = count($this->characters);
		$characterKeys = array_keys($this->characters);
		$maxLines = ($this->totalLines / $readers) * 1.1;
		$readerLines = array();
		// assign characters with most interactions to separate readers
		for ($i = 0; $i < $readers; $i++) {
			$newRole = $this->canonical_name($characterKeys[$i]);
			$roles[$i][0] = $newRole;
			$readerLines['reader' . $i] = $this->characters[$newRole]['lines']; 
		}

		// assign remaining characters
		asort($readerLines);
		
		for ($i = $readers; $i < $totalCharacters; $i++) {
			$notAssigned = true;
			$newRole = $this->canonical_name($characterKeys[$i]);

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
		
			if ($notAssigned) {
				$roles[$readers + 1][] = $newRole;
			}
			
			asort($readerLines);
 		}
	
		return $roles;
	}

	private function createCharacterArray() {
		
	    if ($this->actNumber) {
			$act_number = 1;
			$scene_number = 1;
	
			foreach ($this->xml->ACT as $act) {
				if (in_array($act_number,$this->actNumber)) {
					$this->play['ACT ' . $act_number] = array();
					foreach ($act->SCENE as $scene) {
						$this->createSceneArray($scene,$act_number,$scene_number);
						$scene_number++;
					}
				}
				$act_number++;
			}
		} else {
			$act_number = 1;

			foreach ($this->xml->ACT as $act) {
				$this->play['ACT ' . $act_number] = array();
				$scene_number = 1;

				foreach ($act->SCENE as $scene) {
					$this->createSceneArray($scene,$act_number,$scene_number);
					$scene_number++;
				}
				
				$act_number++;
			}
		}
	}

	private function createSceneArray($scene,$act_number,$scene_number) {
    	$this->play['ACT ' . $act_number][] = 'SCENE ' . $scene_number;
    	$speech_number = 0;

		foreach ($scene->SPEECH as $speech) {
			$speaker = $this->canonical_name($speech->SPEAKER);					
			$line_count = count($speech->LINE);

		    if (!array_key_exists($speaker,$this->characters)) {
		    	$this->characters[$speaker]['display_name'] = $this->combined_name($speech->SPEAKER);
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
          		$this->assignConversations($scene);
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
  
  private function assignConversations($scene) {
      $this->totalSpeeches += count($scene);
		
      $currentSpeaker = "";
      $previousSpeaker = "";
      foreach ($scene as $speech) {
	      $this->totalLines += $speech['Lines'];
	      $currentSpeaker = $speech['Speaker'];		  
		  $currentSpeaker = $this->combined_name($currentSpeaker);

	      if ($previousSpeaker !== "") {
			      $this->conversations[$currentSpeaker][$previousSpeaker]++;
			      $this->conversations[$previousSpeaker][$currentSpeaker]++;
		
			      $this->conLines[$currentSpeaker][$previousSpeaker] += $speech['Lines'];
			      $this->conLines[$previousSpeaker][$currentSpeaker] += $speech['Lines'];
	      } 
	      $previousSpeaker = $currentSpeaker;
     }
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
	
	public function canonical_name($currentSpeaker) {
		
		$currentSpeaker = $this->combined_name($currentSpeaker);
		$currentSpeaker = strtolower(preg_replace('/[\s\/]/',"_",$currentSpeaker));
		
		return $currentSpeaker;	
	}

	public function combined_name($speaker) {
		if ($this->xml_file == "coriolan") {
			if (($speaker == "MARCIUS") || ($speaker == "CORIOLANUS")) {
				$speaker = "MARCIUS/CORIOLANUS";
			}
		} else if ($this->xml_file == "hen_iv_2") {
			if (($speaker == "PRINCE HENRY") || ($speaker == "KING HENRY V")) {
				$speaker = "PRINCE HENRY/KING HENRY V";
			} else if (($speaker == "BARDOLPH") || ($speaker == "LORD BARDOLPH")) {
				$speaker = "BARDOLPH/LORD BARDOLPH";
			}
		} else if ($this->xml_file == "hen_vi_3") {
			if (($speaker == "EDWARD") || ($speaker == "KING EDWARD IV")) {
				$speaker = "EDWARD/KING EDWARD IV";
			} else if (($speaker == "LADY GREY") || ($speaker == "QUEEN ELIZABETH")) {
				$speaker = "LADY GREY/QUEEN ELIZABETH";
			} else if (($speaker == "GEORGE") || ($speaker == "CLARENCE")) {
				$speaker = "GEORGE/CLARENCE";
			} else if (($speaker == "RICHARD") || ($speaker == "GLOUCESTER")) {
				$speaker = "RICHARD/GLOUCESTER";
			}
		} else if ($this->xml_file == "rich_iii") {
			if (($speaker == "GLOUCESTER") || ($speaker == "KING RICHARD III")) {
				$speaker = "GLOUCESTER/KING RICHARD III";
			}
		} else if ($this->xml_file == "hen_viii") {
		        if (($speaker == "KATHARINE") || ($speaker == "QUEEN KATHARINE")) {
		               $speaker = "KATHARINE";
     		       }
		}
		return $speaker;
	}	
};

?>
