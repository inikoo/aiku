<!--
  - Author Louis Perez
  - Created on 20-10-2026-20h-00m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script lang="ts">
const checked = new Map<string, Promise<string[]>>()
</script>

<script setup lang="ts">
import { ref, watch } from "vue"
import axios from "axios"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faExclamationTriangle } from "@fal"
library.add(faExclamationTriangle)

const props = defineProps<{
    userId: number | null
    role: string | null
}>()

const warnings = ref<string[]>([])

const lookUp = (userId: number, role: string | null): Promise<string[]> => {
    const key = `${userId}|${role ?? ""}`
    if (!checked.has(key)) {
        checked.set(key, axios.get(route("grp.product_developments.json.member_access"), { params: { user_id: userId, role } })
            .then((response) => response.data.warnings as string[])
            .catch(() => {
                checked.delete(key)
                return []
            }))
    }
    return checked.get(key)!
}

watch(() => [props.userId, props.role] as const, async ([userId, role]) => {
    warnings.value = []
    if (!userId) {
        return
    }
    const found = await lookUp(userId, role)
    if (props.userId === userId && props.role === role) {
        warnings.value = found
    }
}, { immediate: true })
</script>

<template>
    <ul v-if="warnings.length" class="space-y-1">
        <li v-for="warning in warnings" :key="warning" class="flex items-start gap-1.5 rounded-md bg-amber-50 px-2 py-1.5 text-xs text-amber-800">
            <FontAwesomeIcon icon="fal fa-exclamation-triangle" class="mt-0.5 shrink-0" fixed-width aria-hidden="true" />
            <span>{{ warning }}</span>
        </li>
    </ul>
</template>
