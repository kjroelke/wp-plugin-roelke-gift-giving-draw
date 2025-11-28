import React, { useState, useEffect, useCallback } from 'react';
import { DrawingSettingsPanel } from './DrawingSettingsPanel';
import type { Household, Participant } from '../types';
import { useHouseholds, useParticipants } from '../hooks/useApi';

export function HouseholdManager() {
	const [ households, setHouseholds ] = useState< Household[] >( [] );
	const [ participants, setParticipants ] = useState< Participant[] >( [] );
	const [ loading, setLoading ] = useState< boolean >( true );
	const [ error, setError ] = useState< string | null >( null );
	const [ editingHousehold, setEditingHousehold ] =
		useState< Household | null >( null );
	const [ editingParticipant, setEditingParticipant ] =
		useState< Participant | null >( null );
	const [ showHouseholdForm, setShowHouseholdForm ] =
		useState< boolean >( false );
	const [ showParticipantForm, setShowParticipantForm ] =
		useState< boolean >( false );

	const householdApi = useHouseholds();
	const participantApi = useParticipants();

	// Track if this is the initial load
	const [ initialLoad, setInitialLoad ] = useState( true );
	const loadData = useCallback( async () => {
		if ( initialLoad ) {
			setLoading( true );
		}
		try {
			const [ householdsData, participantsData ] = await Promise.all( [
				householdApi.getAll(),
				participantApi.getAll(),
			] );
			setHouseholds( householdsData );
			setParticipants( participantsData );
		} catch ( err ) {
			setError(
				err instanceof Error ? err.message : 'Failed to load data'
			);
		} finally {
			setLoading( false );
			setInitialLoad( false );
		}
	}, [ householdApi, participantApi, initialLoad ] );

	useEffect( () => {
		loadData();
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ initialLoad ] );

	if ( loading ) {
		return (
			<div className="gift-draw-manager">
				<h2>Manage Households & Participants</h2>
				<div className="gift-draw-loading">Loading...</div>
			</div>
		);
	}

	return (
		<div className="gift-draw-manager">
			<h2>Manage Households & Participants</h2>

			{ error && <div className="gift-draw-error">{ error }</div> }

			<section className="gift-draw-section">
				<div className="gift-draw-section-header">
					<h3>Households</h3>
					<button
						onClick={ () => {
							setEditingHousehold( null );
							setShowHouseholdForm( true );
						} }
						className="gift-draw-btn gift-draw-btn-primary gift-draw-btn-small"
					>
						Add Household
					</button>
				</div>

				{ showHouseholdForm && (
					<HouseholdForm
						household={ editingHousehold }
						onSave={ async ( name ) => {
							try {
								if ( editingHousehold ) {
									await householdApi.update(
										editingHousehold.id,
										name
									);
								} else {
									await householdApi.create( name );
								}
								await loadData();
								setShowHouseholdForm( false );
								setEditingHousehold( null );
							} catch ( err ) {
								setError(
									err instanceof Error
										? err.message
										: 'Failed to save household'
								);
							}
						} }
						onCancel={ () => {
							setShowHouseholdForm( false );
							setEditingHousehold( null );
						} }
					/>
				) }

				{ households.length === 0 ? (
					<p className="gift-draw-empty">
						No households yet. Add one to get started.
					</p>
				) : (
					<ul className="gift-draw-list">
						{ households.map( ( household ) => (
							<li
								key={ household.id }
								className="gift-draw-list-item"
							>
								<span className="gift-draw-list-item-name">
									{ household.name }
								</span>
								<div className="gift-draw-list-item-actions">
									<button
										onClick={ () => {
											setEditingHousehold( household );
											setShowHouseholdForm( true );
										} }
										className="gift-draw-btn gift-draw-btn-secondary gift-draw-btn-small"
									>
										Edit
									</button>
									<button
										onClick={ async () => {
											if (
												window.confirm(
													`Delete household "${ household.name }"?`
												)
											) {
												try {
													await householdApi.remove(
														household.id
													);
													await loadData();
												} catch ( err ) {
													setError(
														err instanceof Error
															? err.message
															: 'Failed to delete'
													);
												}
											}
										} }
										className="gift-draw-btn gift-draw-btn-danger gift-draw-btn-small"
									>
										Delete
									</button>
								</div>
							</li>
						) ) }
					</ul>
				) }
			</section>

			<section className="gift-draw-section">
				<div className="gift-draw-section-header">
					<h3>Participants</h3>
					<button
						onClick={ () => {
							setEditingParticipant( null );
							setShowParticipantForm( true );
						} }
						className="gift-draw-btn gift-draw-btn-primary gift-draw-btn-small"
						disabled={ households.length === 0 }
					>
						Add Participant
					</button>
				</div>

				{ showParticipantForm && (
					<ParticipantForm
						participant={ editingParticipant }
						households={ households }
						onSave={ async ( householdId, name, birthDate ) => {
							try {
								if ( editingParticipant ) {
									await participantApi.update(
										editingParticipant.id,
										householdId,
										name,
										birthDate
									);
								} else {
									await participantApi.create(
										householdId,
										name,
										birthDate
									);
								}
								await loadData();
								setShowParticipantForm( false );
								setEditingParticipant( null );
							} catch ( err ) {
								setError(
									err instanceof Error
										? err.message
										: 'Failed to save participant'
								);
							}
						} }
						onCancel={ () => {
							setShowParticipantForm( false );
							setEditingParticipant( null );
						} }
					/>
				) }

				{ participants.length === 0 ? (
					<p className="gift-draw-empty">
						No participants yet. Add one to get started.
					</p>
				) : (
					<table className="gift-draw-table">
						<thead>
							<tr>
								<th>Name</th>
								<th>Household</th>
								<th>Birth Date</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							{ participants.map( ( participant ) => (
								<tr key={ participant.id }>
									<td>{ participant.name }</td>
									<td>
										{ households.find(
											( h ) =>
												h.id ===
												participant.household_id
										)?.name || 'Unknown' }
									</td>
									<td>{ participant.birth_date }</td>
									<td>
										<button
											onClick={ () => {
												setEditingParticipant(
													participant
												);
												setShowParticipantForm( true );
											} }
											className="gift-draw-btn gift-draw-btn-secondary gift-draw-btn-small"
										>
											Edit
										</button>
										<button
											onClick={ async () => {
												if (
													window.confirm(
														`Delete participant "${ participant.name }"?`
													)
												) {
													try {
														await participantApi.remove(
															participant.id
														);
														await loadData();
													} catch ( err ) {
														setError(
															err instanceof Error
																? err.message
																: 'Failed to delete'
														);
													}
												}
											} }
											className="gift-draw-btn gift-draw-btn-danger gift-draw-btn-small"
										>
											Delete
										</button>
									</td>
								</tr>
							) ) }
						</tbody>
					</table>
				) }
			</section>
		</div>
	);
}

