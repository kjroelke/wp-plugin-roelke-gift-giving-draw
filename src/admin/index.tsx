import { createRoot } from 'react-dom/client';
import { App } from './components/App';

const container = document.getElementById( 'gift-giving-draw-app' );

if ( container ) {
	const root = createRoot( container );
	root.render( <App /> );
}
