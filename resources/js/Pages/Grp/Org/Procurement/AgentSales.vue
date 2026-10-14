<script setup lang="ts">
import { Deferred, Head } from "@inertiajs/vue3"
import { computed, inject, ref } from "vue"
import Chart from "primevue/chart"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { aikuLocaleStructure } from "@/Composables/useLocaleStructure"
import { useFormatTime } from "@/Composables/useFormatTime"
import { PageHeadingTypes } from "@/types/PageHeading"

type Problem = "out" | "low" | "dead"

interface OrganisationRow {
	code: string
	name: string
	products: number
	selling: number
	sold: number
	this_year: number[]
	last_year: number[]
	problems: Record<Problem, number>
}

interface ProductRow {
	code: string
	name: string
	sold: number
	organisations: { code: string; sold: number; stock: number; problem: Problem | null }[]
}

interface Ranking {
	count: number
	rows: ProductRow[]
}

interface Sales {
	currency: string
	from: string
	year: number
	products: number
	selling: number
	organisations: OrganisationRow[]
	top_sellers: ProductRow[]
	out: Ranking
	low: Ranking
	dead: Ranking
}

const props = defineProps<{
	title: string
	pageHead: PageHeadingTypes
	sales?: Sales
}>()

const locale = inject("locale", aikuLocaleStructure)

const COLORS = { thisYear: "#1f845a", lastYear: "#0ea5e9", behind: "#dc2626", forecastBand: "rgba(31, 132, 90, 0.15)" }
const problemClass: Record<Problem, string> = {
	out: "bg-red-100 text-red-700",
	low: "bg-amber-100 text-amber-700",
	dead: "bg-gray-200 text-gray-600",
}

const money = (amount: number) =>
	new Intl.NumberFormat(undefined, { style: "currency", currency: props.sales?.currency ?? "GBP", maximumFractionDigits: 0 }).format(Math.round(amount))
const shortMoney = (amount: number) =>
	new Intl.NumberFormat(undefined, { style: "currency", currency: props.sales?.currency ?? "GBP", notation: "compact", maximumFractionDigits: 1 }).format(amount)
const monthName = (month: number) => new Date(props.sales?.year ?? 2000, month - 1, 1).toLocaleDateString(undefined, { month: "short" })
const signed = (percent: number) => `${percent > 0 ? "+" : ""}${percent.toFixed(1)}%`

const selected = ref("all")
const organisationOptions = computed(() => [
	{ label: ctrans("All"), value: "all" },
	...(props.sales?.organisations ?? []).map((organisation) => ({ label: organisation.name, value: organisation.code })),
])

const summed = (series: "this_year" | "last_year") => {
	const organisations = (props.sales?.organisations ?? []).filter((organisation) => selected.value === "all" || organisation.code === selected.value)

	return (organisations[0]?.[series] ?? []).map((_, index) => organisations.reduce((sum, organisation) => sum + (organisation[series][index] ?? 0), 0))
}
const thisYear = computed(() => summed("this_year"))
const lastYear = computed(() => summed("last_year"))
const cumulative = (series: number[]) => series.reduce<number[]>((running, amount) => [...running, (running[running.length - 1] ?? 0) + amount], [])

const completeMonths = computed(() => Math.max(0, thisYear.value.length - 1))
const yearSoFar = computed(() => thisYear.value.reduce((sum, amount) => sum + amount, 0))
const completeThisYear = computed(() => thisYear.value.slice(0, completeMonths.value).reduce((sum, amount) => sum + amount, 0))
const completeLastYear = computed(() => lastYear.value.slice(0, completeMonths.value).reduce((sum, amount) => sum + amount, 0))
const versusLastYear = computed(() => (completeLastYear.value ? (completeThisYear.value / completeLastYear.value - 1) * 100 : null))

