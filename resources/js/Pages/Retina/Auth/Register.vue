<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref, onMounted, nextTick, watch } from 'vue';
import Button from '@/Components/Elements/Buttons/Button.vue';
import PureInput from '@/Components/Pure/PureInput.vue';
import LayoutIris from '@/Layouts/Iris.vue';
import Password from 'primevue/password';
import Multiselect from "@vueform/multiselect"

// Set default layout
defineOptions({ layout: LayoutIris });

// Define form using Inertia's useForm
const form = useForm({
  first_name: '',
  last_name: '',
  email: '',
  phone_number: '',
  business_name: '',
  website: '',
  what_you_sell: '',
  shipments_per_week: '',
  goods_size_and_weight: '',
  password: '',
  password_confirmation: '',
  interests: []
});

// Define reactive variables
const isLoading = ref(false);


const optionsSend = [
  '0-50', "51-100", "100+"
]
// Form submission handler
const submit = () => {
  isLoading.value = true;
  form.post(route('retina.register.store'), {
    onError: () => {
      isLoading.value = false;
    },
    onFinish: () => {
      form.reset();
    },
  });
};

const interestsList = ref([
  { label: 'Pallets Storage', value: 'pallets_storage' },
  { label: 'Items Storage', value: 'items_storage' },
  { label: 'Dropshipping', value: 'dropshipping' },
]);

// Autofocus first PureInput on mount
onMounted(async () => {
  await nextTick();
  document.getElementById('first-name')?.focus();
});
</script>

<template>
  <form @submit.prevent="submit" class="space-y-12 px-14 py-10">
    <div class="border-b border-gray-900/10 pb-12">
      <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

        <!-- First Name -->
        <div class="sm:col-span-3">
          <label for="first-name" class="block text-sm font-medium text-gray-900">First Name</label>
          <div class="mt-2">
            <PureInput v-model="form.first_name" type="text" id="first-name" name="first_name" required />
          </div>
        </div>

        <!-- Last Name -->
        <div class="sm:col-span-3">
          <label for="last-name" class="block text-sm font-medium text-gray-900">Last Name</label>
          <div class="mt-2">
            <PureInput v-model="form.last_name" type="text" id="last-name" name="last_name" required />
          </div>
        </div>

        <!-- Email -->
        <div class="sm:col-span-3">
          <label for="email" class="block text-sm font-medium text-gray-900">Email</label>
          <div class="mt-2">
            <PureInput v-model="form.email" type="email" id="email" name="email" required />
          </div>
        </div>

        <!-- Phone Number -->
        <div class="sm:col-span-3">
          <label for="phone-number" class="block text-sm font-medium text-gray-900">Phone Number</label>
          <div class="mt-2">
            <PureInput v-model="form.phone_number" type="text" id="phone-number" name="phone_number" required />
          </div>
        </div>

        <!-- Business Name -->
        <div class="sm:col-span-6">
          <label for="business-name" class="block text-sm font-medium text-gray-900">Business Name</label>
          <div class="mt-2">
            <PureInput v-model="form.business_name" type="text" id="business-name" name="business_name" required />
          </div>
        </div>

        <!-- Website -->
        <div class="sm:col-span-6">
          <label for="website" class="block text-sm font-medium text-gray-900">Website</label>
          <div class="mt-2">
            <PureInput v-model="form.website" type="url" id="website" name="website" required />
          </div>
        </div>

        <!-- What Do You Sell -->
        <div class="sm:col-span-6">
          <label for="what-you-sell" class="block text-sm font-medium text-gray-900">What Do You Sell</label>
          <div class="mt-2">
            <PureInput v-model="form.what_you_sell" type="text" id="what-you-sell" name="what_you_sell" required />
          </div>
        </div>

        <!-- Shipments Sent Per Week -->
        <div class="sm:col-span-3">
          <label for="shipments-per-week" class="block text-sm font-medium text-gray-900">Shipments Sent Per
            Week</label>
          <div class="mt-2">
            <select v-model="form.shipments_per_week" id="shipments-per-week" name="shipments_per_week" required
              class="block w-full mt-1 py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
              <option value="" disabled selected>Select shipments per week</option>
              <option v-for="option in optionsSend" :key="option" :value="option">{{ option }}</option>
            </select>
          </div>
        </div>


        <!-- Goods Size and Weight -->
        <div class="sm:col-span-3">
          <label for="goods-size-and-weight" class="block text-sm font-medium text-gray-900">Size and Weight of Your
            Goods</label>
          <div class="mt-2">
            <PureInput v-model="form.goods_size_and_weight" type="text" id="goods-size-and-weight"
              name="goods_size_and_weight" required />
          </div>
        </div>

        <div class="sm:col-span-6 flex flex-col">
          <label class="block text-sm font-medium text-gray-900">User Interests</label>
          <div class="mt-2 flex flex-wrap gap-6">
            <!-- Loop through the interests -->
            <div v-for="interest in interestsList" :key="interest.value"
              class="flex items-center space-x-3 border-2 py-3 px-6 rounded-lg transition-all duration-200 ease-in-out hover:bg-indigo-50 hover:shadow-lg">
              <input v-model="form.interests" :type="'checkbox'" :id="interest.value" :value="interest.value"
                class="h-5 w-5 text-indigo-600 border-gray-300 rounded-sm focus:ring-2 focus:ring-indigo-500" />
              <label :for="interest.value" class="text-sm font-medium text-gray-900">{{ interest.label }}</label>
            </div>
          </div>
        </div>

        <!-- Password -->
        <div class="sm:col-span-3">
          <label for="password" class="block text-sm font-medium text-gray-900">Password</label>
          <div class="mt-2 password">
            <PureInput v-model="form.password" :type="'password'" />
          </div>
        </div>

        <!-- Retype Password -->
        <div class="sm:col-span-3">
          <label for="password-confirmation" class="block text-sm font-medium text-gray-900">Retype Password</label>
          <div class="mt-2 password">
            <PureInput v-model="form.password_confirmation" :type="'password'" />
          </div>
        </div>


      </div>
    </div>

    <!-- Submit Button -->
    <div class="flex justify-end">
      <button type="submit"
        class="inline-flex items-center px-6 bg-black py-3 border border-transparent text-sm font-medium rounded-md shadow-sm text-white  focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        <span v-if="isLoading" class="loader mr-2"></span>
        Register
      </button>
    </div>
  </form>
</template>

<style scoped lang="scss">
.password {
  .p-PureInputtext {
    width: 100% !important;
  }
}
</style>