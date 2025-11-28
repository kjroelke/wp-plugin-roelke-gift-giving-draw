import { useCallback } from 'react';
import type {
	Household,
	Participant,
	Drawing,
	Settings,
	Pairing,
} from '../types';

const getConfig = () => window.giftGivingDrawConfig;

const fetchApi = async < T >(
	endpoint: string,
	options: RequestInit = {}
): Promise< T > => {
	const config = getConfig();
	const response = await fetch( `${ config.restUrl }${ endpoint }`, {
		...options,
		headers: {
			'Content-Type': 'application/json',
			'X-WP-Nonce': config.nonce,
			...options.headers,
		},
	} );

	if ( ! response.ok ) {
		const error = await response.json();
		throw new Error( error.message || 'API request failed' );
	}

	if ( response.status === 204 ) {
		return null as T;
	}

	return response.json();
};

export const useHouseholds = () => {
	const getAll = useCallback( async (): Promise< Household[] > => {
		return fetchApi< Household[] >( 'households' );
	}, [] );

	const getById = useCallback( async ( id: number ): Promise< Household > => {
		return fetchApi< Household >( `households/${ id }` );
	}, [] );

	const create = useCallback(
		async ( name: string ): Promise< Household > => {
			return fetchApi< Household >( 'households', {
				method: 'POST',
				body: JSON.stringify( { name } ),
			} );
		},
		[]
	);

	const update = useCallback(
		async ( id: number, name: string ): Promise< Household > => {
			return fetchApi< Household >( `households/${ id }`, {
				method: 'PUT',
				body: JSON.stringify( { name } ),
			} );
		},
		[]
	);

	const remove = useCallback( async ( id: number ): Promise< void > => {
		return fetchApi< void >( `households/${ id }`, {
			method: 'DELETE',
		} );
	}, [] );

	return { getAll, getById, create, update, remove };
};

export const useParticipants = () => {
	const getAll = useCallback(
		async ( householdId?: number ): Promise< Participant[] > => {
			const endpoint = householdId
				? `participants?household_id=${ householdId }`
				: 'participants';
			return fetchApi< Participant[] >( endpoint );
		},
		[]
	);

	const getById = useCallback(
		async ( id: number ): Promise< Participant > => {
			return fetchApi< Participant >( `participants/${ id }` );
		},
		[]
	);

	const create = useCallback(
		async (
			householdId: number,
			name: string,
			birthDate: string
		): Promise< Participant > => {
			return fetchApi< Participant >( 'participants', {
				method: 'POST',
				body: JSON.stringify( {
					household_id: householdId,
					name,
					birth_date: birthDate,
				} ),
			} );
		},
		[]
	);

	const update = useCallback(
		async (
			id: number,
			householdId: number,
			name: string,
			birthDate: string
		): Promise< Participant > => {
			return fetchApi< Participant >( `participants/${ id }`, {
				method: 'PUT',
				body: JSON.stringify( {
					household_id: householdId,
					name,
					birth_date: birthDate,
				} ),
			} );
		},
		[]
	);

	const remove = useCallback( async ( id: number ): Promise< void > => {
		return fetchApi< void >( `participants/${ id }`, {
			method: 'DELETE',
		} );
	}, [] );

	return { getAll, getById, create, update, remove };
};

export const useDrawings = () => {
	const getYears = useCallback( async (): Promise< number[] > => {
		return fetchApi< number[] >( 'drawings/years' );
	}, [] );

	const getByYear = useCallback( async ( year: number ): Promise< Drawing > => {
		return fetchApi< Drawing >( `drawings/${ year }` );
	}, [] );

	const generate = useCallback( async ( year: number ): Promise< Drawing > => {
		return fetchApi< Drawing >( 'drawings/generate', {
			method: 'POST',
			body: JSON.stringify( { year } ),
		} );
	}, [] );

	const finalize = useCallback(
		async ( year: number, pairings: Pairing[] ): Promise< Drawing > => {
			return fetchApi< Drawing >( 'drawings/finalize', {
				method: 'POST',
				body: JSON.stringify( { year, pairings } ),
			} );
		},
		[]
	);

	const remove = useCallback( async ( year: number ): Promise< void > => {
		return fetchApi< void >( `drawings/${ year }`, {
			method: 'DELETE',
		} );
	}, [] );

	const getSettings = useCallback( async (): Promise< Settings > => {
		return fetchApi< Settings >( 'drawings/settings' );
	}, [] );

	return { getYears, getByYear, generate, finalize, remove, getSettings };
};
