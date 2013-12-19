<?php

include('Classes/ParseText.php');
$parsedText = new ParseText($_GET['play']);
?>

<html>
<head>
	<title><?php echo $parsedText->title;?></title>
		
	<link rel="stylesheet" type="text/css" href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
	<link rel="stylesheet" type="text/css" href="css/shakespeare.css" />

	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$('#tabs').tabs();
		});
	</script>	
		
	</head>
	<body>

<h1><?php echo $parsedText->title; ?></h1>
	<p>Total Speeches - <?php echo $parsedText->totalSpeeches; ?><br />
	Total Lines - <?php echo $parsedText->totalLines; ?></p>

<div id="tabs">
	<ul>
		<li><a href="#characters">Characters</a></li>
		<li><a href="#conversations">Conversations</a></li>
		<?php for ($z = 4; $z < 9; $z++) { ?>

		<li><a href="#roles<?php echo $z; ?>">Roles - <?php echo $z; ?> Readers</a></li>
<?php } ?>
	</ul>

	<div id="characters">
		<table border="1">
			<thead>
			<tr>
				<th rowspan="2">#</th>
				<th rowspan="2">Character</th>
				<th rowspan="2">Speeches</th>
				<th rowspan="2">Lines</th>
		<?php
			foreach ($parsedText->play as $act => $scenes) {
				echo '<th colspan="' . count($scenes) . '">' . $act . '</th>';
			}
		?>
			</tr>
			<tr>
			<?php
			foreach ($parsedText->play as $act => $scenes) {
				foreach ($scenes as $scene) {
					echo '<th>' . $scene . '</th>';
				}
			}
				
			?>
			</tr>
			</thead>
			<tbody>
		<?php
			$i = 1;
		foreach ($parsedText->characters as $key => $value) {
			echo '<tr>
			<td>' . $i . '</td>
				<td>' . $key . '</td>
				<td>' . $value['speeches'] . '</td>
				<td>' . $value['lines'] . '</td>';
		
			foreach ($parsedText->play as $act => $scenes) {
				foreach ($scenes as $scene) {
					echo '<td>' . $value['PLAY'][$act][$scene] . '</td>';
				}
			}
		
			echo '</tr>';
			$i++;
		}
		?>
		</tbody>
		</table>
	</div>
	
	<div id="conversations">
							
		<table border="1">
			<thead>
			<tr>
				<th>Speaker</th>
				<?php foreach ($parsedText->characters as $key => $value) {
					echo '<th>' . $key . '</th>';
				} ?>				
			</tr>
			</thead>
			<tbody>
		<?php
			foreach ($parsedText->characters as $key => $value) {
				?>
				<tr>
					<th><?php echo $key; ?></th>
					<?php
					 foreach ($parsedText->characters as $key2 => $value2) {
						if ($parsedText->conversations[$key][$key2] > 0) {
							echo '<td>' . $parsedText->conversations[$key][$key2] . '</td>';
						} else {
							echo '<td class="green"></td>';
						}
					}
				echo '</tr>';
			}		
		?>
					</tbody>
		</table>
	</div>

	<?php
	for ($z = 4; $z < 9; $z++) {
		$readers = $parsedText->assign_roles($z);
		$x = 1;
		 ?>

		<div id="roles<?php echo $z; ?>">
			<h2>Roles - <?php echo $z; ?> Readers</h2>
<?php
		 $i = 1;
		 foreach ($readers as $reader) { ?>
		 	
		 	<h3>Reader <?php echo $i; ?></h3>

		<table border="1">
			<thead>
			<tr>
				<th rowspan="2">#</th>
				<th rowspan="2">Character</th>
				<th rowspan="2">Speeches</th>
				<th rowspan="2">Lines</th>
		<?php
			foreach ($parsedText->play as $act => $scenes) {
				echo '<th colspan="' . count($scenes) . '">' . $act . '</th>';
			}
		?>
			</tr>
			<tr>
			<?php
			foreach ($parsedText->play as $act => $scenes) {
				foreach ($scenes as $scene) {
					echo '<th>' . $scene . '</th>';
				}
			}
				
			?>
			</tr>
			</thead>
			<tbody>
		<?php
		$readerSpeeches = 0;
		$readerLines = 0;
		$sceneLines = array();

		foreach ($reader as $role) {
			$readerSpeeches += $parsedText->characters[$role]['speeches'];
			$readerLines += $parsedText->characters[$role]['lines'];
			
			echo '<tr>
				<td>' . $x . '</td>
				<td>' . $role . '</td>
				<td>' . $parsedText->characters[$role]['speeches'] . '</td>
				<td>' . $parsedText->characters[$role]['lines'] . '</td>';
		
			foreach ($parsedText->play as $act => $scenes) {
				foreach ($scenes as $scene) {
					echo '<td>' . $parsedText->characters[$role]['PLAY'][$act][$scene] . '</td>';
					$sceneLines[$act][$scene] += $parsedText->characters[$role]['PLAY'][$act][$scene];
				}
			}
		
			echo '</tr>';
			$x++;
		}

		echo '<tr>
		<td></td>
				<td>Total</td>
<td>' . $readerSpeeches . '</td>
			<td>' . $readerLines . '</td>';
			foreach ($sceneLines as $act) {
				foreach ($act as $scene) {
					echo '<td>' . $scene . '</td>';
				}
			}
			echo '</tr>';
		?>
		</tbody>
		</table>
			
		 <?php $i++;
}
		 ?>
	</div>
	<?php } ?>
</div>	
</body>
</html>