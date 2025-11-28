<?php
/**
 * Pairing Domain Model
 *
 * @package KJRoelke
 * @subpackage GiftGivingDraw
 */

namespace KJRoelke\GiftGivingDraw\Domain;

/**
 * Represents a gift-giving pairing between a giver and receiver
 */
class Pairing {
	/**
	 * Giver participant
	 *
	 * @var Participant
	 */
	private Participant $giver;

	/**
	 * Receiver participant
	 *
	 * @var Participant
	 */
	private Participant $receiver;

	/**
	 * Year of the pairing
	 *
	 * @var int
	 */
	private int $year;

	/**
	 * Constructor
	 *
	 * @param Participant $giver    The participant giving a gift.
	 * @param Participant $receiver The participant receiving a gift.
	 * @param int         $year     The year of the drawing.
	 */
	public function __construct( Participant $giver, Participant $receiver, int $year ) {
		$this->giver    = $giver;
		$this->receiver = $receiver;
		$this->year     = $year;
	}

	/**
	 * Get the giver
	 *
	 * @return Participant
	 */
	public function get_giver(): Participant {
		return $this->giver;
	}

	/**
	 * Get the receiver
	 *
	 * @return Participant
	 */
	public function get_receiver(): Participant {
		return $this->receiver;
	}

	/**
	 * Get the year
	 *
	 * @return int
	 */
	public function get_year(): int {
		return $this->year;
	}

	/**
	 * Convert pairing to array for JSON serialization
	 *
	 * @return array{giver: array, receiver: array, year: int}
	 */
	public function to_array(): array {
		return array(
			'giver'    => $this->giver->to_array(),
			'receiver' => $this->receiver->to_array(),
			'year'     => $this->year,
		);
	}
}
