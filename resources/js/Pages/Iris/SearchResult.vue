<script setup lang='ts'>
import 'https://cdn.luigisbox.com/search.js'  // For search
import { onMounted } from 'vue'
import LoadingText from '@/Components/Utils/LoadingText.vue'

const luigiTrackerId = import.meta.env.VITE_LUIGI_TRACKER_ID
const defaultTrackerId = '179075-204259'
    
// const listFieldsRemoved = ['price', 'formatted_price', 'price_amount']
const listFieldsRemoved = ['price', 'price_amount']

const LBInitSearch = async () => {
    await Luigis.Search({
        TrackerId: luigiTrackerId,
        Locale: 'en',
        PriceFilter: {
            decimals: 2,
            locale: 'en',
            prefixed: true,
            symbol: '£'
        },
        Theme: 'boo',
        Size: 10,
        // QuicksearchTypes: ['category', 'brand'],
        DefaultFilters: {
            type: 'item'  // Search query only looking from item (category not included)
        },
        Facets: ['price_amount', 'brand', 'category', 'color'],
        // hit_fields: ['description', 'formatted_price'],
        UrlParamName: {
            QUERY: 'q',
        },
        // RemoveFields: listFieldsRemoved
    }, '#inputLuigi', '#search-ui')
}

onMounted(() => {
    LBInitSearch()
    
})
</script>

<template>
    <div>
        <div class="relative pt-10 px-7 h-full overflow-y-auto">
            <div id="search-ui">
                <div class="text-3xl font-bold w-full flex justify-center py-8">
                    <LoadingText />
                </div>
            </div>
        </div>
    </div>
</template>