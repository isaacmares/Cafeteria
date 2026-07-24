<script setup>

defineProps({

    item:{
        type:Object,
        required:true
    }

})


const emit = defineEmits([

    'increase',

    'decrease',

    'remove'

])


</script>


<template>

<div

class="
rounded-2xl
bg-slate-50
p-4
transition
hover:bg-slate-100
"

>


<!-- INFORMACIÓN -->

<div class="flex justify-between gap-3">


    <div class="flex-1">


        <h3
            class="
            font-semibold
            text-sm
            text-slate-800
            truncate
            "
        >

            {{ item.name }}

        </h3>



        <p
            class="
            mt-1
            text-xs
            text-slate-500
            "
        >

            ${{ Number(item.price).toFixed(2) }}

        </p>


    </div>



    <button

        @click="emit('remove', item.id)"

        class="
        text-slate-400
        hover:text-red-500
        "

    >

        ✕

    </button>


</div>





<!-- CONTROLES -->


<div

class="
mt-4
flex
items-center
justify-between
"

>


<div

class="
flex
items-center
gap-3
rounded-xl
bg-white
border
px-2
py-1
"

>


<button

@click="emit('decrease', item)"

class="
h-7
w-7
rounded-lg
hover:bg-slate-100
"

>

−

</button>



<span

class="
w-6
text-center
text-sm
font-semibold
"

>

{{ item.quantity }}

</span>




<button

@click="emit('increase', item)"

class="
h-7
w-7
rounded-lg
hover:bg-slate-100
"

>

+

</button>


</div>





<strong

class="
text-slate-800
"

>

${{
(
item.price *
item.quantity
).toFixed(2)
}}

</strong>



</div>


</div>


</template>
