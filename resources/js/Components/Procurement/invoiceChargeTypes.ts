import { ctrans } from "@/Composables/useTrans"

export type InvoiceChargeType =
	| "freight"
	| "other"
	| "packing_charge"
	| "label_charge"
	| "barcode_charge"
	| "carton_marking_charge"
	| "insert_or_leaflet_charge"
	| "mould_or_tooling_charge"
	| "sample_charge"
	| "local_delivery_to_port_charge"

export const invoiceChargeTypeOptions = (): { label: string; value: InvoiceChargeType }[] => [
	{ label: ctrans("Freight"), value: "freight" },
	{ label: ctrans("Packing charge"), value: "packing_charge" },
	{ label: ctrans("Label charge"), value: "label_charge" },
	{ label: ctrans("Barcode charge"), value: "barcode_charge" },
	{ label: ctrans("Carton marking charge"), value: "carton_marking_charge" },
	{ label: ctrans("Insert or leaflet charge"), value: "insert_or_leaflet_charge" },
	{ label: ctrans("Mould or tooling charge"), value: "mould_or_tooling_charge" },
	{ label: ctrans("Sample charge"), value: "sample_charge" },
	{ label: ctrans("Local delivery to port charge"), value: "local_delivery_to_port_charge" },
	{ label: ctrans("Other"), value: "other" },
]
