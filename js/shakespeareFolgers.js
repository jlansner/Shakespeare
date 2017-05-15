var highlightColors = [
		"#ffc",
		"#ccf",
		"#fcc",
		"#cff",
		"#fcf",
		"#cfc",
	];

function showCharLines() {
	$('h5').removeClass();
	$('.sortDiv').each(function() {
		var charLines = 0;
		
		$(this).find('.lines').each(function() {
			charLines += parseInt($(this).html());
		});
		
		$(this).find('h4').html(charLines + ' Lines');
		var percent = Math.round((charLines / totalLines) * 10000) / 100 + '%';
		if (charLines > (totalLines / readers) * 1.25) {
			$(this).find('h5').html(percent).addClass('positive');
		} else if (charLines < (totalLines / readers) * .75) {
			$(this).find('h5').html(percent).addClass('negative');			
		} else {
			$(this).find('h5').html(percent);
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
			roles[i][j] = $(this).attr('id');
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
				if ((char1 != char2) && (characters[char1]['interactions'][char2]) && (characters[char1]['interactions'][char2] > 0)) {
					$('.connectedSortable').eq(i).children('li').eq(j).addClass('roleConflict');
					$('.connectedSortable').eq(i).children('li').eq(k).addClass('roleConflict');
				}
			}
		}
	}
}

function updateReaderNames() {
	$('.sortDiv li').each(function() {
		var reader = $(this).parent().parent().find('.renameReader').val();
		$('h4.' + $(this).attr('id')).find('.reader').html('(' + reader + ')');
	});
}

function addHighlighting() {
	var i = 0;
	$('.sortDiv').each(function() {
		var highlight = false;
		if ($(this).find('.highlightInput').is(':checked')) {
			highlight = true;
		}
		$(this).find('li').each(function() {
			$('.' + $(this).attr('id')).removeAttr('style');
			if (highlight) {
				$('.' + $(this).attr('id')).css('background-color',highlightColors[i % highlightColors.length]);
			}
		});
		
		if (highlight) {
			i++;
		}
	});
}

$(document).ready(function() {
	
	$('.connectedSortable li').click(function() {

		if ($(this).hasClass('active')) {
			$('.connectedSortable li').removeClass('conflict active').find('.conflicts').html('&nbsp;');
		} else {

			$('.connectedSortable li').removeClass('conflict active').find('.conflicts').html('&nbsp;');
	
			$(this).addClass('active');
	
			var char1 = $(this).attr('id'); //.replace(/_/g,' ');
	
			$('.connectedSortable li').each(function() {
				if ($(this).attr('id')) {
					var char2 = $(this).attr('id'); //.replace(/_/g,' ');
					if (char1 != char2) {
						if (characters[char1]['interactions'][char2] == 1) {
							$(this).addClass('conflict').find('.conflicts').html(characters[char1]['interactions'][char2] + ' conversation');
						} else if (characters[char1]['interactions'][char2] > 0) {
							$(this).addClass('conflict').find('.conflicts').html(characters[char1]['interactions'][char2] + ' conversations');
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
			$('.sortWrapper').append('<div class="sortDiv"><span><h3>Reader ' + readers + '</h3><input name="reader" type="text" class="renameReader" value="Reader ' + readers + '"></span><h4>0 lines</h4><h5>0%</h5><ul id="reader' + readers + '" class="connectedSortable reader"></ul></div>');
					
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

	$(document).on('change', '.characters input', function() {
		var i = 0;
		$('.characters input').each(function() {
			$('.' + $(this).val()).removeAttr('style');

			if ($(this).prop('checked')) {
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
	
	$(document).on('click', '.showSort', function() {
		updateReaderNames();
		addHighlighting();
		$('.sortSection').toggle();
	});
	
	$('.sortWrapper').on('click', 'span h3', function() {
		$(this).hide();
		$(this).siblings('.renameReader').show().focus().select();
	});
	
	$('.sortWrapper').on('focusout', '.renameReader', function() {
		$('.sortDiv span h3').each(function() {
			$(this).html($(this).siblings('.renameReader').val());
		});
		$('.sortDiv span .renameReader').hide();
		$('.sortDiv span h3').show();
	});

	$('.sortWrapper').on('keypress', '.renameReader', function(event) {

		if (event.keyCode == 13) {
			$(this).trigger('focusout');
		}
	});
	
	$(document).on('scroll', function() {
		var percent = (Math.round(window.scrollY / $(document).height() * 10000) / 100) + "%";
		$('.completed').width(percent);
		$('.percentText').html(percent);
	});
	updateReaderNames();
});
