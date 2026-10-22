<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3"
import { computed, ref } from "vue"
import axios from "axios"
import { notify } from "@kyvg/vue3-notification"
import Button from "@/Components/Elements/Buttons/Button.vue"
import CopyButton from "@/Components/Utils/CopyButton.vue"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Icon from "@/Components/Icon.vue"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"
import { PageHeadingTypes } from "@/types/PageHeading"
import { routeType } from "@/types/route"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faTachometerAlt, faHourglassHalf, faCalendarDay, faCalendarWeek, faCalendarCheck, faHistory, faBan, faTimesCircle, faCheckCircle, faUserSlash, faCode, faPowerOff } from "@fal"

library.add(faTachometerAlt, faHourglassHalf, faCalendarDay, faCalendarWeek, faCalendarCheck, faHistory, faBan, faTimesCircle, faCheckCircle, faUserSlash, faCode, faPowerOff)

type IconData = {
    icon: string | string[]
    tooltip: string
    class: string
}

type CountRow = {
    name: string
    total: number
    upcoming: number
}

const props = defineProps<{
    pageHead: PageHeadingTypes
    title: string
    dashboard: {
        total: number
        people_expected: number
        stats: {
            key: string
            label: string
            value: number
            icon: string
            icon_class: string
            route: routeType
        }[]
        states: {
            label: string
            value: number
            icon: IconData
            route: routeType
        }[]
        types: CountRow[]
        staff: CountRow[]
        sources: {
            label: string
            total: number
        }[]
        days: {
            date: string
            weekday: string
            day: string
            count: number
            is_today: boolean
        }[]
        next: {
            id: number
            when: string
            appointment_type_name: string | null
            contact_name: string
            number_visitors: number
            staff_name: string | null
            state_icon: IconData
        }[]
    }
    widget: {
        enabled: boolean
        embed_code: string | null
        can_toggle: boolean
        update_route: routeType
    }
}>()

const locale = useLocaleStore()

const busiestDayCount = computed(() => Math.max(1, ...props.dashboard.days.map((day) => day.count)))

const share = (count: number, total: number) => (total > 0 ? Math.round((count / total) * 100) : 0)

const breakdowns = computed(() => [
    {
        key: "types",
        title: ctrans("By type"),
        rows: props.dashboard.types,
    },
    {
        key: "staff",
        title: ctrans("By staff"),
        rows: props.dashboard.staff,
    },
])

const isTogglingWidget = ref(false)

const toggleWidget = async (enabled: boolean) => {
    isTogglingWidget.value = true
    try {
        await axios.patch(route(props.widget.update_route.name, props.widget.update_route.parameters), {
            appointment_widget_enabled: enabled,
        })
        router.reload({ only: ["widget"] })
    } catch {
        notify({
            title: ctrans("Something went wrong"),
            text: ctrans("The booking widget could not be changed. Please try again."),
            type: "error",
        })
    } finally {
        isTogglingWidget.value = false
    }
}

