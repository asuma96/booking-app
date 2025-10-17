<script setup>
const props = defineProps({ services: Array })
</script>

<template>
    <div class="p-8 max-w-5xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Услуги</h1>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <a v-for="s in props.services" :key="s.id"
               :href="`/services/${s.id}`"
               class="block rounded-2xl border p-5 shadow-sm hover:shadow-md transition">
                <div class="text-lg font-semibold mb-1">{{ s.name }}</div>
                <div class="text-sm text-gray-500">Записаться →</div>
            </a>
        </div>
    </div>
</template>
