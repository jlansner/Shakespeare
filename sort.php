<?php

include('Classes/ParseText.php');
if ($_GET['act']) {
	$parsedText = new ParseText($_GET['play'],$_GET['act'],$_GET['readers']);
} else {
	$parsedText = new ParseText($_GET['play']);	
}

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
		conversations = <?php echo json_encode($parsedText->conversations); ?>;
		roles = <?php echo json_encode($readers); ?>;
		totalLines =  <?php echo $parsedText->totalLines; ?>;
		readers = <?php echo $_GET['readers']; ?>;
	</script>

	<script type="text/javascript" src="/js/shakespeare.js"></script>
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
	<h1><?php echo $parsedText->title; ?></h1>
	<p>Total Speeches - <?php echo $parsedText->totalSpeeches; ?><br />
	Total Lines - <?php echo number_format($parsedText->totalLines); ?><br />
	Characters - <?php echo count($parsedText->characters); ?><br />
	<a href="/text/<?php echo $_GET['play']; ?>" target="_blank">Original Text</a>
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
		<h4>Lines</h4>
		<h5>Percent</h5>
		<ul id="reader<?php echo $i; ?>" class="connectedSortable reader">
<?php }
		foreach ($reader as $role) {
			echo '<li id="' . $role . '" class="ui-state-default">' . $parsedText->characters[$role]['display_name'] . '<br />
				<span class="lines">' . $parsedText->characters[$role]['lines'] . '</span> Lines<br />
				<span class="conflicts">&nbsp;</span></li>';
		}
?>		
	</ul>
	</div>
<?php
	$i++;
}
?>
</div>
<div class="play">
<?php
$line = 0;
 foreach($parsedText->xml->ACT as $act) { ?>
	<h2><?php echo $act->TITLE; ?></h2>
	
	<?php foreach($act->SCENE as $scene) { ?>
		<h3><?php echo $scene->TITLE; ?></h3>
		
		<?php foreach($scene->children() as $child) { 
			if ($child->getName() == "STAGEDIR") { ?>
				<blockquote class="stage_direction"><?php echo $child; ?></blockquote>
			<?php } elseif ($child->getName() == "SPEECH") { ?>
				<h4 class="<?php echo $parsedText->canonical_name($child->SPEAKER); ?>"><?php echo $child->SPEAKER; ?> <span class="reader"></span></h4>
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
}
?>
</div>
</body>
</html>