<?php

class ParseTextFolgers {
	
	public function __construct($xml_file, $readers = null) {
    	$this->xml_file = $xml_file;
		$this->xml = simplexml_load_file('xml_folgers/' . $xml_file . '.xml');
        $this->xml->registerXPathNamespace('tei', 'http://www.tei-c.org/ns/1.0');
	
        $this->sortField = 'interactions';
        $this->wordTags = array('w','c','pc');
    	$this->actNumber = null;
		$this->title = $this->xml->teiHeader->fileDesc->titleStmt->title;

		$this->characters = array();
        $this->sortCharacters = array();
		$this->lines = array();
		$this->conversations = array();
		$this->conLines = array();
		$this->play = array();

		$this->totalSpeeches = 0;
		$this->totalLines = 0;

		$this->readers = $readers;
    
		$this->createCharacterArray();
        $this->getTotals();
		//$this->createConversationArray();
	}

    public function getTotals() {
        foreach ($this->characters as $key => $value) {
            $this->totalSpeeches += $value['speeches']['total'];
            $this->totalLines += $value['lines']['total'];
        }
    }
	public function assign_roles($readers) {
		$this->readers = $readers;
        $this->sortCharacters = array();
        foreach ($this->characters as $key => $value) {
            
            if ($value['speeches']['total']) {
                $this->sortCharacters[$key] = 0;
                foreach ($value['interactions'] as $ikey => $ivalue) {
                    if ($ivalue > 0) {
                        $this->sortCharacters[$key]++;
                    }
                }
            }
        }
        arsort($this->sortCharacters);
        $sortKeys = array_keys($this->sortCharacters);

		$totalCharacters = count($this->sortCharacters);

		$maxLines = ($this->totalLines / $readers) * 1.1;
		$readerLines = array();
		// assign characters with most interactions to separate readers
		for ($i = 0; $i < $readers; $i++) {
			$newRole = $sortKeys[$i];
			$roles[$i][0] = $newRole;
			$readerLines['reader' . $i] = $this->characters[$newRole]['lines']['total']; 
		}

		// assign remaining characters
		asort($readerLines);
		
		for ($i = $readers; $i < $totalCharacters; $i++) {
			$notAssigned = true;
			$newRole = $sortKeys[$i];

			// start with fewest lines, move up
			foreach ($readerLines as $key => $value) {
				$thisReader = substr($key,6);
				if ($notAssigned) {
					$noConflict = true;

					// check other roles assigned to this reader
					foreach ($roles[$thisReader] as $role) {
						// if this reader has a conflict, move to next reader
						if ($this->characters[$newRole]['interactions'][$role]) {
							$noConflict = false;
							break;
						}
					}
					
					if ($noConflict) {
						$roles[$thisReader][] = $newRole;
						$readerLines[$key] += $this->characters[$newRole]['lines']['total'];
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
    
    public function getWords($element) {
        $textString = '';
        foreach($element->children() as $word) {
            if ($word->getName() == 'lb') {
                $textString .= '<br />';
            } else if (in_array($word->getName(), $this->wordTags)) {
                $textString .= $word;
            } else if ($word->getName() == 'milestone' && $word->attributes()->unit == 'ftln') {
                $sceneLine = substr($word->attributes()->n,strrpos($word->attributes()->n,'.') + 1);
                if ($sceneLine % 5 == 0) {
                    $textString .= '<span class="lineNumber sceneLine">' . $sceneLine . '</span>';
                }
                $playLine = str_replace('ftln-','',$word->attributes('xml',true)->id);
                if ($playLine % 5 == 0) {
                    $textString .= '<span class="lineNumber playLine">' . ($playLine * 1) . '</span>';
                }
            } else if ($word->getName() == 'q') {
                $textString .= '&ldquo;' . $this->getWords($word) . '&rdquo;';
            } else if ($word->getName() == 'foreign') {
                $textString .= '<em>' . $this->getWords($word) . '</em>';
            } else if ($word->getName() == 'stage') {
                $textString .= '<span class="stageDirection">' . $this->getWords($word) . '</span>';
            } else if ($word->getName() == 'sound') {
                $textString .= $this->getWords($word);
            } else if ( $word->getName() === "seg" ) {
                $textString .= $this->getWords( $word );
            }
        }

        return $textString;
    }

	private function createCharacterArray() {

        $results = $this->xml->xpath( '//tei:person' );
        foreach( $results as $result ) {
            $name = ( string )$result->attributes( 'http://www.w3.org/XML/1998/namespace' )->id;
            if ( $name ) {
                $this->characters[ $name ] = array();
                $this->characters[ $name ][ "id" ] = $name;
                if ( $result->persName->name ) {
                    $charName = $result->persName->name;
                    $this->characters[ $name ][ "name" ] = ( string )$charName[ 0 ];
                    $this->characters[ $name ][ "state" ] = ( string )$result->state->p[ 0 ];
                } else {
                    if (strpos($name,"_")) {
                        $charName = substr( $name, 0, strpos( $name, "_" ) );
                    }
                    if ( preg_match( '/[1-9]/', substr( $charName, -1 ) ) ) {
                        $charName = substr($charName,-1) . " " . strtolower(substr($charName,0,-2));
                        if (preg_match('/\.[a-z0-9]/', substr($charName,-2))) {
                            $charName = substr($charName,0,-2);
                        }
                    }
                    $charName = ucwords(strtolower(str_replace(".", " ", $charName)));
                    $this->characters[$name]['name'] = $charName;
                }
            }
        }

        foreach ($this->characters as $char1key => $char1value) {
		
			foreach ($this->characters as $char2key => $char2value) {
				$this->characters[$char1key]['interactions'][$char2key] = 0;
			}
		}

        $acts = $this->xml->xpath('//tei:div1');
        $i = 0;
        foreach ($acts as $act) {
            $j = 0;
            $this->play[$i] = array();
            $actName = strtoupper((string)$act->attributes()->type);

            if (preg_match('/[0-9]/', (string)$act->attributes()->n)) {
                $actName .= " " . $act->attributes()->n;
            }
            $this->play[$i]['title'] = $actName;
            $this->play[$i]['speeches'] = array();

            $k = 0;
            foreach ($act->sp as $speech) {
                $speakers = (string)$speech->attributes()->who;
                $this->play[$i]['speeches'][$k]['speakers'] = explode(" ", str_replace("#","",$speakers));

                foreach ($this->play[$i]['speeches'][$k]['speakers'] as $currentKey => $currentSpeaker) {
                    if ( $this->characters[$currentSpeaker] ) {
                        $this->characters[$currentSpeaker]['speeches']['total']++;
                        $this->characters[$currentSpeaker]['speeches']['acts'][$actName]++;

                        $lines = $speech->ab->milestone->count();
                        if ($speech->ab->seg) {
                            $lines += $speech->ab->seg->milestone->count();
                        }

                        $this->characters[$currentSpeaker]['lines']['total'] += $lines;
                        $this->characters[$currentSpeaker]['lines']['acts'][$actName] += $lines;

                        if ($k > 0) {
                            foreach ($this->play[$i]['speeches'][$k - 1]['speakers'] as $prevKey => $prevSpeaker) {
                                $this->characters[$currentSpeaker]['interactions'][$prevSpeaker]++;
                                $this->characters[$prevSpeaker]['interactions'][$currentSpeaker]++;
                            }
                        }
                    }
                }
                $k++;
            }

            $play[$i]['scenes'] = array();
            foreach ($act->div2 as $scene) {

                $sceneName = strtoupper((string)$scene->attributes()->type);
                if (preg_match('/[0-9]/', (string)$scene->attributes()->n)) {
                    $sceneName .= " " . $scene->attributes()->n;
                }
                $this->play[$i]['scenes'][$j]['title'] = $sceneName;

                $this->play[$i]['scenes'][$j]['speeches'] = array();
                $k = 0;
                foreach ($scene->sp as $speech) {
                    $speakers = (string)$speech->attributes()->who;
                    $this->play[$i]['scenes'][$j]['speeches'][$k]['speakers'] = explode(" ", str_replace("#","",$speakers));
                    foreach ($this->play[$i]['scenes'][$j]['speeches'][$k]['speakers'] as $currentKey => $currentSpeaker) {
                        if ( $this->characters[$currentSpeaker] ) {
                            $this->characters[$currentSpeaker]['speeches']['total']++;
                            $this->characters[$currentSpeaker]['speeches']['acts'][$actName]['total']++;
                            $this->characters[$currentSpeaker]['speeches']['acts'][$actName]['scenes'][$sceneName]++;
    
                            $lines = $speech->ab->milestone->count();
                            if ($speech->ab->seg) {
                                $lines += $speech->ab->seg->milestone->count();
                            }
    
                            $this->characters[$currentSpeaker]['lines']['total'] += $lines;
                            $this->characters[$currentSpeaker]['lines']['acts'][$actName]['total'] += $lines;
                            $this->characters[$currentSpeaker]['lines']['acts'][$actName]['scenes'][$sceneName] += $lines;
    
                            if ($k > 0) {
                                foreach ($this->play[$i]['scenes'][$j]['speeches'][$k - 1]['speakers'] as $prevKey => $prevSpeaker) {
                                    $this->characters[$currentSpeaker]['interactions'][$prevSpeaker]++;
                                    $this->characters[$prevSpeaker]['interactions'][$currentSpeaker]++;
                                }
                            }
                        }
                    }

                    $k++;
                }
                $j++; 
            }
            $i++;
        }
    }


	private function createConversationArray() {		
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