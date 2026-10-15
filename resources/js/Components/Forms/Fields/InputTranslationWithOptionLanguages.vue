<script setup lang="ts">
import { ref, computed, watch, onUnmounted } from "vue"
import { router } from "@inertiajs/vue3"
import InputText from "primevue/inputtext"
import Dialog from "primevue/dialog"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faRobot, faSyncAlt, faTimes, faCheck, faPlus, faExclamationTriangle, faSpinnerThird } from "@fal"
import { notify } from "@kyvg/vue3-notification"
import Button from "@/Components/Elements/Buttons/Button.vue"
import PureMultiselect from "@/Components/Pure/PureMultiselect.vue"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"
library.add(faRobot, faSyncAlt, faTimes, faCheck, faPlus, faExclamationTriangle, faSpinnerThird)

interface Language {
  code: string
  name?: string
}

interface TranslationStatus {
  state: "missing" | "outdated" | "review" | "ok"
  auto: boolean
}

interface TranslationProgress {
  state: "running" | "done"
  done: number
  total: number
  failed: string[]
}

const props = defineProps<{
  form: Record<string, any>
  fieldName: string
  fieldData: {
    languages: Record<string, Language>
    statuses?: Record<string, TranslationStatus>
    translate_route?: routeType
    approve_route?: routeType
    trade_unit_id?: number
    is_translating?: boolean
  }
}>()

const languageOptions = computed(() => {
  return Object.values(props.fieldData.languages || {})
})

const localValues = ref<Record<string, string>>({})

const isSyncing = ref(false)

const isEqual = (a: any, b: any) => {
  return JSON.stringify(a) === JSON.stringify(b)
}

watch(
  () => props.form[props.fieldName],
  (val) => {
    if (isSyncing.value) return

    if (val && typeof val === "object") {
      if (isEqual(val, localValues.value)) return

      isSyncing.value = true
      localValues.value = { ...val }
      isSyncing.value = false
    }
  },
  { immediate: true, deep: true }
)

watch(
  localValues,
  (val) => {
    if (isSyncing.value) return

    if (isEqual(val, props.form[props.fieldName])) return

    isSyncing.value = true
    props.form[props.fieldName] = { ...val }
    isSyncing.value = false
  },
  { deep: true }
)

const statuses = computed(() => props.fieldData.statuses ?? {})

const languageCodes = computed(() => {
  return [...new Set([...Object.keys(statuses.value), ...Object.keys(localValues.value)])]
})

const getLabel = (code: string) => {
  const lang = languageOptions.value.find(l => l.code === code)
  return lang?.name || code
}

const isFilled = (code: string) => {
  const v = localValues.value[code]
  return typeof v === "string" && v.trim().length > 0
}

const isOutdated = (code: string) => isFilled(code) && statuses.value[code]?.state === "outdated"

const isAuto = (code: string) => isFilled(code) && !!statuses.value[code]?.auto

const needsReview = (code: string) => isFilled(code) && statuses.value[code]?.state === "review"

const stateLabel = (code: string) => {
  if (!isFilled(code)) return ctrans("Missing")
  if (isOutdated(code)) return ctrans("Outdated")
  if (needsReview(code)) return ctrans("Needs review")
  return ctrans("Filled")
}

const chipClass = (code: string) => {
  if (!isFilled(code)) return "bg-gray-50 border-dashed border-gray-300 text-gray-500"
  if (isOutdated(code) || needsReview(code)) return "bg-amber-50 border-amber-300 text-amber-700"
  return "bg-green-50 border-green-200 text-green-700"
}

const missingCount = computed(() => Object.keys(statuses.value).filter(code => !isFilled(code)).length)

const outdatedCount = computed(() => Object.keys(statuses.value).filter(code => isOutdated(code)).length)

const reviewCodes = computed(() => Object.keys(statuses.value).filter(code => needsReview(code)))

const showDialog = ref(false)
const tempLanguage = ref<string | null>(null)
const tempValue = ref("")

const openDialog = (code: string | null = null) => {
  tempLanguage.value = code
  tempValue.value = code ? localValues.value[code] || "" : ""
  showDialog.value = true
}

