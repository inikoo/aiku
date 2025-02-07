<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';

// Mendapatkan data customer dari props
const customer = usePage().props.layout.customer;
const fulfilment = usePage().props.layout.fulfilment;
console.log(usePage().props)
</script>

<template>
  <div class="p-8 pb-3 text-4xl font-bold">
    Welcome, {{ customer.contact_name }}!
  </div>

  <!-- Container untuk card -->
  <div v-if="customer?.status == 'pending_approval'" class="grid grid-cols-3 gap-6 p-6">
    <!-- Card Informasi Perusahaan -->
    <div class="col-span-3 bg-green-50 rounded-lg shadow-xl overflow-hidden border border-green-300 p-6">
      <h4 class="text-lg font-semibold text-green-800">{{ trans('Thank you for applying!')}}</h4>
      <p class="mt-2 text-sm text-green-700">{{trans('Your application is under review. Please wait for further information from us.') }}</p>
    </div>


    <div
      class="col-span-2 bg-white rounded-lg shadow-xl overflow-hidden border hover:shadow-2xl transition-shadow duration-300">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-xl font-semibold text-gray-900">{{trans("My Details")}}</h3>
        <p class="mt-1 text-sm text-gray-500">{{trans("Company and contact information.")}}</p>
      </div>
      <div class="p-6 grid grid-cols-2 gap-4">
        <div>
          <h4 class="text-sm font-medium text-gray-500">{{trans("Company Name")}}</h4>
          <p class="mt-1 text-lg font-semibold text-gray-900">{{ customer.company_name }}</p>
        </div>
        <div>
          <h4 class="text-sm font-medium text-gray-500">{{trans("Contact Name")}}</h4>
          <p class="mt-1 text-lg font-semibold text-gray-900">{{ customer.contact_name }}</p>
        </div>
        <div>
          <h4 class="text-sm font-medium text-gray-500">{{trans("Email")}}</h4>
          <p class="mt-1 text-lg font-semibold text-gray-900">{{ customer.email }}</p>
        </div>
        <div>
          <h4 class="text-sm font-medium text-gray-500">{{trans('Phone')}}</h4>
          <p class="mt-1 text-lg font-semibold text-gray-900">{{ customer.phone }}</p>
        </div>
        <div class="col-span-2">
          <h4 class="text-sm font-medium text-gray-500">{{trans('Address')}}</h4>
          <p class="mt-1 text-sm text-gray-700" v-html="customer.address.formatted_address"></p>
        </div>
        <div>
          <h4 class="text-sm font-medium text-gray-500">{{trans("Status")}}</h4>
          <p class="mt-1 text-sm font-semibold" :class="{
            'text-green-700': customer.state === 'active',
            'text-red-700': customer.state !== 'active'
          }">
            {{ customer.state }}
          </p>
        </div>
      </div>
    </div>


    <div class="rounded-lg shadow-2xl overflow-hidden border border-[#0F1626] h-fit">
      <div class="px-6 py-4 border-b border-[#0F1626] bg-gradient-to-r from-gray-900 to-gray-800">
        <h4 class="text-2xl font-bold text-white">{{trans("Contact Us")}}</h4>
      </div>
      <div class="p-6 bg-white">
        <div class="mb-6">
          <h4 class="text-sm font-medium text-gray-500">{{trans("Email")}}</h4>
          <p class="mt-2 text-lg font-semibold text-[#0F1626] hover:text-gray-500">
            <a :href="'mailto:' + 'info@aw-fulfilment.co.uk'" class="hover:underline">{{fulfilment.email}}</a>
          </p>
        </div>
        <div class="mb-6">
          <h4 class="text-sm font-medium text-gray-500">{{trans("Phone")}}</h4>
          <p class="mt-2 text-lg font-semibold text-[#0F1626] hover:text-gray-500">
            {{fulfilment.phone}}
          </p>
        </div>
        <div class="mb-6">
          <h4 class="text-sm font-medium text-gray-500">{{trans("Office Address")}}</h4>
          <div v-html="fulfilment?.address?.formatted_address" class="mt-2 text-lg font-semibold text-gray-900"/>
          
        </div>
      </div>
    </div>

  </div>
</template>