const forecast = computed(() => {
	const complete = completeMonths.value
	const compared = [...Array(complete).keys()].filter((index) => lastYear.value[index] > 0).slice(-6)
	const recent = compared.slice(-3)
	const recentLastYear = recent.reduce((sum, index) => sum + lastYear.value[index], 0)
	if (!recentLastYear || complete >= lastYear.value.length) {
		return null
	}

	const trend = recent.reduce((sum, index) => sum + thisYear.value[index], 0) / recentLastYear
	const ratios = compared.map((index) => thisYear.value[index] / lastYear.value[index])
	const rates = { expected: trend, low: Math.min(trend, ...ratios), high: Math.max(trend, ...ratios) }
	const soFarThisMonth = thisYear.value[complete] ?? 0

	const monthly = (rate: number) => lastYear.value.map((amount, index) => (index < complete ? null : Math.max(index === complete ? soFarThisMonth : 0, amount * rate)))
	const running = (rate: number) => {
		let total = completeThisYear.value

		return monthly(rate).map((amount, index) => {
			if (amount === null) {
				return index === complete - 1 ? total : null
			}
			total += amount

			return total
		})
	}

	return {
		months: monthly(rates.expected),
		expected: running(rates.expected),
		low: running(rates.low),
		high: running(rates.high),
	}
})
const yearEnd = computed(() => (forecast.value ? { expected: forecast.value.expected.at(-1) ?? 0, low: forecast.value.low.at(-1) ?? 0, high: forecast.value.high.at(-1) ?? 0 } : null))
const lastYearTotal = computed(() => lastYear.value.reduce((sum, amount) => sum + amount, 0))

const monthRows = computed(() =>
	lastYear.value.map((amount, index) => {
		const isPartial = index === thisYear.value.length - 1
		const isForecast = index >= thisYear.value.length && !!forecast.value
		const sales = index < thisYear.value.length ? thisYear.value[index] : (forecast.value?.months[index] ?? null)

		return {
			month: index + 1,
			isPartial,
			isForecast,
			sales,
			lastYear: amount,
			change: sales !== null && !isPartial && amount ? (sales / amount - 1) * 100 : null,
		}
	})
)

const monthLabels = computed(() => lastYear.value.map((_, index) => monthName(index + 1)))
const forecastBands = (line: { expected: (number | null)[]; low: (number | null)[]; high: (number | null)[] }) => [
	{ key: "forecast", label: ctrans("Forecast"), data: line.expected, borderColor: COLORS.thisYear, backgroundColor: COLORS.thisYear, borderDash: [4, 3], tension: 0, borderWidth: 1.5, pointRadius: 0 },
	{ key: "band", label: "", data: line.high, borderColor: "transparent", backgroundColor: COLORS.forecastBand, fill: "+1", tension: 0, borderWidth: 0, pointRadius: 0 },
	{ key: "band_low", label: "", data: line.low, borderColor: "transparent", backgroundColor: COLORS.forecastBand, tension: 0, borderWidth: 0, pointRadius: 0 },
]
const withoutBands = (item: any) => item.parsed.y !== null && !String(item.dataset.key ?? "").startsWith("band")

const chartData = computed(() => ({
	labels: monthLabels.value,
	datasets: [
		{ label: String(props.sales?.year ?? ""), data: cumulative(forecast.value ? thisYear.value.slice(0, completeMonths.value) : thisYear.value), borderColor: COLORS.thisYear, backgroundColor: COLORS.thisYear, tension: 0, borderWidth: 1.5, pointRadius: 2 },
		...(forecast.value ? forecastBands(forecast.value) : []),
		{ label: String((props.sales?.year ?? 0) - 1), data: cumulative(lastYear.value), borderColor: COLORS.lastYear, backgroundColor: COLORS.lastYear, borderDash: [4, 3], tension: 0, borderWidth: 1.5, pointRadius: 0 },
	],
}))

const chartOptions = computed(() => ({
	responsive: true,
	maintainAspectRatio: false,
	interaction: { mode: "index", intersect: false },
	plugins: {
		legend: { display: false },
		tooltip: {
			filter: withoutBands,
			callbacks: {
				label: (item: any) =>
					item.dataset.key === "forecast" && forecast.value
						? ctrans(":label: :amount (likely :low to :high)", { label: item.dataset.label, amount: money(item.parsed.y), low: money(forecast.value.low[item.dataIndex] ?? 0), high: money(forecast.value.high[item.dataIndex] ?? 0) })
						: `${item.dataset.label}: ${money(item.parsed.y)}`,
			},
		},
	},
	scales: {
		x: { grid: { display: false }, ticks: { maxRotation: 0 } },
		y: { beginAtZero: true, afterFit: (axis: any) => { axis.width = 64 }, ticks: { callback: (value: number) => shortMoney(value) } },
	},
}))

