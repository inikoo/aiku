import { createApp, h, ref } from "vue"
import axios from "axios"
import PrimeVue from "primevue/config"
import Aura from "@primevue/themes/aura"
import { i18nVue } from "laravel-vue-i18n"
import AppointmentBooking from "@/Components/CMS/Webpage/Appointment/AppointmentBooking.vue"
import type { AppointmentBookingPayload, AppointmentTypeOption } from "@/Components/CMS/Webpage/Appointment/AppointmentBooking.vue"
import widgetStyles from "../css/chat-widget.css?inline"
import { useColorTheme } from "@/Composables/useStockList"
import { setColorStyleRootByEl } from "@/Composables/useApp"
import { ctrans } from "@/Composables/useTrans"

const MOUNT_SELECTOR = "[data-aiku-appointments]"

interface WidgetConfig {
    shop_name: string
    timezone: string
    country_code: string | null
    theme: string[] | null
    appointment_types: AppointmentTypeOption[]
}

const resolveScript = (): HTMLScriptElement | null => {
    const current = document.currentScript as HTMLScriptElement | null

    if (current?.src) {
        return current
    }

    return Array.from(document.getElementsByTagName("script")).reverse().find((script) => script.src.includes("appointment-widget")) ?? null
}

const readSettings = (script: HTMLScriptElement | null) => {
    const url = script?.src ? new URL(script.src, window.location.href) : null

    return {
        script,
        baseUrl: url ? url.origin : window.location.origin,
        stylesheet: url ? url.href.replace(/[^/]*$/, "v1.css").replace(/\?.*$/, "") : null,
        key: script?.dataset.key ?? url?.searchParams.get("k") ?? null,
    }
}

const mountTargets = (script: HTMLScriptElement | null): HTMLElement[] => {
    const targets = Array.from(document.querySelectorAll<HTMLElement>(MOUNT_SELECTOR)).filter((target) => !target.shadowRoot)

    if (targets.length || !script?.closest("body")) {
        return targets
    }

    const target = document.createElement("div")
    script.parentNode?.insertBefore(target, script)

    return [target]
}

const primeVueStyles = () => Array.from(document.head.querySelectorAll<HTMLStyleElement>("style[data-primevue-style-id]"))
    .map((style) => (style.textContent ?? "").replace(/:root/g, ":root,:host"))
    .join("\n")

const mirrorPrimeVueStyles = (shadow: ShadowRoot) => {
    const style = document.createElement("style")
    shadow.prepend(style)

    const sync = () => {
        const css = primeVueStyles()
        if (style.textContent !== css) {
            style.textContent = css
        }
    }

    sync()
    new MutationObserver(sync).observe(document.head, { childList: true, subtree: true, characterData: true })
}

const mount = (target: HTMLElement, config: WidgetConfig, baseUrl: string, key: string, stylesheet: string | null) => {
    target.style.setProperty("display", "block", "important")

    const shadow = target.attachShadow({ mode: "open" })

    const style = document.createElement("style")
    style.textContent = widgetStyles
    shadow.appendChild(style)

    if (stylesheet) {
        const link = document.createElement("link")
        link.rel = "stylesheet"
        link.href = stylesheet
        shadow.appendChild(link)
    }

    const root = document.createElement("div")
    shadow.appendChild(root)

    const theme = config.theme?.length ? config.theme : useColorTheme[3]
    setColorStyleRootByEl(root, theme)

    const appointmentTypes = ref<AppointmentTypeOption[]>(config.appointment_types)

    const book = async (payload: AppointmentBookingPayload) => {
        const { data } = await axios.post(`${baseUrl}/app/api/appointments/widget-booking`, payload, {
            params: { key },
        })

        return data
    }

    const app = createApp({
        render: () => h(AppointmentBooking, {
            appointmentTypes: appointmentTypes.value,
            timezone: config.timezone,
            defaultCountry: config.country_code,
            organizerName: config.shop_name,
            showPickerHeading: true,
            showMarketingOptIn: true,
            book,
        }),
    })

    app.provide("layout", {
        appUrl: baseUrl,
        app: {
            name: "appointment-widget",
            theme,
        },
    })

    app.use(PrimeVue, {
        theme: {
            preset: Aura,
            options: {
                cssLayer: false,
                darkModeSelector: false,
            },
        },
    })

    app.use(i18nVue, {
        resolve: async () => ({ default: {} }),
    })

    app.config.globalProperties.ctrans = ctrans

    app.mount(root)

    mirrorPrimeVueStyles(shadow)
}

const boot = async () => {
    const { script, baseUrl, stylesheet, key } = readSettings(resolveScript())

    if (!key) {
        console.error("Aiku appointment widget: no key on the script tag")

        return
    }

    const targets = mountTargets(script)

    if (!targets.length) {
        console.error(`Aiku appointment widget: add <div data-aiku-appointments></div> where the booking form should appear`)

        return
    }

    try {
        const { data } = await axios.get(`${baseUrl}/app/api/appointments/widget-config`, {
            params: { key },
        })

        if (!data?.data) {
            return
        }

        targets.forEach((target) => mount(target, data.data, baseUrl, key, stylesheet))
    } catch (error) {
        console.error("Aiku appointment widget: could not load its configuration", error)
    }
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot)
} else {
    boot()
}
