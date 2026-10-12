const twoDigits = (value: number): string => String(value).padStart(2, "0")

export const isoToDate = (iso: string | null | undefined): Date | null => {
	if (!iso) {
		return null
	}

	const [year, month, day] = iso.slice(0, 10).split("-").map(Number)

	if (!year || !month || !day) {
		return null
	}

	return new Date(year, month - 1, day)
}

export const dateToIso = (date: Date | null | undefined): string =>
	date ? `${date.getFullYear()}-${twoDigits(date.getMonth() + 1)}-${twoDigits(date.getDate())}` : ""

export const fieldFocusClass = "[&.p-focus]:!border-[--app-accent] [&_input:focus]:!border-[--app-accent]"

export const inputFocusClass = "focus:!border-[--app-accent]"

export const checkboxAccentClass =
	"[&.p-checkbox-checked_.p-checkbox-box]:!border-[--app-accent] [&.p-checkbox-checked_.p-checkbox-box]:!bg-[--app-accent] [&.p-checkbox-checked_.p-checkbox-icon]:!text-[--app-accent-text]"

export const compactSelectPt = { label: { class: "!py-1 !text-xs" } }