const percentOfLastYear = (series: (number | null)[]) => {
	const lastYearRunning = cumulative(lastYear.value)

	return series.map((amount, index) => (amount !== null && lastYearRunning[index] ? Math.round((amount / lastYearRunning[index] - 1) * 1000) / 10 : null))
}
const aheadBehind = computed(() => percentOfLastYear(monthLabels.value.map((_, index) => (index < completeMonths.value ? cumulative(thisYear.value)[index] : null))))
const aheadBehindForecast = computed(() =>
	forecast.value ? { expected: percentOfLastYear(forecast.value.expected), low: percentOfLastYear(forecast.value.low), high: percentOfLastYear(forecast.value.high) } : null
)

const aheadBehindData = computed(() => ({
	labels: monthLabels.value,
	datasets: [
		...(aheadBehindForecast.value ? forecastBands(aheadBehindForecast.value) : []),
		{
			label: ctrans("vs :last_year", { last_year: String((props.sales?.year ?? 0) - 1) }),
			data: aheadBehind.value,
			borderColor: COLORS.thisYear,
			tension: 0,
			borderWidth: 1.5,
			pointRadius: 2,
			fill: { target: { value: 0 }, above: "rgba(31, 132, 90, 0.15)", below: "rgba(220, 38, 38, 0.15)" },
			segment: { borderColor: (context: any) => (context.p1.parsed.y < 0 ? COLORS.behind : COLORS.thisYear) },
			pointBackgroundColor: (context: any) => (context.parsed?.y < 0 ? COLORS.behind : COLORS.thisYear),
		},
	],
}))

const aheadBehindOptions = computed(() => {
	const values = [aheadBehind.value, ...Object.values(aheadBehindForecast.value ?? {})].flat().map((percent) => Math.abs(percent ?? 0))
	const reach = Math.max(2, 2 * Math.ceil(Math.max(0, ...values) * 0.6))

	return {
		responsive: true,
		maintainAspectRatio: false,
		interaction: { mode: "index", intersect: false },
		plugins: {
			legend: { display: false },
			tooltip: { filter: withoutBands, callbacks: { label: (item: any) => `${item.dataset.label}: ${signed(item.parsed.y)}` } },
		},
		scales: {
			x: { grid: { display: false }, ticks: { maxRotation: 0 } },
			y: { min: -reach, max: reach, afterFit: (axis: any) => { axis.width = 64 }, grid: { color: (context: any) => (context.tick.value === 0 ? "#64748b" : "#f1f5f9") }, ticks: { count: 5, callback: (value: number) => `${value > 0 ? "+" : ""}${Math.round(value * 10) / 10}%` } },
		},
	}
})

const totalSold = computed(() => (props.sales?.organisations ?? []).reduce((sum, organisation) => sum + organisation.sold, 0))
const topSold = computed(() => Math.max(1, ...(props.sales?.organisations ?? []).map((organisation) => organisation.sold)))
const problemTotal = (problem: Problem) => (props.sales?.organisations ?? []).reduce((sum, organisation) => sum + organisation.problems[problem], 0)

