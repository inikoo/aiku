<script setup lang="ts">
import { inject, ref } from 'vue'
import { faChevronDown, faCheckSquare, faSquare } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { onMounted } from 'vue'
import { layoutStructure } from '@/Composables/useLayoutStructure'
library.add(faChevronDown, faCheckSquare, faSquare)

interface Period {
    type: string
    label: string
    date?: number
}

const props = defineProps<{
    periodData: Period[]
    tableName: string
}>()

const layout = inject('layout', layoutStructure)

const emits = defineEmits<{
    (e: 'periodChanged', value: Period | {}): void
}>()

const selectedPeriod = ref<string | null>('')
const onClickPeriod = (period: Period) => {
    // If the click on selected period
    if(selectedPeriod.value === period.type) {
        emits('periodChanged', {})
        selectedPeriod.value = null
    }

    else {
        emits('periodChanged', { type: period.type, date: period.date })
        selectedPeriod.value = period.type
    }
}

onMounted(() => {
    // To handle selected period on hard-refresh
    const prefix = props.tableName === 'default' ? 'period' : props.tableName + '_' + 'period'  // To handle banners_elements, users_elements, etc
    const searchParams = new URLSearchParams(window.location.search)

    selectedPeriod.value = searchParams.get(`${prefix}[type]`)
})

</script>

<template>
    <!-- <pre>{{ elements }}</pre> -->

    <div class="shadow border border-gray-300 flex rounded-md overflow-x-hidden">
        <div v-for="period in periodData"
            @click="() => onClickPeriod(period)"
            class="px-3 py-1 cursor-pointer capitalize"
            :class="[selectedPeriod === period.type ? '' : 'bg-white hover:bg-gray-50']"
            :style="{
                backgroundColor: selectedPeriod === period.type ? layout?.app?.theme[4] + '22' : '' 
            }"
        >
            {{ period.label }}
        </div>
    </div>
</template>
