<!--
  - Author Louis Perez
  - Created on 15-10-2026-19h-15m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { ref } from "vue"
import { router } from "@inertiajs/vue3"
import { notify } from "@kyvg/vue3-notification"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { routeType } from "@/types/route"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faCheckCircle, faArrowRight, faLock } from "@fal"
library.add(faCheckCircle, faArrowRight, faLock)

interface SubStage {
    key: string
    number: string
    label: string
    role: string
    state: string
    state_label: string
    due_at: string | null
    is_late: boolean
}

interface Position {
    position: number
    label: string
    current: boolean
    done: boolean
    stages: SubStage[]
}

const props = defineProps<{
    stages: Position[]
    stageMove: {
        can_move: boolean
        reason: string | null
        is_allowed: boolean
        is_done: boolean
        route: routeType & { method: string }
    }
}>()

const moving = ref(false)

function moveToNextStage() {
    router.patch(route(props.stageMove.route.name, props.stageMove.route.parameters), {}, {
        preserveScroll: true,
        onStart: () => moving.value = true,
        onFinish: () => moving.value = false,
        onError: (errors) => notify({ title: ctrans("Stage not moved"), text: Object.values(errors).join(" "), type: "error" }),
    })
}

const formatDate = (date: string | null) => date ? useFormatTime(date, { formatTime: "d MMM" }) : null
</script>

<template>
    <div class="border-b border-gray-200">
        <div class="grid grid-cols-2 gap-px bg-gray-200 md:grid-cols-4 xl:grid-cols-7">
            <div
                v-for="position in stages"
                :key="position.position"
                class="bg-white p-3 text-sm"
                :class="position.current ? 'bg-[--app-accent-soft]' : ''"
            >
                <div class="flex items-center gap-2 font-medium" :class="position.done || position.current ? 'text-gray-800' : 'text-gray-400'">
                    <FontAwesomeIcon v-if="position.done" icon="fal fa-check-circle" class="text-green-600" fixed-width aria-hidden="true" />
                    <span v-else class="w-4 text-center tabular-nums">{{ position.position }}</span>
                    {{ position.label }}
                </div>
                <div v-for="stage in position.stages" :key="stage.key" class="mt-1 flex items-center justify-between gap-2 text-xs">
                    <span class="truncate" :class="stage.state === 'completed' ? 'text-gray-500' : 'text-gray-700'">
                        <span v-if="position.stages.length > 1" class="tabular-nums text-gray-400">{{ stage.number }}</span>
                        {{ stage.role }}
                    </span>
                    <span class="shrink-0 tabular-nums" :class="stage.is_late ? 'text-red-600' : 'text-gray-400'" v-tooltip="stage.state_label">
                        {{ formatDate(stage.due_at) }}
                    </span>
                </div>
            </div>
        </div>

        <div v-if="!stageMove.is_done && stageMove.is_allowed" class="flex flex-wrap items-center justify-end gap-3 px-4 py-2">
            <span v-if="!stageMove.can_move && stageMove.reason" class="flex items-center gap-1.5 text-sm text-amber-700">
                <FontAwesomeIcon icon="fal fa-lock" fixed-width aria-hidden="true" />
                {{ stageMove.reason }}
            </span>
            <Button
                type="tertiary"
                :label="ctrans('Move to next stage')"
                :disabled="!stageMove.can_move"
                :loading="moving"
                :tooltip="!stageMove.can_move ? stageMove.reason ?? undefined : undefined"
                @click="moveToNextStage"
            >
                <template #icon>
                    <FontAwesomeIcon icon="fal fa-arrow-right" class="text-green-600" fixed-width aria-hidden="true" />
                </template>
            </Button>
        </div>
    </div>
</template>
