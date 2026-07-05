@component('mail::message')
# You're Invited to Join Kirada

Hello {{ $tenantName }},

{{ $landlordName }} has invited you to join **Kirada** as a tenant.

Kirada is a rental management platform where you can:
- View your lease and contract documents
- Pay rent and track invoices
- Submit maintenance requests
- Communicate with your landlord

@component('mail::button', ['url' => $acceptUrl, 'color' => 'primary'])
Accept Invitation
@endcomponent

**This invitation expires on {{ $expiresAt }}.**

If the button above doesn't work, copy and paste this link into your browser:

{{ $acceptUrl }}

If you were not expecting this invitation, you can safely ignore this email.

Thanks,
**Kirada**
@endcomponent