const appointmentUrl = (appointmentId: number) =>
    route("grp.org.shops.show.crm.appointments.show", {
        organisation: route().params.organisation,
        shop: route().params.shop,
        appointment: appointmentId,
    })
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />

    <div class="space-y-5 p-4">
        <dl class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <Link v-for="stat in dashboard.stats" :key="stat.key" :href="route(stat.route.name, stat.route.parameters)"
                class="rounded-lg border border-gray-200 bg-white px-4 py-4 transition hover:border-[--app-accent-muted] hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-[--app-accent]">
                <dt class="flex items-center justify-between gap-x-2 text-sm font-medium text-gray-500">
                    <span class="truncate">{{ stat.label }}</span>
                    <FontAwesomeIcon :icon="stat.icon" :class="stat.icon_class" fixed-width aria-hidden="true" />
                </dt>
                <dd class="mt-1 text-3xl font-semibold tabular-nums tracking-tight text-gray-700">
                    {{ locale.number(stat.value) }}
                </dd>
            </Link>
        </dl>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            <section class="rounded-lg border border-gray-200 bg-white p-4 xl:col-span-2">
                <div class="flex flex-wrap items-baseline justify-between gap-x-4">
                    <h3 class="font-semibold text-gray-700">{{ ctrans("Next 14 days") }}</h3>
                    <span class="text-sm text-gray-500">
                        {{ ctrans(":count people expected in total", { count: locale.number(dashboard.people_expected) }) }}
                    </span>
                </div>
                <div class="mt-4 flex h-44 items-stretch gap-x-1.5">
                    <div v-for="day in dashboard.days" :key="day.date" class="flex min-w-0 flex-1 flex-col items-center"
                        v-tooltip="`${day.weekday} ${day.day}: ${locale.number(day.count)}`">
                        <span class="text-xs tabular-nums" :class="day.count ? 'font-semibold text-gray-700' : 'text-gray-300'">
                            {{ locale.number(day.count) }}
                        </span>
                        <div class="flex w-full flex-1 items-end">
                            <div class="w-full rounded-t"
                                :class="day.count ? 'bg-[--app-accent]' : 'bg-gray-200'"
                                :style="{ height: day.count ? `${Math.max(6, (day.count / busiestDayCount) * 100)}%` : '2px' }" />
                        </div>
                        <span class="mt-1 text-xs" :class="day.is_today ? 'font-semibold text-gray-700' : 'text-gray-500'">
                            {{ day.weekday }}
                        </span>
                        <span class="hidden truncate text-[10px] text-gray-400 sm:block">{{ day.day }}</span>
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-4">
                <h3 class="font-semibold text-gray-700">{{ ctrans("Coming up next") }}</h3>
                <ul v-if="dashboard.next.length" class="mt-2 divide-y divide-gray-100">
                    <li v-for="appointment in dashboard.next" :key="appointment.id" class="flex items-start gap-x-2 py-2">
                        <Icon :data="appointment.state_icon" class="mt-0.5" />
                        <div class="min-w-0">
                            <Link :href="appointmentUrl(appointment.id)" class="primaryLink text-sm">
                                {{ appointment.when }}
                            </Link>
                            <div class="truncate text-sm text-gray-700">
                                {{ appointment.contact_name }}
                                <span v-if="appointment.number_visitors > 1" class="text-gray-400">
                                    ({{ locale.number(appointment.number_visitors) }})
                                </span>
                            </div>
                            <div class="truncate text-xs text-gray-500">
                                {{ [appointment.appointment_type_name, appointment.staff_name].filter(Boolean).join(" · ") }}
                            </div>
                        </div>
                    </li>
                </ul>
                <p v-else class="mt-3 text-sm text-gray-400">{{ ctrans("No upcoming appointments") }}</p>
            </section>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <section class="rounded-lg border border-gray-200 bg-white p-4">
                <div class="flex items-baseline justify-between">
                    <h3 class="font-semibold text-gray-700">{{ ctrans("All appointments") }}</h3>
                    <span class="text-lg font-semibold tabular-nums text-gray-700">{{ locale.number(dashboard.total) }}</span>
                </div>
                <ul class="mt-2 divide-y divide-gray-100">
                    <li v-for="state in dashboard.states" :key="state.label" class="flex items-center justify-between gap-x-2 py-2 text-sm">
                        <span class="flex items-center gap-x-2 text-gray-600">
                            <Icon :data="state.icon" />
                            {{ state.label }}
                        </span>
                        <Link :href="route(state.route.name, state.route.parameters)" class="primaryLink tabular-nums">
                            {{ locale.number(state.value) }}
                        </Link>
                    </li>
                </ul>
            </section>

            <section v-for="breakdown in breakdowns" :key="breakdown.key" class="rounded-lg border border-gray-200 bg-white p-4">
                <div class="flex items-baseline justify-between">
                    <h3 class="font-semibold text-gray-700">{{ breakdown.title }}</h3>
                    <span class="text-xs text-gray-400">{{ ctrans("Upcoming / all") }}</span>
                </div>
                <ul v-if="breakdown.rows.length" class="mt-2 divide-y divide-gray-100">
                    <li v-for="row in breakdown.rows" :key="row.name" class="py-2 text-sm">
                        <div class="flex items-center justify-between gap-x-2">
                            <span class="truncate text-gray-600">{{ row.name }}</span>
                            <span class="whitespace-nowrap tabular-nums text-gray-700">
                                <span class="font-semibold">{{ locale.number(row.upcoming) }}</span>
                                <span class="text-gray-400"> / {{ locale.number(row.total) }}</span>
                            </span>
                        </div>
                        <div class="mt-1 h-1 rounded bg-gray-100">
                            <div class="h-1 rounded bg-[--app-accent]" :style="{ width: `${share(row.total, dashboard.total)}%` }" />
                        </div>
                    </li>
                </ul>
                <p v-else class="mt-3 text-sm text-gray-400">{{ ctrans("No appointments yet") }}</p>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-4">
                <h3 class="font-semibold text-gray-700">{{ ctrans("How they were booked") }}</h3>
                <ul class="mt-2 divide-y divide-gray-100">
                    <li v-for="source in dashboard.sources" :key="source.label" class="py-2 text-sm">
                        <div class="flex items-center justify-between gap-x-2">
                            <span class="truncate text-gray-600">{{ source.label }}</span>
                            <span class="whitespace-nowrap tabular-nums text-gray-700">
                                <span class="font-semibold">{{ locale.number(source.total) }}</span>
                                <span class="text-gray-400"> · {{ share(source.total, dashboard.total) }}%</span>
                            </span>
                        </div>
                        <div class="mt-1 h-1 rounded bg-gray-100">
                            <div class="h-1 rounded bg-[--app-accent]" :style="{ width: `${share(source.total, dashboard.total)}%` }" />
                        </div>
                    </li>
                </ul>
            </section>
        </div>

        <section class="rounded-lg border border-gray-200 bg-white p-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <h3 class="flex items-center gap-x-2 font-semibold text-gray-700">
                        <FontAwesomeIcon icon="fal fa-code" class="text-gray-400" fixed-width aria-hidden="true" />
                        {{ ctrans("Booking widget for other websites") }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ ctrans("Lets customers book from a form on another website. Paste the code where the form should appear: a Custom HTML block in WordPress, a Custom Liquid section in Shopify.") }}
                    </p>
                </div>
                <Button v-if="widget.can_toggle" type="tertiary" :loading="isTogglingWidget"
                    :label="widget.enabled ? ctrans('Turn off') : ctrans('Turn on')"
                    @click="toggleWidget(!widget.enabled)">
                    <template #icon>
                        <FontAwesomeIcon icon="fal fa-power-off" :class="widget.enabled ? 'text-red-500' : 'text-green-600'" fixed-width aria-hidden="true" />
                    </template>
                </Button>
            </div>

            <div v-if="widget.enabled && widget.embed_code" class="mt-3 flex items-start gap-x-2 rounded-md border border-gray-200 bg-gray-50 p-3">
                <pre class="min-w-0 flex-1 whitespace-pre-wrap break-all font-mono text-xs text-gray-700">{{ widget.embed_code }}</pre>
                <CopyButton :text="widget.embed_code" class="text-base" v-tooltip="ctrans('Copy code')" />
            </div>
            <p v-else-if="!widget.can_toggle" class="mt-3 text-sm text-gray-400">
                {{ ctrans("The widget is off. An organisation or shop admin can turn it on here or in the shop settings.") }}
            </p>
        </section>
    </div>
</template>
