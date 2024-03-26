<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 09 Feb 2024 02:33:36 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {ref, watchEffect} from 'vue'
import {useForm} from '@inertiajs/vue3'
import {trans} from 'laravel-vue-i18n'
import ValidationErrors from '@/Components/ValidationErrors.vue'
import PureInput from '@/Components/Pure/PureInput.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import Layout from '@/Layouts/GrpAuth.vue';

defineOptions({ layout: Layout })

const isPasswordSame = ref(false)
const repeatPassword = ref('')

const formReset = useForm({
    password: '',
})

const submitResetPassword = () => {
    // grp.reset-password.email.update TODO: Use this route if come from email
    formReset.patch(route('grp.reset-password.update'), {})
}


watchEffect(() => {
    formReset.password == repeatPassword.value ? isPasswordSame.value = true : isPasswordSame.value = false
})
</script>

<template>

    <div class="space-y-4 text-gray-600">

        <form class="space-y-8" @submit.prevent="submitResetPassword">
            <div class="text-center font-semibold text-xl">
                {{ trans("The Administrator ask you to reset password") }}
            </div>

            <div class="flex flex-col gap-y-4">
                <div class="">
                    <label for="password">{{ trans('New Password') }}</label>
                    <PureInput v-model="formReset.password" type="password" inputName="password" placeholder="Enter new password"/>
                    <div v-if="formReset.errors.password">{{ formReset.errors.password }}</div>
                </div>

                <div class="">
                    <label for="repeatPassword">{{ trans('Repeat New Password') }}</label>
                    <PureInput v-model="repeatPassword" type="password" inputName="repeatPassword" placeholder="Repeat your new password"/>
                    <div v-if="!isPasswordSame && repeatPassword && formReset.password" class="text-red-500 mt-1 text-sm">Password is not match</div>
                </div>
            </div>

            <div class="flex justify-center">
                <Button :style="isPasswordSame ? 'primary' : 'disabled'" :key="formReset.password + repeatPassword" :label="'Reset Password'" @click="submitResetPassword" class=""/>
            </div>
        </form>
    </div>
    <ValidationErrors/>
</template>
