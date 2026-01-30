<x-mail::message>
# New Contact Form Submission

You have received a new message from your website's contact form.

**Name:** {{ $data['name'] }}

**Email:** [{{ $data['email'] }}](mailto:{{ $data['email'] }})

---

**Message:**

{{ $data['message'] }}

---

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>