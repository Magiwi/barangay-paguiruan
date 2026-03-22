<x-mail::message>
# Barangay Paguiruan e-Governance

Hello {{ $residentName }},

Your account registration has been **approved**.

You can now sign in and access barangay online services.

<x-mail::panel>
Status: APPROVED

Next step: Log in using your registered email and password.
</x-mail::panel>

<x-mail::button :url="$loginUrl" color="success">
Go to Login
</x-mail::button>

If you did not submit this registration, please contact the barangay office immediately.

Thanks,<br>
Barangay Paguiruan e-Governance Team
</x-mail::message>
