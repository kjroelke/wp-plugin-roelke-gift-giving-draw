import { useState, useEffect, useCallback } from 'react';
import type { Drawing, Pairing } from '../types';
import { useDrawings } from '../hooks/useApi';

export function HistoricalDrawings() {
	const [ years, setYears ] = useState< number[] >( [] );
	const [ selectedYear, setSelectedYear ] = useState< number | null >( null );
	const [ drawing, setDrawing ] = useState< Drawing | null >( null );
	const [ loading, setLoading ] = useState< boolean >( true );
	const [ error, setError ] = useState< string | null >( null );

	const { getYears, getByYear, remove } = useDrawings();

	const loadYears = useCallback( async () => {
		try {
			const data = await getYears();
			setYears( data );
			if ( data.length > 0 && ! selectedYear ) {
				setSelectedYear( data[ 0 ] );
			}
		} catch ( err ) {
			setError(
				err instanceof Error ? err.message : 'Failed to load years'
			);
		} finally {
			setLoading( false );
		}
	}, [ getYears, selectedYear ] );

	const loadDrawing = useCallback( async () => {
		if ( ! selectedYear ) {
			return;
		}

		setLoading( true );
		try {
			const data = await getByYear( selectedYear );
			setDrawing( data );
		} catch ( err ) {
			setDrawing( null );
			setError(
				err instanceof Error ? err.message : 'Failed to load drawing'
			);
		} finally {
			setLoading( false );
		}
	}, [ selectedYear, getByYear ] );

	useEffect( () => {
		loadYears();
	}, [ loadYears ] );

	useEffect( () => {
		if ( selectedYear ) {
			loadDrawing();
		}
	}, [ selectedYear, loadDrawing ] );

	const handleDelete = async () => {
		if (
			! selectedYear ||
			! window.confirm(
				`Are you sure you want to delete the drawing for ${ selectedYear }?`
			)
		) {
			return;
		}

		setLoading( true );
		try {
			await remove( selectedYear );
			setDrawing( null );
			const remainingYears = years.filter( ( y ) => y !== selectedYear );
			setYears( remainingYears );
			setSelectedYear( remainingYears.length > 0 ? remainingYears[ 0 ] : null );
		} catch ( err ) {
			setError(
				err instanceof Error ? err.message : 'Failed to delete drawing'
			);
		} finally {
			setLoading( false );
		}
	};

	if ( loading && years.length === 0 ) {
		return (
			<div className="gift-draw-history">
				<h2>Historical Drawings</h2>
				<div className="gift-draw-loading">Loading...</div>
			</div>
		);
	}

	return (
		<div className="gift-draw-history">
			<h2>Historical Drawings</h2>

			{ error && <div className="gift-draw-error">{ error }</div> }

			{ years.length === 0 ? (
				<p className="gift-draw-empty">
					No historical drawings found. Generate and finalize a drawing
					to see it here.
				</p>
			) : (
				<>
					<div className="gift-draw-form-group">
						<label htmlFor="year-select">Select Year:</label>
						<select
							id="year-select"
							value={ selectedYear || '' }
							onChange={ ( e ) =>
								setSelectedYear(
									parseInt( e.target.value, 10 )
								)
							}
							disabled={ loading }
						>
							{ years.map( ( y ) => (
								<option key={ y } value={ y }>
									{ y }
								</option>
							) ) }
						</select>
					</div>

					{ loading ? (
						<div className="gift-draw-loading">Loading drawing...</div>
					) : drawing ? (
						<div className="gift-draw-drawing">
							<h3>Pairings for { selectedYear }</h3>
							<PairingsTable pairings={ drawing.pairings } />
							<div className="gift-draw-actions">
								<button
									onClick={ handleDelete }
									className="gift-draw-btn gift-draw-btn-danger"
								>
									Delete Drawing
								</button>
							</div>
						</div>
					) : (
						<p>No drawing data available for this year.</p>
					) }
				</>
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
