<script setup>
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import axios from 'axios'

const props = defineProps({
    tenant: {
        type: Object,
        required: true,
    },
})

const loading = ref(true)
const data = ref({
    stats: {
        total_sales: 0,
        total_products: 0,
        total_revenue: 0,
        today_sales: 0,
        weekly_sales: 0,
        monthly_sales: 0,
    },
    sales_by_day: [],
    top_products: [],
    recent_sales: [],
    payment_methods: [],
    today_payment_breakdown: {
        cash: 0,
        card: 0,
        transfer: 0,
    }
})

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-MX', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    })
}

const maxDailySales = computed(() => {
    const max = Math.max(...data.value.sales_by_day.map(d => d.total), 0)
    return max || 1
})

const fetchDashboardData = async () => {
    loading.value = true
    try {
        const response = await axios.get('/api/dashboard/stats', {
            withCredentials: true
        })
        data.value = response.data
    } catch (error) {
        console.error('Error al cargar dashboard:', error)
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    fetchDashboardData()
})
</script>

<template>
    <AppLayout>
        <div class="space-y-6">

            <!-- HEADER -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900">Dashboard</h1>
                    <p class="text-slate-500 mt-1">
                        Bienvenido, {{ tenant.name }}
                    </p>
                </div>
                <div class="text-sm text-slate-400">
                    {{ new Date().toLocaleDateString('es-MX', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}
                </div>
            </div>

            <!-- LOADING -->
            <div v-if="loading" class="flex items-center justify-center py-20">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-green-600 border-t-transparent"></div>
            </div>

            <!-- CONTENIDO -->
            <template v-else>
                <!-- TARJETAS DE ESTADÍSTICAS -->
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-slate-500">Ventas totales</p>
                            <div class="h-10 w-10 rounded-xl bg-green-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v1m0 1v1m0 1v1m0 1v1"/>
                                </svg>
                            </div>
                        </div>
                        <p class="mt-3 text-3xl font-bold text-slate-900">{{ data.stats.total_sales }}</p>
                        <p class="mt-1 text-sm text-slate-400">ventas registradas</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-slate-500">Productos</p>
                            <div class="h-10 w-10 rounded-xl bg-blue-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                        </div>
                        <p class="mt-3 text-3xl font-bold text-slate-900">{{ data.stats.total_products }}</p>
                        <p class="mt-1 text-sm text-slate-400">productos en catálogo</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-slate-500">Ventas hoy</p>
                            <div class="h-10 w-10 rounded-xl bg-amber-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </div>
                        </div>
                        <p class="mt-3 text-3xl font-bold text-slate-900">{{ formatCurrency(data.stats.today_sales) }}</p>
                        <p class="mt-1 text-sm text-slate-400">en ventas de hoy</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-slate-500">Ventas del mes</p>
                            <div class="h-10 w-10 rounded-xl bg-purple-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                        </div>
                        <p class="mt-3 text-3xl font-bold text-slate-900">{{ formatCurrency(data.stats.monthly_sales) }}</p>
                        <p class="mt-1 text-sm text-slate-400">en lo que va del mes</p>
                    </div>
                </div>

                <!-- GRÁFICO DE VENTAS DIARIAS -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Ventas diarias</h3>
                            <p class="text-sm text-slate-500">Últimos 7 días</p>
                        </div>
                        <span class="text-sm text-slate-400">
                            Total: {{ formatCurrency(data.sales_by_day.reduce((sum, d) => sum + d.total, 0)) }}
                        </span>
                    </div>

                    <div class="flex items-end gap-2 h-48">
                        <div
                            v-for="day in data.sales_by_day"
                            :key="day.date"
                            class="flex-1 flex flex-col items-center gap-2"
                        >
                            <div
                                class="w-full max-w-[40px] rounded-lg transition-all hover:opacity-80"
                                :style="{
                                    height: (day.total / maxDailySales * 100) + '%',
                                    minHeight: '4px',
                                    backgroundColor: day.total > 0 ? '#22c55e' : '#e2e8f0'
                                }"
                            ></div>
                            <span class="text-xs text-slate-400 truncate w-full text-center">
                                {{ day.label || formatDate(day.date) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- PRODUCTOS MÁS VENDIDOS Y ÚLTIMAS VENTAS -->
                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Top productos -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Productos más vendidos</h3>
                        <div v-if="data.top_products.length === 0" class="text-center py-8 text-slate-400">
                            <p>Aún no hay productos vendidos</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="(product, index) in data.top_products"
                                :key="product.id"
                                class="flex items-center gap-3"
                            >
                                <span class="text-sm font-bold text-green-600 w-6">#{{ index + 1 }}</span>
                                <div class="flex-1">
                                    <div class="flex justify-between text-sm">
                                        <span class="font-medium text-slate-700">{{ product.name }}</span>
                                        <span class="text-slate-500">{{ product.total_quantity }} unidades</span>
                                    </div>
                                    <div class="h-1.5 w-full rounded-full bg-slate-100 mt-1">
                                        <div
                                            class="h-1.5 rounded-full bg-green-500"
                                            :style="{
                                                width: (product.total_quantity / data.top_products[0].total_quantity * 100) + '%'
                                            }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Últimas ventas -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Últimas ventas</h3>
                        <div v-if="data.recent_sales.length === 0" class="text-center py-8 text-slate-400">
                            <p>Aún no hay ventas registradas</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="sale in data.recent_sales"
                                :key="sale.id"
                                class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition"
                            >
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-slate-900 text-sm truncate">
                                        {{ sale.folio }}
                                    </p>
                                    <p class="text-xs text-slate-500 truncate">
                                        {{ sale.customer_name }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-green-600 text-sm">
                                        {{ formatCurrency(sale.total) }}
                                    </p>
                                    <p class="text-xs text-slate-400">
                                        {{ new Date(sale.created_at).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' }) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FOOTER -->
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-center text-sm text-slate-500">
                    <p>
                        ✅ Sistema operando correctamente · {{ tenant.name }} ·
                        {{ new Date().toLocaleDateString('es-MX', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                    </p>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
