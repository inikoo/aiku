<script setup>
import { ref, computed } from 'vue'
import fontLibrary from './IconPicker/Components/fonts.js'
import { fab } from "@fortawesome/free-brands-svg-icons"
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
library.add(fab)
const props = defineProps({
  label: {
    type: String,
    default: 'Set Social Media'
  },
  modelValue: {
    type: String,
    default: 'fas fa-circle'
  },
  cssClass : String,
  save:Function,
  data:Object
})

const filterText = ref('')
const labelText = ref(props.data.label)
const linkText = ref(props.data.href)
const activeGlyph = ref(props.modelValue)
const isVisible = ref(false)
const tabs = [
      {
        id: 'all',
        title: 'All Icons',
        link: 'all'
      },
    ]

    const allGlyphs = [].concat(
      fontLibrary.fontAwesome.variants.brands.icons
    )

    const glyphs = computed(() => {
      let _glyphs = allGlyphs
      

      if (filterText.value != '') {
        const _filterText = filterText.value.toLowerCase()
        _glyphs = _glyphs.filter(
          item => item.substr(7, filterText.value.length) === _filterText
        )
      }
      return _glyphs
    })

    const setActiveGlyph = glyph => {
      activeGlyph.value = glyph
    }

    const isActiveGlyph = glyph => {
      return activeGlyph.value == glyph
    }

    const getGlyphName = glyph =>
      glyph.replace(/f.. fa-/g, '').replace('-', ' ')

    const insert = (type) => {
      props.save({colum : {...props.data}, value : {label :labelText.value, icon : activeGlyph.value, href:linkText.value }, type : type})
      isVisible.value = false
   
    }

    const togglePicker = () => {
      isVisible.value = !isVisible.value
    }

    const closePicker = () => {
      isVisible.value = false
    }
  
</script>


<template>
    <div @click="togglePicker"><FontAwesomeIcon :icon="props.modelValue" :class="cssClass" aria-hidden="true" /></div>
    <div class="aim-modal aim-open" v-if="isVisible">
      <div class="aim-modal--content">
        <div class="aim-modal--header">
          <div class="aim-modal--header-logo-area">
            <span class="aim-modal--header-logo-title">
              {{ label }}
            </span>
          </div>
          <div class="aim-modal--header-close-btn" @click="closePicker">
            <FontAwesomeIcon icon="fas fa-times"/>
          </div>
        </div>
        <div class="aim-modal--body">
          <div class="aim-modal--icon-preview-wrap">
            <div class="aim-modal--icon-input mb-2">
              <input v-model="linkText" placeholder="https//:" />
            </div>
            <div class="aim-modal--icon-input mb-2">
              <input v-model="labelText" placeholder="Label" />
            </div>
            <div class="aim-modal--icon-input mb-2">
              <input v-model="activeGlyph" placeholder="Icon" :disabled="true"/>
            </div>
            <hr class="mb-2"/>
            <div class="aim-modal--icon-input-search">
              <input v-model="filterText" placeholder="Filter by name..." />
            </div>
            <div class="aim-modal--icon-preview-inner">
              <div class="aim-modal--icon-preview">
                <div
                  class="aim-icon-item"
                  v-for="glyph in glyphs"
                  :key="glyph"
                  :class="{ 'aesthetic-selected': isActiveGlyph(glyph) }"
                  @click="setActiveGlyph(glyph)"
                >
                  <div class="aim-icon-item-inner">
                    <FontAwesomeIcon :icon="glyph"/>
                    <div class="aim-icon-item-name">
                    {{ getGlyphName(glyph) }}
                  </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="aim-modal--footer gap-3">
          <button class="aim-delete-icon-button" @click="insert('delete')">Delete</button>
          <button class="aim-insert-icon-button" @click="insert('save')">Insert</button>
        </div>
      </div>
    </div>
  </template>
  

  
  <style scoped>
  @import './IconPicker/Components/Css/styles.css'
  </style>