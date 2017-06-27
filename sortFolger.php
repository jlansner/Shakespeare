<?php

include('Classes/ParseTextFolgers.php');

$parsedText = new ParseTextFolgers($_GET['play']);	
$readers = $parsedText->assign_roles($_GET['readers']);

?>

<html>
<head>
	<title><?php echo $parsedText->title;?></title>
		
	<link rel="stylesheet" type="text/css" href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
	<link rel="stylesheet" type="text/css" href="/css/shakespeare.css" />

	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
	<script type="text/javascript">
		characters = <?php echo json_encode($parsedText->characters); ?>;
		roles = <?php echo json_encode($readers); ?>;
		totalLines =  <?php echo $parsedText->totalLines; ?>;
		readers = <?php echo $_GET['readers']; ?>;
	</script>

	<script type="text/javascript" src="/js/shakespeareFolgers.js"></script>
	<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-2301275-4']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
	</head>
	<body>

	<div class="header">
		<h1><?php echo $parsedText->title; ?></h1>
	<!--	<div class="totalPlay">
			<span class="percentText">0%</span>
		<span class="completed"></span>
		</div>
	    <div class="showSort">
	        Show/Hide Sort Options
	    </div> -->
	</div>
		<div class="sortSection">
	
	<p>Total Speeches - <?php echo $parsedText->totalSpeeches; ?><br />
	Total Lines - <?php echo number_format($parsedText->totalLines); ?><br />
	Characters - <?php echo count($parsedText->sortCharacters); ?><br />
	</p>

<h2>Roles - <?php echo $_GET['readers']; ?> Readers</h2>
<h3 class="addReader">Add Reader</h3>
<div class="sortWrapper">
<?php
$i = 1;

foreach ($readers as $reader) { ?>
	<div class="sortDiv">
	<?php if ($i > $_GET['readers']) { ?>
		<h3>Unassigned</h3>
		<h4>Lines</h4>
		<h5>Percent</h5>
		<ul id="reader<?php echo $i; ?>" class="connectedSortable">
	<?php } else { ?>
		<span>
		<h3>Reader <?php echo $i; ?></h3>
		<input name="reader" type="text" class="renameReader" value="Reader <?php echo $i; ?>" />
		</span>
		<?php // <p><input name="highilght" type="checkbox" class="highlightInput" /> Highlight</p> ?>
		<h4>Lines</h4>
		<h5>Percent</h5>
		<ul id="reader<?php echo $i; ?>" class="connectedSortable reader">
<?php }
		foreach ($reader as $role) {
			echo '<li id="' . $role . '" class="ui-state-default">' . $parsedText->characters[$role]['name'] . '<br />
				<span>
				<span class="lines">' . $parsedText->characters[$role]['lines']['total'] . '</span> Lines<br />
				<table class="actLines">
				<tr>';
			foreach ($parsedText->play as $key => $value) {
                $value = substr(str_replace("ACT ", "",$value['title']),0,3);
                
				echo "<th>" . $value . "</th>";
			}
			echo '</tr>
            <tr>';
			foreach ($parsedText->play as $key => $value) {
				$actLines = 0;
				
				$actLines += $parsedText->characters[$role]['lines']['acts'][$value['title']]['total'];
				
				echo '<td>' . $actLines . '</td>';
			}
				echo '</tr></table>
				<span class="conflicts">&nbsp;</span>
				</span></li>';
		}
?>		
	</ul>
	</div>
<?php
	$i++;
}
?>
</div>
</div>
<?php /*
<div class="play">

<?php
$line = 0;
$i = 1;
foreach($parsedText->xml->xpath('//tei:div1') as $act) { ?>
		<h2><?php 
        if ($act->head) {
            foreach ($act->head->children() as $child) {
                echo $child;
            }        
    } else {
        echo strtoupper((string)$act->attributes()->type);
    }
     
            ?></h2>
		
		<?php foreach($act->div2 as $scene) { ?>
			<h3><?php 
                if ($scene->head) {
                    foreach ($scene->head->children() as $child) {
                        echo $child;
                    }
                } else {
                    echo ucfirst((string)$scene->attributes()->type);
                }
             ?></h3>
			
			<?php foreach($scene->children() as $child) { 
				if ($child->getName() == "stage") { ?>
					<blockquote class="stage_direction"><?php echo $child; ?></blockquote>
				<?php } elseif ($child->getName() == "sp") { ?>
					<h4 class="<?php echo $child->attributes()->who; ?>"><?php 
                    foreach($child->speaker->children() as $speaker) {
                        echo $speaker;

                    }
                    ?> <span class="reader"></span></h4>
					<p class="<?php echo $parsedText->canonical_name($child->SPEAKER); ?>">						
						<?php foreach($child->children() as $speechChild) {
							if ($speechChild->getName() == "LINE") { 
								$line++;
								echo $speechChild . "<br />";
							} else if ($speechChild->getName() == "STAGEDIR") { ?>
							</p>
								<blockquote><?php echo $speechChild; ?></blockquote>
								<p class="<?php echo strtolower($child->SPEAKER); ?>">
						<?php }
					} ?>
				</p>
				<?php }
			}
		}
	
	$i++;
}
?>
</div>*/ ?>
</body>
</html>