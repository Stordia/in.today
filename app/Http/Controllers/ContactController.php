<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function submit(Request $request, string $locale)
    {
        // Honeypot check - if filled, silently reject
        if ($request->filled('website_confirm')) {
            return redirect()
                ->route('landing', ['locale' => $locale])
                ->with('success', __('landing.contact.success'));
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'restaurant_name' => 'required|string|max:150',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'website_url' => 'nullable|url|max:255',
            'type' => 'required|in:restaurant,cafe,bar,club,hotel,other',
            'services' => 'required|array|min:1',
            'services.*' => 'in:website,menu,reservations,photography,seo,multilingual',
            'budget' => 'required|in:under_2k,2k_5k,5k_10k,above_10k,not_sure',
            'message' => 'nullable|string|max:2000',
        ], [
            'name.required' => __('landing.contact.errors.name_required'),
            'email.required' => __('landing.contact.errors.email_required'),
            'email.email' => __('landing.contact.errors.email_invalid'),
            'restaurant_name.required' => __('landing.contact.errors.restaurant_name_required'),
            'city.required' => __('landing.contact.errors.city_required'),
            'country.required' => __('landing.contact.errors.country_required'),
            'type.required' => __('landing.contact.errors.type_required'),
            'services.required' => __('landing.contact.errors.services_required'),
            'services.min' => __('landing.contact.errors.services_required'),
            'budget.required' => __('landing.contact.errors.budget_required'),
        ]);

        // Build email content
        $emailContent = $this->buildEmailContent($validated, $locale);

        try {
            // Try to send email
            Mail::raw($emailContent, function ($message) use ($validated) {
                $message->to('proposals@in.today')
                    ->replyTo($validated['email'], $validated['name'])
                    ->subject('New Proposal Request: ' . $validated['restaurant_name']);
            });

            Log::info('Contact form submitted and email sent', [
                'restaurant' => $validated['restaurant_name'],
                'email' => $validated['email'],
                'locale' => $locale,
            ]);
        } catch (\Exception $e) {
            // If email fails, log the submission instead
            Log::warning('Email sending failed, logging submission instead', [
                'error' => $e->getMessage(),
                'submission' => $validated,
                'locale' => $locale,
            ]);
        }

        return redirect()
            ->route('landing', ['locale' => $locale])
            ->with('success', __('landing.contact.success'))
            ->withFragment('contact');
    }

    private function buildEmailContent(array $data, string $locale): string
    {
        $services = implode(', ', $data['services']);

        $budgetLabels = [
            'under_2k' => 'Under €2,000',
            '2k_5k' => '€2,000 – €5,000',
            '5k_10k' => '€5,000 – €10,000',
            'above_10k' => 'Above €10,000',
            'not_sure' => 'Not sure yet',
        ];

        $typeLabels = [
            'restaurant' => 'Restaurant',
            'cafe' => 'Café / Coffee shop',
            'bar' => 'Bar / Lounge',
            'club' => 'Club / Nightlife',
            'hotel' => 'Hotel / Hospitality',
            'other' => 'Other',
        ];

        $content = "NEW PROPOSAL REQUEST\n";
        $content .= "====================\n\n";
        $content .= "Contact Information\n";
        $content .= "-------------------\n";
        $content .= "Name: {$data['name']}\n";
        $content .= "Email: {$data['email']}\n";
        $content .= "Phone: " . ($data['phone'] ?? 'Not provided') . "\n\n";
        $content .= "Business Details\n";
        $content .= "----------------\n";
        $content .= "Restaurant/Business: {$data['restaurant_name']}\n";
        $content .= "Type: " . ($typeLabels[$data['type']] ?? $data['type']) . "\n";
        $content .= "City: {$data['city']}\n";
        $content .= "Country: {$data['country']}\n";
        $content .= "Current Website: " . ($data['website_url'] ?? 'None') . "\n\n";
        $content .= "Project Requirements\n";
        $content .= "--------------------\n";
        $content .= "Services Needed: {$services}\n";
        $content .= "Budget Range: " . ($budgetLabels[$data['budget']] ?? $data['budget']) . "\n\n";
        $content .= "Message\n";
        $content .= "-------\n";
        $content .= ($data['message'] ?? 'No additional message') . "\n\n";
        $content .= "---\n";
        $content .= "Submitted from: in.today ({$locale})\n";
        $content .= "Time: " . now()->toDateTimeString() . "\n";

        return $content;
    }
}
