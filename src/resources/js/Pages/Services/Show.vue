<script setup>
import { ref, watch, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
    service: Object,
    durations: Array,
    durationId: Number,
    days: Array,
    selectedDay: String,
    slots: Array,
    booked: Boolean,
})

const showSuccess = ref(false)

onMounted(() => { if (props.booked) showSuccess.value = true })

function book(time) {
    const name = prompt('Ваше имя:')
    const phone = prompt('Телефон:')
    if (!name || !phone) return

    router.post('/bookings', {
        service_duration_id: props.durationId,
        date: props.selectedDay,
        time,
        customer_name: name,
        customer_phone: phone,
    }, {
        onSuccess: () => {
            router.reload({ only: ['slots'] })
            showSuccess.value = true
            setTimeout(() => router.visit('/'), 1200)
        },
        onError: (errors) => {
            const msg = Object.values(errors).join('\n') || 'Проверьте введённые данные';
            alert(`Ошибка 422\n${msg}`);
        },
    })
}
</script>

<template>
    <div class="p-6 max-w-4xl mx-auto space-y-4">
        <div><a href="/" class="text-sm underline">&larr; ко всем услугам</a></div>
        <h1 class="text-2xl font-bold">{{ service.name }}</h1>

        <div class="flex gap-2">
            <Link v-for="d in durations" :key="d.id"
                  class="px-3 py-1 rounded border"
                  :class="d.id===durationId ? 'bg-black text-white' : ''"
                  :href="`/services/${service.id}?duration_id=${d.id}&date=${selectedDay}`">
                {{ d.minutes }} мин
            </Link>
        </div>

        <div class="flex gap-2">
            <Link v-for="d in days" :key="d.iso"
                  class="px-3 py-1 rounded border"
                  :class="d.iso===selectedDay ? 'bg-black text-white' : ''"
                  :href="`/services/${service.id}?duration_id=${durationId}&date=${d.iso}`">
                {{ d.label }}
            </Link>
        </div>

        <div class="mt-4 font-semibold">Свободные слоты на {{ selectedDay }}</div>
        <div class="flex flex-wrap gap-3 mt-2">
            <button v-for="t in slots" :key="t"
                    class="px-4 py-2 border rounded hover:bg-gray-100"
                    @click="book(t)">
                {{ t }}
            </button>
            <span v-if="!slots.length" class="text-gray-500">Нет доступных слотов</span>
        </div>

        <div v-if="showSuccess" class="fixed inset-0 bg-black/40 grid place-items-center">
            <div class="bg-white p-6 rounded shadow">
                <div class="text-lg font-semibold">Бронирование создано</div>
                <div class="text-sm text-gray-600 mt-1">Возвращаемся на главную…</div>
            </div>
        </div>
    </div>
</template>