const saveLanguage = () => {
  if (!tempLanguage.value) return

  localValues.value = {
    ...localValues.value,
    [tempLanguage.value]: tempValue.value
  }

  showDialog.value = false
}

const removeLanguage = (code: string) => {
  const newVal = { ...localValues.value }
  delete newVal[code]
  localValues.value = newVal
}

const isTranslating = ref(!!props.fieldData.is_translating)
const progress = ref<TranslationProgress | null>(null)
const showRetranslateConfirm = ref(false)
const isApproving = ref(false)

const channelName = computed(() => props.fieldData.trade_unit_id ? `grp.trade-unit.${props.fieldData.trade_unit_id}` : null)

if (channelName.value && window.Echo) {
  window.Echo.private(channelName.value)
    .listen(".name-translation-progress", (event: TranslationProgress) => {
      progress.value = event
      isTranslating.value = event.state === "running"

      if (event.state === "done") {
        router.reload({ preserveScroll: true } as any)
        if (event.failed.length) {
          notify({
            title: ctrans("Some names could not be translated"),
            text: `${event.failed.join(", ").toUpperCase()}. ${ctrans("Please try again or type them in.")}`,
            type: "error",
          })
        }
      }
    })
}

onUnmounted(() => {
  if (channelName.value && window.Echo) {
    window.Echo.leave(channelName.value)
  }
})

const translate = (includeOutdated: boolean) => {
  if (!props.fieldData.translate_route) return

  router.post(
    route(props.fieldData.translate_route.name, props.fieldData.translate_route.parameters),
    { include_outdated: includeOutdated },
    {
      preserveScroll: true,
      onSuccess: () => {
        isTranslating.value = true
        progress.value = null
      },
      onFinish: () => {
        showRetranslateConfirm.value = false
      },
    }
  )
}

