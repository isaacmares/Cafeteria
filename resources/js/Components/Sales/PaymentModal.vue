<script setup>
import { computed, ref } from 'vue'
import axios from 'axios'
import { cartStore } from '@/Stores/cartStore'

const props = defineProps({
    cart: {
        type: Array,
        required: true
    },
    total: {
        type: Number,
        required: true
    }
})

const emit = defineEmits(['close', 'success'])
const cart = cartStore
const paymentMethod = ref('cash')
const received = ref(0)
const loading = ref(false)
const error = ref(null)

const change = computed(() => {
    const result = Number(received.value) - Number(props.total)
    return result > 0 ? result : 0
})

async function confirmSale() {
    if (props.cart.length === 0) {
        error.value = 'El carrito está vacío'
        return
    }

    if (paymentMethod.value === 'cash' && Number(received.value) < props.total) {
        error.value = 'El monto recibido es insuficiente'
        return
    }

    try {
        loading.value = true
        error.value = null

        const payload = {
            customer_name: cart.state.customerName || 'Cliente general',
            customer_email: cart.state.customerEmail || null,
            items: props.cart.map(item => ({
                product_id: item.id,
                quantity: item.quantity,
                price: item.price
            })),
            payment: {
                method: paymentMethod.value,
                received: Number(received.value),
                change: paymentMethod.value === 'cash' ? change.value : 0,
                reference: null
            }
        }

        const response = await axios.post('/api/sales', payload, {
            withCredentials: true
        })

        emit('success', {
            ...response.data.sale,
            items: props.cart.map(item => ({
                name: item.name,
                quantity: item.quantity,
                price: item.price
            })),
            payment_method: paymentMethod.value,
            received: Number(received.value),
            change: paymentMethod.value === 'cash' ? change.value : 0
        })

    } catch (e) {
        console.error(e)
        error.value = e.response?.data?.message || 'Error al crear la venta'
    } finally {
        loading.value = false
    }
}

function closeModal() {
    emit('close')
}
</script>



<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="closeModal">
        <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Cobrar venta</h2>
                    <p class="text-sm text-slate-400">Finalizar pedido</p>
                </div>
                <button @click="closeModal" class="text-slate-400 hover:text-red-500 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="rounded-2xl bg-slate-100 p-5 mb-5 text-center">
                <p class="text-sm text-slate-500">Total a pagar</p>
                <h3 class="text-4xl font-bold text-amber-600">
                    ${{ total.toFixed(2) }}
                </h3>
            </div>

            <div class="mb-5">
                <label class="text-sm font-medium text-slate-600">Nombre del cliente</label>
                <input
                    v-model="cart.state.customerName"
                    type="text"
                    placeholder="Nombre del cliente"
                    class="mt-2 w-full rounded-xl border-slate-200 px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                />
            </div>

            <div class="mb-5">
                <label class="text-sm font-medium text-slate-600">Método de pago</label>
                <div class="grid grid-cols-3 gap-3 mt-2">
                    <button
                        v-for="method in [
                            { id: 'cash', name: 'Efectivo' },
                            { id: 'card', name: 'Tarjeta' },
                            { id: 'transfer', name: 'Transferencia' }
                        ]"
                        :key="method.id"
                        @click="paymentMethod = method.id"
                        :class="[
                            'rounded-xl py-3 text-sm font-medium border transition',
                            paymentMethod === method.id
                                ? 'bg-amber-600 text-white border-amber-600'
                                : 'bg-white text-slate-600 hover:bg-slate-50'
                        ]"
                    >
                        {{ method.name }}
                    </button>
                </div>
            </div>

            <div v-if="paymentMethod === 'cash'" class="mb-5">
                <label class="text-sm font-medium text-slate-600">Recibido</label>
                <input
                    v-model="received"
                    type="number"
                    step="0.01"
                    min="0"
                    class="mt-2 w-full rounded-xl border-slate-200 px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                    placeholder="0.00"
                />
                <div
                    v-if="received > 0"
                    class="mt-3 rounded-xl p-3 text-sm"
                    :class="[
                        received >= total ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'
                    ]"
                >
                    <span v-if="received >= total">
                        Cambio: <strong>${{ change.toFixed(2) }}</strong>
                    </span>
                    <span v-else>
                        Faltan: <strong>${{ (total - received).toFixed(2) }}</strong>
                    </span>
                </div>
            </div>

            <p v-if="error" class="mb-4 text-sm text-red-600">{{ error }}</p>

            <button
                @click="confirmSale"
                :disabled="loading || (paymentMethod === 'cash' && received < total && received > 0)"
                class="w-full rounded-2xl py-4 font-bold text-white transition disabled:opacity-50 disabled:cursor-not-allowed"
                :class="[
                    loading ? 'bg-slate-400' :
                    (paymentMethod === 'cash' && received < total && received > 0) ? 'bg-slate-400' :
                    'bg-slate-900 hover:bg-black'
                ]"
            >
                <span v-if="loading">Guardando...</span>
                <span v-else-if="paymentMethod === 'cash' && received < total && received > 0">
                    Falta dinero
                </span>
                <span v-else>Confirmar venta</span>
            </button>

            <div class="mt-4 text-center text-xs text-slate-400">
                {{ props.cart.length }} productos en el carrito
            </div>
        </div>
    </div>
</template>
