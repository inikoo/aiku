export type ProductBadge = "new_in" | "back_in_stock" | "bestseller"

export type ProductWithBadge = {
	badge?: ProductBadge | null
	top_seller?: number | null
	stock?: number | null
	is_coming_soon?: boolean
	first_available_at?: string | null
	back_in_stock_since?: string | null
	out_of_stock_since?: string | null
}

const HOUR = 60 * 60 * 1000
const DAY = 24 * HOUR
const QUALIFYING_STOCKOUT = 24 * HOUR
const BACK_IN_STOCK_PERIOD = 14 * DAY

export const isBackInStock = (product?: ProductWithBadge | null, now: number = Date.now()): boolean => {
	if (!product?.back_in_stock_since || !product.out_of_stock_since || !product.first_available_at) {
		return false
	}

	if (!(Number(product.stock) > 0)) {
		return false
	}

	const backInStockAt = Date.parse(product.back_in_stock_since)
	const wasLaunchedBefore = Date.parse(product.first_available_at) < backInStockAt
	const stockoutLasted = backInStockAt - Date.parse(product.out_of_stock_since)

	return wasLaunchedBefore && stockoutLasted >= QUALIFYING_STOCKOUT && now - backInStockAt < BACK_IN_STOCK_PERIOD
}

export const resolveProductBadge = (product?: ProductWithBadge | null, bestsellerNeedsStock = false): ProductBadge | null => {
	const serverBadge = product?.badge ?? (product?.top_seller ? "bestseller" : null)

	if (serverBadge === "new_in") {
		return "new_in"
	}

	if (isBackInStock(product)) {
		return "back_in_stock"
	}

	if (serverBadge !== "bestseller") {
		return null
	}

	if (bestsellerNeedsStock && !(Number(product?.stock) > 0 || product?.is_coming_soon)) {
		return null
	}

	return "bestseller"
}
