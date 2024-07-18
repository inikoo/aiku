<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import LayoutRetinaAuth from '@/Layouts/RetinaAuth.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import PureInput from '@/Components/Pure/PureInput.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faArrowLeft, faCheckCircle } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { ref } from 'vue'
import InputError from '@/Components/InputError.vue'
defineOptions({ layout: null })
</script>

<template>
    <Head title="Preparing you account" />
    <div>
        <div class="text-center font-bold text-xl">We still prepare your account</div>
        <div class="mt-2 mb-4 text-sm text-gray-600 italic">
            please come back shortly.
        </div>
    </div>
</template>
