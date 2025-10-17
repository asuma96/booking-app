<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'service_duration_id' => ['required','integer','exists:service_durations,id'],
            'date' => ['required','date_format:Y-m-d'],
            'time' => ['required','date_format:H:i'],
            'customer_name'  => ['required','string','min:2','max:100'],
            'customer_phone' => ['required','string','min:6','max:32','regex:/^[\s\-\+\(\)\d]+$/'],
        ];
    }
    public function messages(): array
    {
        return [
            'service_duration_id.required' => 'Не выбрана длительность услуги.',
            'service_duration_id.integer'  => 'Неверная длительность услуги.',
            'service_duration_id.exists'   => 'Длительность услуги не найдена.',

            'date.required'      => 'Дата обязательна.',
            'date.date_format'   => 'Дата должна быть в формате ГГГГ-ММ-ДД.',

            'time.required'      => 'Время обязательно.',
            'time.date_format'   => 'Время должно быть в формате ЧЧ:ММ.',

            'customer_name.required' => 'Укажите имя.',
            'customer_name.min'      => 'Имя слишком короткое.',
            'customer_name.max'      => 'Имя слишком длинное.',

            'customer_phone.required' => 'Укажите телефон.',
            'customer_phone.regex'    => 'Телефон должен содержать только цифры, пробелы, +, (), -.',
            'customer_phone.min'      => 'Телефон слишком короткий.',
            'customer_phone.max'      => 'Телефон слишком длинный.',
        ];
    }

    public function attributes(): array
    {
        return [
            'customer_name'  => 'имя',
            'customer_phone' => 'телефон',
        ];
    }
}
