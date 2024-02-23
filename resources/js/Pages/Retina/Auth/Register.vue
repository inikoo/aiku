<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import LoginPassword from '@/Components/Auth/LoginPassword.vue'
import ValidationErrors from '@/Components/ValidationErrors.vue'
import { trans } from 'laravel-vue-i18n'
import { onMounted, ref, nextTick, watch } from 'vue'
import Button from '@/Components/Elements/Buttons/Button.vue'

const form = useForm({
    contact_name: '',
    username: '',
    password: '',
    password_confirmation: '',
    remember: false,
})

const isLoading = ref(false)
const isPasswordRepeated = ref(true)

const submit = () => {
    isLoading.value = true
    form.post(route('retina.register.store'), {
        onError: () => isLoading.value = false,
        onFinish: () => form.reset('password'),
    })
}

const inputUsername = ref(null)
const inputContactName = ref(null)

onMounted(async () => {
    await nextTick()
    inputUsername.value?.focus()
})

watch(() => form.password_confirmation, () => {
    console.log('qq')
    if(form.password.length > 0) {
        console.log('ww')
        isPasswordRepeated.value = form.password_confirmation == form.password
    }
})

</script>

<template layout="RetinaAuth">
    <Head title="Login" />
    <h1 class="text-center text-2xl font-bold text-slate-800">Register</h1>
    <form class="space-y-6 mt-7">
        <div>
            <label for="login" class="block text-sm font-medium text-gray-700">{{ trans('Contact Name') }}</label>
            <div class="mt-1">
                <input v-model="form.contact_name" ref="inputContactName" id="contact_name" name="contact_name" :autofocus="true"
                       autocomplete="contact_name" required
                       class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
            </div>
        </div>

        <div>
            <label for="login" class="block text-sm font-medium text-gray-700">{{ trans('Username') }}</label>
            <div class="mt-1">
                <input v-model="form.username" ref="inputUsername" id="username" name="username" :autofocus="true"
                    autocomplete="username" required
                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
            </div>
        </div>

        <!-- Section: Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700"> {{ trans('Password') }} </label>
            <div class="mt-1 flex flex-col rounded-md shadow-sm">
                <LoginPassword :showProcessing="false" id="password" name="password" :form="form" fieldName="password" />
            </div>
        </div>

        <!-- Section: Password repeat -->
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700"> {{ trans('Repeat password') }} </label>
            <div class="mt-1 flex flex-col rounded-md shadow-sm" :class="[isPasswordRepeated ? '' : 'ring-2 ring-red-500']">
                <LoginPassword :showProcessing="false" id="password_confirmation" name="password_confirmation" :form="form" fieldName="password_confirmation" />
            </div>
            <p class="text-red-500 italic" :class="isPasswordRepeated ? 'invisible' : ''">*{{ trans("Password doesn't match") }}</p>
        </div>

        <!-- <div class="flex items-center justify-between">
            <div class="flex items-center">
                <Checkbox name="remember-me" id="remember-me" v-model:checked="form.remember" />
                <label for="remember-me" class="ml-2 block text-sm text-gray-900"> {{ trans('Remember me') }} </label>
            </div>
        </div> -->

        <div class="space-y-2">
            <Button full @click.prevent="submit" :loading="isLoading" label="Register"> </Button>
            <p class="text-gray-600">Already have an account? <Link as="span" :href="route('retina.login.show')" class="cursor-pointer font-bold hover:underline">Login</Link></p>
        </div>
    </form>

    <ValidationErrors />
</template>
