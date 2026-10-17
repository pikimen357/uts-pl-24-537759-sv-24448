<script setup>
import { RouterView } from 'vue-router'
</script>

<template>
  <RouterView />
</template>

<style>
body { font-family: system-ui, sans-serif; margin: 0; background: #f4f5f7; color: #222; }
.container { max-width: 900px; margin: 2rem auto; padding: 0 1rem; }
.card { background: #fff; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 4px rgba(0,0,0,.1); margin-bottom: 1.5rem; }
input { display: block; width: 100%; padding: .5rem; margin: .25rem 0 .75rem; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
button { padding: .5rem 1rem; border: 0; border-radius: 4px; cursor: pointer; background: #2563eb; color: #fff; }
button.secondary { background: #6b7280; }
button.danger { background: #dc2626; }
table { width: 100%; border-collapse: collapse; }
th, td { text-align: left; padding: .5rem; border-bottom: 1px solid #eee; }
.error { color: #dc2626; font-size: .9rem; margin: 0 0 .5rem; }
.header { display: flex; justify-content: space-between; align-items: center; }
</style>