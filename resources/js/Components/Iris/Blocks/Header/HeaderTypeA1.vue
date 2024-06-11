<script setup lang='ts'>
import { ref } from 'vue'
import Image1 from '@/Components/Iris/Blocks/Header/Image1.vue'
import Headline1 from '@/Components/Iris/Blocks/Header/Headline1.vue'
import PureInput from '@/Components/Pure/PureInput.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { Dialog, DialogPanel, Popover, PopoverButton, PopoverGroup, PopoverPanel } from '@headlessui/vue'
import { faSearch, faArrowRight, faChevronDown } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { Link } from '@inertiajs/vue3'
import IrisSearch from '@/Components/Iris/Blocks/Header/IrisSearch.vue'

library.add(faSearch, faArrowRight, faChevronDown)

const props = defineProps<{
    data: {
        name: string
        data: {
            imgLogo?: string
            title?: string
            description?: string
        }
    }[]
}>()



const componentList = (componentName: string) => {
    const components: any = {
        'Image1': Image1,
        'Headline1': Headline1
    }

    return components[componentName]
}

// const inputSearch = ref()
// const inputValue = ref('')
</script>

<template>
    <div class="grid grid-cols-3 h-24 items-center">
        <component v-for="component in data" :is="componentList(component.name)" :data="component.data" />

        <div class="justify-self-end pr-7">
            <IrisSearch />
        </div>
        <!-- <Image1 :data="data" />

        <Headline1 :data="data" /> -->
    </div>
</template>