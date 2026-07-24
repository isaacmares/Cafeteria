<script setup>

import { computed, ref } from 'vue'
import axios from 'axios'


const props = defineProps({

    cart:{
        type:Array,
        required:true
    },

    total:{
        type:Number,
        required:true
    }

})


const emit = defineEmits([

    'close',

    'success'

])



/*
|--------------------------------------------------------------------------
| Payment
|--------------------------------------------------------------------------
*/


const paymentMethod = ref('cash')


const received = ref(0)


const loading = ref(false)


const error = ref(null)



/*
|--------------------------------------------------------------------------
| Change
|--------------------------------------------------------------------------
*/


const change = computed(()=>{


    const result =
        Number(received.value)
        -
        Number(props.total)


    return result > 0
        ? result
        : 0


})





/*
|--------------------------------------------------------------------------
| Create Sale
|--------------------------------------------------------------------------
*/


async function confirmSale()
{

    try {


        loading.value=true

        error.value=null



        const payload = {


            payment_method:
                paymentMethod.value,


            received:
                Number(received.value),


            items:
                props.cart.map(item=>({


                    product_id:
                        item.id,


                    quantity:
                        item.quantity


                }))


        }





        const response =
            await axios.post(
                '/api/sales',
                payload
            )




        emit(
            'success',
            response.data.sale
        )



    } catch(e){


        console.error(e)


        error.value =
            e.response?.data?.message
            ??
            'Error al crear la venta'


    } finally {


        loading.value=false


    }


}



</script>



<template>


<div

class="
fixed
inset-0
z-50
flex
items-center
justify-center
bg-black/40
backdrop-blur-sm
"

>


<div

class="
w-full
max-w-md
rounded-3xl
bg-white
shadow-2xl
p-6
"

>



<!-- HEADER -->

<div class="flex justify-between items-center mb-6">


<div>


<h2

class="
text-xl
font-bold
text-slate-800
"

>
Cobrar venta
</h2>


<p class="text-sm text-slate-400">
Finalizar pedido
</p>


</div>


<button

@click="emit('close')"

class="
text-slate-400
hover:text-red-500
"

>

✕


</button>


</div>





<!-- TOTAL -->


<div

class="
rounded-2xl
bg-slate-100
p-5
mb-5
text-center
"

>


<p class="text-sm text-slate-500">
Total a pagar
</p>


<h3

class="
text-4xl
font-bold
text-amber-600
"

>

${{ total.toFixed(2) }}

</h3>


</div>







<!-- METHOD -->


<div class="mb-5">


<label

class="
text-sm
font-medium
text-slate-600
"

>
Método de pago
</label>



<div

class="
grid
grid-cols-3
gap-3
mt-2
"

>


<button

v-for="method in [
{
id:'cash',
name:'Efectivo'
},
{
id:'card',
name:'Tarjeta'
},
{
id:'transfer',
name:'Transferencia'
}
]"

:key="method.id"


@click="paymentMethod = method.id"


:class="[

'rounded-xl py-3 text-sm font-medium border transition',

paymentMethod===method.id

?
'bg-amber-600 text-white border-amber-600'

:

'bg-white text-slate-600 hover:bg-slate-50'

]"

>


{{ method.name }}


</button>


</div>


</div>








<!-- RECEIVED -->


<div

v-if="paymentMethod==='cash'"

class="mb-5"

>


<label class="
text-sm
font-medium
text-slate-600
">

Recibido

</label>



<input

v-model="received"

type="number"

class="
mt-2
w-full
rounded-xl
border-slate-200
px-4
py-3
"

placeholder="0.00"

/>



<div

class="
mt-3
rounded-xl
bg-green-50
p-3
text-sm
text-green-700
"

>

Cambio:

<strong>

${{ change.toFixed(2) }}

</strong>


</div>


</div>







<!-- ERROR -->


<p

v-if="error"

class="
mb-4
text-sm
text-red-600
"

>

{{ error }}

</p>








<!-- BUTTON -->


<button

@click="confirmSale"

:disabled="loading"

class="
w-full
rounded-2xl
bg-slate-900
py-4
font-bold
text-white
hover:bg-black
disabled:opacity-50
"

>


<span v-if="loading">

Guardando...

</span>


<span v-else>

Confirmar venta

</span>


</button>



</div>


</div>


</template>
