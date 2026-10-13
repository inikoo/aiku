import { ctrans } from "@/Composables/useTrans"

export type OrderingLevel = "cartons" | "skos" | "units" | "supplier_units"

export const getOrderingLevels = () => [
	{
		key: "cartons" as OrderingLevel,
		icon: "fal fa-pallet",
		tab: ctrans("Ordering Cartons"),
		description: ctrans("Carton description"),
		quantity: ctrans("Cartons"),
		cost: ctrans("Carton cost"),
		singular: ctrans("Carton"),
	},
	{
		key: "skos" as OrderingLevel,
		icon: "fal fa-box",
		tab: ctrans("Ordering SKOs"),
		description: ctrans("SKO description"),
		quantity: ctrans("SKOs"),
		cost: ctrans("SKO cost"),
		singular: ctrans("SKO"),
	},
	{
		key: "units" as OrderingLevel,
		icon: "fal fa-stop-circle",
		tab: ctrans("Ordering Units"),
		description: ctrans("Unit description"),
		quantity: ctrans("Units"),
		cost: ctrans("Unit cost"),
		singular: ctrans("Unit"),
	},
]

export const unitsPerOrderingLevel = (item: any, level: OrderingLevel): number => {
	if (level === "cartons") {
		return Number(item?.units_per_carton) || 1
	}

	if (level === "skos") {
		return Number(item?.units_per_pack) || 1
	}

	if (level === "supplier_units") {
		return Number(item?.units_per_supplier_unit) || 1
	}

	return 1
}

export const suggestionAtOrderingLevel = (item: any, skos: number, level: OrderingLevel): number => {
	const quantity = (skos * (Number(item?.units_per_pack) || 1)) / unitsPerOrderingLevel(item, level)

	return level === "cartons" ? Math.ceil(Math.round(quantity * 1e6) / 1e6) : Math.round(quantity * 1000) / 1000
}
