<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 10 May 2023 09:17:59 Malaysia Time, Pantai Lembeng, Bali, Id
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->
<script setup lang="ts">

import {computed} from 'vue'
import Multiselect from '@vueform/multiselect';

import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';



const props = defineProps<{
    form: any,
    fieldName: string,
    options: any
}>()


let addressValues=props.form[props.fieldName];



const countries = {};
for (const item in props.options.countriesAddressData) {
    countries[item] = props.options.countriesAddressData[item]['label'];
    // console.log("-------------------------------")
    // console.log(countries)
}

const comptCountries = computed(() => {
    return countries.sort()
})

const administrativeAreas = (countryID) => props.options.countriesAddressData[countryID]['administrativeAreas'];

const inAdministrativeAreas = (administrativeArea, countryID) => !!props.options.countriesAddressData[countryID]['administrativeAreas'].find(c => c.name === administrativeArea);

const addressFields = (countryID) => {
    return props.options.countriesAddressData[countryID]['fields'];
}

const handleChange = () => props.form.clearErrors();


</script>

<template>
    <div class="grid grid-cols-2 gap-3">
        <div class="col-span-2">
            <Multiselect :options="countries" v-model="addressValues['country_id']" searchable />
        </div>
        <template v-for="(addressFieldData,addressField) in addressFields(addressValues['country_id'])" :key="addressField">
            <div class="grid col-span-2">
                <div class="w-full ">
                    <div v-if="addressField==='administrative_area'" >
                        <label for="administrative_area" class="capitalize block text-sm font-medium text-gray-700">{{ addressFieldData.label }}</label>
                        <Multiselect v-if="administrativeAreas(addressValues['country_id']).length && (!addressValues['administrative_area'] || inAdministrativeAreas(addressValues['administrative_area'],addressValues['country_id']))"
                                :options="administrativeAreas(addressValues['country_id'])" :label="'name'" :value-prop="'name'"
                                v-model="addressValues['administrative_area']"/>
                        <input v-else v-model="addressValues['administrative_area']" type="text" name="administrative_area" id="administrative_area" autocomplete="password"
                               class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"/>
                    </div>
                    <div v-else >
                        <label :for="addressField" class="capitalize block text-xs font-medium text-gray-700">{{ addressFieldData.label }}
                            <span v-if="form.errors[addressField]" class="mt-2 text-sm text-red-600">{{form.errors[addressField] }}</span>
                        </label>
                        <input  @input="handleChange()"  v-model="addressValues[addressField]" type="text" name="address_line_2" :id="addressField" autocomplete="password"
                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"/>
                    </div>
                </div>
                <div class="w-5 self-end">
                    <FontAwesomeIcon icon="fas fa-exclamation-circle" v-if="form.errors[addressField]" class="h-5 w-5 text-red-500" aria-hidden="true" />

                </div>
            </div>
        </template>
    </div>
</template>


