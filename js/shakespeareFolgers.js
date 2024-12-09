var highlightColors = [
		"#ffc",
		"#ccf",
		"#fcc",
		"#cff",
		"#fcf",
		"#cfc",
	];

function showCharLines() {
	$( "h5" ).removeClass();
	$( ".sortDiv" ).each( function() {
		let charLines = 0;
		
		$( this ).find( ".lines" ).each( function() {
			charLines += parseInt( $( this ).html() );
		} );
		
		$( this ).find( "h4" ).html( `${ charLines } Lines` );

		const percent = `${ Math.round( ( charLines / totalLines ) * 10000) / 100 }%`,
		    evenSplit = totalLines / readers;

		$( this ).find( "h5" ).html( percent )
		if ( charLines > evenSplit * 1.25 ) {
			$( this ).find( "h5" ).addClass( "positive" );
		} else if ( charLines < evenSplit * .75 ) {
			$( this ).find( "h5" ).addClass( "negative" );			
		}
	} );
}

function updateRoles() {
	const roles = [];
	let i = 0;
	
	$( '.connectedSortable').each(function() {
		let j = 0;
		roles[ i ] = [];

		$( this ).find( "li ").each( function() {
			roles[ i ][ j ] = $( this ).attr( "id" );
			j++;
		} );
		
		i++;
	} );
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
	$( ".sortDiv li" ).each( function() {
		var reader = $( this ).parent().parent().find( ".renameReader" ).val();
		$( `h4.${ $( this ).attr( "id" ) }` ).find( ".reader" ).html( `(${ reader })` );
	});
}

function addHighlighting() {
	var i = 0;
	$('.sortDiv').each(function() {
		var highlight = false;
		if ($(this).find('.highlightInput').is(':checked')) {
			highlight = true;
		}
		
		$( this ).find( "li" ).each( function() {
		    const highlightClass = `.${ $( this ).attr( "id" ) }`;
			$( highlightClass ).removeAttr( "style" );
			if ( highlight ) {
				$( highlightClass ).css( {
					'background-color': highlightColors[ i % highlightColors.length ]
				} );
			}
		} );
		
		if (highlight) {
			i++;
		}
	} );
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
		
		if ($('.sortDiv').last().find('h3').hasClass('unassignedReader')) {
			$('.sortDiv').last().find('h3').removeClass('unassignedReader').html('Reader ' + readers);
		} else {
			$('.sortWrapper').append(
				'<div class="sortDiv"><span><h3>Reader ' + readers + '</h3>' + 
				'<input name="reader" type="text" class="renameReader" value="Reader ' + readers + '"></span>' +
				'<p><input name="highilght" type="checkbox" class="highlightInput" /> Highlight</p>' +
				'<h4>0 lines</h4>' +
				'<h5>0%</h5>' + 
				'<ul id="reader' + readers + '" class="connectedSortable reader"></ul></div>');
					
			$('.connectedSortable').sortable({
				connectWith: ".connectedSortable",
				deactivate: function() {
					updateRoles();
					checkConflicts();
					showCharLines();			
				},
				placeholder: "ui-state-highlight"
			}).disableSelection();			
		}
		
		$( "h2" ).html( `Roles - ${ readers } Readers`);
		
		showCharLines();
	});

	$( document ).on( "change", ".characters input", function() {
		var i = 0;
		$( ".characters input" ).each( function() {
		    const highlightClass = `.${ $( this ).val() }`;

			$( highlightClass ).removeAttr( "style" );

			if ( $( this ).prop( "checked" ) ) {
				$( highlightClass ).css( {
					'background-color': highlightColors[ i % highlightColors.length ]
				} );
				i++;
			}
		} );
	} );

	$(document).on('click', '.characters label', function() {
		$(this).siblings('input').trigger('click');
	});
	
	$( document ).on( "click", ".showSort span", function() {
		updateReaderNames();
		addHighlighting();
		$( ".showSort" ).find( "span" ).toggle();
		$( ".sortSection" ).toggle();
	});

	$(document).on('change', '#lineNumberDisplay', function() {
		$('.sceneLine, .playLine').hide();
		if ($(this).val() == 'scene') {
			$('.sceneLine').show();
		} else if ($(this).val() == 'play') {
			$('.playLine').show();
		}
	});
	
	$('.sortWrapper').on('click', 'span h3', function() {
		if (!$(this).hasClass('unassignedReader')) {
			$(this).hide();
			$(this).siblings('.renameReader').show().focus().select();
		}
	});
	
	$('.sortWrapper').on('focusout', '.renameReader', function() {
		$('.sortDiv span h3').each(function() {
			$(this).html($(this).siblings('.renameReader').val());
		});
		$('.sortDiv span .renameReader').hide();
		$('.sortDiv span h3').show();
	});

	$( ".sortWrapper" ).on( "keypress", ".renameReader", function( event ) {

		if ( event.keyCode === 13 ) {
			$( this ).trigger( "focusout" );
		}
	} );
	
	$( ".playWrapper" ).scroll( function() {
	    var height = $( ".play" ).height(),
	        offset = $( ".play" ).offset() || { top: 0 },
	        currentTop = -offset.top,
	        wrapperOffset = $( ".playWrapper" ).height(),
	        percentage = Math.max( Math.round( ( ( ( wrapperOffset / 2 ) + currentTop ) - ( wrapperOffset / 2 ) ) / (  height - wrapperOffset )
 * 10000 ) / 100, 0 ),
	        percent = `${ percentage }%`;
		$( ".completed" ).width( percent ).css( {
		    'background-color': `rgba( ${ 255 - ( 255 * percentage / 100 ) }, ${ 255 * percentage / 100 }, 0, 1 )`
		} );
		$( ".percentText" ).html( percent );
	} );

	updateReaderNames();
});
