<?php
/**
 * Household Repository
 *
 * @package KJRoelke
 * @subpackage GiftGivingDraw
 */

namespace KJRoelke\GiftGivingDraw\Persistence;

use KJRoelke\GiftGivingDraw\Domain\Household;

/**
 * Repository for managing households in the database
 */
class Household_Repository {
	/**
	 * Get all households
	 *
	 * @return Household[]
	 */
	public function get_all(): array {
		global $wpdb;
		$table = Database_Schema::get_table_name( Database_Schema::TABLE_HOUSEHOLDS );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT id, name FROM {$table} ORDER BY name ASC",
			ARRAY_A
		);

		if ( ! $results ) {
			return array();
		}

		return array_map(
			fn( $row ) => new Household( (int) $row['id'], $row['name'] ),
			$results
		);
	}

	/**
	 * Get a household by ID
	 *
	 * @param int $id Household ID.
	 * @return Household|null
	 */
	public function get_by_id( int $id ): ?Household {
		global $wpdb;
		$table = Database_Schema::get_table_name( Database_Schema::TABLE_HOUSEHOLDS );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$row = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT id, name FROM {$table} WHERE id = %d",
				$id
			),
			ARRAY_A
		);

		if ( ! $row ) {
			return null;
		}

		return new Household( (int) $row['id'], $row['name'] );
	}

	/**
	 * Create a new household
	 *
	 * @param string $name Household name.
	 * @return Household|null
	 */
	public function create( string $name ): ?Household {
		global $wpdb;
		$table = Database_Schema::get_table_name( Database_Schema::TABLE_HOUSEHOLDS );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert(
			$table,
			array( 'name' => $name ),
			array( '%s' )
		);

		if ( false === $result ) {
			return null;
		}

		return new Household( (int) $wpdb->insert_id, $name );
	}

	/**
	 * Update a household
	 *
	 * @param int    $id   Household ID.
	 * @param string $name New name.
	 * @return bool
	 */
	public function update( int $id, string $name ): bool {
		global $wpdb;
		$table = Database_Schema::get_table_name( Database_Schema::TABLE_HOUSEHOLDS );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->update(
			$table,
			array( 'name' => $name ),
			array( 'id' => $id ),
			array( '%s' ),
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Delete a household
	 *
	 * @param int $id Household ID.
	 * @return bool
	 */
	public function delete( int $id ): bool {
		global $wpdb;
		$table = Database_Schema::get_table_name( Database_Schema::TABLE_HOUSEHOLDS );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->delete(
			$table,
			array( 'id' => $id ),
			array( '%d' )
		);

		return false !== $result;
	}
}
