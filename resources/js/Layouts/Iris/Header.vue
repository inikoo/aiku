<script setup lang='ts'>
import { ref } from 'vue'
import PureInput from '@/Components/Pure/PureInput.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faSearch, faArrowRight } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { Link } from '@inertiajs/vue3'
library.add(faSearch, faArrowRight)

const inputSearch = ref()
const inputValue = ref('')
</script>

<template>
    <div class="grid grid-cols-3 h-24 items-center">
        <div class="justify-self-start self-center">
            <img src="https://www.aw-fulfilment.co.uk/wi.php?id=1837721" class="h-20">
        </div>
        
        <div class="text-center place-self-center">
            <h3 class="text-indigo-600 text-xl font-bold leading-5">Your UK's Best Fulfilment Warehouse</h3>
            <span class="text-gray-500 text-sm">Storage - Pick & Pack - Distribution</span>
        </div>
        
        <!-- <div class="w-3/4 justify-self-end text-sm pr-4">
            <p class="text-gray-500 text-xs">We Can Fulfil Your Orders</p>
            <div class="flex gap-x-2 items-center relative">
                <PureInput v-model="inputValue" placeholder="Search your needs" ref="inputSearch" />
                <FontAwesomeIcon icon='fal fa-search' @click="() => inputSearch.inputRef.focus()" class='absolute right-3 h-6 text-gray-400' aria-hidden='true' />
            </div>
            <div class="mt-1 flex gap-x-4 text-xxs text-gray-500">
                <div>Tel: <span class="font-semibold">+44 (0) 1142729165</span></div>
                <div>Email: <span class="font-semibold">info@aw-fulfilment.co.uk</span></div>
            </div>
        </div> -->
        <a href="/app/login" class="justify-self-end pr-4">
            <Button label="login" type="indigo" iconRight="fal fa-arrow-right" />
        </a>
    </div>
</template>