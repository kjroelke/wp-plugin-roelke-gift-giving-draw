<?php
/**
 * Database Schema Management
 *
 * @package KJRoelke
 * @subpackage GiftGivingDraw
 */

namespace KJRoelke\GiftGivingDraw\Persistence;

/**
 * Manages the database schema for the plugin
 */
class Database_Schema {
	/**
	 * Table names
	 */
	public const TABLE_HOUSEHOLDS   = 'gift_draw_households';
	public const TABLE_PARTICIPANTS = 'gift_draw_participants';
	public const TABLE_DRAWINGS     = 'gift_draw_drawings';

	/**
	 * Create all database tables
	 *
	 * @return void
	 */
	public static function create_tables(): void {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$households_table   = $wpdb->prefix . self::TABLE_HOUSEHOLDS;
		$participants_table = $wpdb->prefix . self::TABLE_PARTICIPANTS;
		$drawings_table     = $wpdb->prefix . self::TABLE_DRAWINGS;

		$sql = "CREATE TABLE {$households_table} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) {$charset_collate};

		CREATE TABLE {$participants_table} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			household_id bigint(20) UNSIGNED NOT NULL,
			name varchar(255) NOT NULL,
			birth_date date NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY household_id (household_id)
		) {$charset_collate};

		CREATE TABLE {$drawings_table} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			year YEAR NOT NULL,
			giver_id bigint(20) UNSIGNED NOT NULL,
			receiver_id bigint(20) UNSIGNED NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY year (year),
			KEY giver_id (giver_id),
			KEY receiver_id (receiver_id),
			UNIQUE KEY year_giver (year, giver_id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Drop all database tables
	 *
	 * @return void
	 */
	public static function drop_tables(): void {
		global $wpdb;

		$drawings_table     = $wpdb->prefix . self::TABLE_DRAWINGS;
		$participants_table = $wpdb->prefix . self::TABLE_PARTICIPANTS;
		$households_table   = $wpdb->prefix . self::TABLE_HOUSEHOLDS;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "DROP TABLE IF EXISTS {$drawings_table}" );
		$wpdb->query( "DROP TABLE IF EXISTS {$participants_table}" );
		$wpdb->query( "DROP TABLE IF EXISTS {$households_table}" );
		// phpcs:enable
	}

	/**
	 * Get the full table name with prefix
	 *
	 * @param string $table_name The base table name constant.
	 * @return string
	 */
	public static function get_table_name( string $table_name ): string {
		global $wpdb;
		return $wpdb->prefix . $table_name;
	}
}
