import React from 'react';
import { __experimentalInputControl as InputControl, Button, TextControl, ExternalLink } from '@wordpress/components';
import { withState } from '@wordpress/compose';
import { RichText } from '@wordpress/block-editor';
import GifResults from './gif-results';

// Fragment will be used as wrapper if we do not want to include markup, like div, etc
const {Fragment} = wp.element; 

// InspectorControls will be used to wrap Panel body component
// we need this two wrapper component if we want to display our settings
// in the right panel (where we have document and block tabs, next to the content)
const { InspectorControls } = wp.editor;
const { PanelBody, Text } = wp.components;
const __ = wp.i18n.__; // The __() for internationalization.

const GifSearch = ( {attributes, setAttributes, searchTerm, setGifSearch, gifResults, pagePos, hasNextPage, isLoading, setGif, plugin_settings } ) => {
	
	const {altText,currentGif} = attributes;
	const keyLabel = sprintf( __('Please enter your Tenor API key on the <a href="%s">settings page</a>.','gg'), plugin_settings.gg_settings_page );
	
	//alt text box
	let altTextInput = <TextControl
		label={ __( 'Alt text (alternative text)' ) }
		value={ altText }
		help={<ExternalLink href="https://www.w3.org/WAI/tutorials/images/decision-tree">Describe the purpose of the image</ExternalLink>}
		onChange={ ( value ) => setAttributes({
			altText: value
		}) }
	/>
	//remove alt text box if not key has been entered or there is no gif defined
	if(!currentGif || plugin_settings.tenor_api_key == '') {
		altTextInput = '';
	}

	//input for searching for gifs
	let gifSearchInput = <Fragment>
		<div class="powered-by">{ __( 'Powered by Tenor' ) }</div>
		<InputControl
		label={ __( 'Set Gif' ) }
		placeholder={ __( 'Search Tenor' ) }
		value={searchTerm}
		isPressEnterToChange='true'
		suffix={<Button className='is-primary'>{ __( 'Search' ) }</Button>}
		onChange={ ( newSearchTerm ) => setGifSearch( newSearchTerm, 'reset' ) }
		/>
	</Fragment>
	
	//remove input if they havent entered their key
	if(plugin_settings.tenor_api_key == '') {
		gifSearchInput = <Fragment>
			<RichText.Content tagName="p" value={keyLabel} />
			<Button className='components-button is-secondary' href={plugin_settings.gg_settings_page}>{ __( 'Visit Settings Page' ) }</Button>
		</Fragment>
	}

	return(	
		<Fragment>
			<InspectorControls>
				<PanelBody title={ __( 'Settings' ) }>
					{altTextInput}
					{gifSearchInput}
					<GifResults gifResults={gifResults} pagePos={pagePos} setGifSearch={setGifSearch} searchTerm={searchTerm} hasNextPage={hasNextPage} isLoading={isLoading} setGif={setGif} />
				</PanelBody>
			</InspectorControls>
		</Fragment>
	);
}

// wrap the component with withState so we can manipulate the state
// by using nativelly supporeted WordPress functions
export default withState() (GifSearch);