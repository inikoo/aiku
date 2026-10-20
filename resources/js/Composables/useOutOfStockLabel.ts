import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"

type ProductWithExpectedBackInStock = { expected_back_in_stock_at?: string | null; expected_back_in_stock_is_estimate?: boolean }

export const useExpectedBackInStockDate = (product?: ProductWithExpectedBackInStock): string | null => {
	const expectedAt = product?.expected_back_in_stock_at

	return expectedAt ? useFormatTime(expectedAt, { formatTime: "mdy" }) : null
}

export const useExpectedBackInStockLabel = (product?: ProductWithExpectedBackInStock): string | null => {
	const date = useExpectedBackInStockDate(product)

	if (!date) {
		return null
	}

	return product?.expected_back_in_stock_is_estimate
		? ctrans("Estimated back around :date", { date })
		: ctrans("Expected back around :date", { date })
}
