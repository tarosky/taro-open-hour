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

$( document ).ready( function() {
	$( '.location-selector' ).each( function( index, select ) {
		makeSelect2( select );
	} );
} );
