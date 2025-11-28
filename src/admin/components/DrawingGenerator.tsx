import { useState } from 'react';
import type { Settings, Drawing, Pairing } from '../types';
import { useDrawings } from '../hooks/useApi';

interface DrawingGeneratorProps {
	settings: Settings;
}

export function DrawingGenerator( { settings }: DrawingGeneratorProps ) {
	const [ year, setYear ] = useState< number >( settings.current_year );
	const [ draft, setDraft ] = useState< Drawing | null >( null );
	const [ loading, setLoading ] = useState< boolean >( false );
	const [ error, setError ] = useState< string | null >( null );
	const [ success, setSuccess ] = useState< string | null >( null );

	const { generate, finalize } = useDrawings();

	const handleGenerate = async () => {
		setLoading( true );
		setError( null );
		setSuccess( null );

		try {
			const result = await generate( year );
			setDraft( result );
		} catch ( err ) {
			setError(
				err instanceof Error ? err.message : 'Failed to generate drawing'
			);
		} finally {
			setLoading( false );
		}
	};

	const handleRedraw = async () => {
		await handleGenerate();
	};

	const handleFinalize = async () => {
		if ( ! draft ) {
			return;
		}

		setLoading( true );
		setError( null );

		try {
			await finalize( year, draft.pairings );
			setSuccess( `Drawing for ${ year } has been saved!` );
			setDraft( null );
		} catch ( err ) {
			setError(
				err instanceof Error ? err.message : 'Failed to save drawing'
			);
		} finally {
			setLoading( false );
		}
	};

	return (
		<div className="gift-draw-generator">
			<h2>Generate New Drawing</h2>

			<div className="gift-draw-form-group">
				<label htmlFor="year">Year:</label>
				<input
					type="number"
					id="year"
					value={ year }
					onChange={ ( e ) => setYear( parseInt( e.target.value, 10 ) ) }
					min={ 2000 }
					max={ 2100 }
					disabled={ loading }
				/>
			</div>

			<div className="gift-draw-info">
				<p>
					<strong>Settings:</strong> Looking back{ ' ' }
					{ settings.years_lookback } years. Minimum age:{ ' ' }
					{ settings.minimum_age }.
				</p>
			</div>

			{ error && <div className="gift-draw-error">{ error }</div> }
			{ success && <div className="gift-draw-success">{ success }</div> }

			<div className="gift-draw-actions">
				{ ! draft && (
					<button
						onClick={ handleGenerate }
						disabled={ loading }
						className="gift-draw-btn gift-draw-btn-primary"
					>
						{ loading ? 'Generating...' : 'Generate Draft' }
					</button>
				) }
			</div>

			{ draft && (
				<div className="gift-draw-draft">
					<h3>Draft Pairings for { year }</h3>
					<p className="gift-draw-draft-notice">
						This is a draft. Review and finalize, or redraw to get
						different pairings.
					</p>

					<PairingsTable pairings={ draft.pairings } />

					<div className="gift-draw-actions">
						<button
							onClick={ handleRedraw }
							disabled={ loading }
							className="gift-draw-btn gift-draw-btn-secondary"
						>
							{ loading ? 'Generating...' : 'Redraw' }
						</button>
						<button
							onClick={ handleFinalize }
							disabled={ loading }
							className="gift-draw-btn gift-draw-btn-primary"
						>
							{ loading ? 'Saving...' : 'Finalize & Save' }
						</button>
					</div>
				</div>
			) }
		</div>
	);
}

interface PairingsTableProps {
	pairings: Pairing[];
}

function PairingsTable( { pairings }: PairingsTableProps ) {
	return (
		<table className="gift-draw-table">
			<thead>
				<tr>
					<th>Giver</th>
					<th>→</th>
					<th>Receiver</th>
				</tr>
			</thead>
			<tbody>
				{ pairings.map( ( pairing, index ) => (
					<tr key={ index }>
						<td>{ pairing.giver.name }</td>
						<td className="gift-draw-arrow">→</td>
						<td>{ pairing.receiver.name }</td>
					</tr>
				) ) }
			</tbody>
		</table>
	);
}
