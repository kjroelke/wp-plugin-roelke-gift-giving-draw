<?php
/**
 * Pairing Engine - Core Business Logic
 *
 * @package KJRoelke
 * @subpackage GiftGivingDraw
 */

namespace KJRoelke\GiftGivingDraw\Domain;

/**
 * Engine for generating valid gift-giving pairings
 *
 * Applies constraints:
 * - Same-household members cannot be paired
 * - Only adults are included
 * - No repeat pairings within the last X years
 */
class Pairing_Engine {
	/**
	 * Maximum number of retry attempts for generating pairings
	 */
	private const MAX_RETRIES = 100;

	/**
	 * Minimum number of participants required to generate pairings
	 */
	private const MIN_PARTICIPANTS = 2;

	/**
	 * Number of years to look back for repeat prevention
	 *
	 * @var int
	 */
	private int $years_lookback;

	/**
	 * Minimum age to be considered adult
	 *
	 * @var int
	 */
	private int $minimum_age;

	/**
	 * Constructor
	 *
	 * @param int $years_lookback Number of years to look back for repeat prevention. Default 3.
	 * @param int $minimum_age    Minimum age to be considered adult. Default 18.
	 */
	public function __construct( int $years_lookback = 3, int $minimum_age = 18 ) {
		$this->years_lookback = $years_lookback;
		$this->minimum_age    = $minimum_age;
	}

	/**
	 * Generate pairings for the given year
	 *
	 * @param Participant[] $participants     Array of all participants.
	 * @param array         $past_pairings    Array of past pairings indexed by giver_id => [receiver_ids].
	 * @param int           $year             The year for the drawing.
	 * @return Pairing[]|null Array of pairings or null if generation failed.
	 */
	public function generate( array $participants, array $past_pairings, int $year ): ?array {
		// Filter to only adults.
		$eligible = array_filter(
			$participants,
			fn( Participant $p ) => $p->is_adult( $this->minimum_age )
		);

		if ( count( $eligible ) < self::MIN_PARTICIPANTS ) {
			return null;
		}

		// Re-index array.
		$eligible = array_values( $eligible );

		for ( $attempt = 0; $attempt < self::MAX_RETRIES; $attempt++ ) {
			$result = $this->try_generate( $eligible, $past_pairings, $year );
			if ( null !== $result ) {
				return $result;
			}
		}

		return null;
	}

	/**
	 * Attempt to generate a valid set of pairings using shuffle-and-match
	 *
	 * @param Participant[] $participants  Array of eligible participants.
	 * @param array         $past_pairings Past pairings indexed by giver_id => [receiver_ids].
	 * @param int           $year          The year for the drawing.
	 * @return Pairing[]|null
	 */
	private function try_generate( array $participants, array $past_pairings, int $year ): ?array {
		$givers    = $participants;
		$receivers = $participants;

		// Shuffle both arrays.
		shuffle( $givers );
		shuffle( $receivers );

		$pairings          = array();
		$used_receiver_ids = array();

		foreach ( $givers as $giver ) {
			$matched = false;

			foreach ( $receivers as $receiver ) {
				// Skip if already used as receiver.
				if ( in_array( $receiver->get_id(), $used_receiver_ids, true ) ) {
					continue;
				}

				// Validate the pairing.
				if ( $this->is_valid_pairing( $giver, $receiver, $past_pairings ) ) {
					$pairings[]          = new Pairing( $giver, $receiver, $year );
					$used_receiver_ids[] = $receiver->get_id();
					$matched             = true;
					break;
				}
			}

			if ( ! $matched ) {
				// Could not find valid receiver for this giver, retry.
				return null;
			}
		}

		return $pairings;
	}

	/**
	 * Check if a pairing is valid according to constraints
	 *
	 * @param Participant $giver         The giver.
	 * @param Participant $receiver      The receiver.
	 * @param array       $past_pairings Past pairings indexed by giver_id => [receiver_ids].
	 * @return bool
	 */
	private function is_valid_pairing( Participant $giver, Participant $receiver, array $past_pairings ): bool {
		// Cannot give to self.
		if ( $giver->get_id() === $receiver->get_id() ) {
			return false;
		}

		// Cannot give to same household.
		if ( $giver->get_household_id() === $receiver->get_household_id() ) {
			return false;
		}

		// Cannot give to someone they gave to in the last X years.
		$giver_id = $giver->get_id();
		if ( isset( $past_pairings[ $giver_id ] ) ) {
			if ( in_array( $receiver->get_id(), $past_pairings[ $giver_id ], true ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get the years lookback setting
	 *
	 * @return int
	 */
	public function get_years_lookback(): int {
		return $this->years_lookback;
	}

	/**
	 * Get the minimum age setting
	 *
	 * @return int
	 */
	public function get_minimum_age(): int {
		return $this->minimum_age;
	}
}
