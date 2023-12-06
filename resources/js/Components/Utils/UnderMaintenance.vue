<script setup lang="ts">
import Button from '@/Components/Elements/Buttons/Button.vue'
import { router } from '@inertiajs/vue3'
import { trans } from 'laravel-vue-i18n'


import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faRocketLaunch, faDoNotEnter } from '@fas'
import { faConstruction, faDraftingCompass } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faConstruction, faRocketLaunch, faDoNotEnter, faDraftingCompass)

const props = defineProps<{
    data: {
        id: integer,
        slug: string
        state: string
        status: string
        created_at: string
        updated_at: string
    }
}>()

// Handle Button: Launch
const handleLaunch = () => {
    router.patch(
        route('org.models.website.state.update',{website:props.data.id}),
        { state: 'live'},
    )
}
</script>

<template>
    <div class="grid min-h-full place-items-center">
        <div class="text-center">
            <FontAwesomeIcon v-if="data.state === 'closed'" icon='fas fa-do-not-enter' class='h-12 text-red-500' aria-hidden='true' />
            <FontAwesomeIcon v-else icon='fal fa-construction' class='h-12' aria-hidden='true' />

            <div class="mt-4 text-3xl font-bold tracking-tight text-gray-700 sm:text-5xl">
                <h3 v-if="data.state == 'in-process'">
                    {{ trans("Page is under construction") }}
                </h3>
                <h3 v-else-if="data.state === 'live'">
                    <div v-if="!data.status">
                        {{ trans("Page is under maintenance") }}
                    </div>
                </h3>
                <h3 v-else-if="data.state == 'closed'">
                    {{ trans("Page closed") }}
                </h3>
            </div>

            <div class="mt-10 flex items-center justify-center gap-x-6">
                <Button v-if="data.state === 'in-process'" :style="`secondary`" @click="router.visit(route('org.websites.workshop',data.slug))">
                    <div class="flex items-center gap-x-1">
                        <FontAwesomeIcon icon='fal fa-drafting-compass' class='' aria-hidden='true' />
                        {{ trans("Workshop") }}
                    </div>
                </Button>
                <Button v-if="data.state === 'in-process'" :style="`primary`" @click="handleLaunch">
                    <div class="flex items-center gap-x-1">
                        <span>{{ trans("Launch") }}</span>
                        <FontAwesomeIcon icon='fas fa-rocket-launch' class='' aria-hidden='true' />
                    </div>
                </Button>
                <Button v-if="data.state === 'live' && !data.status" :style="`primary`" @click="handleLaunch">
                    <div class="flex items-center gap-x-1">
                        <span>{{ trans("Restore") }}</span>
                        <FontAwesomeIcon icon='fas fa-rocket-launch' class='' aria-hidden='true' />
                    </div>
                </Button>
            </div>
        </div>
    </div>
</template>
