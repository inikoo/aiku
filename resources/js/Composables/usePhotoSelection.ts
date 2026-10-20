import { notify } from "@kyvg/vue3-notification"
import { ctrans } from "@/Composables/useTrans"

const MAX_PHOTO_BYTES = 20 * 1024 * 1024
const MAX_PHOTOS = 10

export const addPhotos = (current: File[], incoming: File[]): File[] => {
	const tooBig = incoming.filter((file) => file.size > MAX_PHOTO_BYTES)
	const fitting = incoming.filter((file) => file.size <= MAX_PHOTO_BYTES)
	const room = Math.max(MAX_PHOTOS - current.length, 0)
	const accepted = fitting.slice(0, room)
	const skipped = [...tooBig, ...fitting.slice(room)]

	if (skipped.length) {
		notify({
			title: ctrans("Some photos were skipped"),
			text: ctrans("Skipped (over 20 MB or more than 10 files): :names", { names: skipped.map((file) => file.name).join(", ") }),
			type: "warning",
		})
	}

	return [...current, ...accepted]
}
