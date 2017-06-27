<html>
<head>
    <title>Two Noble Kinsmen</title>
</head>
<body>

<?php 

$xml = simplexml_load_file('xml/TNK.xml');
$xml->registerXPathNamespace('tei', 'http://www.tei-c.org/ns/1.0');

$characters = array();

$results = $xml->xpath('//tei:person');
foreach($results as $result) {
    $name = (string)$result->attributes('http://www.w3.org/XML/1998/namespace')->id;
    if ($name) {
        $characters[$name] = array();
        $characters[$name]['id'] = $name;
        if ($result->xpath('persName/name')) {
            $charName = $result->xpath('persName/name');
            $characters[$name]['name'] = (string)$charName[0];
        } else {
            $charName = str_replace("_TNK","",$name);
            if (preg_match('/[1-9]/', substr($charName,-1))) {
                $charName = substr($charName,-1) . " " . ucwords(strtolower(substr($charName,0,-2)));
                if (preg_match('/\.[a-z0-9]/', substr($charName,-2))) {
                    $charName = substr($charName,0,-2);
                }
            }

            $characters[$name]['name'] = $charName;
        }
    }
}

$play = array();

$acts = $xml->xpath('//tei:div1');
$i = 0;
foreach ($acts as $act) {
    $j = 0;
    $play[$i] = array();
    $play[$i]['title'] = strtoupper((string)$act->attributes()->type);

    if (preg_match('/[0-9]/', (string)$act->attributes()->n)) {
        $play[$i]['title'] .= " " . $act->attributes()->n;
    }

    $play[$i]['speeches'] = array();
    $k = 0;
    foreach ($act->sp as $speech) {
        $speakers = (string)$speech->attributes()->who;
        $play[$i]['speeches'][$k]['speakers'] = explode(" ", str_replace("#","",$speakers));

        foreach ($play[$i]['speeches'][$k]['speakers'] as $currentKey => $currentSpeaker) {
            $characters[$currentSpeaker]['speeches']['total']++;
            $characters[$currentSpeaker]['speeches'][$play[$i]['title']]++;

            $characters[$currentSpeaker]['lines']['total'] += $speech->ab->lb->count();
            $characters[$currentSpeaker]['lines'][$play[$i]['title']] += $speech->ab->lb->count();

            if ($k > 0) {
                foreach ($play[$i]['speeches'][$k - 1]['speakers'] as $prevKey => $prevSpeaker) {
                    $characters[$currentSpeaker]['interactions'][$prevSpeaker]++;
                    $characters[$prevSpeaker]['interactions'][$currentSpeaker]++;
                }
            }
        }
        $k++;
    }

    $play[$i]['scenes'] = array();
    foreach ($act->div2 as $scene) {
        $play[$i]['scenes'][$j]['title'] = strtoupper((string)$scene->attributes()->type);
        if (preg_match('/[0-9]/', (string)$scene->attributes()->n)) {
            $play[$i]['scenes'][$j]['title'] .= " " . $scene->attributes()->n;
        }

        $play[$i]['scenes'][$j]['speeches'] = array();
        $k = 0;
        foreach ($scene->sp as $speech) {
            $speakers = (string)$speech->attributes()->who;
            $play[$i]['scenes'][$j]['speeches'][$k]['speakers'] = explode(" ", str_replace("#","",$speakers));
            foreach ($play[$i]['scenes'][$j]['speeches'][$k]['speakers'] as $currentKey => $currentSpeaker) {
                $characters[$currentSpeaker]['speeches']['total']++;
                $characters[$currentSpeaker]['speeches'][$play[$i]['title']][$play[$i]['scenes'][$j]['title']]++;

               $characters[$currentSpeaker]['lines']['total'] += $speech->ab->lb->count();
                $characters[$currentSpeaker]['lines'][$play[$i]['title']][$play[$i]['scenes'][$j]['title']] += $speech->ab->lb->count();

                if ($k > 0) {
                    foreach ($play[$i]['scenes'][$j]['speeches'][$k - 1]['speakers'] as $prevKey => $prevSpeaker) {
                        $characters[$currentSpeaker]['interactions'][$prevSpeaker]++;
                        $characters[$prevSpeaker]['interactions'][$currentSpeaker]++;
                    }
                }
            }

            $k++;
        }
        $j++; 
    }
    $i++;
}

echo '<h1>' . $xml->teiHeader->fileDesc->titleStmt->title . '</h1>';

echo '<ul>';
foreach ($characters as $charKey => $charValue) {
    if ($charValue['speeches']) {
        echo '<li>' . $charValue['name'];

        if ($charValue['interactions']) {
            echo " - " . count($charValue['interactions']);
            echo "<ul>";
            foreach ($charValue['interactions'] as $key => $value) {
                echo "<li>" . $key . " - " .$value . "</li>";
            }
            echo "</ul>";
        }
        echo "</li>";
    }
}
echo "</ul>";
?>

</body>
</html>