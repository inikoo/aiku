export type MinimumStockRowValues = {
	id: number
	stock: number
	minimum_stock: number | null
	shortfall: number | null
}

export type SavedMinimumStocks = Record<number, number | null>

export const minimumStockShortfall = (stock: number, minimumStock: number | null): number | null =>
	minimumStock === null ? null : Math.max(0, minimumStock - Number(stock))

export const currentMinimumStock = (row: MinimumStockRowValues, savedMinimumStocks: SavedMinimumStocks): number | null =>
	row.id in savedMinimumStocks ? savedMinimumStocks[row.id] : row.minimum_stock

export const currentShortfall = (row: MinimumStockRowValues, savedMinimumStocks: SavedMinimumStocks): number | null =>
	row.id in savedMinimumStocks ? minimumStockShortfall(row.stock, savedMinimumStocks[row.id]) : row.shortfall
