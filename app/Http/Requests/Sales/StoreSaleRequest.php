<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSaleRequest extends FormRequest
{

    public function authorize(): bool
    {
        return auth()->check();
    }



    public function rules(): array
    {

        return [

            'customer_id'=>[
                'nullable',
                'integer',
                'exists:customers,id',
            ],



            'items'=>[
                'required',
                'array',
                'min:1',
            ],



            'items.*.product_id'=>[
                'required',
                'integer',
                'exists:products,id',
            ],



            'items.*.quantity'=>[
                'required',
                'integer',
                'min:1',
            ],




            'payment'=>[
                'required',
                'array'
            ],



            'payment.method'=>[

                'required',

                Rule::in([
                    'cash',
                    'card',
                    'transfer'
                ])

            ],




            'payment.received'=>[

                'required',

                'numeric',

                'min:0'

            ],



            'payment.reference'=>[

                'nullable',

                'string'

            ]

        ];

    }




    public function messages(): array
    {

        return [

            'payment.required'
            =>
            'Debe seleccionar un método de pago.',


            'payment.method.required'
            =>
            'Seleccione un método de pago.',


            'payment.received.required'
            =>
            'Debe ingresar el monto recibido.',


            'items.required'
            =>
            'Debe agregar productos.',


            'items.min'
            =>
            'La venta debe tener productos.',


        ];

    }



}
