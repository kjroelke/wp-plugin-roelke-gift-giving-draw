<?php
/**
 * Participant Domain Model
 *
 * @package KJRoelke
 * @subpackage GiftGivingDraw
 */

namespace KJRoelke\GiftGivingDraw\Domain;

/**
 * Represents a participant in the gift-giving draw
 */
class Participant {
	/**
	 * Unique identifier
	 *
	 * @var int
	 */
	private int $id;

	/**
	 * Household identifier
	 *
	 * @var int
	 */
	private int $household_id;

	/**
	 * Display name
	 *
	 * @var string
	 */
	private string $name;

	/**
	 * Birth date (Y-m-d format)
	 *
	 * @var string
	 */
	private string $birth_date;

	/**
	 * Constructor
	 *
	 * @param int    $id           Unique identifier.
	 * @param int    $household_id Household identifier.
	 * @param string $name         Display name.
	 * @param string $birth_date   Birth date in Y-m-d format.
	 */
	public function __construct( int $id, int $household_id, string $name, string $birth_date ) {
		$this->id           = $id;
		$this->household_id = $household_id;
		$this->name         = $name;
		$this->birth_date   = $birth_date;
	}

	/**
	 * Get the participant ID
	 *
	 * @return int
	 */
	public function get_id(): int {
		return $this->id;
	}

	/**
	 * Get the household ID
	 *
	 * @return int
	 */
	public function get_household_id(): int {
		return $this->household_id;
	}

	/**
	 * Get the display name
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Get the birth date
	 *
	 * @return string
	 */
	public function get_birth_date(): string {
		return $this->birth_date;
	}

	/**
	 * Check if participant is an adult (18+)
	 *
	 * @param int $minimum_age Minimum age to be considered adult. Default 18.
	 * @return bool
	 */
	public function is_adult( int $minimum_age = 18 ): bool {
		$birth_date = new \DateTime( $this->birth_date );
		$today      = new \DateTime();
		$age        = $today->diff( $birth_date )->y;
		return $age >= $minimum_age;
	}

	/**
	 * Convert participant to array for JSON serialization
	 *
	 * @return array{id: int, household_id: int, name: string, birth_date: string}
	 */
	public function to_array(): array {
		return array(
			'id'           => $this->id,
			'household_id' => $this->household_id,
			'name'         => $this->name,
			'birth_date'   => $this->birth_date,
		);
	}
}
