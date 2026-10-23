import { ctrans } from "@/Composables/useTrans"

export const mixBatchesLabel = (kg: number, batchKg: number | null | undefined): string | null => {
	if (!batchKg) {
		return null
	}
	const batches = Math.ceil(Math.max(0, kg) / batchKg - 1e-9)

	return ctrans(':batches × :size kg batches = :kg kg', { batches, size: batchKg, kg: Math.round(batches * batchKg * 1000) / 1000 })
}
