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
import FooterTab from '@/Components/Footer/FooterTab.vue'
import { trans, loadLanguageAsync } from 'laravel-vue-i18n'
import { useForm } from '@inertiajs/vue3'
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue'
import type { Language } from '@/types/Locale'
import LoadingText from '@/Components/Utils/LoadingText.vue'

const form = useForm<{
    language_id: number | null
}>({
    language_id: null,
})

const locale = useLocaleStore()

const onSelectLanguage = (language: Language) => {
    if(form.language_id != language.id) {
        form.language_id = language.id
        form.patch(route('grp.models.profile.update'), {
            preserveScroll: true,
            onSuccess: () => (
                locale.language = language,
                loadLanguageAsync(language.code)
            )
        })
    }
    
}

</script>

<template>
    <Popover v-slot="{ open }" class="relative h-full">
        <PopoverButton :class="open ? 'bg-white/50 text-white' : 'hover:bg-white/25 text-gray-200'"
            class="group inline-flex items-center px-3 h-full font-medium ">
            <!-- Label: Loading on select language -->
            <template v-if="form.processing">
                <FontAwesomeIcon icon='fad fa-spinner-third' class='animate-spin mr-2' aria-hidden='true' />
                <LoadingText class="h-full font-extralight text-xs flex items-center gap-x-1 leading-none" />
            </template>

            <!-- Label: Language -->
            <template v-else>
                <FontAwesomeIcon icon="fal fa-language" class="text-xs mr-1 h-5 " />
                <div class="h-full font-extralight text-xs flex items-center gap-x-1 leading-none">
                    {{ locale.language.name }}
                </div>
            </template>
        </PopoverButton>

        <transition name="headlessui">
            <PopoverPanel class="absolute bottom-full right-0 z-10 sm:px-0">
                <FooterTab tabName="language">
                    <template #default>
                        <form v-if="Object.keys(locale.languageOptions).length > 0"
                            v-for="(language, index) in locale.languageOptions"
                            @submit.prevent="() => onSelectLanguage(language)"
                            :class="[ language.id == locale.language.id ? 'bg-white/50' : 'hover:bg-white/20 ', 'grid ']"
                        >
                            <button
                                type="submit"
                                class="py-1.5">
                                {{ language.name }}
                            </button>
                        </form>
                        <div v-else class="grid pt-2.5 pb-1.5">{{ trans('Nothing to show here') }}</div>
                    </template>
                </FooterTab>
            </PopoverPanel>
        </transition>
    </Popover>
</template>
