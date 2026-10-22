<!--
  - Author Louis Perez
  - Created on 18-10-2026-01h-20m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, ref } from "vue"
import { router } from "@inertiajs/vue3"
import Popover from "primevue/popover"
import Dialog from "primevue/dialog"
import Button from "@/Components/Elements/Buttons/Button.vue"
import TicketAttachmentPreview, { type TicketAttachment } from "@/Components/Tickets/TicketAttachmentPreview.vue"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faCamera, faCheck, faSpinner, faImages, faRedo, faEye } from "@fal"
library.add(faCamera, faCheck, faSpinner, faImages, faRedo, faEye)

interface Angle {
    key: string
    label: string
    url: string | null
    preview_url: string | null
    name: string | null
    mime: string | null
}

const props = defineProps<{
    value: { angles: Angle[], upload_route: routeType }
    editable: boolean
}>()

const emit = defineEmits<{
    error: [message: string | null]
}>()

const galleryInput = ref<HTMLInputElement | null>(null)
const cameraInput = ref<HTMLInputElement | null>(null)
const sourceMenu = ref<InstanceType<typeof Popover> | null>(null)
const pendingAngle = ref<Angle | null>(null)
const uploading = ref<string | null>(null)

const nextMissing = computed(() => props.value.angles.find((angle) => !angle.preview_url) ?? null)

function pick(angle: Angle, event: Event) {
    if (!props.editable || uploading.value) {
        return
    }
    pendingAngle.value = angle
    const menu = sourceMenu.value as (InstanceType<typeof Popover> & { visible?: boolean, alignOverlay?: () => void }) | null
    const wasOpen = !!menu?.visible
    menu?.show(event, event.currentTarget as HTMLElement)
    if (wasOpen) {
        nextTick(() => menu?.alignOverlay?.())
    }
}

/** The same full-screen preview as the Files tab, stepping through the angles that have a photo */
const preview = ref<{ files: TicketAttachment[], index: number | null }>({ files: [], index: null })

function viewImage() {
    sourceMenu.value?.hide()
    const photographed = props.value.angles.filter((angle) => angle.url)
    preview.value = {
        files: photographed.map((angle) => ({ name: angle.name ?? `${angle.label}.jpg`, url: angle.url!, mime: angle.mime })),
        index: photographed.findIndex((angle) => angle.key === pendingAngle.value?.key),
    }
}

function fromPhotos() {
    sourceMenu.value?.hide()
    galleryInput.value?.click()
}

function upload(photo: File) {
    if (!pendingAngle.value) {
        return
    }
    const angle = pendingAngle.value.key
    emit("error", null)
    router.post(route(props.value.upload_route.name, props.value.upload_route.parameters), { angle, photo }, {
        forceFormData: true,
        preserveScroll: true,
        preserveState: true,
        onStart: () => uploading.value = angle,
        onFinish: () => uploading.value = null,
        onError: (errors) => emit("error", Object.values(errors)[0] ?? null),
    })
}

function onGalleryPicked(event: Event) {
    const target = event.target as HTMLInputElement
    const photo = target.files?.[0]
    target.value = ""
    if (photo) {
        upload(photo)
    }
}

/* Camera: phones and tablets hand off to their own camera app for full quality; computers use the webcam inside the page. Either way the shot is confirmed before it is used */

const cameraOpen = ref(false)
const video = ref<HTMLVideoElement | null>(null)
const stream = ref<MediaStream | null>(null)
const starting = ref(false)
const shot = ref<{ file: File, url: string } | null>(null)

const isPhoneOrTablet = () => {
    if (typeof navigator === "undefined") {
        return false
    }
    const userAgentData = (navigator as Navigator & { userAgentData?: { mobile?: boolean } }).userAgentData
    return !!userAgentData?.mobile
        || /Android|iPhone|iPad|iPod|Mobile/i.test(navigator.userAgent)
        || (navigator.platform === "MacIntel" && navigator.maxTouchPoints > 1)
}

