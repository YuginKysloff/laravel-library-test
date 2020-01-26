<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class GetStatRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

		/**
		 * Prepare the data for validation.
		 *
		 * @return void
		 */
    public function prepareForValidation() {
			$data['dateFrom'] = Carbon::parse($this->get('dateFrom'))->startOfDay();
			$data['dateTo'] = Carbon::parse($this->get('dateTo'))->endOfDay();

			$this->replace($data);
    }

	/**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
        	'dateFrom' => 'required',
        	'dateTo' => 'required',
				];
    }
}
