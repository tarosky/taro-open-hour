/**
 * Widget helper.
 */

/* global wpApiSettings: false */

const $ = jQuery;

const makeSelect2 = ( select ) => {
	$( select ).select2( {
		allowClear: true,
		multiple: false,
		placeholder: $( select ).attr( 'data-placeholder' ),
		ajax: {
			url: wpApiSettings.root + 'business-places/v1/places',
			datatype: 'json',
			delay: 300,
			data( params ) {
				return {
					s: params.term,
				};
			},
			beforeSend( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
			},
			processResults( data ) {
				return {
					results: data.map( ( item ) => {
						return {
							id: item.ID,
							text: item.label,
						};
					} ),
				};
			},
			minimumInputLength: 2,
		},
	} );
};

// Executed on domready.
$( document ).ready( function () {
	$( '.location-selector' ).each( function ( index, select ) {
		if ( $( select ).attr( 'id' ).match( /__i__/ ) ) {
			return true;
		}
		makeSelect2( select );
	} );
} );

// Widgets updated.
$( document ).on( 'widget-updated widget-added', function () {
	$( '.location-selector' ).each( function ( index, select ) {
		if ( $( select ).attr( 'id' ).match( /__i__/ ) ) {
			return true;
		}
		if ( $( select ).hasClass( '.select2-hidden-accessible' ) ) {
			return true;
		}
		makeSelect2( select );
	} );
} );
