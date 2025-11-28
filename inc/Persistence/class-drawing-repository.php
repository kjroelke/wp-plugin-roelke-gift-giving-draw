<?php
/**
 * Drawing Repository
 *
 * @package KJRoelke
 * @subpackage GiftGivingDraw
 */

namespace KJRoelke\GiftGivingDraw\Persistence;

use KJRoelke\GiftGivingDraw\Domain\Participant;
use KJRoelke\GiftGivingDraw\Domain\Pairing;

/**
 * Repository for managing drawings in the database
 */
class Drawing_Repository {
	/**
	 * Participant repository
	 *
	 * @var Participant_Repository
	 */
	private Participant_Repository $participant_repository;

	/**
	 * Constructor
	 *
	 * @param Participant_Repository $participant_repository Participant repository.
	 */
	public function __construct( Participant_Repository $participant_repository ) {
		$this->participant_repository = $participant_repository;
	}

	/**
	 * Get all drawings for a specific year
	 *
	 * @param int $year The year.
	 * @return Pairing[]
	 */
	public function get_by_year( int $year ): array {
		global $wpdb;
		$table = Database_Schema::get_table_name( Database_Schema::TABLE_DRAWINGS );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT giver_id, receiver_id, year FROM {$table} WHERE year = %d",
				$year
			),
			ARRAY_A
		);

		if ( ! $results ) {
			return array();
		}

		$pairings = array();
		foreach ( $results as $row ) {
			$giver    = $this->participant_repository->get_by_id( (int) $row['giver_id'] );
			$receiver = $this->participant_repository->get_by_id( (int) $row['receiver_id'] );

			if ( $giver && $receiver ) {
				$pairings[] = new Pairing( $giver, $receiver, (int) $row['year'] );
			}
		}

		return $pairings;
	}

	/**
	 * Get all years that have drawings
	 *
	 * @return int[]
	 */
	public function get_years(): array {
		global $wpdb;
		$table = Database_Schema::get_table_name( Database_Schema::TABLE_DRAWINGS );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_col(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT DISTINCT year FROM {$table} ORDER BY year DESC"
		);

		if ( ! $results ) {
			return array();
		}

		return array_map( 'intval', $results );
	}

	/**
	 * Get past pairings for a giver within the last X years
	 *
	 * Returns an array indexed by giver_id => [receiver_ids]
	 *
	 * @param int $years_lookback Number of years to look back.
	 * @param int $current_year   The current year.
	 * @return array<int, int[]>
	 */
	public function get_past_pairings( int $years_lookback, int $current_year ): array {
		global $wpdb;
		$table    = Database_Schema::get_table_name( Database_Schema::TABLE_DRAWINGS );
		$min_year = $current_year - $years_lookback;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT giver_id, receiver_id FROM {$table} WHERE year > %d AND year < %d",
				$min_year,
				$current_year
			),
			ARRAY_A
		);

		if ( ! $results ) {
			return array();
		}

		$past_pairings = array();
		foreach ( $results as $row ) {
			$giver_id    = (int) $row['giver_id'];
			$receiver_id = (int) $row['receiver_id'];

			if ( ! isset( $past_pairings[ $giver_id ] ) ) {
				$past_pairings[ $giver_id ] = array();
			}

			$past_pairings[ $giver_id ][] = $receiver_id;
		}

		return $past_pairings;
	}

	/**
	 * Check if a drawing exists for a specific year
	 *
	 * @param int $year The year.
	 * @return bool
	 */
	public function year_exists( int $year ): bool {
		global $wpdb;
		$table = Database_Schema::get_table_name( Database_Schema::TABLE_DRAWINGS );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$count = $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT COUNT(*) FROM {$table} WHERE year = %d",
				$year
			)
		);

		return (int) $count > 0;
	}

	/**
	 * Save a finalized drawing for the year
	 *
	 * @param Pairing[] $pairings Array of pairings.
	 * @param int       $year     The year.
	 * @return bool
	 */
	public function save( array $pairings, int $year ): bool {
		global $wpdb;
		$table = Database_Schema::get_table_name( Database_Schema::TABLE_DRAWINGS );

		// Delete existing drawings for this year first.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->delete(
			$table,
			array( 'year' => $year ),
			array( '%d' )
		);

		// Insert all pairings.
		foreach ( $pairings as $pairing ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$result = $wpdb->insert(
				$table,
				array(
					'year'        => $year,
					'giver_id'    => $pairing->get_giver()->get_id(),
					'receiver_id' => $pairing->get_receiver()->get_id(),
				),
				array( '%d', '%d', '%d' )
			);

			if ( false === $result ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Delete all drawings for a specific year
	 *
	 * @param int $year The year.
	 * @return bool
	 */
	public function delete_year( int $year ): bool {
		global $wpdb;
		$table = Database_Schema::get_table_name( Database_Schema::TABLE_DRAWINGS );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->delete(
			$table,
			array( 'year' => $year ),
			array( '%d' )
		);

		return false !== $result;
	}
}
