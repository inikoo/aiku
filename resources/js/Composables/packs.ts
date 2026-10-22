import { ctrans } from "@/Composables/useTrans"

export const packsLabel = (packs: number, packSize: number | null | undefined): string => {
	if (!packSize) {
		return String(packs)
	}
	const pieces = Math.round(packs * packSize)
	const loose = pieces % packSize
	const whole = (pieces - loose) / packSize

	return loose ? ctrans(':packs packs + :loose loose', { packs: whole, loose }) : ctrans(':packs packs', { packs: whole })
}
