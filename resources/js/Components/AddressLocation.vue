<script setup lang="ts">
import { faMapMarkerAlt } from '@fal';
import { library } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";

library.add(faMapMarkerAlt);


const props = defineProps<{
    data?: {
        address_line_1: string
        address_line_2: string
        sorting_code: string
        postal_code: number
        locality: string
        dependent_locality: string
        administrative_area: string
        country_code: string
        country_id: number
        checksum: string
        created_at: string
        updated_at: string
        country: {
            data: {
                code: string
                iso3: string
                name: string
            }
        }
    }
}>()



</script>

<template>
    <FontAwesomeIcon fixed-width icon="fal fa-map-marker-alt" class="text-gray-400 mr-4 mt-1" aria-hidden="true" />
    <p class="">
        {{ props.data.address_line_1 ? props.data.address_line_1 : '' }} <span v-if="props.data.address_line_2">({{ props.data.address_line_2}})<br /></span>
        {{ props.data.locality }}<span v-if="props.data.administrative_area">{{ ', ' + props.data.administrative_area}}<br /></span>
        <span v-if="data.country">{{ props.data.country.data.name }}</span> <span v-if="props.data.postal_code">{{ ', ' + props.data.postal_code }}</span>
    </p>
</template>


