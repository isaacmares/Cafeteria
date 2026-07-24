<script setup>
import { computed, onMounted, onBeforeUnmount, ref, watch, nextTick } from 'vue'
import { cartStore } from '@/Stores/cartStore'
import axios from 'axios'

const cart = cartStore
const loading = ref(false)
const paymentComplete = ref(false)
const lastUpdate = ref(new Date())
const pollingInterval = ref(null)
const isMounted = ref(true)
const lastSaleData = ref(null)

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value)
}

// Computed properties reactivas
const items = computed(() => cart.state.items)
const isComplete = computed(() => cart.state.isComplete)
const total = computed(() => cart.subtotal.value)
const subtotal = computed(() => cart.subtotal.value)
const customerName = computed(() => cart.state.customerName)
const totalItemsCount = computed(() => cart.totalItems.value)
const hasItems = computed(() => items.value.length > 0)

// Función para verificar y actualizar desde localStorage
const checkForUpdates = () => {
    if (!isMounted.value) return

    const updated = cart.syncFromStorage()
    if (updated) {
        lastUpdate.value = new Date()
        // Si se limpió el carrito (venta completada), mostrar mensaje pero mantener ventana abierta
        if (cart.state.items.length === 0 && !paymentComplete.value) {
            // La venta se completó, mostrar mensaje pero no cerrar
            paymentComplete.value = true
            setTimeout(() => {
                paymentComplete.value = false
            }, 5000)
        }
        nextTick(() => {})
    }
}

// Escuchar cambios en localStorage desde otras ventanas
const handleStorageChange = (event) => {
    if (event.key === 'cart_state') {
        checkForUpdates()
    }
}

// Iniciar polling automático
const startPolling = () => {
    pollingInterval.value = setInterval(() => {
        checkForUpdates()
    }, 500)
}

// Detener polling
const stopPolling = () => {
    if (pollingInterval.value) {
        clearInterval(pollingInterval.value)
        pollingInterval.value = null
    }
}

onMounted(() => {
    isMounted.value = true
    cart.syncFromStorage()
    lastUpdate.value = new Date()

    window.addEventListener('storage', handleStorageChange)
    startPolling()

    // Verificar si hay un carrito vacío pero con venta completada
    if (cart.state.items.length === 0 && cart.state.saleId) {
        paymentComplete.value = true
        setTimeout(() => {
            paymentComplete.value = false
        }, 5000)
    }
})

onBeforeUnmount(() => {
    isMounted.value = false
    stopPolling()
    window.removeEventListener('storage', handleStorageChange)
})
</script>

