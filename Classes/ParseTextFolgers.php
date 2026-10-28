<?php

class ParseTextFolgers {
    public string $xml_file;
    public object $xml;
    public string $sortField;
    public array $wordTags;
    public array $selectedActs;
    public string $title;
    public array $characters;
    public array $sortCharacters;
    public array $lines;
    public array $conversations;
    public array $conLines;
    public array $play;
    public int $totalSpeeches;
    public int $totalLines;
    public int $readers;
	
	public function __construct( $xml_file, $acts = null ) {
    	$this->xml_file = $xml_file;
		$this->xml = simplexml_load_file( 'xml_folgers/' . $xml_file . '.xml' );
        $this->xml->registerXPathNamespace('tei', 'http://www.tei-c.org/ns/1.0');
	
        $this->sortField = 'interactions';
        $this->wordTags = array('w','c','pc');
    	$this->selectedActs = array();
		$this->title = $this->xml->teiHeader->fileDesc->titleStmt->title;

        if ( $acts ) {
			$this->selectedActs = explode( ",", strtoupper( $acts ) );
			sort( $this->selectedActs );
		}

		$this->characters = array();
        $this->sortCharacters = array();
		$this->lines = array();
		$this->conversations = array();
		$this->conLines = array();
		$this->play = array();

		$this->totalSpeeches = 0;
		$this->totalLines = 0;
    
		$this->createCharacterArray();
        if ( $acts ) {
            $this->title .= ' - Acts: ' . implode( ", ", $this->selectedActs );
        }
        $this->getTotals();
		//$this->createConversationArray();
	}