interface HouseholdFormProps {
	household: Household | null;
	onSave: ( name: string ) => void;
	onCancel: () => void;
}

function HouseholdForm( { household, onSave, onCancel }: HouseholdFormProps ) {
	const [ name, setName ] = useState( household?.name || '' );

	const handleSubmit = ( e: React.FormEvent ) => {
		e.preventDefault();
		if ( name.trim() ) {
			onSave( name.trim() );
		}
	};

	return (
		<form className="gift-draw-form" onSubmit={ handleSubmit }>
			<div className="gift-draw-form-group">
				<label htmlFor="household-name">Household Name:</label>
				<input
					type="text"
					id="household-name"
					value={ name }
					onChange={ ( e ) => setName( e.target.value ) }
					required
				/>
			</div>
			<div className="gift-draw-form-actions">
				<button
					type="button"
					onClick={ onCancel }
					className="gift-draw-btn gift-draw-btn-secondary"
				>
					Cancel
				</button>
				<button
					type="submit"
					className="gift-draw-btn gift-draw-btn-primary"
				>
					{ household ? 'Update' : 'Add' }
				</button>
			</div>
		</form>
	);
}

interface ParticipantFormProps {
	participant: Participant | null;
	households: Household[];
	onSave: ( householdId: number, name: string, birthDate: string ) => void;
	onCancel: () => void;
}

function ParticipantForm( {
	participant,
	households,
	onSave,
	onCancel,
}: ParticipantFormProps ) {
	const [ householdId, setHouseholdId ] = useState(
		participant?.household_id || households[ 0 ]?.id || 0
	);
	const [ name, setName ] = useState( participant?.name || '' );
	const [ birthDate, setBirthDate ] = useState(
		participant?.birth_date || ''
	);

	const handleSubmit = ( e: React.FormEvent ) => {
		e.preventDefault();
		if ( name.trim() && birthDate && householdId ) {
			onSave( householdId, name.trim(), birthDate );
		}
	};

	return (
		<form className="gift-draw-form" onSubmit={ handleSubmit }>
			<div className="gift-draw-form-group">
				<label htmlFor="participant-household">Household:</label>
				<select
					id="participant-household"
					value={ householdId }
					onChange={ ( e ) =>
						setHouseholdId( parseInt( e.target.value, 10 ) )
					}
					required
				>
					{ households.map( ( h ) => (
						<option key={ h.id } value={ h.id }>
							{ h.name }
						</option>
					) ) }
				</select>
			</div>
			<div className="gift-draw-form-group">
				<label htmlFor="participant-name">Name:</label>
				<input
					type="text"
					id="participant-name"
					value={ name }
					onChange={ ( e ) => setName( e.target.value ) }
					required
				/>
			</div>
			<div className="gift-draw-form-group">
				<label htmlFor="participant-birthdate">Birth Date:</label>
				<input
					type="date"
					id="participant-birthdate"
					value={ birthDate }
					onChange={ ( e ) => setBirthDate( e.target.value ) }
					required
				/>
			</div>
			<div className="gift-draw-form-actions">
				<button
					type="button"
					onClick={ onCancel }
					className="gift-draw-btn gift-draw-btn-secondary"
				>
					Cancel
				</button>
				<button
					type="submit"
					className="gift-draw-btn gift-draw-btn-primary"
				>
					{ participant ? 'Update' : 'Add' }
				</button>
			</div>
		</form>
	);
}
