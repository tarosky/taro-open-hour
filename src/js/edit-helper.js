/*!
 * Admin screen helper for
 */

/*global TsOpenHour:false */

jQuery( document ).ready( function( $ ) {
	'use strict';

	const $table = $( '#tsoh-time-table' );
	// Add button
	$( '#timeadd' ).click( function( e ) {
		e.preventDefault();
		const start = $( '#tsoh-time-start' ).val();
		const end = $( '#tsoh-time-end' ).val();
		let errorMsg = '';
		if ( ! start.match( /^[0-9]{2}:[0-9]{2}$/ ) ) {
			errorMsg += TsOpenHour.startError + '\n';
		}
		if ( ! end.match( /^[0-9]{2}:[0-9]{2}$/ ) ) {
			errorMsg += TsOpenHour.endError + '\n';
		}
		if ( parseInt( start.replace( ':', '' ), 10 ) >= parseInt( end.replace( ':', '' ), 10 ) ) {
			errorMsg += TsOpenHour.pastStartError;
		}
		if ( '' === errorMsg ) {
			createTable( start, end );
		} else {
			/*eslint no-alert: "off"*/
			window.alert( errorMsg );
		}
	} );

	// Default Button
	$( '#timeadd-default' ).click( function( e ) {
		e.preventDefault();
		// Check if table exists
		if ( $table.find( 'tbody tr' ).length > 0 ) {
			window.alert( TsOpenHour.notEmpty );
		} else {
			$.each( TsOpenHour.defaultTime, function( index, time ) {
				createTable( time[ 0 ], time[ 1 ] );
			} );
		}
	} );

	// Add table
	const createTable = function( start, end ) {
		// Count table line
		const row = $table.find( 'tbody tr' ).length;
		let tag = '<tr>';
		tag += '<th scope="row"><input name="tsoh_open_hour[]" type="text" value="' + start + '-' + end + '" /></th>';
		for ( let i = 0; i < 7; i++ ) {
			tag += '<td><select name="tsoh_date_' + i + '[]">';
			tag += '<option value=""';
			if ( i >= 5 ) {
				tag += ' selected="selected"';
			}
			tag += '>-</option>';
			tag += '<option value="0"';
			if ( -1 < TsOpenHour.defaultDays.indexOf( i ) ) {
				tag += ' selected="selected"';
			}
			tag += '>&#x2713;</option>';
			tag += '</select></td>';
		}
		tag += '<td><a class="delete-time-shift" href="#">' + TsOpenHour.deleteBtn + '</a></td>';
		tag += '</tr>';
		if ( row < 1 ) {
			$table.find( 'tbody' ).append( tag );
			$table.find( 'tbody tr:first' ).effect( 'highlight', {}, 1000 );
		} else {
			//Find place
			$table.find( 'tbody tr' ).each( function( index, tr ) {
				// Get start time
				const currentStart = $( tr ).find( 'th input' ).val().replace( /-[0-9]{2}:[0-9]{2}$/, '' );
				if ( parseInt( start.replace( ':', '' ), 10 ) < parseInt( currentStart.replace( ':', '' ), 10 ) ) {
					// Compare time and insert if earlier.
					$( tr ).before( tag );
					$table.find( 'tbody tr:eq(' + index + ')' ).effect( 'highlight', {}, 1000 );
					return false;
				} else if ( row - 1 === index ) {
					// no next lien
					$( tr ).after( tag );
					$table.find( 'tbody tr:eq(' + ( index + 1 ) + ')' ).effect( 'highlight', {}, 1000 );
					return false;
				}
			} );
		}
		reZebra();
	};

	// delete handler
	$table.on( 'click', 'tbody tr a', function( e ) {
		e.preventDefault();
		if ( window.confirm( TsOpenHour.deleteConfirm ) ) {
			$( this ).parent( 'td' ).parent( 'tr' ).hide( 'highlight', {}, 1000, function() {
				$( this ).remove();
				reZebra();
			} );
		}
	} );

	//ゼブラクラスの振りなおし
	const reZebra = function() {
		$( '#tsoh-time-table tbody tr' ).each( function( index, elt ) {
			const className = ( 0 === ( index + 1 ) % 2 ) ? 'alt' : 'odd';
			//既存のクラスを削除
			$( elt ).removeClass( 'alt' ).removeClass( 'odd' ).addClass( className );
			//inputタグのname属性を振りなおし
			$( elt ).find( 'th input' ).attr( 'name', 'tsoh_open_hour[' + index + ']' );
			$( elt ).find( 'input[type=checkbox]' ).each( function( i, e ) {
				$( e ).attr( 'name', 'tsoh_date_' + i + '[' + index + ']' );
			} );
		} );
	};
} );
