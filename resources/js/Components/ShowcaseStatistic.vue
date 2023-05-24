<template>
    <dl class="border border-gray-900/5 rounded-lg overflow-hidden shadow mx-auto grid grid-cols-1 gap-px bg-gray-900/5 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="stat in stats" :key="stat.name"
            class="flex flex-wrap items-baseline justify-between gap-x-4 bg-white px-4 pt-3 pb-2">
            <dt class="text-xs font-medium text-gray-400">{{ stat.name }}</dt>
            <!-- <dd :class="[stat.changeType === 'negative' ? 'text-rose-600' : 'text-gray-700', 'text-xs font-medium']">
                {{ stat.change }}
            </dd> -->
            <dd class="w-full flex-none text-xl font-medium leading-8 tracking-tight text-indigo-700">{{ stat.value }}</dd>
        </div>
    </dl>
</template>
  
<script setup>
const stats = [
    { name: 'Revenue', value: '200.00', change: '+4.75%', changeType: 'positive' },
    { name: 'Overdue', value: '2283 ', change: '+54.02%', changeType: 'negative' },
    { name: 'Outstanding', value: '43', change: '-1.39%', changeType: 'positive' },
    { name: 'Expenses', value: '$40', change: '+10.18%', changeType: 'negative' },
]
</script>