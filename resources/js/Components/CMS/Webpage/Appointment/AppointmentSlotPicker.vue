<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from "vue"
import DatePicker from "primevue/datepicker"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faGlobe } from "@fal"
import LoadingIcon from "@/Components/Utils/LoadingIcon.vue"
import { ctrans } from "@/Composables/useTrans"

library.add(faGlobe)

const props = defineProps<{
    slots: Record<string, string[]>
    timezone?: string
    confirmLabel?: string
    error?: string
    isLoading?: boolean
    hideTitle?: boolean
}>()

const emits = defineEmits<{
    (e: "confirm"): void
}>()

const selectedDate = defineModel<Date | null>("date", { default: null })
const selectedTime = defineModel<string | null>("time", { default: null })

const toIsoDate = (date: Date): string => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`
const fromIsoDate = (iso: string): Date => {
    const [year, month, day] = iso.split("-").map(Number)
    return new Date(year, month - 1, day)
}

const availableDates = computed(() => Object.keys(props.slots ?? {}).sort())
const availableDateSet = computed(() => new Set(availableDates.value))
const today = new Date()
today.setHours(0, 0, 0, 0)
const maxDate = computed(() => (availableDates.value.length ? fromIsoDate(availableDates.value[availableDates.value.length - 1]) : today))
const disabledDates = computed(() => {
    const disabled: Date[] = []
    for (const day = new Date(today); day <= maxDate.value; day.setDate(day.getDate() + 1)) {
        if (!availableDateSet.value.has(toIsoDate(day))) {
            disabled.push(new Date(day))
        }
    }
    return disabled
})

const timesForDate = computed(() => (selectedDate.value ? props.slots?.[toIsoDate(selectedDate.value)] ?? [] : []))

watch(selectedDate, () => {
    selectedTime.value = null
})

const dayHeading = (date: Date) => date.toLocaleDateString(undefined, { weekday: "long", month: "long", day: "numeric" })

const clock = ref(new Date())
const clockTimer = setInterval(() => (clock.value = new Date()), 30000)
onBeforeUnmount(() => clearInterval(clockTimer))
const timezoneNow = computed(() => {
    try {
        return clock.value.toLocaleTimeString(undefined, { hour: "2-digit", minute: "2-digit", timeZone: props.timezone })
    } catch {
        return ""
    }
})

const datePickerDt = {
    panel: { background: "transparent", borderColor: "transparent", shadow: "none", padding: "0" },
    header: { background: "transparent", borderColor: "transparent", padding: "0 0 0.5rem 0" },
    title: { gap: "0.35rem", fontWeight: "600" },
    dayView: { margin: "0" },
    weekDay: { padding: "0.5rem 0", fontWeight: "500", color: "#334155" },
    date: {
        width: "2.75rem",
        height: "2.75rem",
        padding: "0",
        borderRadius: "50%",
        color: "#334155",
        selectedBackground: "var(--theme-color-0)",
        selectedColor: "var(--theme-color-1)",
        hoverBackground: "color-mix(in srgb, var(--theme-color-0) 20%, white)",
        hoverColor: "var(--theme-color-0)",
    },
    colorScheme: {
        light: {
            today: { background: "transparent", color: "inherit" },
        },
    },
}

type DayContext = { disabled: boolean, selected: boolean, today: boolean }

const datePickerPt = {
    root: { class: "w-full" },
    panel: { class: "!block w-full !overflow-visible" },
    header: { class: "!justify-center !gap-6 !border-b-0" },
    title: { class: "!flex-none" },
    selectMonth: { class: "!p-0 !text-sm !font-semibold !text-slate-800 hover:!bg-transparent" },
    selectYear: { class: "!p-0 !text-sm !font-semibold !text-slate-800 hover:!bg-transparent" },
    pcPrevButton: { root: { class: "!text-slate-500 disabled:!text-slate-300" } },
    pcNextButton: { root: { class: "!text-[var(--theme-color-0)] disabled:!text-slate-300" } },
    dayView: { class: "!my-0 !w-full !table-fixed !border-separate !border-0" },
    tableHeaderRow: { class: "!bg-transparent" },
    tableHeaderCell: { class: "!border-0 !bg-transparent !p-0 !pb-2 !text-center !font-normal" },
    weekDay: { class: "!text-xs !text-slate-700" },
    dayCell: { class: "!border-0 !bg-transparent !p-0.5 !text-center" },
    day: ({ context }: { context: DayContext }) => ({
        class: [
            "appointment-day",
            context.disabled ? "appointment-day-disabled" : "",
            context.selected ? "appointment-day-selected" : "",
            !context.disabled && !context.selected ? "appointment-day-available" : "",
            context.today ? "appointment-day-today" : "",
        ],
    }),
}
</script>

<template>
    <section class="appointment-pick">
        <div class="min-w-0 flex-1">
            <h3 v-if="!hideTitle" class="mb-6 text-xl font-bold text-slate-800">{{ ctrans("Select a Date & Time") }}</h3>

            <div v-if="!availableDates.length" class="py-10 text-center text-sm text-slate-500">
                {{ ctrans("No free times right now. Please check again later.") }}
            </div>

            <template v-else>
                <DatePicker
                    v-model="selectedDate"
                    inline
                    :showOtherMonths="false"
                    :minDate="today"
                    :maxDate="maxDate"
                    :disabledDates="disabledDates"
                    :dt="datePickerDt"
                    :pt="datePickerPt" />

                <div v-if="timezone" class="mt-6">
                    <div class="text-sm font-bold text-slate-800">{{ ctrans("Time zone") }}</div>
                    <div class="mt-1 flex items-center gap-1.5 text-sm text-slate-700">
                        <FontAwesomeIcon icon="fal fa-globe" fixed-width aria-hidden="true" />
                        {{ timezone }}<template v-if="timezoneNow"> ({{ timezoneNow }})</template>
                    </div>
                </div>
            </template>
        </div>

        <div v-if="selectedDate" class="appointment-times">
            <div class="mb-4 shrink-0 text-base text-slate-800">{{ dayHeading(selectedDate) }}</div>
            <div class="appointment-times-frame">
                <div class="appointment-times-list">
                    <div v-for="time in timesForDate" :key="time" class="appointment-time-row">
                        <button
                            type="button"
                            class="appointment-time"
                            :class="time === selectedTime ? 'appointment-time-selected' : ''"
                            :aria-pressed="time === selectedTime"
                            @click="selectedTime = time">
                            {{ time }}
                        </button>
                        <button
                            v-if="confirmLabel && time === selectedTime" type="button"
                            class="appointment-time appointment-time-next"
                            :disabled="isLoading"
                            @click="emits('confirm')">
                            <LoadingIcon v-if="isLoading" />
                            <template v-else>{{ confirmLabel }}</template>
                        </button>
                    </div>
                </div>
            </div>
            <p v-if="error" class="mt-3 text-sm text-red-600">{{ error }}</p>
        </div>
    </section>
</template>

<style scoped>
:deep(.appointment-day) {
    position: relative;
    display: flex;
    box-sizing: border-box;
    width: 2.5rem !important;
    min-width: 2.5rem;
    height: 2.5rem !important;
    flex: none;
    padding: 0 !important;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    overflow: visible;
    border-radius: 9999px;
    font-size: 0.95rem;
    line-height: 1;
}

:deep(.appointment-day-disabled) {
    color: #94a3b8 !important;
    opacity: 1 !important;
}

:deep(.appointment-day-available) {
    background: color-mix(in srgb, var(--theme-color-0) 9%, white) !important;
    color: var(--theme-color-0) !important;
    font-weight: 600;
}

:deep(.appointment-day-available:hover) {
    background: color-mix(in srgb, var(--theme-color-0) 20%, white) !important;
}

:deep(.appointment-day-selected) {
    box-shadow: none !important;
    outline: none !important;
    font-weight: 700;
}

:deep(.appointment-day-today)::after {
    position: absolute;
    bottom: 0.3rem;
    width: 0.25rem;
    height: 0.25rem;
    border-radius: 9999px;
    background: currentColor;
    content: "";
}

@media (max-width: 639px) {
    :deep(.appointment-day) {
        width: 2rem !important;
        min-width: 2rem;
        height: 2rem !important;
        font-size: 0.875rem;
    }
}

.appointment-pick {
    display: flex;
    min-width: 0;
    flex: 1 1 0%;
    flex-direction: column;
    gap: 1.5rem;
}

.appointment-times {
    display: flex;
    flex-direction: column;
}

.appointment-times-frame {
    position: relative;
    min-height: 0;
    flex: 1 1 0%;
}

.appointment-times-list {
    display: flex;
    max-height: 24rem;
    flex-direction: column;
    gap: 0.625rem;
    overflow-y: auto;
    padding-right: 0.25rem;
    scrollbar-width: thin;
}

.appointment-time-row {
    display: flex;
    flex-shrink: 0;
    gap: 0.5rem;
}

.appointment-time {
    height: 52px;
    min-width: 0;
    flex: 1 1 0%;
    border: 1px solid color-mix(in srgb, var(--theme-color-0) 50%, white);
    border-radius: 0.25rem;
    background: #ffffff;
    color: var(--theme-color-0);
    font-size: 1rem;
    font-weight: 700;
    transition: background-color 0.15s, border-color 0.15s, box-shadow 0.15s, opacity 0.15s;
}

.appointment-time:hover {
    border-color: var(--theme-color-0);
    box-shadow: inset 0 0 0 1px var(--theme-color-0);
}

.appointment-time-selected,
.appointment-time-selected:hover {
    border-color: #475569;
    background: #475569;
    box-shadow: none;
    color: #ffffff;
}

.appointment-time-next,
.appointment-time-next:hover {
    border-color: var(--theme-color-0);
    background: var(--theme-color-0);
    box-shadow: none;
    color: var(--theme-color-1);
}

.appointment-time-next:hover {
    opacity: 0.9;
}

@media (min-width: 1024px) {
    .appointment-pick {
        flex-direction: row;
    }

    .appointment-times {
        width: 220px;
        flex-shrink: 0;
        padding-top: 3.5rem;
    }

    .appointment-times-list {
        position: absolute;
        inset: 0;
        max-height: none;
    }
}
</style>
