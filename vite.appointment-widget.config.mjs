import { defineConfig, mergeConfig } from "vite"
import { fileURLToPath, URL } from "node:url"
import chatWidgetConfig from "./vite.chat-widget.config.mjs"

export default mergeConfig(chatWidgetConfig, defineConfig({
    cacheDir: "node_modules/.vite-appointment-widget",
    build: {
        outDir: "public/appointment-widget",
        lib: {
            entry: fileURLToPath(new URL("./resources/js/appointment-widget.ts", import.meta.url)),
            name: "AikuAppointmentWidget",
        },
    },
}))
