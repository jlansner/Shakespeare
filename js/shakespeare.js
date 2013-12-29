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

$(document).ready(function() {
	
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
