<script setup lang="ts">
import CountUp from "vue-countup-v3"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCheck, faExclamation, faInfo } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
import { inject } from 'vue'
import { aikuLocaleStructure } from '@/Composables/useLocaleStructure'
library.add(faCheck, faExclamation, faInfo)

// Props for dynamic behavior
const props = withDefaults(defineProps<{
    showRedBorder: boolean
    widgetData: {
        value: string
        description: string
        status: 'success' | 'warning' | 'danger' | 'information' | 'neutral'
        type?: 'number' | 'currency'
        currency_code?: string
    }
}>(), {
    widgetData: () => {
        return {
            value: '0',
            description: '',
            status: 'information'
        }
    }
})


// Example data to use in grand parent (Dashboard)
const widgets = {
    'column_count': 4,
    'components': [
        {
            'type': 'basic',
            'col_span': 1,
            'row_span': 2,
            'data': {
                'value': 0,
                'description': 'xxxxxxx',
                'status': 'success',
            }
        },
        {
            'type': 'basic',
            'col_span': 1,
            'row_span': 1,
            'data': {
                'value': 180000,
                'description': 'ggggggg',
                'status': 'danger',
                'type': 'currency',
                'currency_code': 'GBP'
            }
        },
        {
            'type': 'basic',
            'col_span': 1,
            'row_span': 1,
            'data': {
                'value': 662137,
                'description': 'ggggggg',
                // 'status': 'information',
                'type': 'currency',
                'currency_code': 'GBP'
            }
        },
        {
            'type': 'basic',
            'col_span': 1,
            'row_span': 1,
            'data': {
                'value': 99,
                'type': 'number',
                'description': 'Hell owrodl',
                'status': 'warning',
            }
        },
        {
            'type': 'basic',
            'col_span': 3,
            'row_span': 1,
            'data': {
                'value': 44400,
                'description': '6666',
                'status': 'information',
                // 'status': 'success',
            }
        },
    ]
}

const locale = inject('locale', aikuLocaleStructure)

const getStatusColor = (status: string) => {
    switch (status) {
        case 'success':
            return 'bg-green-100 border border-green-400 text-green-600'
        case 'warning':
            return 'bg-yellow-100 border border-yellow-400 text-yellow-600'
        case 'danger':
            return 'bg-red-100 border border-red-400 text-red-600'
        case 'information':
            return 'bg-gray-200 border border-gray-400'
        default:
            return 'bg-white border border-gray-200'
    }
}

const getIcon = (status?: string) => {
    switch (status) {
        case 'success':
            return 'fas fa-check'
        case 'warning':
            return 'fas fa-exclamation'
        case 'danger':
            return 'fas fa-exclamation'
        case 'information':
            return 'fas fa-info'
    }
}

const getIconColor = (status?: string) => {
    switch (status) {
        case 'success':
            return 'bg-green-400 text-white'
        case 'warning':
            return 'bg-yellow-400 text-white'
        case 'danger':
            return 'bg-red-400 text-white'
        case 'information':
            return 'bg-gray-400 text-white'
    }
}

const printLabelByType = (label?: string) => {
    switch (props.widgetData.type) {
        case 'currency':
            return locale.currencyFormat(props.widgetData.currency_code || 'usd', Number(label))
        default:
            return label
    }
}
</script>

<template>
	<div
        :class="[
			'rounded-lg p-6 shadow-md relative h-full',
			getStatusColor(widgetData.status)
		]"
    >
		<p v-tooltip="printLabelByType(widgetData.value)" class="text-4xl font-bold leading-tight truncate">
            <CountUp v-if="widgetData.type === 'number'"
                :endVal="widgetData.value"
                :duration="1.5"
                :scrollSpyOnce="true"
                :options="{
                    formattingFn: (value: number) => locale.number(value)
                }"
            />
            <span v-else>
                {{ printLabelByType(widgetData.value) }}
            </span>
        </p>
		<p class="text-base mt-2">{{ widgetData.description }}</p>
        
		<!-- Conditional Red Exclamation Icon -->
		<div v-if="getIcon(widgetData.status)"
			class="absolute bottom-0 right-0 transform translate-x-1/2 translate-y-1/2 rounded-full w-6 h-6 text-xs flex items-center justify-center shadow-md"
            :class="getIconColor(widgetData.status)"
        >
			<FontAwesomeIcon v-if="getIcon(widgetData.status)" :icon='getIcon(widgetData.status)' fixed-width aria-hidden='true' />
		</div>
	</div>
</template>
