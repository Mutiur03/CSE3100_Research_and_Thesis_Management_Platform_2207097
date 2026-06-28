<?php

namespace App\Http\Requests\Meeting;

use App\Enums\MeetingRsvpStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMeetingRsvpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rsvp_status' => ['required', Rule::enum(MeetingRsvpStatus::class)],
        ];
    }
}
