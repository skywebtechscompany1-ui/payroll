<template>
  <div class="animate-pulse">
    <table class="table">
      <thead>
        <tr>
          <th v-for="i in columns" :key="i">
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-24"></div>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="i in rows" :key="i">
          <td v-for="j in columns" :key="j">
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded" :class="getWidth(j)"></div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
const props = withDefaults(defineProps<{
  rows?: number
  columns?: number
}>(), {
  rows: 5,
  columns: 4
})

const getWidth = (index: number) => {
  const widths = ['w-32', 'w-48', 'w-24', 'w-40', 'w-36']
  return widths[index % widths.length]
}
</script>
