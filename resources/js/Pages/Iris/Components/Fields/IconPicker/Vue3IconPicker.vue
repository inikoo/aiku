<script setup>
import { ref, computed } from 'vue'
import fontLibrary from './Components/fonts.js'
import { fas } from '@/../private/pro-solid-svg-icons';
import { fal } from '@/../private/pro-light-svg-icons';
import { far } from '@/../private/pro-regular-svg-icons';
import { fad } from '@/../private/pro-duotone-svg-icons';
import { fab } from "@fortawesome/free-brands-svg-icons"
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
library.add(fas, fal, far, fad, fab)
const props = defineProps({
  label: {
    type: String,
    default: 'Vue3 Icon Picker'
  },
  modelValue: {
    type: String,
    default: 'fas fa-circle'
  }
})

const filterText = ref('')
const activeGlyph = ref(props.modelValue)
const isVisible = ref(false)
console.log('dsdfgfadfhjysdffsdg',fontLibrary)
const tabs = [
      {
        id: 'all',
        title: 'All Icons',
        icon: 'fas fa-star-of-life',
        link: 'all'
      },
      {
        id: 'far',
        title: 'Font Awesome Regular',
        icon: 'fab fa-font-awesome-alt',
        link: fontLibrary.fontAwesome.variants.regular
      },
      {
        id: 'fas',
        title: 'Font Awesome Solid',
        icon: 'fab fa-font-awesome',
        link: fontLibrary.fontAwesome.variants.solid
      },
      {
        id: 'fab',
        title: 'Font Awesome Brands',
        icon: 'fab fa-font-awesome-flag',
        link: fontLibrary.fontAwesome.variants.brands
      }
    ]

    const activeTab = ref(tabs[0])
    const allGlyphs = [].concat(
      tabs[1].link.icons,
      tabs[2].link.icons,
      tabs[3].link.icons
    )

    const glyphs = computed(() => {
      let _glyphs = []
      if (activeTab.value.id !== 'all') {
        _glyphs = activeTab.value.link.icons
      } else {
        _glyphs = allGlyphs
      }

      if (filterText.value != '') {
        const _filterText = filterText.value.toLowerCase()
        _glyphs = _glyphs.filter(
          item => item.substr(7, filterText.value.length) === _filterText
        )
      }
      console.log('sdaaf',_glyphs)
      return _glyphs
    })

    const setActiveGlyph = glyph => {
      activeGlyph.value = glyph
    }

    const isActiveGlyph = glyph => {
      return activeGlyph.value == glyph
    }

    const isActiveTab = tab => {
      return tab == activeTab.value.id
    }

    const setActiveTab = tab => {
      activeTab.value = tab
      // filterText.value=''; //nice feature
    }

    const getGlyphName = glyph =>
      glyph.replace(/f.. fa-/g, '').replace('-', ' ')

    const insert = () => {
      context.emit('update:modelValue', activeGlyph.value)
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
    <div @click="togglePicker">icon Picker</div>
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
          <div class="aim-modal--sidebar">
            <div class="aim-modal--sidebar-tabs">
              <div
                class="aim-modal--sidebar-tab-item"
                data-library-id="all"
                v-for="tab in tabs"
                :key="tab.id"
                :class="{ 'aesthetic-active': isActiveTab(tab.id) }"
                @click="setActiveTab(tab)"
              >
              <FontAwesomeIcon :icon="tab.icon"/>
                <span>{{ tab.title }}</span>
              </div>
            </div>
            <div class="aim-sidebar-preview">
              <div class="aim-icon-item ">
                <div class="aim-icon-item-inner">
                  <FontAwesomeIcon :icon="activeGlyph"/>
                  <div class="aim-icon-item-name">
                    {{ activeGlyph }}
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="aim-modal--icon-preview-wrap">
            <div class="aim-modal--icon-search">
              <input v-model="filterText" placeholder="Filter by name..." />
              <FontAwesomeIcon icon="fas fa-search"/>
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
        <div class="aim-modal--footer">
          <button class="aim-insert-icon-button" @click="insert">Insert</button>
        </div>
      </div>
    </div>
  </template>
  

  
  <style scoped>
  @import './Components/Css/styles.css';
  </style>