const approve = (languages: string[]) => {
  if (!props.fieldData.approve_route || !languages.length) return

  router.post(
    route(props.fieldData.approve_route.name, props.fieldData.approve_route.parameters),
    { languages },
    {
      preserveScroll: true,
      onStart: () => { isApproving.value = true },
      onFinish: () => {
        isApproving.value = false
        showDialog.value = false
      },
    }
  )
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-2">
      <p class="text-xs text-gray-400">
        {{ ctrans("Add and manage language content") }}
      </p>

      <div class="flex flex-wrap gap-2">
        <Button
          v-if="fieldData.translate_route"
          type="tertiary"
          :label="missingCount ? `${ctrans('Translate missing')} (${missingCount})` : ctrans('Translate missing')"
          :disabled="isTranslating || (!missingCount && !outdatedCount)"
          @click="translate(false)"
        >
          <template #icon>
            <FontAwesomeIcon icon="fal fa-robot" class="text-[--app-accent]" fixed-width aria-hidden="true" />
          </template>
        </Button>
        <Button
          v-if="fieldData.translate_route && outdatedCount"
          type="tertiary"
          :label="`${ctrans('Retranslate outdated')} (${outdatedCount})`"
          :disabled="isTranslating"
          @click="showRetranslateConfirm = true"
        >
          <template #icon>
            <FontAwesomeIcon icon="fal fa-sync-alt" class="text-amber-500" fixed-width aria-hidden="true" />
          </template>
        </Button>
        <Button
          v-if="fieldData.approve_route && reviewCodes.length"
          v-tooltip="ctrans('Mark every machine translation as checked by you')"
          type="tertiary"
          :label="`${ctrans('Approve all')} (${reviewCodes.length})`"
          :disabled="isTranslating"
          :loading="isApproving"
          @click="approve(reviewCodes)"
        >
          <template #icon>
            <FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
          </template>
        </Button>
        <Button type="tertiary" :label="ctrans('Language')" @click="openDialog()">
          <template #icon>
            <FontAwesomeIcon icon="fal fa-plus" class="text-green-600" fixed-width aria-hidden="true" />
          </template>
        </Button>
      </div>
    </div>

    <div v-if="isTranslating" class="flex items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-xs text-gray-600">
      <FontAwesomeIcon icon="fal fa-spinner-third" class="animate-spin text-[--app-accent]" fixed-width aria-hidden="true" />
      <span>{{ ctrans("Translating names in the background, you can keep working") }}</span>
      <span v-if="progress?.total" class="ml-auto tabular-nums">{{ progress.done }} / {{ progress.total }}</span>
    </div>

    <div v-if="languageCodes.length" class="flex flex-wrap gap-2">
      <div
        v-for="code in languageCodes"
        :key="code"
        class="flex items-center gap-2 px-3 py-1.5 text-xs rounded-full border"
        :class="chipClass(code)"
      >
        <span class="cursor-pointer font-medium" @click="openDialog(code)">
          {{ getLabel(code) }}
        </span>

        <FontAwesomeIcon
          v-if="isAuto(code)"
          v-tooltip="ctrans('Translated automatically, edit it to make it yours')"
          icon="fal fa-robot"
          fixed-width
          aria-hidden="true"
        />

        <span class="text-[10px] opacity-70">
          {{ stateLabel(code) }}
        </span>

        <span
          v-if="isFilled(code)"
          class="cursor-pointer text-red-400 hover:text-red-600"
          @click.stop="removeLanguage(code)"
        >
          <FontAwesomeIcon icon="fal fa-times" fixed-width aria-hidden="true" />
        </span>
      </div>
    </div>

    <div v-else class="text-sm text-gray-400 border border-dashed rounded p-3 text-center">
      {{ ctrans("No translations added yet") }}
    </div>

    <Dialog
      v-model:visible="showDialog"
      modal
      :header="ctrans('Language Content')"
      :style="{ width: '420px' }"
      :content-style="{ overflow: 'visible', paddingLeft: '20px', paddingRight: '20px' }"
    >
      <div class="space-y-4">
        <div>
          <label class="text-xs text-gray-500 block mb-1">
            {{ ctrans("Language") }}
          </label>

          <PureMultiselect
            v-model="tempLanguage"
            :options="languageOptions"
            label="name"
            valueProp="code"
            :multiple="false"
            class="w-full"
            :searchable="true"
          />
        </div>

        <div>
          <label class="text-xs text-gray-500 block mb-1">
            {{ ctrans("Content") }}
          </label>

          <InputText
            v-model="tempValue"
            :placeholder="tempLanguage ? `${ctrans('Input for')} ${tempLanguage}` : ctrans('Enter content')"
            class="w-full"
          />
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <Button as="div" type="tertiary" :label="ctrans('Cancel')" @click="showDialog = false">
            <template #icon>
              <FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
            </template>
          </Button>
          <Button
            v-if="fieldData.approve_route && tempLanguage && needsReview(tempLanguage) && tempValue === localValues[tempLanguage]"
            as="div"
            type="tertiary"
            :label="ctrans('Approve')"
            :loading="isApproving"
            @click="approve([tempLanguage])"
          >
            <template #icon>
              <FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
            </template>
          </Button>
          <Button as="div" type="tertiary" :label="ctrans('Save')" :disabled="!tempLanguage" @click="saveLanguage">
            <template #icon>
              <FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
            </template>
          </Button>
        </div>
      </div>
    </Dialog>

    <Dialog
      v-model:visible="showRetranslateConfirm"
      modal
      :header="ctrans('Retranslate outdated names')"
      :style="{ width: '28rem' }"
    >
      <p class="text-sm text-gray-600">
        {{ ctrans("The English name changed after these names were written. Retranslating replaces them, including the ones a person typed in.") }}
      </p>
      <div class="flex justify-end gap-2 pt-4">
        <Button type="tertiary" :label="ctrans('Cancel')" @click="showRetranslateConfirm = false">
          <template #icon>
            <FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
          </template>
        </Button>
        <Button type="tertiary" :label="ctrans('Retranslate')" @click="translate(true)">
          <template #icon>
            <FontAwesomeIcon icon="fal fa-exclamation-triangle" class="text-amber-500" fixed-width aria-hidden="true" />
          </template>
        </Button>
      </div>
    </Dialog>
  </div>
</template>
