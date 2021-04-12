<?php


namespace Palasthotel\WordPress\TrafficIncidents\Model;


/**
 * @property string boundingBoxString
 */
class BoundingBox {

	public function __construct(
		string $latTopLeft, string $lngTopLeft,
		string $latBottomRight, string $lngBottomRight
	) {
		$this->boundingBoxString = "$latTopLeft,$lngTopLeft,$latBottomRight,$lngBottomRight";
	}

	public function __toString() {
		return $this->boundingBoxString;
	}

	/**
	 * @param string $value
	 *
	 * @return static|null
	 */
	public static function parse($value){
		if(!is_string($value)) return null;
		$parts = explode(",", str_replace(" ", "", $value));
		if(count($parts) < 4) return null;
		return new static($parts[0],$parts[1],$parts[2],$parts[3]);
	}
}