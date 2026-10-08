/*!
 * Block editor scripts.
 *
 * @handle tsoh-block-editor
 * @deps wp-blocks, wp-element, wp-block-editor, wp-components, wp-server-side-render, wp-api-fetch, wp-i18n, wp-url
 */

const { registerBlockType } = wp.blocks;
const { useState, useEffect } = wp.element;
const { InspectorControls, useBlockProps } = wp.blockEditor;
const { PanelBody, ComboboxControl, ToggleControl, Placeholder } =
	wp.components;
const ServerSideRender = wp.serverSideRender;
const apiFetch = wp.apiFetch;
const { addQueryArgs } = wp.url;
const { __, sprintf } = wp.i18n;

/**
 * Place selector which searches places via REST API.
 *
 * @param {Object}   props
 * @param {number}   props.value    Selected post ID. 0 means default.
 * @param {Function} props.onChange Callback with post ID.
 */
const PlaceSelector = ( { value, onChange } ) => {
	const [ search, setSearch ] = useState( '' );
	const [ places, setPlaces ] = useState( {} );

	useEffect( () => {
		const timer = setTimeout( () => {
			apiFetch( {
				path: addQueryArgs( '/business-places/v1/places', {
					s: search,
					page: 1,
				} ),
			} )
				.then( ( result ) => {
					const found = {};
					( Array.isArray( result ) ? result : [] ).forEach(
						( place ) => {
							found[ place.ID ] = place.label;
						}
					);
					setPlaces( ( current ) => ( { ...current, ...found } ) );
				} )
				.catch( () => {
					// Not found or no permission. Keep current options.
				} );
		}, 300 );
		return () => clearTimeout( timer );
	}, [ search ] );

	const options = Object.keys( places ).map( ( id ) => ( {
		value: String( id ),
		label: places[ id ],
	} ) );
	if ( value && ! places[ value ] ) {
		options.unshift( {
			value: String( value ),
			// translators: %d is post ID.
			label: sprintf( __( 'Post #%d', 'taro-open-hour' ), value ),
		} );
	}

	return (
		<ComboboxControl
			__nextHasNoMarginBottom
			__next40pxDefaultSize
			label={ __( 'Place', 'taro-open-hour' ) }
			help={ __(
				'If empty, the current post or the site location will be used.',
				'taro-open-hour'
			) }
			value={ value ? String( value ) : null }
			options={ options }
			onFilterValueChange={ setSearch }
			onChange={ ( id ) => onChange( id ? parseInt( id, 10 ) : 0 ) }
		/>
	);
};

/**
 * Create edit component.
 *
 * @param {string}  name       Block name.
 * @param {string}  icon       Dashicon name.
 * @param {boolean} hasToggles Whether to show map/access toggles.
 * @return {Function} Edit component.
 */
const createEdit = ( name, icon, hasToggles ) => {
	return ( { attributes, setAttributes } ) => {
		const blockProps = useBlockProps();
		return (
			<div { ...blockProps }>
				<InspectorControls>
					<PanelBody title={ __( 'Settings', 'taro-open-hour' ) }>
						<PlaceSelector
							value={ attributes.postId }
							onChange={ ( postId ) => setAttributes( { postId } ) }
						/>
						{ hasToggles && (
							<>
								<ToggleControl
									__nextHasNoMarginBottom
									label={ __(
										'Hide Google Map',
										'taro-open-hour'
									) }
									checked={ attributes.noMap }
									onChange={ ( noMap ) =>
										setAttributes( { noMap } )
									}
								/>
								<ToggleControl
									__nextHasNoMarginBottom
									label={ __(
										'Hide access information',
										'taro-open-hour'
									) }
									checked={ attributes.noAccess }
									onChange={ ( noAccess ) =>
										setAttributes( { noAccess } )
									}
								/>
							</>
						) }
					</PanelBody>
				</InspectorControls>
				<ServerSideRender
					block={ name }
					attributes={ attributes }
					EmptyResponsePlaceholder={ () => (
						<Placeholder
							icon={ icon }
							label={ __(
								'No place information found.',
								'taro-open-hour'
							) }
							instructions={ __(
								'Select a place in the block settings.',
								'taro-open-hour'
							) }
						/>
					) }
				/>
			</div>
		);
	};
};

registerBlockType( 'tsoh/open-hour', {
	apiVersion: 3,
	title: __( 'Open Hour', 'taro-open-hour' ),
	description: __( 'Display the time table of a place.', 'taro-open-hour' ),
	category: 'widgets',
	icon: 'clock',
	keywords: [ 'business', 'time table' ],
	attributes: {
		postId: { type: 'number', default: 0 },
	},
	edit: createEdit( 'tsoh/open-hour', 'clock', false ),
	save: () => null,
} );

registerBlockType( 'tsoh/business-place', {
	apiVersion: 3,
	title: __( 'Business Place', 'taro-open-hour' ),
	description: __(
		'Display address, map and contacts of a place.',
		'taro-open-hour'
	),
	category: 'widgets',
	icon: 'location',
	keywords: [ 'location', 'address', 'map' ],
	attributes: {
		postId: { type: 'number', default: 0 },
		noMap: { type: 'boolean', default: false },
		noAccess: { type: 'boolean', default: false },
	},
	edit: createEdit( 'tsoh/business-place', 'location', true ),
	save: () => null,
} );
