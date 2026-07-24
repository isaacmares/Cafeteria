<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import axios from 'axios'

const cashRegister = ref(null)
const history = ref([])
const loading = ref(false)
const showOpenModal = ref(false)
const showCloseModal = ref(false)
const showReportModal = ref(false)
const selectedReport = ref(null)
const saleDetails = ref([])

const openingBalance = ref('')
const closingBalance = ref('')
const notes = ref('')

const formatCurrency = (value) => {
    if (value === null || value === undefined || isNaN(value)) return '$0.00'
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(Number(value))
}

const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleString('es-MX', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

// Obtener caja actual
const fetchCurrentRegister = async () => {
    loading.value = true
    try {
        const response = await axios.get('/api/cash-register/current', {
            withCredentials: true
        })
        cashRegister.value = response.data
    } catch (error) {
        console.error('Error:', error)
    } finally {
        loading.value = false
    }
}

// Obtener historial
const fetchHistory = async () => {
    try {
        const response = await axios.get('/api/cash-register/history', {
            withCredentials: true
        })
        history.value = response.data.data || []
    } catch (error) {
        console.error('Error:', error)
    }
}

// Abrir caja
const openCashRegister = async () => {
    if (!openingBalance.value || parseFloat(openingBalance.value) < 0) {
        alert('Ingresa un monto válido para la apertura')
        return
    }

    loading.value = true
    try {
        await axios.post('/api/cash-register/open', {
            opening_balance: parseFloat(openingBalance.value),
            notes: notes.value
        }, {
            withCredentials: true
        })

        showOpenModal.value = false
        openingBalance.value = ''
        notes.value = ''
        await fetchCurrentRegister()
        alert('Caja abierta correctamente')
    } catch (error) {
        alert(error.response?.data?.message || 'Error al abrir la caja')
    } finally {
        loading.value = false
    }
}

// Cerrar caja
const closeCashRegister = async () => {
    if (!closingBalance.value || parseFloat(closingBalance.value) < 0) {
        alert('Ingresa un monto válido para el cierre')
        return
    }

    loading.value = true
    try {
        await axios.post('/api/cash-register/close', {
            closing_balance: parseFloat(closingBalance.value),
            notes: notes.value
        }, {
            withCredentials: true
        })

        showCloseModal.value = false
        closingBalance.value = ''
        notes.value = ''
        await fetchCurrentRegister()
        await fetchHistory()
        alert('Caja cerrada correctamente')
    } catch (error) {
        alert(error.response?.data?.message || 'Error al cerrar la caja')
    } finally {
        loading.value = false
    }
}

// Ver reporte
const viewReport = async (id) => {
    loading.value = true
    try {
        const response = await axios.get(`/api/cash-register/report/${id}`, {
            withCredentials: true
        })
        selectedReport.value = response.data
        saleDetails.value = response.data.sales || []
        showReportModal.value = true
    } catch (error) {
        console.error('Error:', error)
        alert('Error al cargar el reporte')
    } finally {
        loading.value = false
    }
}

const isOpen = computed(() => {
    return cashRegister.value && cashRegister.value.status === 'open'
})

const totalSales = computed(() => {
    return cashRegister.value?.total_sales || 0
})

const expectedBalance = computed(() => {
    if (!cashRegister.value) return 0
    const opening = parseFloat(cashRegister.value.opening_balance) || 0
    const cashSales = parseFloat(cashRegister.value.cash_sales) || 0
    return opening + cashSales
})

const difference = computed(() => {
    if (!cashRegister.value || !cashRegister.value.closing_balance) return null
    const closing = parseFloat(cashRegister.value.closing_balance) || 0
    const expected = expectedBalance.value
    return closing - expected
})



onMounted(() => {
    fetchCurrentRegister()
    fetchHistory()
})
</script>

<template>
    <AppLayout>
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900">
                        Caja
                    </h1>
                    <p class="text-slate-500 mt-1">
                        Control de apertura y cierre de caja
                    </p>
                </div>

                <div v-if="!isOpen" class="flex gap-3">
                    <button
                        @click="showOpenModal = true"
                        class="px-6 py-3 rounded-2xl bg-green-600 text-white font-semibold hover:bg-green-700 transition shadow-lg shadow-green-200"
                    >
                        Abrir Caja
                    </button>
                </div>
            </div>

            <!-- Estado actual -->
            <div v-if="cashRegister" class="bg-white rounded-3xl shadow-lg border border-slate-100 p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <p class="text-sm text-slate-500">Estado</p>
                        <span
                            class="inline-block px-4 py-2 rounded-xl text-sm font-semibold mt-1"
                            :class="[
                                isOpen ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-700'
                            ]"
                        >
                            {{ isOpen ? 'Caja Abierta' : 'Caja Cerrada' }}
                        </span>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Apertura</p>
                        <p class="text-xl font-bold text-slate-900">
                            {{ formatCurrency(cashRegister.opening_balance) }}
                        </p>
                        <p class="text-xs text-slate-400">
                            {{ formatDate(cashRegister.opened_at) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Ventas (efectivo)</p>
                        <p class="text-xl font-bold text-amber-600">
                            {{ formatCurrency(cashRegister.cash_sales) }}
                        </p>
                    </div>

                    <div v-if="!isOpen">
                        <p class="text-sm text-slate-500">Cierre</p>
                        <p class="text-xl font-bold text-slate-900">
                            {{ formatCurrency(cashRegister.closing_balance) }}
                        </p>
                        <p class="text-xs text-slate-400">
                            {{ formatDate(cashRegister.closed_at) }}
                        </p>
                    </div>
                </div>

                <!-- Acciones si está abierta -->
                <div v-if="isOpen" class="mt-6 pt-6 border-t border-slate-100 flex justify-end">
                    <button
                        @click="showCloseModal = true"
                        class="px-6 py-3 rounded-2xl bg-red-600 text-white font-semibold hover:bg-red-700 transition shadow-lg shadow-red-200"
                    >
                        Cerrar Caja
                    </button>
                </div>
            </div>

            <!-- Sin caja abierta -->
            <div v-else class="bg-white rounded-3xl shadow-lg border border-slate-100 p-12 text-center">
                <div class="w-24 h-24 mx-auto bg-slate-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-700 mb-2">No hay caja abierta</h3>
                <p class="text-slate-500 mb-6">Abre la caja para comenzar a registrar ventas</p>
                <button
                    @click="showOpenModal = true"
                    class="px-8 py-3 rounded-2xl bg-green-600 text-white font-semibold hover:bg-green-700 transition shadow-lg shadow-green-200"
                >
                    Abrir Caja
                </button>
            </div>

            <!-- Historial -->
            <div class="mt-8">
                <h2 class="text-xl font-bold text-slate-900 mb-4">Historial de cierres</h2>

                <div class="bg-white rounded-3xl shadow-lg border border-slate-100 overflow-hidden">
                    <div v-if="history.length === 0" class="p-8 text-center text-slate-500">
                        No hay cierres registrados
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-50 border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Fecha</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Apertura</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Cierre</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Ventas</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Transacciones</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Diferencia</th>
                                    <th class="px-6 py-4 text-right text-sm font-semibold text-slate-600">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="item in history" :key="item.id" class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 text-sm text-slate-700">
                                        {{ formatDate(item.closed_at) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-slate-900">
                                        {{ formatCurrency(item.opening_balance) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-slate-900">
                                        {{ formatCurrency(item.closing_balance) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-bold text-amber-600">
                                        {{ formatCurrency(item.total_sales) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700">
                                        {{ item.total_transactions }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-semibold"
                                        :class="[
                                            (item.closing_balance - (item.opening_balance + item.cash_sales)) !== 0
                                                ? 'text-red-600'
                                                : 'text-green-600'
                                        ]"
                                    >
                                        {{ formatCurrency(item.closing_balance - (item.opening_balance + item.cash_sales)) }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button
                                            @click="viewReport(item.id)"
                                            class="px-4 py-2 rounded-xl bg-amber-50 text-amber-700 text-sm font-medium hover:bg-amber-100 transition"
                                        >
                                            Ver reporte
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Apertura -->
            <div v-if="showOpenModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
                <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-slate-900">Abrir Caja</h2>
                        <button @click="showOpenModal = false" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Monto inicial
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">$</span>
                                <input
                                    v-model="openingBalance"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    class="w-full rounded-xl border-slate-200 pl-8 pr-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Notas
                            </label>
                            <textarea
                                v-model="notes"
                                rows="3"
                                placeholder="Notas sobre la apertura..."
                                class="w-full rounded-xl border-slate-200 px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                            />
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button
                            @click="showOpenModal = false"
                            class="flex-1 rounded-xl border border-slate-300 py-3 font-medium text-slate-700 hover:bg-slate-50 transition"
                        >
                            Cancelar
                        </button>
                        <button
                            @click="openCashRegister"
                            :disabled="loading"
                            class="flex-1 rounded-xl bg-green-600 py-3 font-semibold text-white hover:bg-green-700 transition disabled:opacity-50"
                        >
                            {{ loading ? 'Abriendo...' : 'Abrir Caja' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal Cierre -->
            <div v-if="showCloseModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
                <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-slate-900">Cerrar Caja</h2>
                        <button @click="showCloseModal = false" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="bg-slate-50 rounded-2xl p-4 mb-6">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Ventas en efectivo</span>
                            <span class="font-semibold text-amber-600">{{ formatCurrency(cashRegister?.cash_sales) }}</span>
                        </div>
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-slate-600">Monto inicial</span>
                            <span class="font-semibold text-slate-900">{{ formatCurrency(cashRegister?.opening_balance) }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold mt-3 pt-3 border-t border-slate-200">
                            <span class="text-slate-900">Total esperado</span>
                            <span class="text-amber-600">{{ formatCurrency(expectedBalance) }}</span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Monto en caja
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">$</span>
                                <input
                                    v-model="closingBalance"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    class="w-full rounded-xl border-slate-200 pl-8 pr-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                />
                            </div>
                            <div v-if="closingBalance && expectedBalance" class="mt-2 text-sm"
                                :class="[
                                    parseFloat(closingBalance) === expectedBalance
                                        ? 'text-green-600'
                                        : 'text-red-600'
                                ]"
                            >
                                {{ parseFloat(closingBalance) === expectedBalance
                                    ? '✓ Coincide con el total esperado'
                                    : '⚠ Diferencia: ' + formatCurrency(parseFloat(closingBalance) - expectedBalance)
                                }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Notas
                            </label>
                            <textarea
                                v-model="notes"
                                rows="3"
                                placeholder="Notas sobre el cierre..."
                                class="w-full rounded-xl border-slate-200 px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                            />
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button
                            @click="showCloseModal = false"
                            class="flex-1 rounded-xl border border-slate-300 py-3 font-medium text-slate-700 hover:bg-slate-50 transition"
                        >
                            Cancelar
                        </button>
                        <button
                            @click="closeCashRegister"
                            :disabled="loading"
                            class="flex-1 rounded-xl bg-red-600 py-3 font-semibold text-white hover:bg-red-700 transition disabled:opacity-50"
                        >
                            {{ loading ? 'Cerrando...' : 'Cerrar Caja' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal Reporte -->
            <div v-if="showReportModal && selectedReport" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm overflow-y-auto py-10">
                <div class="w-full max-w-4xl bg-white rounded-3xl shadow-2xl p-6 max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between mb-6 sticky top-0 bg-white py-2">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-900">Reporte de Caja</h2>
                            <p class="text-sm text-slate-500">
                                {{ formatDate(selectedReport.cash_register.opened_at) }} -
                                {{ formatDate(selectedReport.cash_register.closed_at) }}
                            </p>
                        </div>
                        <button @click="showReportModal = false" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Resumen -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-slate-50 rounded-2xl p-4 text-center">
                            <p class="text-sm text-slate-500">Total Ventas</p>
                            <p class="text-2xl font-bold text-amber-600">{{ formatCurrency(selectedReport.summary.total_sales) }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-2xl p-4 text-center">
                            <p class="text-sm text-slate-500">Efectivo</p>
                            <p class="text-2xl font-bold text-green-600">{{ formatCurrency(selectedReport.summary.cash_sales) }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-2xl p-4 text-center">
                            <p class="text-sm text-slate-500">Tarjeta</p>
                            <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(selectedReport.summary.card_sales) }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-2xl p-4 text-center">
                            <p class="text-sm text-slate-500">Transferencia</p>
                            <p class="text-2xl font-bold text-purple-600">{{ formatCurrency(selectedReport.summary.transfer_sales) }}</p>
                        </div>
                    </div>

                    <!-- Diferencia -->
                    <div class="bg-amber-50 rounded-2xl p-4 mb-6 border border-amber-200">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-slate-700">Diferencia en caja</span>
                            <span class="text-2xl font-bold"
                                :class="[
                                    selectedReport.summary.difference === 0
                                        ? 'text-green-600'
                                        : 'text-red-600'
                                ]"
                            >
                                {{ formatCurrency(selectedReport.summary.difference) }}
                            </span>
                        </div>
                        <p class="text-sm text-slate-500 mt-1">
                            Apertura: {{ formatCurrency(selectedReport.cash_register.opening_balance) }} |
                            Ventas efectivo: {{ formatCurrency(selectedReport.summary.cash_sales) }} |
                            Esperado: {{ formatCurrency(selectedReport.summary.expected_balance) }} |
                            Cierre: {{ formatCurrency(selectedReport.cash_register.closing_balance) }}
                        </p>
                    </div>

                    <!-- Ventas -->
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Ventas del período</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-slate-50 border-b border-slate-100">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-600">Folio</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-600">Cliente</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-600">Productos</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-600">Pago</th>
                                        <th class="px-4 py-3 text-right text-sm font-semibold text-slate-600">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="sale in saleDetails" :key="sale.id" class="hover:bg-slate-50 transition">
                                        <td class="px-4 py-3 text-sm font-medium text-slate-900">
                                            {{ sale.folio || '#' + sale.id }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-700">
                                            {{ sale.customer_name || 'Cliente general' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-700">
                                            {{ sale.items?.length || 0 }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-700">
                                            <span class="capitalize">{{ sale.payment_method }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-sm font-bold text-amber-600 text-right">
                                            {{ formatCurrency(sale.total) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="flex justify-end mt-6 pt-4 border-t border-slate-100">
                        <button
                            @click="showReportModal = false"
                            class="px-6 py-2 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition"
                        >
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
