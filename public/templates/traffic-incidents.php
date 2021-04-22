<?php

use Palasthotel\WordPress\TrafficIncidents\Data\IncidentQuery;
use Palasthotel\WordPress\TrafficIncidents\Model\IncidentQueryArgs;
use Palasthotel\WordPress\TrafficIncidents\Plugin;

/**
 * @var IncidentQueryArgs $args
 */

$query = new IncidentQuery( $args );
if ( $query->haveIncidents() ) {
	echo "<ul>";
	while ( $incident = $query->nextIncident() ) {
		?>

        <li>
            <div><?= implode(", ",$incident->events); ?></div>
            <div>Kategorie: <?= $incident->category; ?></div>
            <div>Schwere der Verzögerung: <?= $incident->magnitudeOfDelay; ?></div>
			<?php
			if ( $incident->start ) {
				echo "<div>";
				printf(
					__( "Started: %s", Plugin::DOMAIN ),
					$incident->start->format( "H:i:s" )
				);
				if ( $incident->end ) {
					printf(
						__( " -> will approximatly end: %s", Plugin::DOMAIN ),
						$incident->end->format( "H:i:s" )
					);
				}
				echo "</div>";
			}

			echo "<div>";
			printf(
				__( "From <b>%s</b> to <b>%s</b>", Plugin::DOMAIN ),
				$incident->intersectionFrom,
				$incident->intersectionTo
			);
			echo "</div>";

			printf(
				__( "<i>Last update: %s</i>", Plugin::DOMAIN ),
				$incident->modified->format( "Y-m-d H:i" )
			);

			?>
        </li>
		<?php
	}
	echo "</ul>";
}
