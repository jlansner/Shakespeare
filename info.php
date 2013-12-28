<?php
include('Classes/ParseText.php');
include('Classes/FileList.php');
$fileList = new FileList();

$list = $fileList->getDirectoryList();
?>

<html>
<head>
	<title>All Plays Info</title>
		
	<link rel="stylesheet" type="text/css" href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
	<link rel="stylesheet" type="text/css" href="/css/shakespeare.css" />

	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
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
	<h1>All Plays</h1>
<?php foreach ($list as $key => $value) {
	if ($_GET['sortField']) {
		$play = new ParseText($key,$_GET['sortField']);
	} else { 
		$play = new ParseText($key);
	}	
?>
	<h2><a href="play/<?php echo $key; ?>"><?php echo $play->title; ?></a> &ndash; <?php echo $play->totalLines; ?> Lines</h2>
	<table class="infoTable" border="1">
		<thead>
			<tr>
			<?php	
			for ($z = 4; $z < 9; $z++) { ?>
				<th><?php echo $z ?> Readers</th>
			<?php } ?>
		</thead>
		<tbody>
			<tr>
	<?php
	for ($z = 4; $z < 9; $z++) { ?>
		<td>
		<ul>
			<?php
		$readers = $play->assign_roles($z);

		$i = 1;
		foreach ($readers as $reader) { 
			$lines = 0;
			foreach ($reader as $role) {
				$lines += $play->characters[$role]['lines'];
			}
			$percent = round(($lines / $play->totalLines) * 100,2);
			$variance = round(($lines - ($play->totalLines / $z)) / ($play->totalLines / $z) * 100,2);
			if ($variance > 0) {
				$varianceText = '<span class="positive">' . $variance . '%</span>';
			} else if ($variance < 0) {
				$varianceText = '<span class="negative">' . $variance . '%</span>';				
			}
		 	if ($i > $z) { ?>
		 		<li class="unassigned">Unassigned &ndash; <?php echo $lines; ?> Lines | <?php echo $percent ?>%</li> 
		 	<?php } else { ?>
		 		<li>Reader <?php echo $i; ?> &ndash; <?php echo $lines; ?> Lines | <?php echo $percent; ?>% | <?php echo $varianceText; ?></li>
			<?php } 
			$i++; ?>
		<?php } ?>	
		</ul>
		</td>
	<?php } ?>
	</tr>
	</tbody>
	</table>
	<hr />
<?php } ?>

</body>
</html>