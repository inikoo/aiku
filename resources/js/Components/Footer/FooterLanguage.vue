<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 04 Sep 2023 10:22:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
// This file is used on CustomerApp, PublicApp

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faLanguage } from '@fal'
import { faSpinnerThird } from '@fad'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faLanguage, faSpinnerThird)
import { useLocaleStore } from "@/Stores/locale"
import { useLayoutStore } from "@/Stores/layout"
import FooterTab from '@/Components/Footer/FooterTab.vue'
import { trans, loadLanguageAsync } from 'laravel-vue-i18n'
import { useForm } from '@inertiajs/vue3'
const form = useForm({
    language_id: null,
})
const locale = useLocaleStore()
const layout = useLayoutStore()

defineProps<{
    isTabActive: string | boolean
}>()

defineEmits<{
    (e: 'isTabActive', value: boolean | string): void
}>()

</script>

<template>
    <!-- <div class="fixed top-10 right-48 bg-red-500 "><pre>{{form}}</pre></div> -->
    <div class="relative h-full flex z-50 select-none justify-center items-center px-8 cursor-pointer"
        :class="[
            isTabActive == 'language'
                ? layout.app.name === 'org' ? 'bg-gray-200 text-gray-700' : 'bg-gray-700 text-gray-300'
                : layout.app.name === 'org' ? 'hover:bg-gray-300' : 'text-gray-300 hover:bg-gray-600',
            ,
        ]"
    >
        <FontAwesomeIcon v-if="form.processing" icon='fad fa-spinner-third' class='animate-spin mr-2' aria-hidden='true' />
        <FontAwesomeIcon v-else icon="fal fa-language" class="text-xs mr-1 h-5 " />
        <div class="h-full font-extralight text-xs flex items-center gap-x-1 leading-none">
            {{ locale.language.code }}
        </div>
        <div class="absolute inset-0 bg-transparent" @click="isTabActive == 'language' ? $emit('isTabActive', !isTabActive) : $emit('isTabActive', 'language')" />

        <FooterTab v-if="isTabActive === 'language'" :tabName="`language`">
            <template #default>
                <!-- <div v-if="Object.keys(locale.languageOptions).length > 0" v-for="(option, index) in locale.languageOptions"
                    :class="[ locale.language.id == index ? 'bg-gray-400 text-gray-100' : 'text-gray-100 hover:bg-gray-500', 'grid py-1.5']"
                    @click="locale.language = option, loadLanguageAsync(option.code)"
                >
                    {{ option.name }}
                </div> -->
                <form v-if="Object.keys(locale.languageOptions).length > 0"
                    @submit.prevent="form.patch(route('grp.models.profile.update'))"
                    v-for="(option, index) in locale.languageOptions"
                    :class="[ option.id == locale.language.id ? 'bg-gray-400' : 'hover:bg-gray-300 hover:text-gray-700 ', 'grid ']"
                >
                    <button @click="form.language_id = option.id, locale.language = option, loadLanguageAsync(locale.language.code)" type="submit" class="py-1.5">
                        {{ option.name }}
                    </button>
                </form>
                <div v-else class="grid pt-2.5 pb-1.5">{{ trans('Nothing to show here') }}</div>
            </template>
        </FooterTab>
    </div>
</template>