const canUseLiveCamera = () => typeof navigator !== "undefined" && !isPhoneOrTablet() && !!navigator.mediaDevices?.getUserMedia && window.isSecureContext

function stopStream() {
    stream.value?.getTracks().forEach((track) => track.stop())
    stream.value = null
}

function clearShot() {
    if (shot.value) {
        URL.revokeObjectURL(shot.value.url)
    }
    shot.value = null
}

async function startLiveCamera() {
    starting.value = true
    try {
        stream.value = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: { ideal: "environment" }, width: { ideal: 1920 }, height: { ideal: 1440 } },
            audio: false,
        })
        await nextTick()
        if (video.value) {
            video.value.srcObject = stream.value
            await video.value.play().catch(() => undefined)
        }
    } catch (error) {
        cameraOpen.value = false
        const denied = error instanceof DOMException && (error.name === "NotAllowedError" || error.name === "SecurityError")
        emit("error", denied ? ctrans("Camera access was refused. Allow the camera for this site, or upload from photos.") : ctrans("The camera could not be opened. Upload from photos instead."))
    } finally {
        starting.value = false
    }
}

async function takePicture() {
    sourceMenu.value?.hide()
    emit("error", null)
    clearShot()
    if (!canUseLiveCamera()) {
        cameraInput.value?.click()
        return
    }
    cameraOpen.value = true
    await startLiveCamera()
}

function capture() {
    const element = video.value
    if (!element || !element.videoWidth || !pendingAngle.value) {
        return
    }
    const canvas = document.createElement("canvas")
    canvas.width = element.videoWidth
    canvas.height = element.videoHeight
    canvas.getContext("2d")!.drawImage(element, 0, 0, canvas.width, canvas.height)
    const angle = pendingAngle.value.key
    canvas.toBlob((blob) => {
        if (!blob) {
            return
        }
        const file = new File([blob], `${angle}-${Date.now()}.jpg`, { type: "image/jpeg" })
        shot.value = { file, url: URL.createObjectURL(file) }
        stopStream()
    }, "image/jpeg", 0.9)
}

function onCameraPicked(event: Event) {
    const target = event.target as HTMLInputElement
    const photo = target.files?.[0]
    target.value = ""
    if (!photo) {
        return
    }
    clearShot()
    shot.value = { file: photo, url: URL.createObjectURL(photo) }
    cameraOpen.value = true
}

async function retake() {
    clearShot()
    if (canUseLiveCamera()) {
        await startLiveCamera()
    } else {
        cameraOpen.value = false
        cameraInput.value?.click()
    }
}

function usePhoto() {
    if (!shot.value) {
        return
    }
    upload(shot.value.file)
    closeCamera()
}

function closeCamera() {
    stopStream()
    clearShot()
    cameraOpen.value = false
}

onBeforeUnmount(closeCamera)
</script>

