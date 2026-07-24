<script setup>

import CartItem from './CartItem.vue'


const props = defineProps({

    cart:{
        type:Array,
        required:true
    },

    subtotal:{
        type:Number,
        required:true
    }

})


const emit = defineEmits([

    'increase',

    'decrease',

    'remove',

    'pay'

])


</script>


<template>

<div class="h-full flex flex-col">


    <!-- HEADER -->

    <div
        class="
        flex
        items-center
        justify-between
        p-5
        border-b
        "
    >

        <div>

            <h2
                class="
                text-lg
                font-bold
                text-slate-800
                "
            >
                Orden actual
            </h2>


            <p
                class="
                text-xs
                text-slate-400
                "
            >
                Productos seleccionados
            </p>

        </div>



        <div
            class="
            flex
            h-10
            w-10
            items-center
            justify-center
            rounded-2xl
            bg-amber-100
            text-amber-700
            font-bold
            "
        >

            {{ cart.length }}

        </div>


    </div>





    <!-- ITEMS -->

    <div
        class="
        flex-1
        overflow-y-auto
        p-5
        space-y-3
        "
    >


        <!-- CARRITO VACIO -->

        <div
            v-if="cart.length === 0"

            class="
            h-full
            flex
            flex-col
            items-center
            justify-center
            text-slate-400
            "
        >

            <span class="text-5xl mb-3">
                🛒
            </span>


            <p>
                Carrito vacío
            </p>


        </div>





        <!-- PRODUCTOS -->

        <CartItem

            v-for="item in cart"

            :key="item.id"

            :item="item"

            @increase="emit('increase',$event)"

            @decrease="emit('decrease',$event)"

            @remove="emit('remove',$event)"

        />


    </div>







    <!-- FOOTER -->

    <div
        class="
        border-t
        p-5
        "
    >



        <div
            class="
            flex
            justify-between
            items-center
            mb-5
            "
        >


            <span
                class="
                text-sm
                text-slate-500
                "
            >
                Total
            </span>



            <strong
                class="
                text-2xl
                font-bold
                text-slate-900
                "
            >

                ${{ Number(subtotal).toFixed(2) }}

            </strong>


        </div>





        <button

            @click="emit('pay')"

            :disabled="cart.length === 0"

            class="
            w-full
            rounded-2xl
            bg-amber-600
            py-4
            font-bold
            text-white
            transition
            hover:bg-amber-700
            disabled:opacity-40
            disabled:cursor-not-allowed
            "

        >

            Cobrar venta

        </button>



    </div>



</div>


</template>
