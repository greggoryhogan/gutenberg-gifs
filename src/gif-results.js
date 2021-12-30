import React from 'react';
const __ = wp.i18n.__; // The __() for internationalization.
import { Button, Spinner } from '@wordpress/components';
const GifResults = ({ gifResults, setGif, setGifSearch, pagePos, searchTerm, hasNextPage, isLoading}) => {
	
	if(isLoading) {
		return (
			<div class="centercontent"><Spinner /></div>
		)
	}
	const prevPage = (where) => {
		setGifSearch( searchTerm, 'prev');
	}
	const nextPage = (where) => {
		setGifSearch( searchTerm, 'next');
	}

	let prevdisabled = true;
	if(pagePos > 0 || hasNextPage == false) {
		prevdisabled = false;
	} 	
	let prev = <Button className="is-primary" onClick={prevPage} disabled={prevdisabled}>Previous</Button>
	
	let nextdisabled = true;
	if(pagePos != null && hasNextPage != false) {
		nextdisabled = false;
	} 
	let next = <Button className="is-primary" onClick={nextPage} disabled={nextdisabled}>Next</Button>

	if(!gifResults) {
		return (
			<div>
				<div class="gifresults">
					
				</div>
				<div class="gif-navigation">{prev}{next}</div>
			</div>
		)
	}

	// if we do not have any results to show, show the message and prevent code from further rendering
	if ( !gifResults.length ) return '';
	console.log(gifResults);
	return (
		<div>
			<div class="gifresults">
				{ gifResults.map(value => (
					<img src={value.preview} width={value.width} height={value.height} url={value.url} onClick={() => setGif(value)} />
					)
				)}
			</div>
			<div class="gif-navigation">{prev}{next}</div>
		</div>
	)
	
}

export default GifResults;