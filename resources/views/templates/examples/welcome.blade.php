<!-- Welcome Email Template -->
@component('notigen::templates.email')
    # Welcome to {{ $company_name }}!

    Dear {{ $user_name }},

    Thank you for joining us! We're excited to have you on board.

    @if (isset($verification_url))
        Please verify your email address by clicking the button below:

        @component('mail::button', ['url' => $verification_url])
            Verify Email Address
        @endcomponent
    @endif

    If you have any questions, feel free to reach out to our support team.

    Best regards,
    The {{ $company_name }} Team
@endcomponent
