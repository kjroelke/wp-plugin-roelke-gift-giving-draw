import { useCallback } from 'react';
import type { Participant, Pairing } from '../types';

export interface PairingEngineOptions {
	yearsLookback: number;
	minimumAge: number;
	currentYear: number;
	pastPairings: Pairing[];
}

function isEligible(
	participant: Participant,
	minimumAge: number,
	currentYear: number
): boolean {
	if ( ! participant.birth_date ) {
		return false;
	}
	const birthYear = parseInt( participant.birth_date.split( '-' )[ 0 ], 10 );
	return currentYear - birthYear >= minimumAge;
}

function hasRecentPairing(
	giverId: number,
	receiverId: number,
	pastPairings: Pairing[],
	yearsLookback: number,
	currentYear: number
): boolean {
	return pastPairings.some(
		( pairing ) =>
			pairing.giver.id === giverId &&
			pairing.receiver.id === receiverId &&
			pairing.year >= currentYear - yearsLookback
	);
}

export function usePairingEngine( options: PairingEngineOptions ) {
	const generatePairings = useCallback(
		( participants: Participant[] ): Pairing[] | null => {
			const eligible = participants.filter( ( p ) =>
				isEligible( p, options.minimumAge, options.currentYear )
			);
			if ( eligible.length < 2 ) {
				return null;
			}

			// Simple random pairing avoiding recent pairings
			const receivers = [ ...eligible ];
			const pairings: Pairing[] = [];
			let attempts = 0;
			const maxAttempts = 1000;

			while ( attempts < maxAttempts ) {
				attempts++;
				// Shuffle receivers
				for ( let i = receivers.length - 1; i > 0; i-- ) {
					const j = Math.floor( Math.random() * ( i + 1 ) );
					[ receivers[ i ], receivers[ j ] ] = [
						receivers[ j ],
						receivers[ i ],
					];
				}
				let valid = true;
				pairings.length = 0;
				for ( let i = 0; i < eligible.length; i++ ) {
					const giver = eligible[ i ];
					const receiver = receivers[ i ];
					if (
						giver.id === receiver.id ||
						giver.household_id === receiver.household_id ||
						hasRecentPairing(
							giver.id,
							receiver.id,
							options.pastPairings,
							options.yearsLookback,
							options.currentYear
						)
					) {
						valid = false;
						break;
					}
					pairings.push( {
						giver,
						receiver,
						year: options.currentYear,
					} );
				}
				if ( valid ) {
					return pairings;
				}
			}
			return null;
		},
		[ options ]
	);

	return { generatePairings };
}
