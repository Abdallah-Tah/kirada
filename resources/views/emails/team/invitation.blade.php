<x-mail::message>
# {{ __('Join :name on Kirada', ['name' => $landlordName]) }}

{{ __('You have been invited to help manage this property portfolio as :role.', ['role' => $role]) }}

<x-mail::button :url="$acceptUrl">
{{ __('Accept team invitation') }}
</x-mail::button>

{{ __('This secure invitation expires on :date.', ['date' => $expiresAt]) }}

{{ __('If you were not expecting this invitation, you can ignore this email.') }}
</x-mail::message>
