<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Tue, 20 Sept 2022 19:04:29 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup>

</script>

<template>

    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <img class="mx-auto h-12 w-auto" src="/art/logo-name.png" alt="Pika" />
            <h2 class="mt-6 text-center text-3xl text-indigo-600">@{{$page.props.tenant.code}}</h2>

        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">

                <slot></slot>
            </div>
        </div>
    </div>
</template>
