@php($settings = \App\Models\Setting::get_settings())

@component('mail::message')
# New Contact Form Submission

Hello Admin,

You have received a new message from your website contact form.

**From:** {{ $name }}  
**Email:** {{ $email }}  
**Subject:** {{ $subject }}

---

## Message:

{{ $message }}

---

@component('mail::button', ['url' => route('admin.contact.index')])
View All Messages
@endcomponent

Thank you,  
{{ $settings->site_name }}
@endcomponent
