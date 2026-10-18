<script setup lang="ts">
import { computed, ref } from "vue"
import { Link } from "@inertiajs/vue3"
import Popover from "primevue/popover"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faHeartbeat, faQuestionCircle, faChevronRight } from "@fal"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"

library.add(faHeartbeat, faQuestionCircle, faChevronRight)

type Route = { name: string; parameters: Record<string, string> }
type Figures = {
	stock_health: Record<string, number>
}

const props = defineProps<{
	overview: {
		totals: Figures
		stock_levels: { bucket: string; label: string; description: string | null; tone: string }[]
		organisations: (Figures & {
			name: string
			slug: string
			routes: Record<"stock_health", Route | null>
		})[]
		routes?: { stock_levels: Route | null }
	}
}>()

const locale = useLocaleStore()

const toneColor: Record<string, string> = {
	"red-deep": "bg-red-700",
	red: "bg-red-500",
	orange: "bg-orange-500",
	"orange-light": "bg-orange-400",
	amber: "bg-amber-400",
	yellow: "bg-yellow-300",
	green: "bg-green-500",
	"red-light": "bg-red-400",
	blue: "bg-sky-400",
	gray: "bg-gray-400",
}

const stockItems = computed(() => props.overview.stock_levels.map((level) => ({
	key: level.bucket,
	label: level.label,
	help: level.description,
	color: toneColor[level.tone] ?? "bg-gray-300",
})))

const stockTotal = (health: Figures["stock_health"]) => Object.values(health).reduce((sum, value) => sum + value, 0)

const stockPercentage = (health: Figures["stock_health"], key: string) => {
	const total = stockTotal(health)
	return total > 0 ? (health[key] / total) * 100 : 0
}

const groupStockTotal = computed(() => stockTotal(props.overview.totals.stock_health))

type Organisation = typeof props.overview.organisations[number]

const organisationHref = (organisation: Organisation, bucket?: string): string | null => {
	const stockHealthRoute = organisation.routes.stock_health
	if (!stockHealthRoute) return null

	return route(stockHealthRoute.name, bucket
		? { ...stockHealthRoute.parameters, _query: { elements: { cover: bucket } } }
		: stockHealthRoute.parameters)
}

const groupBucketHref = (bucket: string): string | null => {
	const stockLevelsRoute = props.overview.routes?.stock_levels
	if (!stockLevelsRoute) return null

	return route(stockLevelsRoute.name, { ...stockLevelsRoute.parameters, _query: { elements: { cover: bucket } } })
}

const bucketPopover = ref<InstanceType<typeof Popover> | null>(null)
const selectedBucket = ref<(typeof stockItems.value)[number] | null>(null)

const organisationsInSelectedBucket = computed(() => {
	const bucket = selectedBucket.value?.key
	if (!bucket) return []

	return props.overview.organisations
		.filter((organisation) => (organisation.stock_health[bucket] ?? 0) > 0)
		.map((organisation) => ({
			slug: organisation.slug,
			name: organisation.name,
			count: organisation.stock_health[bucket],
			href: organisationHref(organisation, bucket),
		}))
})

const openBucket = (event: Event, item: (typeof stockItems.value)[number]) => {
	if (selectedBucket.value?.key === item.key) {
		bucketPopover.value?.toggle(event)
		return
	}

	const target = event.currentTarget as HTMLElement
	bucketPopover.value?.hide()
	selectedBucket.value = item
	requestAnimationFrame(() => bucketPopover.value?.show(event, target))
}
</script>

