function showCharLines() {
	$('h5').removeClass();
	$('.sortDiv').each(function() {
		var charLines = 0;
		
		$(this).find('.lines').each(function() {
			charLines += parseInt($(this).html());
		});
		
		$(this).find('h4').html(charLines + ' Lines');
		var percent = Math.round((charLines / totalLines) * 10000) / 100 + '%';
		if (charLines > totalLines / readers) {
			$(this).find('h5').html(percent).addClass('positive');
		} else {
			$(this).find('h5').html(percent).addClass('negative');			
		}
	});
}

function updateRoles() {
	roles = new Array();
	var i = 0;
	
	$('.connectedSortable').each(function() {
		var j = 0;
		roles[i] = new Array();

		$(this).find('li').each(function() {
			roles[i][j] = $(this).attr('id').replace(/_/g,' ');
			j++;			
		});
		
		i++;
	});
}

function checkConflicts() {
	$('li').removeClass('roleConflict');
	for (i = 0; i < roles.length; i++) {
		for (j = 0; j < roles[i].length; j++ ) {
			var char1 = roles[i][j];
			for (k = 0; k < roles[i].length; k++) {
				var char2 = roles[i][k];
				if ((char1 != char2) && (conversations[char1][char2] > 0)) {
					$('.connectedSortable').eq(i).children('li').eq(j).addClass('roleConflict');
					$('.connectedSortable').eq(i).children('li').eq(k).addClass('roleConflict');
				}
			}
		}
	}
}

$(document).ready(function() {
	
	$('.connectedSortable li').click(function() {

		if ($(this).hasClass('active')) {
			$('.connectedSortable li').removeClass('conflict active').children('.conflicts').html('&nbsp;');
		} else {

			$('.connectedSortable li').removeClass('conflict active').children('.conflicts').html('&nbsp;');
	
			$(this).addClass('active');
	
			var char1 = $(this).attr('id').replace(/_/g,' ');
	
			$('.connectedSortable li').each(function() {
				if ($(this).attr('id')) {
					var char2 = $(this).attr('id').replace(/_/g,' ');
					if (char1 != char2) {
						if (conversations[char1][char2] == 1) {
							$(this).addClass('conflict').children('.conflicts').html(conversations[char1][char2] + ' conversation');
						} else if (conversations[char1][char2] > 0) {
							$(this).addClass('conflict').children('.conflicts').html(conversations[char1][char2] + ' conversations');
						}
					}
				}
			});
		}
	});

	$('.connectedSortable').sortable({
		connectWith: ".connectedSortable",
		deactivate: function() {
			updateRoles();
			checkConflicts();
			showCharLines();			
		},
		placeholder: "ui-state-highlight"
	}).disableSelection();
	
	showCharLines();
	
	$(document).on('click', '.addReader', function() {
		readers++;
		
		if ($('.sortDiv').last().children('ul').hasClass('reader')) {
			$('.sortWrapper').append('<div class="sortDiv"><h3>Reader ' + readers + '</h3><h4>0 lines</h4><h5>0%</h5><ul id="reader' + readers + '" class="connectedSortable reader"></ul></div>');
					
			$('.connectedSortable').sortable({
				connectWith: ".connectedSortable",
				deactivate: function() {
					updateRoles();
					checkConflicts();
					showCharLines();			
				},
				placeholder: "ui-state-highlight"
			}).disableSelection();
		} else {
			$('.sortDiv').last().find('h3').html('Reader ' + readers);
			$('.sortDiv').last().children('ul').addClass('reader');
		}
		
		$('h2').html('Roles - ' + readers + ' Readers');
		
		showCharLines();
	});
	
	highlightColors = [
		"#ffc",
		"#ccf",
		"#fcc",
		"#cff",
		"#fcf",
		"#cfc" 
	];

	$(document).on('change', '.characters input', function() {
		var i = 0;
		$('.characters input').each(function() {
			if ($(this).prop('checked')) {
				console.log(i + "|" + $(this).val());
				$('.' + $(this).val()).css({
					'background-color': highlightColors[i % highlightColors.length]
				});
				i++;
			}
		});
	});
	
	$(document).on('click', '.showHideCharacters', function() {
		$('.charactersWrapper').toggleClass('showCharacters');
	});

	$(document).on('click', '.characters label', function() {
		$(this).siblings('input').trigger('click');
	});

});
