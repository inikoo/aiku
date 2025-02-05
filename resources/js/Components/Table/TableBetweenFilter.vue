<script setup lang="ts">
import { inject, onBeforeMount, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faChevronDown, faCheckSquare, faSquare, faCalendarAlt } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import VueDatePicker from '@vuepic/vue-datepicker'
import DatePicker from 'primevue/datepicker'
import { debounce } from 'lodash'
import PureMultiselect from '@/Components/Pure/PureMultiselect.vue'
import LoadingIcon from '../Utils/LoadingIcon.vue'


library.add(faChevronDown, faCheckSquare, faSquare, faCalendarAlt)


const props = defineProps<{
    optionsList: string[]
    tableName: string
}>()

const formattedDateRange = (date: string[] | Date[]) => {
    return date?.map(dateString => {
        const date = dateString ? new Date(dateString) : new Date();
        const year = date.getFullYear();
        const month = (date.getMonth() + 1).toString().padStart(2, '0'); // Ensure two digits for month
        const day = date.getDate().toString().padStart(2, '0'); // Ensure two digits for day

        return `${year}${month}${day}`;
    }).join('-')
}
const isLoadingReload = ref(false)

// Watch the datepicker
const dateFilterValue = ref([new Date(), new Date()])
watch(dateFilterValue, (newValue) => {
    router.reload(
        {
            data: { [`between[${selectedPeriodType.value}]`]: formattedDateRange(newValue) },  // Sent to url parameter (?tab=showcase, ?tab=menu)
            onStart: () => {
                isLoadingReload.value = true
            },
            onFinish: () => {
                isLoadingReload.value = false
            },
            onSuccess: () => {
                // console.log('success');
            },
            onError: (e) => {
                // console.log('eeerr', e)
            }
        }
    )
})

// Section: multiselect
const selectedPeriodType = ref(props.optionsList?.[0])
watch(selectedPeriodType, (newValue, oldValue) => {
    const oldBetween = oldValue ? {
        [`between[${oldValue}]`]: null
    } : {}

    if(dateFilterValue.value) {
        router.reload(
            {
                data: {
                    ...oldBetween,
                    [`between[${newValue}]`]: formattedDateRange(dateFilterValue.value),
                },
                onStart: () => {
                    isLoadingReload.value = true
                },
                onFinish: () => {
                    isLoadingReload.value = false
                },
                onSuccess: () => {
                },
                onError: (e) => {
                    // console.log('eeerr', e)
                }
            }
        )
    }    
})

// Convert Date to '20250206'
function formatDate(dateString: string) {
    const year = dateString.substring(0, 4);
    const month = dateString.substring(4, 6);
    const day = dateString.substring(6, 8);
    return `${year}-${month}-${day}`;
}

onBeforeMount(() => {
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);

    // To assign init value
    for (let param of urlParams.keys()) {
        if (param.startsWith('between[') && param.endsWith(']')) {
            const fieldName = param.slice(8, -1)
            const dateRangeString = urlParams.get(param)  // the value of params ('20250206-20250223')

            if (dateRangeString) {
                const dates = dateRangeString.split('-')  // split '20250206-20250223'
                // console.log('dates', dates)

                if (dates.length === 2) {
                    // Store the field name and the date range
                    dateFilterValue.value = [new Date(formatDate(dates[0])), new Date(formatDate(dates[1]))];
                    // console.log('dateFilterValue', dateFilterValue.value)
                    selectedPeriodType.value = fieldName;
                }
            } else {
                continue // Skip to the next iteration
            }

            break;
        }
    }

})
</script>

<template>
    <div class="border border-gray-300 flex rounded-md">
        <div cl="(period, idxPeriod) in dateFilter"
            :key="'datePickerPeriod' + 'idxPeriod'"
            class="px-3 py-1 cursor-pointer capitalize flex items-center gap-x-2"
            :xxstyle="{
                // backgroundColor: selectedPeriodType === dateFilter[0].type ? layout?.app?.theme[4] + '22' : '' 
            }"
        >
            <div class="w-40 text-xs" >
                <PureMultiselect
                    v-model="selectedPeriodType"
                    :options="optionsList"
                    required
                    caret
                    :isLoading="isLoadingReload"
                />
            </div>

            <div class="w-fit">
                <VueDatePicker
                    v-model="dateFilterValue"
                    range
                    auto-apply
                    :enableTimePicker="false"
                >
                    <template #trigger>
                        <div class="h-9 w-9 bg-gray-500 hover:bg-gray-700 rounded flex justify-center items-center">
                            <FontAwesomeIcon v-if="!isLoadingReload" icon='fal fa-calendar-alt' class='cursor-pointer text-gray-200 '
                                fixed-width aria-hidden='true' />
                            <LoadingIcon v-else />
                        </div>
                    </template>
                </VueDatePicker>
            </div>

        </div>
    </div>
</template>