    public function getTotals() {
        foreach ($this->characters as $key => $value) {
            $this->totalSpeeches += $value['speeches']['total'];
            $this->totalLines += $value['lines']['total'];
        }
    }
	public function assign_roles( $readers ) {
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

        echo "---- \n";
        var_export( $sortKeys );
		$totalCharacters = count($this->sortCharacters);

		$maxLines = ($this->totalLines / $readers) * 1.1;
		$readerLines = array();
		// assign characters with most interactions to separate readers
		for ($i = 0; $i < $readers; $i++) {
			$newRole = $sortKeys[ $i ];
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
            $name = ( string ) $result->attributes( 'http://www.w3.org/XML/1998/namespace' )->id;
            if ( $name ) {
                $this->characters[ $name ] = array();
                $this->characters[ $name ][ "id" ] = $name;
                $this->characters[ $name ][ "corresp" ] = str_replace( "#", "", ( string ) $result->attributes()->corresp );

                $charName = $result->persName->name;

                if ( $charName ) {
                    $this->characters[ $name ][ "name" ] = ( string ) $charName;
                    $this->characters[ $name ][ "state" ] = ( string ) strip_tags( $result->state->asXml() );
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
                    $charName = ucwords( strtolower( str_replace( ".", " ", $charName ) ) );
                    $this->characters[ $name ][ "name" ] = $charName;
                    $this->characters[ $name ][ "state" ] = "";
                }

                $this->characters[ $name ][ "speeches" ] = array( "total" => 0, "acts" => array() );
                $this->characters[ $name ][ "lines" ] = array( "total" => 0, "acts" => array()  );
            }
        }

        foreach ($this->characters as $char1key => $char1value) {
		
			foreach ($this->characters as $char2key => $char2value) {
				$this->characters[$char1key]['interactions'][$char2key] = 0;
			}
		}

        $acts = $this->xml->xpath('//tei:div1');
        $i = 0;
        foreach ( $acts as $act ) {
            $j = 0;
            $actData = array();
            $actName = strtoupper( ( string ) $act->attributes()->type );
            $actNumber = ( string ) $act->attributes()->n;

            if ( intval( $actNumber ) > 0 ) {
                $actName .= " " . $actNumber;
            }

            $actData[ 'title' ] = $actName;
            $actData[ 'number' ] = $actNumber;

            $actData[ 'speeches' ] = array();

            $k = 0;

            if ( empty( $this->selectedActs ) || in_array( strtoupper( $actNumber ), $this->selectedActs ) ) {

                foreach ( $this->characters as $key => $value ) {
                    $this->characters[ $key ][ "speeches" ][ "acts" ][ $actName ] = array( "total" => 0, "none" => 0, "scenes" => array() );
                    $this->characters[ $key ][ "lines" ][ "acts" ][ $actName ] = array( "total" => 0, "none" => 0, "scenes" => array() );
                }

                foreach ( $act->sp as $speech ) {
                    $speakersList = ( string ) $speech->attributes()->who;
                    $speakers = explode( " ", $speakersList );

                    foreach ( $speakers as $speaker ) {
                        $speaker = str_replace( "#", "", $speaker );
                        if ( $this->characters[ $speaker] ) {
                            $actData[ "speeches" ][ $k ][ "speakers" ][] = $speaker;
                        } else {
                            foreach( $this->characters as $character ) {
                                if ( $character[ "corresp" ] == $speaker ) {
                                    $actData[ "speeches" ][ $k ][ "speakers" ][] = $character[ "id" ];
                                }
                            }
                        }
                    }

                    foreach ( $actData['speeches'][$k]['speakers'] as $currentSpeaker ) {
                        if ( $this->characters[ $currentSpeaker ] ) {
                            $this->characters[ $currentSpeaker ][ "speeches" ][ "total" ]++;
                            $this->characters[ $currentSpeaker ][ "speeches" ][ "acts" ][ $actName ][ "none" ]++;
                            
                            $lines = $speech->ab->milestone->count();
                            if ( $speech->ab->seg ) {
                                $lines += $speech->ab->seg->milestone->count();
                            }

                            $this->characters[$currentSpeaker]['lines']['total'] += $lines;
                            $this->characters[$currentSpeaker]['lines']['acts'][$actName][ "none"] += $lines;

                            if ($k > 0) {
                                foreach ($actData['speeches'][$k - 1]['speakers'] as $prevKey => $prevSpeaker) {
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

                    foreach ( $this->characters as $key => $value ) {
                        $this->characters[ $key ][ "speeches" ][ "acts" ][ $actName ][ "scenes" ][ $sceneName ] = 0;
                        $this->characters[ $key ][ "lines" ][ "acts" ][ $actName ][ "scenes" ][ $sceneName ] = 0;
                    }
                    $actData['scenes'][$j]['title'] = $sceneName;

                    $actData['scenes'][$j]['speeches'] = array();
                    $k = 0;

                    $actData[ "scenes" ][ $j ][ "speeches" ][ $k ] = array ( "speakers" => array() );

                    foreach ($scene->sp as $speech) {
                    
                        $speakersList = ( string ) $speech->attributes()->who;
                        $speakers = explode( " ", $speakersList );

                        foreach ( $speakers as $speaker ) {
                            $speaker = str_replace( "#", "", $speaker );
                            if ( isset( $this->characters[ $speaker ] ) ) {
                                $actData[ "scenes" ][ $j ][ "speeches" ][ $k ][ "speakers" ][] = $speaker;
                            } else {
                                foreach( $this->characters as $character ) {
                                    if ( $character[ "corresp" ] == $speaker ) {
                                        $actData[ "scenes" ][ $j ][ "speeches" ][ $k ][ "speakers" ][] = $character[ "id" ];
                                    }
                                }
                            }
                        }

                        foreach ( $actData[ "scenes" ][ $j ][ "speeches" ][ $k ][ "speakers" ] as $currentSpeaker ) {
                            if ( $this->characters[$currentSpeaker] ) {
                                $this->characters[$currentSpeaker]['speeches']['total']++;
                                $this->characters[$currentSpeaker]['speeches']['acts'][$actName]['total']++;
                                $this->characters[$currentSpeaker]['speeches']['acts'][$actName]['scenes'][$sceneName]++;
        
                                $lines = $speech->ab->milestone->count();
                                if ( $speech->ab->seg ) {
                                    $lines += $speech->ab->seg->milestone->count();
                                }
        
                                $this->characters[$currentSpeaker]['lines']['total'] += $lines;
                                $this->characters[$currentSpeaker]['lines']['acts'][$actName]['total'] += $lines;
                                $this->characters[$currentSpeaker]['lines']['acts'][$actName]['scenes'][$sceneName] += $lines;
        
                                if ( $k > 0 ) {
                                    foreach ( $actData[ "scenes" ][ $j ][ "speeches" ][ $k - 1 ][ "speakers"] as $prevKey => $prevSpeaker) {
                                        $this->characters[ $currentSpeaker ][ "interactions" ][ $prevSpeaker ]++;
                                        $this->characters[ $prevSpeaker ][ "interactions" ][ $currentSpeaker ]++;
                                    }
                                }
                            }
                        }

                        $k++;
                    }
                    $j++; 
                }

                $this->play[ $i ] = $actData;
            
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