<template>
	<div class="px-3 sm:px-6 mb-4">
		<div class="bg-white rounded-lg shadow ring-1 ring-gray-200 overflow-hidden">
			<div class="px-5 pt-4 pb-3">
				<h3 class="flex items-center gap-x-1.5 text-sm font-semibold text-gray-700">
					<FontAwesomeIcon icon="fal fa-heartbeat" class="text-gray-400" fixed-width aria-hidden="true" />
					{{ ctrans("Stock health") }}
					<FontAwesomeIcon icon="fal fa-question-circle" class="cursor-help text-gray-300" fixed-width aria-hidden="true" v-tooltip="ctrans('Active SKOs by days of cover against their supplier lead time, the same levels as the procurement stock cover. Out of stock uses the same rule as the Out of Stock figure above.')" />
				</h3>
				<p class="mt-3 flex items-baseline gap-x-2">
					<span class="text-xl font-semibold tabular-nums text-gray-800">{{ locale.numberShort(groupStockTotal) }}</span>
					<span class="text-xs text-gray-400">{{ ctrans("active SKOs, all organisations") }}</span>
				</p>
				<div class="mt-2 flex h-3 w-full overflow-hidden rounded-full bg-gray-100">
					<button
						v-for="item in stockItems"
						:key="item.key"
						type="button"
						class="h-full cursor-pointer transition-opacity hover:opacity-75 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[--app-accent]"
						:class="item.color"
						:style="{ width: stockPercentage(overview.totals.stock_health, item.key) + '%' }"
						:aria-label="`${item.label}: ${overview.totals.stock_health[item.key]}`"
						v-tooltip="`${item.label}: ${overview.totals.stock_health[item.key]}`"
						@click="openBucket($event, item)"
					/>
				</div>
				<div class="mt-3 grid grid-cols-4 lg:grid-cols-8 gap-x-4 gap-y-2">
					<button
						v-for="item in stockItems"
						:key="item.key"
						type="button"
						class="-mx-1 flex flex-col rounded px-1 text-left transition-colors hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-[--app-accent]"
						v-tooltip="item.help"
						@click="openBucket($event, item)"
					>
						<span class="flex items-center gap-x-1.5 text-xs font-medium text-gray-500">
							<span class="inline-block h-2 w-2 rounded-full" :class="item.color" />
							{{ item.label }}
						</span>
						<span class="text-sm font-semibold tabular-nums text-gray-800">{{ locale.numberShort(overview.totals.stock_health[item.key]) }}</span>
					</button>
				</div>
			</div>
			<ul v-if="overview.organisations.length > 0" class="divide-y divide-gray-100 border-t border-gray-100 text-xs">
				<li v-for="org in overview.organisations" :key="org.slug" class="group flex items-center gap-x-3 px-4 py-2 hover:bg-gray-50 transition-colors">
					<span class="w-1/3 truncate font-medium">
						<Link v-if="organisationHref(org)" :href="organisationHref(org)!" class="text-gray-800 group-hover:text-[--app-accent] hover:underline">{{ org.name }}</Link>
						<span v-else class="text-gray-800">{{ org.name }}</span>
					</span>
					<div class="flex h-2 flex-1 overflow-hidden rounded-full bg-gray-100">
						<template v-for="item in stockItems" :key="item.key">
							<Link
								v-if="organisationHref(org, item.key) && org.stock_health[item.key] > 0"
								:href="organisationHref(org, item.key)!"
								class="h-full transition-opacity hover:opacity-75"
								:class="item.color"
								:style="{ width: stockPercentage(org.stock_health, item.key) + '%' }"
								:aria-label="`${org.name} — ${item.label}: ${org.stock_health[item.key]}`"
								v-tooltip="`${item.label}: ${org.stock_health[item.key]}`"
							/>
							<div
								v-else
								class="h-full"
								:class="item.color"
								:style="{ width: stockPercentage(org.stock_health, item.key) + '%' }"
							/>
						</template>
					</div>
					<Link v-if="organisationHref(org)" :href="organisationHref(org)!" class="w-12 text-right tabular-nums text-gray-700 hover:text-[--app-accent] hover:underline">
						{{ locale.numberShort(stockTotal(org.stock_health)) }}
					</Link>
					<span v-else class="w-12 text-right tabular-nums text-gray-700">{{ locale.numberShort(stockTotal(org.stock_health)) }}</span>
				</li>
			</ul>
		</div>

		<Popover ref="bucketPopover">
			<div v-if="selectedBucket" class="w-72 text-sm">
				<Link
					v-if="groupBucketHref(selectedBucket.key)"
					:href="groupBucketHref(selectedBucket.key)!"
					class="-mx-1 flex items-center gap-x-2 rounded border-b border-gray-100 px-1 pb-2 pt-1 hover:bg-gray-50 group/head"
					v-tooltip="ctrans('All organisations together')"
				>
					<span class="inline-block h-2.5 w-2.5 rounded-full" :class="selectedBucket.color" />
					<span class="font-semibold text-gray-800 group-hover/head:text-[--app-accent]">{{ selectedBucket.label }}</span>
					<span class="ml-auto tabular-nums text-gray-500">{{ locale.number(overview.totals.stock_health[selectedBucket.key]) }}</span>
					<FontAwesomeIcon icon="fal fa-chevron-right" class="text-gray-300" fixed-width aria-hidden="true" />
				</Link>
				<div v-else class="flex items-center gap-x-2 border-b border-gray-100 pb-2">
					<span class="inline-block h-2.5 w-2.5 rounded-full" :class="selectedBucket.color" />
					<span class="font-semibold text-gray-800">{{ selectedBucket.label }}</span>
					<span class="ml-auto tabular-nums text-gray-500">{{ locale.number(overview.totals.stock_health[selectedBucket.key]) }}</span>
				</div>
				<p v-if="selectedBucket.help" class="pt-2 text-xs text-gray-500">{{ selectedBucket.help }}</p>
				<ul v-if="organisationsInSelectedBucket.length" class="mt-2 -mx-1">
					<li v-for="organisation in organisationsInSelectedBucket" :key="organisation.slug">
						<Link
							v-if="organisation.href"
							:href="organisation.href"
							class="flex items-center gap-x-2 rounded px-1 py-1.5 text-gray-700 hover:bg-gray-50 hover:text-[--app-accent]"
						>
							<span class="truncate">{{ organisation.name }}</span>
							<span class="ml-auto tabular-nums">{{ locale.number(organisation.count) }}</span>
							<FontAwesomeIcon icon="fal fa-chevron-right" class="text-gray-300" fixed-width aria-hidden="true" />
						</Link>
						<div v-else class="flex items-center gap-x-2 px-1 py-1.5 text-gray-700">
							<span class="truncate">{{ organisation.name }}</span>
							<span class="ml-auto tabular-nums">{{ locale.number(organisation.count) }}</span>
						</div>
					</li>
				</ul>
				<p v-else class="pt-2 text-xs text-gray-400">{{ ctrans("No SKOs at this level") }}</p>
			</div>
		</Popover>
	</div>
</template>
