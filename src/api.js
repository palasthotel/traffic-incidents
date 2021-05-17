import apiFetch from '@wordpress/api-fetch';

TrafficIncidents.api = {
    fetchAreas(){
        return apiFetch({path: `${TrafficIncidents.rest_namespace}/areas`})
    },
    fetchIncidents(post_id) {
        return apiFetch({path: `${TrafficIncidents.rest_namespace}/incidents/${post_id}`})
    },
    fetchIncidentsCount(post_id) {
        return apiFetch({path: `${TrafficIncidents.rest_namespace}/incidents/${post_id}/count`})
    }
}