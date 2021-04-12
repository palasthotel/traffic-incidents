<?php


namespace Palasthotel\WordPress\TrafficIncidents\Model;


/**
 * @property int zoom
 * @property BoundingBox boundingBox
 * @property int modelId
 * @property string format
 * @property string style
 * @property int version
 * @property string projection
 * @property string language
 */
class TomTomIncidentRequestArgs {

	private function __construct( BoundingBox $boundingBox ) {
		$this->version     = 4;
		$this->boundingBox = $boundingBox;
		$this->zoom        = 10;
		$this->modelId     = - 1;
		$this->format      = "json";
		$this->style       = "night";
		$this->projection  = "EPSG4326";
		$this->language    = substr(get_locale(), 0, 2);
	}

	public static function build( BoundingBox $bounding_box ): TomTomIncidentRequestArgs {
		return new static( $bounding_box );
	}

	public function zoom( int $zoom ): self {
		$this->zoom = $zoom;

		return $this;
	}

	public function modelId( int $modelId ): self {
		$this->modelId = $modelId;

		return $this;
	}

	public function format( string $format ): self {
		$this->format = $format;

		return $this;
	}

	public function style( string $style ): self {
		$this->style = $style;

		return $this;
	}

	public function version( int $value ): self {
		$this->version = $value;

		return $this;
	}

	public function projection( string $value ): self {
		$this->projection = $value;

		return $this;
	}

	public function language( string $value ): self {
		$this->language = $value;

		return $this;
	}


}