<template>
    <div class="min-h-screen bg-white">
        <!-- HEADER -->
        <div class="bg-green-600 text-white p-6 shadow-lg">
            <div class="max-w-4xl mx-auto flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">
                        Tu compra
                    </h1>
                    <p class="text-green-100 mt-1">
                        {{ customerName || 'Cliente' }}, revisa tu pedido
                    </p>
                </div>
                <div class="text-right">
                    <span class="inline-block px-4 py-2 bg-green-500 rounded-xl text-sm font-semibold">
                        {{ totalItemsCount }} productos
                    </span>
                </div>
            </div>
        </div>

        <div class="max-w-4xl mx-auto p-6">
            <!-- CARRITO VACÍO - ESPERANDO PRODUCTOS -->
            <div v-if="!hasItems && !paymentComplete" class="text-center py-20">
                <div class="text-8xl text-slate-300 mb-4">
                    🛒
                </div>
                <h2 class="text-3xl font-bold text-slate-700 mb-2">
                    Esperando productos
                </h2>
                <p class="text-slate-500 text-lg">
                    El cajero está agregando productos a tu pedido
                </p>
                <div class="mt-8 flex justify-center">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-green-600 border-t-transparent"></div>
                </div>
                <p class="mt-4 text-sm text-slate-400">
                    La pantalla se actualiza automáticamente
                </p>
                <p class="mt-2 text-xs text-slate-400">
                    Última actualización: {{ lastUpdate.toLocaleTimeString() }}
                </p>
            </div>

            <!-- PAGO COMPLETADO - PERO LA VENTANA SIGUE ABIERTA -->
            <div v-else-if="paymentComplete" class="text-center py-20">
                <div class="text-8xl text-green-500 mb-4">
                    ✅
                </div>
                <h2 class="text-3xl font-bold text-green-700 mb-2">
                    ¡Pago completado!
                </h2>
                <p class="text-slate-500 text-lg">
                    Gracias por tu compra
                </p>
                <p class="text-sm text-slate-400 mt-4">
                    El ticket se está imprimiendo
                </p>
                <p class="text-sm text-slate-400 mt-2">
                    La pantalla se mantiene abierta para la siguiente compra
                </p>
                <div class="mt-6 flex justify-center">
                    <span class="inline-block px-4 py-2 bg-green-100 text-green-700 rounded-xl text-sm font-medium">
                        Esperando nuevos productos...
                    </span>
                </div>
            </div>

            <!-- CARRITO CON PRODUCTOS -->
            <div v-else>
                <!-- LISTA DE PRODUCTOS -->
                <div class="space-y-4 mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-slate-700">
                            Productos en tu pedido
                        </h3>
                        <div class="flex items-center gap-3">
                            <span class="inline-block h-2 w-2 bg-green-500 rounded-full animate-pulse"></span>
                            <span class="text-sm text-slate-400">
                                Actualizado: {{ lastUpdate.toLocaleTimeString() }}
                            </span>
                        </div>
                    </div>

                    <div
                        v-for="item in items"
                        :key="item.id"
                        class="flex items-center gap-4 bg-slate-50 rounded-2xl p-4 border border-slate-200 shadow-sm hover:shadow-md transition"
                    >
                        <div class="h-20 w-20 rounded-xl bg-slate-200 flex items-center justify-center flex-shrink-0">
                            <span v-if="!item.image" class="text-3xl text-slate-400">
                                📦
                            </span>
                            <img v-else :src="item.image" :alt="item.name" class="h-full w-full object-cover rounded-xl">
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-slate-900 text-lg truncate">
                                {{ item.name }}
                            </h3>
                            <p class="text-sm text-slate-500">
                                {{ formatCurrency(item.price) }} c/u
                            </p>
                            <p class="text-xs text-slate-400">
                                SKU: {{ item.sku || 'N/A' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-green-600">
                                {{ formatCurrency(item.price * item.quantity) }}
                            </div>
                            <div class="text-sm text-slate-500">
                                Cantidad: <span class="font-semibold">{{ item.quantity }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TOTALES -->
                <div class="bg-green-50 rounded-2xl p-6 border border-green-200 mb-8 shadow-sm">
                    <div class="space-y-3">
                        <div class="border-t-2 border-green-200 pt-4 flex justify-between text-3xl font-bold">
                            <span class="text-slate-900">Total a pagar</span>
                            <span class="text-green-600">{{ formatCurrency(total) }}</span>
                        </div>
                        <div class="text-sm text-slate-500 text-center pt-2">
                            {{ totalItemsCount }} productos en tu carrito
                        </div>
                    </div>
                </div>

                <!-- INDICADOR DE ACTUALIZACIÓN -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-sm text-slate-400">
                        <span class="inline-block h-2 w-2 bg-green-500 rounded-full animate-pulse"></span>
                        Actualizando en tiempo real
                    </div>
                    <span class="text-xs text-slate-400">
                        Última actualización: {{ lastUpdate.toLocaleTimeString() }}
                    </span>
                </div>

                <!-- CLIENTE INFO -->
                <div class="mt-6 bg-slate-50 rounded-2xl p-4 border border-slate-200">
                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-700">
                                Cliente
                            </p>
                            <p class="text-slate-800 font-semibold">
                                {{ customerName || 'Cliente general' }}
                            </p>
                            <p v-if="cart.state.customerEmail" class="text-sm text-slate-600">
                                {{ cart.state.customerEmail }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-4 py-2 bg-green-100 text-green-700 rounded-xl text-sm font-medium">
                                ✨ Compra en proceso
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="mt-8 text-center text-sm text-slate-400 border-t border-slate-200 pt-4">
                <p>
                    Esta pantalla se actualiza automáticamente cuando el cajero agrega productos
                </p>
                <p class="mt-1 text-xs">
                    Pedido #{{ new Date().getTime().toString().slice(-8) }}
                </p>
                <p class="mt-1 text-xs text-green-500 font-medium">
                    ✅ Pantalla siempre activa
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}
</style>
