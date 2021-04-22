import apiFetch from '@wordpress/api-fetch';

TrafficIncidents.api = {
    fetchIncidents(post_id){
        return apiFetch({path: `${TrafficIncidents.rest_namespace}/incidents/${post_id}`})
    }
}