const rankings = computed(() =>
	props.sales
		? [
				{
					key: "top",
					title: ctrans("Best sellers"),
					hint: ctrans("Goods sold in the last 12 months at cost, every organisation together"),
					empty: ctrans("Nothing sold in the period"),
					rows: props.sales.top_sellers,
					count: null,
					tone: "text-gray-700",
					figure: "sold" as const,
				},
				{
					key: "out",
					title: ctrans("Out of stock"),
					hint: ctrans("Nothing left to sell where shown; the best sellers there first"),
					empty: ctrans("No product is out of stock"),
					rows: props.sales.out.rows,
					count: props.sales.out.count,
					tone: "text-red-600",
					figure: "sold" as const,
				},
				{
					key: "low",
					title: ctrans("About to run out"),
					hint: ctrans("At the rate it sells, the stock will not last until a new order can arrive"),
					empty: ctrans("No product is about to run out"),
					rows: props.sales.low.rows,
					count: props.sales.low.count,
					tone: "text-amber-600",
					figure: "sold" as const,
				},
				{
					key: "dead",
					title: ctrans("In stock but not selling"),
					hint: ctrans("Stock sitting in the warehouse with no sales expected, at what it cost; the largest first"),
					empty: ctrans("No product is sitting unsold"),
					rows: props.sales.dead.rows,
					count: props.sales.dead.count,
					tone: "text-gray-500",
					figure: "stock" as const,
				},
			]
		: []
)
</script>

