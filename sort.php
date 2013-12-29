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
		function showCharLines() {
			$('.sortDiv').each(function() {
				var charLines = 0;
				
				$(this).find('.lines').each(function() {
					charLines += parseInt($(this).html());
				});
				
				$(this).find('h4').html(charLines + ' Lines');
			});
		}

		$(document).ready(function() {
			conversations = <?php echo json_encode($parsedText->conversations); ?>;
			
			$('.connectedSortable li').click(function() {
				$('.connectedSortable li').removeClass('conflict active').children('.conflicts').html('&nbsp;');

				$(this).addClass('active');

				var char1 = $(this).attr('id').replace(/_/g,' ');

				$('.connectedSortable li').each(function() {
					if ($(this).attr('id')) {
						var char2 = $(this).attr('id').replace(/_/g,' ');
						if (conversations[char1][char2] > 0) {
							$(this).addClass('conflict').children('.conflicts').html(conversations[char1][char2]);
						}
					}
				});
			});

			$('.connectedSortable').sortable({
				connectWith: ".connectedSortable",
				update: function() {
					showCharLines();
				}
			}).disableSelection();
			
			showCharLines();
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
	Total Lines - <?php echo number_format($parsedText->totalLines); ?><br />
	Characters - <?php echo count($parsedText->characters); ?><br />
	<a href="/xml/<?php echo $_GET['play']; ?>.xml" target="_blank">Original Text</a>
	</p>

<?php $readers = $parsedText->assign_roles($_GET['readers']); ?>

<h2>Roles - <?php echo $_GET['readers']; ?> Readers</h2>
<div class="sortWrapper">
<?php
$i = 1;

foreach ($readers as $reader) { ?>

	<div class="sortDiv">
	<?php if ($i > $_GET['readers']) { ?>
		<h3>Unassigned</h3>
		<h4>Lines</h4>
		<ul id=reader<?php echo $i; ?> class="connectedSortable">
	<?php } else { ?>
		<h3>Reader <?php echo $i; ?></h3>
		<h4>Lines</h4>
		<ul id=reader<?php echo $i; ?> class="connectedSortable reader">
<?php }
		foreach ($reader as $role) {
			echo '<li id="' . str_replace(" ", "_", $role) . '" class="ui-state-default">' . $role . '<br />
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
</body>
</html>