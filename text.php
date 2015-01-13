<?php

include('Classes/ParseText.php');
$parsedText = new ParseText($_GET['play']);

?>

<html>
<head>
	<title><?php echo $parsedText->title; ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
	<link rel="stylesheet" type="text/css" href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
	<link rel="stylesheet" type="text/css" href="/css/shakespeare.css" />

	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
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

<div class="charactersWrapper">
	<div class="showHideCharacters">Show/Hide Character List</div>
	<div class="characters">
		<h2>Characters</h2>
		<ul>
		<?php 
		$characters = $parsedText->characters;
		ksort($characters);
		foreach ($characters as $key => $value) { ?>
			<li><input type="checkbox" name="characters" value="<?php echo $parsedText->canonical_name($key); ?>" /> <label for="<?php echo $parsedText->canonical_name($key); ?>"><?php echo $key . " (" . $value['lines'] .  " lines)"; ?></label></li>
		<?php } ?>
		</ul>
	</div>
</div>
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
				<h4 class="<?php echo $parsedText->canonical_name($child->SPEAKER); ?>"><?php echo $child->SPEAKER; ?></h4>
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
</body>
</html>