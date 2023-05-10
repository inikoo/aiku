<template>
    <div class=" w-fit">
        <!-- <p class="text-sm font-medium text-gray-700" aria-hidden="true">
            {{ pageBody.layout.profile.fields["avatar"].label }}
        </p>
        <div class="mt-1 lg:hidden">
            <div class="flex items-center">
                <div class="inline-block h-12 w-12 flex-shrink-0 overflow-hidden rounded-full" aria-hidden="true">
                    <img id="avatar_mobile" class="h-full w-full rounded-full" :src="route('media.group.show', 1)" alt="" />
                </div>
                <div class="ml-5 rounded-md shadow-sm">
                    <div
                        class="group relative flex items-center justify-center rounded-md border border-gray-300 py-2 px-3 focus-within:ring-2 focus-within:ring-sky-500 focus-within:ring-offset-2 hover:bg-gray-50">
                        <label for="mobile-user-photo"
                            class="pointer-events-none relative text-sm font-medium leading-4 text-gray-700">
                            <span>{{ trans("Change") }}</span>
                            <span class="sr-only">
                                {{ pageBody.layout.profile.fields["avatar"].info }}
                            </span>
                        </label>
                        <input id="mobile-user-photo" name="user-photo" type="file"
                            @input="avatarUploaded($event.target.files[0])"
                            class="absolute h-full w-full cursor-pointer rounded-md border-gray-300 opacity-0" />
                    </div>
                </div>
            </div>
        </div>
        <div class="relative hidden overflow-hidden rounded-full lg:block">
            <img class="relative h-40 w-40 rounded-full" :src="route('media.group.show', 1)" alt="" />
            <label id="desktop-user-photo-mask" for="desktop-user-photo"
                class="absolute inset-0 flex h-full w-full items-center justify-center bg-black bg-opacity-50 text-sm font-medium text-white opacity-0 hover:opacity-100">
                <span>{{ trans("Change") }}</span>
                <span class="sr-only">
                    {{ pageBody.layout.profile.fields["photo"].info }}</span>
                <input type="file" @input="avatarUploaded($event.target.files[0])" id="desktop-user-photo" name="user-photo"
                    class="absolute inset-0 h-full w-full cursor-pointer rounded-md border-gray-300 opacity-0" />
            </label>
        </div>
        <div v-if="profileForm.errors.avatar" class="text-red-700">
            {{ profileForm.errors.avatar }}
        </div> -->
    </div>
</template>

<script setup>
import { trans } from "laravel-vue-i18n"
const props = defineProps(['form', 'fieldName', 'option'])
console.log(props.form)
console.log(props.fieldName)
console.log(props.option)
const avatarUploaded = (file) => {
    props.profileForm.avatar = file
    const reader = new FileReader()
    reader.readAsDataURL(file)
    reader.onload = (e) => {
        props.profile.avatar = e.target.result
    }
}


</script>