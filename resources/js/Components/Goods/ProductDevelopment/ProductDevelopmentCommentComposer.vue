<!--
  - Author Louis Perez
  - Created on 16-10-2026-17h-10m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed, ref } from "vue"
import { router } from "@inertiajs/vue3"
import Button from "@/Components/Elements/Buttons/Button.vue"
import TicketComposer from "@/Components/Tickets/TicketComposer.vue"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faPaperPlane } from "@fal"
library.add(faPaperPlane)

export interface Mentionable {
    id: number
    name: string
    username: string
    avatar: Record<string, string> | null
}

const props = defineProps<{
    route: routeType
    mentionables: Mentionable[]
    parentId?: number | null
    placeholder?: string
    rows?: number
    extra?: Record<string, string | number | null | Record<string, number>>
    only?: string[]
}>()

const emits = defineEmits<{
    (e: "posted"): void
}>()

const body = ref("")
const images = ref<File[]>([])
const posting = ref(false)
const error = ref<string | null>(null)

const mentionable = computed(() => props.mentionables.map((person) => ({ username: person.username, name: person.name })))
const canPost = computed(() => (body.value.trim() !== "" || images.value.length > 0) && !posting.value)

function post() {
    if (!canPost.value) {
        return
    }
    router.post(route(props.route.name, props.route.parameters), { body: body.value, images: images.value, parent_id: props.parentId ?? null, ...(props.extra ?? {}) }, {
        forceFormData: true,
        preserveScroll: true,
        only: props.only ?? ["comments", "recentActivity"],
        onStart: () => { posting.value = true; error.value = null },
        onFinish: () => posting.value = false,
        onError: (errors) => error.value = Object.values(errors)[0] ?? null,
        onSuccess: () => { body.value = ""; images.value = []; emits("posted") },
    })
}
</script>

<template>
    <div @keydown.ctrl.enter.prevent="post" @keydown.meta.enter.prevent="post">
        <TicketComposer
            v-model:body="body"
            v-model:images="images"
            :mentionable="mentionable"
            :rows="rows ?? 3"
            :placeholder="placeholder ?? ctrans('Write a comment, type @ to mention someone')"
        />
        <div class="mt-1.5 flex items-center justify-between gap-2">
            <span class="text-xs" :class="error ? 'text-red-600' : 'text-gray-400'">{{ error ?? ctrans("Ctrl + Enter to post") }}</span>
            <Button type="primary" size="xs" icon="fal fa-paper-plane" :label="parentId ? ctrans('Reply') : ctrans('Post')" :loading="posting" :disabled="!canPost" class="disabled:cursor-not-allowed disabled:opacity-50" @click="post" />
        </div>
    </div>
</template>
