import React, { useState, useEffect } from 'react';
import type { Settings } from '../types';

interface DrawingSettingsPanelProps {
	settings: Settings;
	setSettings: ( settings: Settings ) => void;
}

import { useDrawings } from '../hooks/useApi';

export function DrawingSettingsPanel( {
	settings,
	setSettings,
}: DrawingSettingsPanelProps ) {
	const drawingsApi = useDrawings();
	const [ loading, setLoading ] = useState( false );
	const [ error, setError ] = useState< string | null >( null );
	const [ yearsLookback, setYearsLookback ] = useState(
		settings.years_lookback
	);
	const [ minimumAge, setMinimumAge ] = useState( settings.minimum_age );

	useEffect( () => {
		setYearsLookback( settings.years_lookback );
		setMinimumAge( settings.minimum_age );
	}, [ settings ] );

	const handleSave = async () => {
		setLoading( true );
		setError( null );
		try {
			const updated = await drawingsApi.saveSettings(
				yearsLookback,
				minimumAge
			);
			setSettings( updated );
		} catch ( err ) {
			setError(
				err instanceof Error ? err.message : 'Failed to save settings'
			);
		} finally {
			setLoading( false );
		}
	};

	return (
		<div className="gift-draw-settings-panel">
			<h3>Drawing Settings</h3>
			{ error && <div className="gift-draw-error">{ error }</div> }
			<div className="gift-draw-form-group">
				<label htmlFor="years-lookback">Years to look back:</label>
				<input
					type="number"
					id="years-lookback"
					min={ 1 }
					max={ 10 }
					value={ yearsLookback }
					onChange={ ( e ) =>
						setYearsLookback( Number( e.target.value ) )
					}
				/>
			</div>
			<div className="gift-draw-form-group">
				<label htmlFor="minimum-age">Minimum age requirement:</label>
				<input
					type="number"
					id="minimum-age"
					min={ 0 }
					max={ 120 }
					value={ minimumAge }
					onChange={ ( e ) =>
						setMinimumAge( Number( e.target.value ) )
					}
				/>
			</div>
			<button
				className="gift-draw-btn gift-draw-btn-primary"
				onClick={ handleSave }
				disabled={ loading }
			>
				{ loading ? 'Saving...' : 'Save Settings' }
			</button>
		</div>
	);
}