<template>
    <div class="space-y-3">
        <div class="grid grid-cols-3 gap-2 sm:grid-cols-6">
            <button
                v-for="angle in value.angles"
                :key="angle.key"
                type="button"
                class="relative flex aspect-[4/3] flex-col items-center justify-center overflow-hidden rounded-md text-xs transition-colors"
                :class="[
                    angle.preview_url ? 'border border-gray-200 bg-gray-100 text-green-800' : 'border border-dashed border-orange-300 bg-orange-50/40 text-orange-700',
                    editable ? 'cursor-pointer hover:border-[--app-accent]' : 'cursor-default',
                ]"
                :disabled="!editable"
                :aria-label="angle.preview_url ? ctrans('Replace the :angle photo', { angle: angle.label }) : ctrans('Add the :angle photo', { angle: angle.label })"
                aria-haspopup="menu"
                @click="(event) => pick(angle, event)"
            >
                <img v-if="angle.preview_url" :src="angle.preview_url" :alt="angle.label" class="absolute inset-0 h-full w-full object-cover" loading="lazy" />
                <span class="relative z-10 flex items-center gap-1 rounded px-1.5 py-0.5" :class="angle.preview_url ? 'bg-white/85' : ''">
                    <FontAwesomeIcon v-if="uploading === angle.key" icon="fal fa-spinner" spin fixed-width aria-hidden="true" />
                    {{ angle.label }}
                    <FontAwesomeIcon v-if="angle.preview_url && uploading !== angle.key" icon="fal fa-check" fixed-width aria-hidden="true" />
                </span>
            </button>
        </div>

        <Button
            v-if="editable && nextMissing"
            type="primary"
            full
            icon="fal fa-camera"
            :label="ctrans('Add photo of :angle', { angle: nextMissing.label })"
            :loading="!!uploading"
            aria-haspopup="menu"
            @click="(event: Event) => pick(nextMissing!, event)"
        />

        <Popover ref="sourceMenu">
            <div class="w-56 text-sm" role="menu">
                <p v-if="pendingAngle" class="px-2 pb-1.5 text-xs text-gray-500">{{ pendingAngle.label }}</p>
                <button v-if="pendingAngle?.url" type="button" role="menuitem" class="flex w-full items-center gap-2.5 rounded-md px-2 py-2.5 text-left text-gray-800 hover:bg-gray-50 active:bg-gray-100" @click="viewImage">
                    <FontAwesomeIcon icon="fal fa-eye" class="text-[--app-accent]" fixed-width aria-hidden="true" />
                    {{ ctrans("View image") }}
                </button>
                <button type="button" role="menuitem" class="flex w-full items-center gap-2.5 rounded-md px-2 py-2.5 text-left text-gray-800 hover:bg-gray-50 active:bg-gray-100" @click="fromPhotos">
                    <FontAwesomeIcon icon="fal fa-images" class="text-[--app-accent]" fixed-width aria-hidden="true" />
                    {{ ctrans("Upload from photos") }}
                </button>
                <button type="button" role="menuitem" class="flex w-full items-center gap-2.5 rounded-md px-2 py-2.5 text-left text-gray-800 hover:bg-gray-50 active:bg-gray-100" @click="takePicture">
                    <FontAwesomeIcon icon="fal fa-camera" class="text-[--app-accent]" fixed-width aria-hidden="true" />
                    {{ ctrans("Take picture") }}
                </button>
            </div>
        </Popover>

        <Dialog
            :visible="cameraOpen"
            modal
            :header="pendingAngle ? ctrans('Photo: :angle', { angle: pendingAngle.label }) : ctrans('Photo')"
            :style="{ width: '40rem' }"
            :breakpoints="{ '640px': '100vw' }"
            :draggable="false"
            @update:visible="(visible) => !visible && closeCamera()"
        >
            <div class="space-y-3">
                <div class="relative flex aspect-[4/3] items-center justify-center overflow-hidden rounded-md bg-black">
                    <img v-if="shot" :src="shot.url" :alt="pendingAngle?.label ?? ''" class="h-full w-full object-contain" />
                    <video v-else ref="video" class="h-full w-full object-contain" autoplay playsinline muted />
                    <FontAwesomeIcon v-if="starting" icon="fal fa-spinner" spin class="absolute text-2xl text-white" fixed-width aria-hidden="true" />
                </div>

                <p v-if="shot" class="text-center text-sm text-gray-600">{{ ctrans("Use this photo?") }}</p>

                <div v-if="shot" class="grid grid-cols-2 gap-2">
                    <Button type="tertiary" full icon="fal fa-redo" :label="ctrans('Retake')" @click="retake" />
                    <Button type="primary" full icon="fal fa-check" :label="ctrans('Use photo')" @click="usePhoto" />
                </div>
                <Button v-else type="primary" full icon="fal fa-camera" :label="ctrans('Capture')" :loading="starting" @click="capture" />
            </div>
        </Dialog>

        <TicketAttachmentPreview v-model:index="preview.index" :files="preview.files" />

        <input ref="galleryInput" type="file" accept="image/*" class="hidden" @change="onGalleryPicked" />
        <input ref="cameraInput" type="file" accept="image/*" capture="environment" class="hidden" @change="onCameraPicked" />
    </div>
</template>
