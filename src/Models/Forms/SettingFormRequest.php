<?php

namespace WalkerChiu\API\Models\Forms;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use WalkerChiu\Core\Models\Forms\FormRequest;

class SettingFormRequest extends FormRequest
{
    /**
     * @Override Illuminate\Foundation\Http\FormRequest::getValidatorInstance
     */
    protected function getValidatorInstance()
    {
        $request = Request::instance();
        $data = $this->all();
        if (
            $request->isMethod('put')
            && empty($data['id'])
            && isset($request->id)
        ) {
            $data['id'] = (string) $request->id;
            $this->getInputSource()->replace($data);
        }

        return parent::getValidatorInstance();
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return Array
     */
    public function attributes()
    {
        return [
            'host_type'   => trans('php-api::setting.host_type'),
            'host_id'     => trans('php-api::setting.host_id'),
            'serial'      => trans('php-api::setting.serial'),
            'type'        => trans('php-api::setting.type'),
            'app_id'      => trans('php-api::setting.app_id'),
            'app_token'   => trans('php-api::setting.app_token'),
            'app_key'     => trans('php-api::setting.app_key'),
            'app_secret'  => trans('php-api::setting.app_secret'),
            'function_id' => trans('php-api::setting.function_id'),
            'hash_key'    => trans('php-api::setting.hash_key'),
            'hash_iv'     => trans('php-api::setting.hash_iv'),
            'url_notify'  => trans('php-api::setting.url_notify'),
            'url_return'  => trans('php-api::setting.url_return'),
            'url_success' => trans('php-api::setting.url_success'),
            'url_cancel'  => trans('php-api::setting.url_cancel'),
            'is_enabled'  => trans('php-api::setting.is_enabled'),

            'name'        => trans('php-api::setting.name'),
            'description' => trans('php-api::setting.description'),
            'remarks'     => trans('php-api::setting.remarks')
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return Array
     */
    public function rules()
    {
        $rules = [
            'host_type'   => 'required_with:host_id|string',
            'host_id'     => 'required_with:host_type|string',
            'serial'      => '',
            'type'        => '',
            'app_id'      => 'required|string|max:255',
            'app_key'     => 'string',
            'app_secret'  => 'string',
            'function_id' => 'string',
            'hash_key'    => 'string',
            'hash_iv'     => 'string',
            'url_notify'  => 'url',
            'url_return'  => 'url',
            'url_success' => 'url',
            'url_cancel'  => 'url',
            'is_enabled'  => 'boolean',

            'name'        => 'required|string|max:255',
            'description' => '',
            'remarks'     => ''
        ];

        $request = Request::instance();
        if (
            $request->isMethod('put')
            && isset($request->id)
        ) {
            $rules = array_merge($rules, ['id' => ['required','string','exists:'.config('wk-core.table.api.settings').',id']]);
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return Array
     */
    public function messages()
    {
        return [
            'id.required'             => trans('php-core::validation.required'),
            'id.string'               => trans('php-core::validation.string'),
            'id.exists'               => trans('php-core::validation.exists'),
            'host_type.required_with' => trans('php-core::validation.required_with'),
            'host_type.string'        => trans('php-core::validation.string'),
            'host_id.required_with'   => trans('php-core::validation.required_with'),
            'host_id.string'          => trans('php-core::validation.string'),
            'app_id.required'         => trans('php-core::validation.required'),
            'app_id.string'           => trans('php-core::validation.string'),
            'app_id.max'              => trans('php-core::validation.max'),
            'app_key.string'          => trans('php-core::validation.string'),
            'app_secret.string'       => trans('php-core::validation.string'),
            'function_id.string'      => trans('php-core::validation.string'),
            'hash_key.string'         => trans('php-core::validation.string'),
            'hash_iv.string'          => trans('php-core::validation.string'),
            'url_notify.url'          => trans('php-core::validation.url'),
            'url_return.url'          => trans('php-core::validation.url'),
            'url_success.url'         => trans('php-core::validation.url'),
            'url_cancel.url'          => trans('php-core::validation.url'),
            'is_enabled.boolean'      => trans('php-core::validation.boolean'),

            'name.required' => trans('php-core::validation.required'),
            'name.string'   => trans('php-core::validation.string'),
            'name.max'      => trans('php-core::validation.max')
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after( function ($validator) {
            $data = $validator->getData();
            if (
                isset($data['host_type'])
                && isset($data['host_id'])
            ) {
                if (
                    config('wk-api.onoff.site')
                    && !empty(config('wk-core.class.site.site'))
                    && $data['host_type'] == config('wk-core.class.site.site')
                ) {
                    $result = DB::table(config('wk-core.table.site.sites'))
                                ->where('id', $data['host_id'])
                                ->exists();
                    if (!$result)
                        $validator->errors()->add('host_id', trans('php-core::validation.exists'));
                } elseif (
                    config('wk-api.onoff.group')
                    && !empty(config('wk-core.class.group.group'))
                    && $data['host_type'] == config('wk-core.class.group.group')
                ) {
                    $result = DB::table(config('wk-core.table.group.groups'))
                                ->where('id', $data['host_id'])
                                ->exists();
                    if (!$result)
                        $validator->errors()->add('host_id', trans('php-core::validation.exists'));
                }
            }
        });
    }
}
