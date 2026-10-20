<!--
  - Author Louis Perez
  - Created on 20-10-2026-19h-20m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed, ref } from "vue"
import { Link } from "@inertiajs/vue3"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"
import { isIosDevice, isPushSupported, isStandaloneApp, useWebPushDevice } from "@/Composables/useWebPushDevice"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faBellOn, faTimes, faCheckCircle } from "@fal"
library.add(faBellOn, faTimes, faCheckCircle)

const props = defineProps<{
    push: { public_key: string, profile_route: routeType }
}>()

const DISMISSED_KEY = "pd-push-prompt-dismissed"

const readDismissed = () => {
    try {
        return localStorage.getItem(DISMISSED_KEY) === "1"
    } catch {
        return false
    }
}

const dismissed = ref(readDismissed())
const justEnabled = ref(false)
const { permission, endpoint, checked, busy, failed, enable } = useWebPushDevice()

const needsHomeScreen = isIosDevice && !isStandaloneApp
const visible = computed(() => isPushSupported && checked.value && (!endpoint.value || justEnabled.value) && !dismissed.value)

function dismiss() {
    dismissed.value = true
    try {
        localStorage.setItem(DISMISSED_KEY, "1")
    } catch {
        // the prompt simply shows again next time
    }
}

async function turnOn() {
    await enable(props.push.public_key)
    if (endpoint.value) {
        justEnabled.value = true
        setTimeout(() => dismissed.value = true, 4000)
    }
}
</script>

<template>
    <section v-if="visible || (needsHomeScreen && !dismissed)" class="rounded-lg border border-[--app-accent-muted] bg-[--app-accent-soft] px-4 py-3 text-sm">
        <div class="flex items-start gap-3">
            <FontAwesomeIcon :icon="justEnabled ? 'fal fa-check-circle' : 'fal fa-bell-on'" class="mt-0.5 shrink-0 text-[--app-accent-strong]" fixed-width aria-hidden="true" />
            <div class="min-w-0 flex-1 space-y-2">
                <template v-if="justEnabled">
                    <p class="font-medium text-gray-900">{{ ctrans("Notifications are on for this device") }}</p>
                    <p class="text-xs text-gray-600">{{ ctrans("Reminders and new tasks will arrive here, even when Aiku is closed.") }}</p>
                </template>
                <template v-else-if="needsHomeScreen">
                    <p class="font-medium text-gray-900">{{ ctrans("Get reminders on this iPhone") }}</p>
                    <p class="text-xs text-gray-600">{{ ctrans("Add Aiku to your Home Screen (Share → Add to Home Screen), open it from there, and turn notifications on.") }}</p>
                </template>
                <template v-else-if="permission === 'denied'">
                    <p class="font-medium text-gray-900">{{ ctrans("Notifications are blocked for Aiku") }}</p>
                    <p class="text-xs text-gray-600">{{ ctrans("Allow notifications for this site in the browser's site settings, then come back to turn them on.") }}</p>
                </template>
                <template v-else>
                    <p class="font-medium text-gray-900">{{ ctrans("Get reminders on this device") }}</p>
                    <p class="text-xs text-gray-600">{{ ctrans("Reminders and new tasks from product developments pop up here, even when Aiku is closed.") }}</p>
                    <p v-if="failed" class="text-xs text-red-600">{{ ctrans("Could not turn on notifications on this device") }}</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <Button type="primary" size="xs" icon="fal fa-bell-on" :label="ctrans('Turn on')" :loading="busy" @click="turnOn" />
                        <Link :href="route(push.profile_route.name, push.profile_route.parameters)" class="text-xs text-[--app-accent-strong] hover:underline">{{ ctrans("Choose what to get") }}</Link>
                    </div>
                </template>
            </div>
            <button v-if="!justEnabled" type="button" class="shrink-0 rounded p-1 text-gray-400 hover:text-gray-700" :aria-label="ctrans('Not now')" v-tooltip="ctrans('Not now')" @click="dismiss">
                <FontAwesomeIcon icon="fal fa-times" fixed-width aria-hidden="true" />
            </button>
        </div>
    </section>
</template>
