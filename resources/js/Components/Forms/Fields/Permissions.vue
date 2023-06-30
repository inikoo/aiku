<script setup lang="ts">
import { ref } from 'vue';
import { Collapse } from 'vue-collapsed';
import CardPermissions from './Components/Permissions/Card.vue';

const props = defineProps({
  data: Object
});

const checkboxValues = ref([]);

const features = [
   {
      title: 'shop',
      branchShop: [
         {
            title: 'area',
            permissions: [
               { name: 'Invite team members', value: 1 },
               { name: 'List view', value: 2 },
               { name: 'Keyboard shortcuts', value: 3 },
               { name: 'Calendars', value: 4 },
               { name: 'Notifications', value: 5 },
               { name: 'Boards', value: 6 },
               { name: 'Reporting', value: 7 },
               { name: 'Mobile app', value: 8 }
            ]
         },
         {
            title: '12123',
            permissions: [
               { name: 'Invite team members', value: 1 },
               { name: 'List view', value: 2 },
               { name: 'Keyboard shortcuts', value: 3 },
               { name: 'Calendars', value: 4 },
               { name: 'Notifications', value: 5 },
               { name: 'Boards', value: 6 },
               { name: 'Reporting', value: 7 },
               { name: 'Mobile app', value: 8 }
            ]
         },
         {
            title: '123a',
            permissions: [
               { name: 'Invite team members', value: 1 },
               { name: 'List view', value: 2 },
               { name: 'Keyboard shortcuts', value: 3 },
               { name: 'Calendars', value: 4 },
               { name: 'Notifications', value: 5 },
               { name: 'Boards', value: 6 },
               { name: 'Reporting', value: 7 },
               { name: 'Mobile app', value: 8 }
            ]
         },
      ]
   },
   {
      title: 'Inventory',
      branchShop: [
         {
            title: 'area',
            permissions: [
               { name: 'Invite team members', value: 1 },
               { name: 'List view', value: 2 },
               { name: 'Keyboard shortcuts', value: 3 },
               { name: 'Calendars', value: 4 },
               { name: 'Notifications', value: 5 },
               { name: 'Boards', value: 6 },
               { name: 'Reporting', value: 7 },
               { name: 'Mobile app', value: 8 }
            ]
         },
         {
            title: '12123',
            permissions: [
               { name: 'Invite team members', value: 1 },
               { name: 'List view', value: 2 },
               { name: 'Keyboard shortcuts', value: 3 },
               { name: 'Calendars', value: 4 },
               { name: 'Notifications', value: 5 },
               { name: 'Boards', value: 6 },
               { name: 'Reporting', value: 7 },
               { name: 'Mobile app', value: 8 }
            ]
         },
         {
            title: '123a',
            permissions: [
               { name: 'Invite team members', value: 1 },
               { name: 'List view', value: 2 },
               { name: 'Keyboard shortcuts', value: 3 },
               { name: 'Calendars', value: 4 },
               { name: 'Notifications', value: 5 },
               { name: 'Boards', value: 6 },
               { name: 'Reporting', value: 7 },
               { name: 'Mobile app', value: 8 }
            ]
         },
         {
            title: '123a',
            permissions: [
               { name: 'Invite team members', value: 1 },
               { name: 'List view', value: 2 },
               { name: 'Keyboard shortcuts', value: 3 },
               { name: 'Calendars', value: 4 },
               { name: 'Notifications', value: 5 },
               { name: 'Boards', value: 6 },
               { name: 'Reporting', value: 7 },
               { name: 'Mobile app', value: 8 }
            ]
         },

      ]
   }
];

const data = ref(
  features.map((item, index) => ({
    ...item,
    isExpanded: index === 2
  }))
);

function handleAccordion(selectedIndex) {
  data.value.forEach((question, index) => {
    question.isExpanded = index === selectedIndex ? !question.isExpanded : false;
  });
}

function toggleCheckbox(value) {
  if (checkboxValues.value.includes(value)) {
    checkboxValues.value = checkboxValues.value.filter(item => item !== value);
  } else {
    checkboxValues.value.push(value);
  }
  console.log('masuk',checkboxValues)
}

</script>

<template>
  <div>
    <article>
      <div v-for="(question, index) in data" :key="question.title" class="Section">
        <button :class="['Panel', { Active: question.isExpanded }]" @click="() => handleAccordion(index)">
          {{ question.title }}
        </button>
        <Collapse v-model="question.isExpanded" :when="question.isExpanded" class="Collapse"
          style="border-top: 1px solid #d9d9d9;">
          <div class="Content">
            <div class="grid grid-cols-2 gap-4">
              <div v-for="(item, key) in question.branchShop" :key="key">
                <CardPermissions :data="item" :checkboxValues="checkboxValues" :toggleCheckbox="toggleCheckbox" />
              </div>
            </div>
          </div>
        </Collapse>
      </div>
    </article>
  </div>
</template>

<style scoped>
.Content {
  padding: 15px;
}

.Panel {
  width: 100%;
  font-size: 1rem;
  color: var(--ForegroundColor);
  text-align: left;
  font-weight: 600;
}

.Panel:hover {
  color: indigo;
}

.Active {
  color: indigo;
  background: #eef2ff;
}

.Section {
  background: #fafafa;
  width: 100%;
  border: 1px solid #d9d9d9;
  margin: 0;
  border-radius: 6px;
  margin-bottom: 5px;
}

.Section button {
  width: 100%;
  padding: 10px 10px;
  cursor: pointer;
}

.Collapse {
  transition: height var(--vc-auto-duration) cubic-bezier(0.37, 0, 0.63, 1);
}

.Collapse {
  background: #ffff;
  border-top: 1px solid #d9d9d9;
}
</style>
