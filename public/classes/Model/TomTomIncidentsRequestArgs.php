<?php


namespace Palasthotel\WordPress\TrafficIncidents\Model;


/**
 * @property BoundingBox boundingBox
 * @property int $trafficModelId
 * @property string language
 * @property string fields
 */
class TomTomIncidentsRequestArgs {

	private function __construct( BoundingBox $boundingBox ) {
		$this->boundingBox    = $boundingBox;
		$this->trafficModelId = - 1;
		$this->language       = "de-DE";
		$this->fields = "{incidents{type,properties{id,iconCategory,magnitudeOfDelay,events{description,code},startTime,endTime,from,to,length,delay,roadNumbers}}}";
	}

	public static function build( BoundingBox $bounding_box ): TomTomIncidentsRequestArgs {
		return new static( $bounding_box );
	}

	public function trafficModelId( int $modelId ): self {
		$this->trafficModelId = $modelId;

		return $this;
	}

	public function language( string $value ): self {
		$this->language = $value;

		return $this;
	}

	public function fields( string $fields ): self {
		$this->fields = $fields;

		return $this;
	}


}