<?php
/**
 * Participant Repository
 *
 * @package KJRoelke
 * @subpackage GiftGivingDraw
 */

namespace KJRoelke\GiftGivingDraw\Persistence;

use KJRoelke\GiftGivingDraw\Domain\Participant;

/**
 * Repository for managing participants in the database
 */
class Participant_Repository {
	/**
	 * Get all participants
	 *
	 * @return Participant[]
	 */
	public function get_all(): array {
		global $wpdb;
		$table = Database_Schema::get_table_name( Database_Schema::TABLE_PARTICIPANTS );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT id, household_id, name, birth_date FROM {$table} ORDER BY name ASC",
			ARRAY_A
		);

		if ( ! $results ) {
			return array();
		}

		return array_map(
			fn( $row ) => new Participant(
				(int) $row['id'],
				(int) $row['household_id'],
				$row['name'],
				$row['birth_date']
			),
			$results
		);
	}

	/**
	 * Get participants by household ID
	 *
	 * @param int $household_id Household ID.
	 * @return Participant[]
	 */
	public function get_by_household( int $household_id ): array {
		global $wpdb;
		$table = Database_Schema::get_table_name( Database_Schema::TABLE_PARTICIPANTS );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT id, household_id, name, birth_date FROM {$table} WHERE household_id = %d ORDER BY name ASC",
				$household_id
			),
			ARRAY_A
		);

		if ( ! $results ) {
			return array();
		}

		return array_map(
			fn( $row ) => new Participant(
				(int) $row['id'],
				(int) $row['household_id'],
				$row['name'],
				$row['birth_date']
			),
			$results
		);
	}

	/**
	 * Get a participant by ID
	 *
	 * @param int $id Participant ID.
	 * @return Participant|null
	 */
	public function get_by_id( int $id ): ?Participant {
		global $wpdb;
		$table = Database_Schema::get_table_name( Database_Schema::TABLE_PARTICIPANTS );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$row = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT id, household_id, name, birth_date FROM {$table} WHERE id = %d",
				$id
			),
			ARRAY_A
		);

		if ( ! $row ) {
			return null;
		}

		return new Participant(
			(int) $row['id'],
			(int) $row['household_id'],
			$row['name'],
			$row['birth_date']
		);
	}

	/**
	 * Create a new participant
	 *
	 * @param int    $household_id Household ID.
	 * @param string $name         Display name.
	 * @param string $birth_date   Birth date in Y-m-d format.
	 * @return Participant|null
	 */
	public function create( int $household_id, string $name, string $birth_date ): ?Participant {
		global $wpdb;
		$table = Database_Schema::get_table_name( Database_Schema::TABLE_PARTICIPANTS );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert(
			$table,
			array(
				'household_id' => $household_id,
				'name'         => $name,
				'birth_date'   => $birth_date,
			),
			array( '%d', '%s', '%s' )
		);

		if ( false === $result ) {
			return null;
		}

		return new Participant( (int) $wpdb->insert_id, $household_id, $name, $birth_date );
	}

	/**
	 * Update a participant
	 *
	 * @param int    $id           Participant ID.
	 * @param int    $household_id Household ID.
	 * @param string $name         Display name.
	 * @param string $birth_date   Birth date in Y-m-d format.
	 * @return bool
	 */
	public function update( int $id, int $household_id, string $name, string $birth_date ): bool {
		global $wpdb;
		$table = Database_Schema::get_table_name( Database_Schema::TABLE_PARTICIPANTS );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->update(
			$table,
			array(
				'household_id' => $household_id,
				'name'         => $name,
				'birth_date'   => $birth_date,
			),
			array( 'id' => $id ),
			array( '%d', '%s', '%s' ),
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Delete a participant
	 *
	 * @param int $id Participant ID.
	 * @return bool
	 */
	public function delete( int $id ): bool {
		global $wpdb;
		$table = Database_Schema::get_table_name( Database_Schema::TABLE_PARTICIPANTS );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->delete(
			$table,
			array( 'id' => $id ),
			array( '%d' )
		);

		return false !== $result;
	}
}
