import { useState, useEffect, useCallback } from 'react';
import type { Settings } from '../types';
import { useDrawings } from '../hooks/useApi';
import { DrawingGenerator } from './DrawingGenerator';
import { HistoricalDrawings } from './HistoricalDrawings';
import { HouseholdManager } from './HouseholdManager';
import './App.css';
import { DrawingSettingsPanel } from './DrawingSettingsPanel';

type TabId = 'draw' | 'history' | 'manage';

export function App() {
	const [ activeTab, setActiveTab ] = useState< TabId >( 'draw' );
	const [ settings, setSettings ] = useState< Settings | null >( null );
	const [ error, setError ] = useState< string | null >( null );

	const { getSettings } = useDrawings();

	const loadSettings = useCallback( async () => {
		try {
			const data = await getSettings();
			setSettings( data );
		} catch ( err ) {
			setError(
				err instanceof Error ? err.message : 'Failed to load settings'
			);
		}
	}, [ getSettings ] );

	useEffect( () => {
		loadSettings();
	}, [ loadSettings ] );

	const tabs: { id: TabId; label: string }[] = [
		{ id: 'draw', label: 'Generate Drawing' },
		{ id: 'history', label: 'History' },
		{ id: 'manage', label: 'Manage' },
		{ id: 'settings', label: 'Settings' },
	];

	if ( error ) {
		return (
			<div className="gift-draw-app">
				<div className="gift-draw-error">{ error }</div>
			</div>
		);
	}

	if ( ! settings ) {
		return (
			<div className="gift-draw-app">
				<div className="gift-draw-loading">Loading...</div>
			</div>
		);
	}

	return (
		<div className="gift-draw-app">
			<header className="gift-draw-header">
				<h1>Gift-Giving Draw</h1>
				<nav className="gift-draw-tabs">
					{ tabs.map( ( tab ) => (
						<button
							key={ tab.id }
							className={ `gift-draw-tab ${
								activeTab === tab.id ? 'active' : ''
							}` }
							onClick={ () => setActiveTab( tab.id ) }
						>
							{ tab.label }
						</button>
					) ) }
				</nav>
			</header>

			<main className="gift-draw-content">
				{ activeTab === 'draw' && (
					<DrawingGenerator settings={ settings } />
				) }
				{ activeTab === 'history' && <HistoricalDrawings /> }
				{ activeTab === 'manage' && <HouseholdManager /> }
				{ activeTab === 'settings' && (
					<DrawingSettingsPanel
						settings={ settings }
						setSettings={ setSettings }
					/>
				) }
			</main>
		</div>
	);
}
