<?php

include('Classes/FileListFolgers.php');
$fileList = new FileList();
?>

<html>
<head>
	<title><?php echo $parsedText->title;?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		
	<link rel="stylesheet" type="text/css" href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
	<link rel="stylesheet" type="text/css" href="css/shakespeare.css" />

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

		<h1>Shakespeare Plays</h1>
		<ul>
<?php $list = $fileList->getDirectoryList();
foreach ($list as $key => $value) { ?>
			<li><a href="<?php echo $key; ?>/newSort/5"><?php echo $value; ?></a></li>
<?php } ?>
			
		</ul>
		
		<!-- <p><a href="info.php">Full Stats</a></p> -->
</body>
</html>