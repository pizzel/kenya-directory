<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail; // For sending email
use App\Mail\ContactFormMail;       // We will create this Mailable

class ContactController extends Controller
{
    /**
     * Display the contact page.
     */
    public function show()
    {
        return view('contact.show'); // We will create this Blade view
    }

    /**
     * Handle the contact form submission.
     */
    public function send(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:200', // Max 200 characters
        ]);

        // Try to send the email
        try {
            // Send to your admin email address (or a dedicated contact email)
            // It's good practice to get this from .env or config
            $adminEmail = config('mail.contact_form_recipient', env('ADMIN_EMAIL', 'admin@mtalii.co.ke'));

            Mail::to($adminEmail)->send(new ContactFormMail($validatedData));

            return back()->with('success', 'Thank you for your message! We will get back to you soon.');

        } catch (\Exception $e) {
            // Log the error for debugging
            // \Log::error('Contact form submission error: ' . $e->getMessage()); // Uncomment for debugging

            // Return with a generic error message
            return back()->with('error', 'Sorry, there was an issue sending your message. Please try again later or contact us directly via phone.')
                         ->withInput(); // Send back old input
        }
    }
}