<script setup>

defineProps({

    products: {
        type: Array,
        required: true,
    }

})


const emit = defineEmits([

    'add'

])


function addProduct(product)
{
    emit('add', product)
}


</script>


<template>

<div>


    <!-- SIN PRODUCTOS -->

    <div
        v-if="products.length === 0"
        class="
        flex
        h-64
        items-center
        justify-center
        text-slate-400
        "
    >

        No hay productos disponibles

    </div>





    <!-- GRID -->

    <div
        v-else
        class="
        grid
        grid-cols-2
        md:grid-cols-3
        xl:grid-cols-4
        gap-5
        "
    >


        <button

            v-for="product in products"

            :key="product.id"

            @click="addProduct(product)"

            class="
            group
            text-left
            rounded-3xl
            border
            border-slate-200
            bg-white
            p-4
            transition-all
            duration-200
            hover:-translate-y-1
            hover:shadow-xl
            hover:border-amber-300
            "

        >



            <!-- IMAGEN -->

            <div

                class="
                h-32
                rounded-2xl
                bg-slate-100
                flex
                items-center
                justify-center
                overflow-hidden
                mb-4
                "

            >


                <img

                    v-if="product.image"

                    :src="`/storage/${product.image}`"

                    class="
                    h-full
                    w-full
                    object-cover
                    "

                />


                <span
                    v-else
                    class="
                    text-4xl
                    "
                >
                    ☕
                </span>


            </div>





            <!-- INFORMACION -->

            <h3
                class="
                font-semibold
                text-slate-800
                truncate
                "
            >

                {{ product.name }}

            </h3>



            <div
                class="
                mt-2
                flex
                items-center
                justify-between
                "
            >


                <span
                    class="
                    text-lg
                    font-bold
                    text-amber-600
                    "
                >

                    ${{ Number(product.price).toFixed(2) }}

                </span>



                <span

                    class="
                    text-xs
                    text-slate-400
                    "

                >

                    Stock:
                    {{ product.stock }}

                </span>


            </div>





            <!-- BOTON -->

            <div

                class="
                mt-4
                rounded-xl
                bg-slate-900
                py-2
                text-center
                text-sm
                font-medium
                text-white
                opacity-0
                transition
                group-hover:opacity-100
                "

            >

                Agregar

            </div>



        </button>



    </div>



</div>

</template>
