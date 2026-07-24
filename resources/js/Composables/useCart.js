import { ref, computed } from 'vue'


const cart = ref([])



export function useCart(){



    const addProduct = (product)=>{


        const exists = cart.value.find(
            item => item.id === product.id
        )


        if(exists){

            exists.quantity++

        }else{


            cart.value.push({

                id: product.id,

                name: product.name,

                price: product.price,

                quantity:1

            })

        }


    }



    const removeProduct = (id)=>{


        cart.value =
            cart.value.filter(
                item => item.id !== id
            )


    }



    const increase = (item)=>{

        item.quantity++

    }



    const decrease = (item)=>{


        if(item.quantity > 1){

            item.quantity--

        }else{

            removeProduct(item.id)

        }


    }



    const clear = ()=>{

        cart.value=[]

    }



    const subtotal = computed(()=>{


        return cart.value.reduce(
            (total,item)=>{

                return total +
                (item.price * item.quantity)

            },
            0
        )


    })



    return {

        cart,

        addProduct,

        removeProduct,

        increase,

        decrease,

        clear,

        subtotal

    }


}
