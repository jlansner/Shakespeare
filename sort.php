<?php

include('Classes/ParseText.php');
$parsedText = new ParseText($_GET['play']);
?>

<html>
<head>
	<title><?php echo $parsedText->title;?></title>
		
	<link rel="stylesheet" type="text/css" href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
	<link rel="stylesheet" type="text/css" href="/css/shakespeare.css" />

	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			conversations = <?php echo json_encode($parsedText->conversations); ?>;
			$('.connectedSortable').sortable({
				connectWith: ".connectedSortable",
				start: function(event,ui) {
					var char1 = $(ui.item).attr('id').replace(/_/g,' ');
					$('.connectedSortable li').each(function() {
						if ($(this).attr('id')) {
							var char2 = $(this).attr('id').replace(/_/g,' ');
							if (conversations[char1][char2] > 0) {
								$(this).addClass('conflict').children('span').html(conversations[char1][char2]);
							}
						}
					});
					$('.connectedSortable').sortable( "option", "disabled", false);
				},
				stop: function(event,ui) {
					$('.connectedSortable li').removeClass('conflict').children('span').html('&nbsp;');
				},
				update: function(event,ui) {
					$('.connectedSortable').each(function() {
						if ($(this).children('li').length == 1) {
							$(this).sortable( "option", "disabled", true);
						}
					});
				}
			}).disableSelection();
			
			$('.connectedSortable').each(function() {
				if ($(this).children('li').length == 1) {
					$(this).sortable( "option", "disabled", true);
				}
			});
		});
	</script>	
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
	Total Lines - <?php echo $parsedText->totalLines; ?><br />
	Characters - <?php echo count($parsedText->characters); ?><br />
	<a href="xml/<?php echo $_GET['play']; ?>.xml" target="_blank">Original Text</a>
	</p>

<?php $readers = $parsedText->assign_roles($_GET['readers']); ?>

<h2>Roles - <?php echo $z; ?> Readers</h2>
<div class="sortWrapper">
<?php
$i = 1;

foreach ($readers as $reader) { ?>

	<div class="sortDiv">
	<h3>Reader <?php echo $i; ?></h3>
	<ul id=reader<?php echo $i; ?> class="connectedSortable">
<?php 
		foreach ($reader as $role) {
			echo '<li id="' . str_replace(" ", "_", $role) . '" class="ui-state-default">' . $role . '<br />
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
</body>
</html>