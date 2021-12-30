import React from 'react';
const __ = wp.i18n.__; // The __() for internationalization.
import { RichText  } from '@wordpress/block-editor';
import { ResizableBox } from '@wordpress/components';
const GifBox = ({attributes, setCaption, setAttributes, plugin_settings }) => {
	// extract the properties we will use from user data
	const {currentGif,currentGifWidth,currentGifHeight, align, captionText, altText, gifBoxWidth, gifBoxHeight} = attributes;
	
	if(!currentGif && plugin_settings.tenor_api_key == '') {
		return (
			<RichText.Content tagName="p" value={sprintf( __('Please enter your Tenor API key on the <a href="%s">settings page</a>.','gg'), plugin_settings.gg_settings_page )} />
		)
	}
	if(!currentGif) {
		return (
			<div className="nogif"><RichText.Content tagName="p" value={__('Use the search in the block settings to find a gif...','gg')} /></div>
		)
	}
	
	let alignment = align;
	if(!align) {
		alignment = 'none';
	}
	
	let classes = 'align'+alignment;
	return(
		
		<div className="wp-block-image gg-gif-block">
			<figure className={classes}>
			<ResizableBox
				size={ {
					height: gifBoxHeight,
					width: gifBoxWidth,
				} }
				minHeight="50"
				minWidth="50"
				enable={ {
					top: false,
					right: true,
					bottom: false,
					left: true,
					topRight: false,
					bottomRight: false,
					bottomLeft: false,
					topLeft: false,
				} }
				lockAspectRatio="true"
				onResizeStop={ ( event, direction, elt, delta ) => {
					setAttributes( {
						gifBoxHeight: parseInt( gifBoxHeight + delta.height, 10 ),
						gifBoxWidth: parseInt( gifBoxWidth + delta.width, 10 ),
					} );
				} }
				onResizeStart={ () => {
					
				} }
			>
				<img 
					src={currentGif}
					width={currentGifWidth}
					height={currentGifHeight}
					alt={altText} />
				</ResizableBox>
				<figcaption>
					<RichText
						tagName="span" // The tag here is the element output and editable in the admin
						value={captionText} // Any existing content, either from the database or an attribute default
						onChange={ ( newCaption ) => setCaption( newCaption ) }
						placeholder={ __( 'Enter a caption. Via Tenor' ) } // Display this text before any content has been added by the user
					/>
					
				</figcaption>
			</figure>
		</div>
	);
}

export default GifBox;