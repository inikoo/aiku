/*
 * Author Louis Perez
 * Created on 20-10-2026-19h-10m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

import axios from "axios"
import { ref } from "vue"

const SERVICE_WORKER = "/aiku-sw.js"

export const isPushSupported = typeof window !== "undefined" && "serviceWorker" in navigator && "PushManager" in window && "Notification" in window
export const isIosDevice = typeof navigator !== "undefined" && /iPad|iPhone|iPod/.test(navigator.userAgent)
export const isStandaloneApp = typeof window !== "undefined" && (window.matchMedia("(display-mode: standalone)").matches || (navigator as Navigator & { standalone?: boolean }).standalone === true)

const urlBase64ToUint8Array = (base64: string): Uint8Array => {
    const padded = (base64 + "=".repeat((4 - base64.length % 4) % 4)).replace(/-/g, "+").replace(/_/g, "/")
    return Uint8Array.from(atob(padded), (char) => char.charCodeAt(0))
}

/** The push endpoint this browser is subscribed with, or null */
export async function currentPushEndpoint(): Promise<string | null> {
    if (!isPushSupported) {
        return null
    }
    const registration = await navigator.serviceWorker.getRegistration("/")
    return (await registration?.pushManager.getSubscription())?.endpoint ?? null
}

/**
 * Asks for permission, registers Aiku's service worker and saves this browser as one of the user's push
 * devices, so notifications reach it even when Aiku is closed. Returns the endpoint, or null if refused.
 */
export async function subscribeThisDevice(publicKey: string, storeRouteName = "grp.profile.push-subscriptions.store"): Promise<string | null> {
    const permission = await Notification.requestPermission()
    if (permission !== "granted") {
        return null
    }
    const registration = await navigator.serviceWorker.register(SERVICE_WORKER, { scope: "/" })
    await navigator.serviceWorker.ready
    const subscription = (await registration.pushManager.getSubscription()) ?? await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(publicKey),
    })
    await axios.post(route(storeRouteName), subscription.toJSON())

    return subscription.endpoint
}

/** State for a "turn on notifications on this device" control */
export function useWebPushDevice() {
    const permission = ref<NotificationPermission | "unsupported">(isPushSupported ? Notification.permission : "unsupported")
    const endpoint = ref<string | null>(null)
    const checked = ref(false)
    const busy = ref(false)
    const failed = ref(false)

    currentPushEndpoint().then((current) => {
        endpoint.value = current
        checked.value = true
    }).catch(() => checked.value = true)

    const enable = async (publicKey: string) => {
        busy.value = true
        failed.value = false
        try {
            endpoint.value = await subscribeThisDevice(publicKey)
            failed.value = !endpoint.value
        } catch {
            failed.value = true
        } finally {
            permission.value = isPushSupported ? Notification.permission : "unsupported"
            busy.value = false
        }
    }

    return { permission, endpoint, checked, busy, failed, enable }
}
