<?php
/**
 * Household Domain Model
 *
 * @package KJRoelke
 * @subpackage GiftGivingDraw
 */

namespace KJRoelke\GiftGivingDraw\Domain;

/**
 * Represents a household in the gift-giving draw
 */
class Household {
	/**
	 * Unique identifier
	 *
	 * @var int
	 */
	private int $id;

	/**
	 * Household name
	 *
	 * @var string
	 */
	private string $name;

	/**
	 * Constructor
	 *
	 * @param int    $id   Unique identifier.
	 * @param string $name Household name.
	 */
	public function __construct( int $id, string $name ) {
		$this->id   = $id;
		$this->name = $name;
	}

	/**
	 * Get the household ID
	 *
	 * @return int
	 */
	public function get_id(): int {
		return $this->id;
	}

	/**
	 * Get the household name
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Convert household to array for JSON serialization
	 *
	 * @return array{id: int, name: string}
	 */
	public function to_array(): array {
		return array(
			'id'   => $this->id,
			'name' => $this->name,
		);
	}
}
