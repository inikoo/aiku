<script setup lang="ts">
import { DatePicker } from 'v-calendar'
import 'v-calendar/style.css'

const props = defineProps<{
    data: Object
}>()


// Attribute for DatePicker
const attrs = [
    {
        key: "today",
        highlight: {
            color: "gray",
            fillMode: "outline",
        },
        dates: new Date(),
    },
    {
        // Object
        content: {
            color: 'purple',
            style: {
                fontStyle: 'italic',
            },
        },
        dates: [
            new Date(2024, 0, 12),
            new Date(2024, 0, 26),
            new Date(2024, 0, 15),
        ],
    },
]
</script>


<template  layout="App">
    <div class="w-full mx-auto mt-5">
        <DatePicker expanded :attributes="attrs" :min-date='new Date()'>
            <template #day-content="{ day, attributes }">
                <div class="hover:bg-slate-300 p-0.5 rounded h-10">
                    <div class="text-sm text-slate-500">
                        {{ day.day }}
                    </div>
                </div>
            </template>
        </DatePicker>
    </div>
</template>

<style lang="scss">

.vc-container {
    position: relative;
    display: inline-flex;
    width: max-content;
    height: max-content;
    font-family: var(--vc-font-family);
    color: var(--vc-color);
    background-color: var(--vc-bg);
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    -webkit-tap-highlight-color: transparent;

    &,
    & * {
        box-sizing: border-box;

        &:focus {
            outline: none;
        }
    }

    /* Hides double border within popovers */
    & .vc-container {
        @apply border border-red-500
    }
}

.vc-bordered {
    @apply border border-blue-600
}

.vc-expanded {
    min-width: 100%;
}

.vc-transparent {
    @apply bg-transparent
}

.vc-date-picker-content {
    @apply bg-lime-300 p-2;

    .vc-container {
        @apply bg-lime-300 p-2;
    }
}


.vc-weekday-1, .vc-weekday-7 {
    @apply text-red-500

}

.vc-week {
    .on-left, .on-right {
        @apply bg-red-300/20 p-2;
    }
} 
</style>

