<x-mail::message>
# Barangay Paguiruan e-Governance

Hello {{ $residentName }},

We regret to inform you that your account registration has been **rejected**.

<x-mail::panel>
Status: REJECTED
@if(!empty($reasonLabel))
Reason: {{ $reasonLabel }}
@endif
@if(!empty($reasonDetails))
Details: {{ $reasonDetails }}
@endif
</x-mail::panel>

Please review your submitted information and register again with correct and complete details.

<x-mail::button :url="$loginUrl" color="primary">
Open Portal
</x-mail::button>

If you need help, coordinate with the barangay office for verification support.

Thanks,<br>
Barangay Paguiruan e-Governance Team
</x-mail::message>
