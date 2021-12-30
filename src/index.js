import React from 'react';
import { withState } from '@wordpress/compose';
import apiFetch from '@wordpress/api-fetch'; 
import { RichText } from '@wordpress/block-editor';
import GifSearch from './gif-search';
import GifBox from './gif-box';
import { BlockControls, BlockAlignmentToolbar } from '@wordpress/block-editor'; //to add alignment without using support

const __ = wp.i18n.__; // The __() for internationalization.
const registerBlockType = wp.blocks.registerBlockType; // The registerBlockType() to register blocks.
const {Fragment} = wp.element; // Wrapper we can use instead of adding markup, like div, etc

let plugin_settings = gg_settings; //localized settings from enqueue scripts
/**
 * Register: a Gutenberg Block.
 *
 * Registers a new block provided a unique name and an object defining its
 * behavior. Once registered, the block is made editor as an option to any
 * editor interface where blocks are implemented.
 *
 * @link https://wordpress.org/gutenberg/handbook/block-api/
 * @param  {string}   name     Block name.
 * @param  {Object}   settings Block settings.
 * @return {?WPBlock}          The block, if it has been successfully
 *                             registered; otherwise `undefined`.
 */
registerBlockType("fragment/gif-search-and-embed", {
	title: __("Gif Search"), // Our block title
	description: __( 'Search and embed gifs directly from Tenor.', 'gg' ),
	icon: "format-image",
	category: "media", // pick a category from core provided ones or create a custom one
	keywords: [__("Image"), __("Gif")],
	// attributes start here
	attributes: {
		currentGif: {
			type: 'string',
		},
		currentGifWidth: {
			type: 'number',
		},
		currentGifHeight: {
			type: 'number',
		},
		altText: {
			type: 'string',
		},
		captionText: {
			type: 'string',
		},
		align: {
			type: "string",
		},
		gifBoxWidth: {
			type: 'number',
		},
		gifBoxHeight: {
			type: 'number',
		},
	},
	// attributes end here
	//show example when hovering to select block
	example: {
		attributes: {
			currentGif: 'https://media.tenor.com/images/a8e4ceb0e6e1eaa33da1233bad36bd98/tenor.gif',
			currentGifWidth: 500,
			currentGifHeight: 280,
			gifBoxWidth: 500,
			gifBoxHeight: 280,
			captionText: 'Via Tenor',
		},
	},
	/**
	 * Edit function will render our block code
	 * inside the Gutemberg editor once inserted
	 */
	edit: withState( {gifResults: []} )( ( {gifResults, setState, attributes, setAttributes, searchTerm, pagePos, hasNextPage, isLoading } ) => {
		
		const setGifSearch = (newSearchTerm, where = null) => {
			if(where == 'reset') {
				//new search, reset counter
				pagePos = 0;
			} else if(where == 'prev') {
				//previous button pushed
				pagePos = pagePos - 1;
			} else {
				//next button pushed
				pagePos = pagePos + 1;
			}
			
			if(newSearchTerm) {
				//show spinner
				setState({isLoading: true});
				//get results
				apiFetch( { path: '/gg/v1/search/'+newSearchTerm+'/pos/'+pagePos } )
					.then( response => {
						//see if we have a next page
						hasNextPage = true;
						if(response.last_page == 1) {
							hasNextPage = false;
						}
						//set state to update display
						setState({gifResults: response.options, pagePos: pagePos, searchTerm: newSearchTerm, hasNextPage: hasNextPage, isLoading: false});
					} );
			} 
			
		}
		
		const setCaption = (caption) => {
			if(!caption.includes('Via Tenor')) {
				caption = caption + ' Via Tenor';
			}
			setAttributes({
				captionText: caption
			});
		}
		
		const setGif = (newGif) => {
			setAttributes({
				currentGif: newGif.url,
				currentGifWidth: newGif.width,
				currentGifHeight: newGif.height,
				gifBoxWidth: newGif.width, //reset resizable box container
				gifBoxHeight: newGif.height //reset resizable box container
			});
		}

		

		return(
			<Fragment>
				<BlockControls>
				<BlockAlignmentToolbar
					value={ attributes.align }
					onChange={ ( nextAlign ) => {
						setAttributes( { align: nextAlign } );
					} }
				/>
				</BlockControls>
				<GifSearch attributes={attributes} gifResults={gifResults} setAttributes={setAttributes} setGifSearch={setGifSearch} searchTerm={searchTerm} pagePos={pagePos} hasNextPage={hasNextPage} isLoading={isLoading} setGif={setGif} plugin_settings={plugin_settings} />
				<GifBox attributes={attributes} setCaption={setCaption} setAttributes={setAttributes} plugin_settings={plugin_settings} />
			</Fragment>
		)
  	}),

	/**
	 * Save function will handle the client side rendering
	 * This is the code (html markup) which will be saved into the_content
	 * once post is saved
	 */
	save: props => {
		const {attributes} = props;
		const {currentGif,currentGifWidth,currentGifHeight, align, captionText, altText,gifBoxWidth} = attributes;
		
		if ( !currentGif ) return '';
		let alignment = align;
		if(!align) {
			alignment = 'none';
		}
		let classes = 'align'+alignment;
		let caption = 'Via Tenor';
		if(captionText) {
			caption = captionText;
		}
		return(
			<Fragment>
				<div className="wp-block-image gg-gif-block">
					<figure className={classes} style={{width:gifBoxWidth}}>
						<div className="components-resizable-box__container">
							<img 
								src={currentGif}
								width={currentGifWidth}
								height={currentGifHeight}
								alt={altText} />
						</div>
						<figcaption>
							<RichText.Content tagName="span" value={ caption } />
						</figcaption>
					</figure>
				</div>
			</Fragment>
		)
	},
	
});