@php($settings = \App\Models\Setting::get_settings())

@component('mail::message')
# Thank You for Contacting Us

Hi {{ $name }},

Thank you for reaching out to {{ $settings->site_name }}! We have received your message regarding "{{ $subject }}" and our team will get back to you as soon as possible.

We appreciate your interest and look forward to connecting with you.

---

@component('mail::button', ['url' => route('home')])
Visit Our Website
@endcomponent

Best regards,  
**The {{ $settings->site_name }} Team**
@endcomponent