<template>
	<Head :title="capitalize(title)" />
	<PageHeading :data="pageHead" />
	<div class="space-y-4 p-4">
		<Deferred data="sales">
			<template #fallback>
				<div class="space-y-4">
					<div class="h-96 animate-pulse rounded-lg bg-gray-100" />
					<div class="h-40 animate-pulse rounded-lg bg-gray-100" />
					<div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
						<div v-for="card in 4" :key="card" class="h-64 animate-pulse rounded-lg bg-gray-100" />
					</div>
				</div>
			</template>

			<div v-if="sales" class="space-y-4">
				<section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
					<div class="mb-4 flex flex-wrap items-center gap-x-6 gap-y-2">
						<h2 class="text-sm font-semibold text-gray-600">{{ ctrans(":year goods sold at cost, year to date", { year: String(sales.year) }) }}</h2>
						<span class="flex items-center gap-3 text-xs text-gray-500">
							<span class="flex items-center gap-1.5"><span class="w-4 border-t-2" :style="{ borderColor: COLORS.thisYear }" />{{ sales.year }}</span>
							<span v-if="forecast" class="flex items-center gap-1.5"><span class="h-2.5 w-4 rounded-sm" :style="{ backgroundColor: COLORS.forecastBand }" />{{ ctrans("Forecast") }}</span>
							<span class="flex items-center gap-1.5"><span class="w-4 border-t-2 border-dashed" :style="{ borderColor: COLORS.lastYear }" />{{ sales.year - 1 }}</span>
						</span>
						<span class="ml-auto text-xs text-gray-400">
							{{ ctrans("What each organisation sold of your products to its own customers, at the cost of the goods, in :currency", { currency: sales.currency }) }}
						</span>
					</div>

					<SegmentedToggle v-if="sales.organisations.length > 1" v-model="selected" :options="organisationOptions" :aria-label="ctrans('Organisation')" class="mb-4" />

					<div class="grid gap-6 xl:grid-cols-5">
						<div class="flex min-w-0 flex-col xl:col-span-3">
							<div class="h-56">
								<Chart type="line" :data="chartData" :options="chartOptions" class="h-full" />
							</div>
							<div class="mt-4 flex flex-1 flex-col">
								<div class="mb-1 text-xs text-gray-500">{{ ctrans("Ahead or behind :last_year", { last_year: String(sales.year - 1) }) }}</div>
								<div class="relative min-h-40 flex-1">
									<Chart type="line" :data="aheadBehindData" :options="aheadBehindOptions" class="!absolute inset-0" />
								</div>
							</div>
						</div>

						<div class="min-w-0 xl:col-span-2 xl:border-l xl:border-gray-100 xl:pl-6">
							<div class="flex flex-wrap items-start justify-between gap-2">
								<div>
									<p class="text-2xl font-bold tabular-nums">{{ money(yearSoFar) }}</p>
									<p class="text-xs text-gray-400">{{ ctrans("Cost of the goods sold in :year so far", { year: String(sales.year) }) }}</p>
								</div>
								<div v-if="yearEnd" class="text-right">
									<p class="text-xs text-gray-500">{{ ctrans("Expected by year end") }}</p>
									<p class="text-2xl font-bold tabular-nums" :class="yearEnd.expected < lastYearTotal ? 'text-red-600' : 'text-green-600'">{{ money(yearEnd.expected) }}</p>
									<p class="text-xs text-gray-400">
										{{ ctrans("likely :low to :high", { low: shortMoney(yearEnd.low), high: shortMoney(yearEnd.high) }) }}
										<span v-if="lastYearTotal"> · {{ ctrans(":change on :last_year", { change: signed((yearEnd.expected / lastYearTotal - 1) * 100), last_year: String(sales.year - 1) }) }}</span>
									</p>
								</div>
							</div>
							<p v-if="versusLastYear !== null" class="mt-2 text-xs text-gray-500">
								<span :class="versusLastYear < 0 ? 'text-red-600' : 'text-green-600'">{{ ctrans(":change vs the same months of :last_year", { change: signed(versusLastYear), last_year: String(sales.year - 1) }) }}</span>
								· {{ ctrans(":amount against :last_year_amount, up to :month", { amount: money(completeThisYear), last_year_amount: money(completeLastYear), month: monthName(completeMonths) }) }}
							</p>

							<table class="mt-4 w-full text-xs tabular-nums">
								<thead class="text-gray-400">
									<tr>
										<th class="py-0.5 text-left font-normal">{{ ctrans("Month") }}</th>
										<th class="py-0.5 text-right font-normal">{{ sales.year }}</th>
										<th class="py-0.5 text-right font-normal">{{ sales.year - 1 }}</th>
										<th class="py-0.5 text-right font-normal">{{ ctrans("Change") }}</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="row in monthRows" :key="row.month" :class="row.isForecast ? 'italic text-gray-400' : 'text-gray-700'">
										<td class="py-0.5">{{ monthName(row.month) }}<span v-if="row.isPartial" class="text-gray-400"> · {{ ctrans("so far") }}</span><span v-if="row.isForecast"> · {{ ctrans("forecast") }}</span></td>
										<td class="py-0.5 text-right">{{ row.sales === null ? "—" : money(row.sales) }}</td>
										<td class="py-0.5 text-right text-gray-500">{{ money(row.lastYear) }}</td>
										<td class="py-0.5 text-right" :class="row.change === null ? '' : row.change < 0 ? 'text-red-600' : 'text-green-600'">{{ row.change === null ? "" : signed(row.change) }}</td>
									</tr>
								</tbody>
							</table>
							<p v-if="forecast" class="mt-3 text-xs text-gray-400">
								{{ ctrans("Forecast: the rest of :last_year, scaled by how the last three full months ran against the same months then. The band is the worst and the best of the last six.", { last_year: String(sales.year - 1) }) }}
							</p>
						</div>
					</div>
				</section>

				<section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
					<h2 class="text-sm font-semibold text-gray-700">{{ ctrans("Per organisation") }}</h2>
					<p class="mt-0.5 text-xs text-gray-500">{{ ctrans("Goods sold in the last 12 months, from :date, at cost, and the state of the stock today", { date: useFormatTime(sales.from) }) }}</p>
					<p v-if="!sales.organisations.length" class="py-6 text-center text-xs text-gray-400">{{ ctrans("No organisation buys from this agent yet") }}</p>
					<table v-else class="mt-3 w-full text-sm">
						<thead class="text-xs uppercase tracking-wide text-gray-400">
							<tr>
								<th class="pb-1 text-left font-medium">{{ ctrans("Organisation") }}</th>
								<th class="pb-1 text-right font-medium">{{ ctrans("Sold, at cost") }}</th>
								<th class="pb-1 text-right font-medium">{{ ctrans("Products that sold") }}</th>
								<th class="pb-1 text-right font-medium">{{ ctrans("Out of stock") }}</th>
								<th class="pb-1 text-right font-medium">{{ ctrans("About to run out") }}</th>
								<th class="pb-1 text-right font-medium">{{ ctrans("Not selling") }}</th>
							</tr>
						</thead>
						<tbody class="divide-y divide-gray-100">
							<tr v-for="organisation in sales.organisations" :key="organisation.code">
								<td class="w-1/3 py-1.5 pr-4">
									<div class="truncate font-medium text-gray-800" :title="organisation.name">{{ organisation.name }}</div>
									<div class="mt-0.5 h-1 rounded bg-gray-100">
										<div class="h-1 rounded bg-gray-400" :style="{ width: `${(100 * organisation.sold) / topSold}%` }" />
									</div>
								</td>
								<td class="py-1.5 text-right font-medium tabular-nums text-gray-800">{{ money(organisation.sold) }}</td>
								<td class="py-1.5 text-right tabular-nums text-gray-600">
									{{ locale.number(organisation.selling) }} <span class="text-xs text-gray-400">/ {{ locale.number(organisation.products) }}</span>
								</td>
								<td class="py-1.5 text-right tabular-nums" :class="organisation.problems.out ? 'text-red-600' : 'text-gray-400'">{{ locale.number(organisation.problems.out) }}</td>
								<td class="py-1.5 text-right tabular-nums" :class="organisation.problems.low ? 'text-amber-600' : 'text-gray-400'">{{ locale.number(organisation.problems.low) }}</td>
								<td class="py-1.5 text-right tabular-nums text-gray-500">{{ locale.number(organisation.problems.dead) }}</td>
							</tr>
						</tbody>
						<tfoot v-if="sales.organisations.length > 1" class="border-t border-gray-200 text-gray-500">
							<tr>
								<td class="py-1.5 text-xs uppercase tracking-wide">{{ ctrans("Total") }}</td>
								<td class="py-1.5 text-right font-medium tabular-nums text-gray-800">{{ money(totalSold) }}</td>
								<td />
								<td class="py-1.5 text-right tabular-nums">{{ locale.number(problemTotal("out")) }}</td>
								<td class="py-1.5 text-right tabular-nums">{{ locale.number(problemTotal("low")) }}</td>
								<td class="py-1.5 text-right tabular-nums">{{ locale.number(problemTotal("dead")) }}</td>
							</tr>
						</tfoot>
					</table>
				</section>

				<div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
					<section v-for="ranking in rankings" :key="ranking.key" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
						<div class="flex items-baseline justify-between gap-2">
							<h2 class="text-sm font-semibold text-gray-700">
								{{ ranking.title }}
								<span v-if="ranking.count !== null" class="ml-1 tabular-nums" :class="ranking.tone">{{ locale.number(ranking.count) }}</span>
							</h2>
							<span v-if="ranking.count !== null && ranking.count > ranking.rows.length" class="text-xs text-gray-400">
								{{ ctrans("first :shown shown", { shown: ranking.rows.length }) }}
							</span>
						</div>
						<p class="mt-0.5 text-xs text-gray-500">{{ ranking.hint }}</p>
						<p v-if="!ranking.rows.length" class="py-6 text-center text-xs text-gray-400">{{ ranking.empty }}</p>
						<ul v-else class="mt-3 divide-y divide-gray-100 text-sm">
							<li v-for="product in ranking.rows" :key="product.code" class="flex items-center gap-3 py-1.5">
								<div class="min-w-0 flex-1">
									<div class="truncate text-gray-800" :title="product.name">
										<span class="font-medium">{{ product.code }}</span>
										<span class="ml-1.5 text-gray-500">{{ product.name }}</span>
									</div>
									<div class="mt-0.5 flex flex-wrap gap-1">
										<span
											v-for="organisation in product.organisations"
											:key="organisation.code"
											class="rounded px-1.5 py-0.5 text-xs tabular-nums"
											:class="organisation.problem ? problemClass[organisation.problem] : 'bg-gray-100 text-gray-600'"
										>
											{{ organisation.code }} {{ shortMoney(organisation[ranking.figure]) }}
										</span>
									</div>
								</div>
								<span class="whitespace-nowrap text-right tabular-nums text-gray-600">
									{{ ranking.figure === "stock" ? ctrans(":amount in stock", { amount: money(product.organisations.reduce((sum, organisation) => sum + organisation.stock, 0)) }) : money(product.sold) }}
								</span>
							</li>
						</ul>
					</section>
				</div>
			</div>
		</Deferred>
	</div>
</template>
