export interface Household {
	id: number;
	name: string;
}

export interface Participant {
	id: number;
	household_id: number;
	name: string;
	birth_date: string;
}

export interface Pairing {
	giver: Participant;
	receiver: Participant;
	year: number;
}

export interface Drawing {
	year: number;
	pairings: Pairing[];
	is_draft?: boolean;
}

export interface Settings {
	years_lookback: number;
	minimum_age: number;
	current_year: number;
}

export interface ApiConfig {
	restUrl: string;
	nonce: string;
	pluginUrl: string;
}

declare global {
	interface Window {
		giftGivingDrawConfig: ApiConfig;
	}
}
