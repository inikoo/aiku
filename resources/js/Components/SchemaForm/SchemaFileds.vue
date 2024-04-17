<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 14 Mar 2023 03:12:13 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

  <script setup lang="ts">

  import { routeType } from '@/types/route'
  import type { Component } from 'vue'
  
  import Input from '@/Components/Forms/Fields/Input.vue'
  import Phone from '@/Components/Forms/Fields/Phone.vue'
  import Date from '@/Components/Forms/Fields/Date.vue'
  import Theme from '@/Components/Forms/Fields/Theme.vue'
  import ColorMode from '@/Components/Forms/Fields/ColorMode.vue'
  import Avatar from '@/Components/Forms/Fields/Avatar.vue'
  import Password from '@/Components/Forms/Fields/Password.vue'
  import Textarea from '@/Components/Forms/Fields/Textarea.vue'
  import Select from '@/Components/Forms/Fields/Select.vue'
  import Radio from '@/Components/Forms/Fields/Radio.vue'
  import TextEditor from '@/Components/Forms/Fields/TextEditor.vue'
  import Address from "@/Components/Forms/Fields/Address.vue"
  import Country from "@/Components/Forms/Fields/Country.vue"
  import Currency from "@/Components/Forms/Fields/Currency.vue"
  import Language from "@/Components/Forms/Fields/Language.vue"
  import Permissions from "@/Components/Forms/Fields/Permissions.vue"
  import InputWithAddOn from '@/Components/Forms/Fields/InputWithAddOn.vue'
  import Checkbox from '@/Components/Forms/Fields/Checkbox.vue'
  import EmployeePosition from '@/Components/Forms/Fields/EmployeePosition.vue'
  import AppLogin from '@/Components/Forms/Fields/AppLogin.vue'
  import AppTheme from '@/Components/Forms/Fields/AppTheme.vue'
  import Interest from '@/Components/Forms/Fields/Interest.vue'
  
  
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
  import { faSave as fadSave, } from '@fad'
  import { faSave as falSave, faInfoCircle } from '@fal'
  import { faAsterisk, faQuestion } from '@fas'
  import { library } from '@fortawesome/fontawesome-svg-core'
  library.add(fadSave, faQuestion, falSave, faInfoCircle, faAsterisk)
  
  const props = defineProps<{
      field: any
      form : any,
      fieldData: {
          type: string
          label: string
          verification?: {
              route: routeType
          }
          value: any
          mode?: string
          required?: boolean
          options?: {}[]
          full: boolean
          noTitle?: boolean
          noSaveButton?: boolean  // Button: save
      }
  }>()
  
  
  const components: {[key: string]: Component} = {
      'select': Select,
      'input': Input,
      'inputWithAddOn': InputWithAddOn,
      'phone': Phone,
      'date': Date,
      'theme': Theme,
      'colorMode': ColorMode,
      'password': Password,
      'avatar': Avatar,
      'textarea': Textarea,
      'radio': Radio,
      'textEditor': TextEditor,
      'address': Address,
      'country': Country,
      'currency': Currency,
      'language': Language,
      'permissions': Permissions,
      'checkbox': Checkbox,
      'employeePosition': EmployeePosition,
      'app_login': AppLogin,
      'app_theme': AppTheme,
      'interest': Interest,
  }
  
  const getComponent = (componentName: string) => {
      return components[componentName] ?? null;
  };
  
  </script>
  
  <template>
      <form class="divide-y divide-gray-200 w-full" :class="props.fieldData.full ? '' : 'max-w-2xl'">
          <dl class="pb-4 sm:pb-5">
              <!-- Title -->
              <dt v-if="!fieldData.noTitle" class="text-sm font-medium text-gray-400 capitalize">
                  <div class="inline-flex items-start leading-none"><FontAwesomeIcon v-if="fieldData.required" :icon="['fas', 'asterisk']" class="font-light text-[12px] text-red-400 mr-1"/>{{ fieldData.label }}</div>
              </dt>
  
              <dd :class="props.fieldData.full ? 'sm:col-span-3' : fieldData.noTitle ? 'sm:col-span-3' : 'sm:col-span-2'" class="flex items-start text-sm text-gray-700 sm:mt-0">
                  <div class="relative w-full">
                      <component :is="getComponent(fieldData.type)" :form="form" :fieldName="field"
                          :options="fieldData.options" :fieldData="fieldData">
                      </component>
  
                      <!-- Verification: Label -->
                      <div v-if="labelVerification" class="mt-1" :class="classVerification">
                          <FontAwesomeIcon icon='fal fa-info-circle' class='opacity-80' aria-hidden='true' />
                          <span class="ml-1 font-medium">{{ labelVerification }}</span>
                      </div>
                  </div>
              </dd>
          </dl>
      </form>
  </template>
  