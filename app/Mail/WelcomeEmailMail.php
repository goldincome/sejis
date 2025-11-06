<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class WelcomeEmailMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public string $companyName;
    public string $supportEmail;
    public bool $isVerificationRequired;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, bool $isVerificationRequired = false)
    {
        $this->user = $user;
        $this->isVerificationRequired = $isVerificationRequired;
        $this->companyName = setting('site_name', 'Sejis Kitchen Rental');
        $this->supportEmail = setting('contact_email', 'support@sejiskitchenrental.com');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->supportEmail, $this->companyName),
            subject: "Welcome to {$this->companyName}! 🎉",
            tags: ['welcome-email', 'new-customer'],
            metadata: [
                'user_id' => $this->user->id,
                'user_email' => $this->user->email,
                'registration_date' => $this->user->created_at->toDateString(),
                'verification_required' => $this->isVerificationRequired,
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.user.welcome',
            with: [
                'user' => $this->user,
                'userName' => $this->user->name,
                'userEmail' => $this->user->email,
                'companyName' => $this->companyName,
                'supportEmail' => $this->supportEmail,
                'isVerificationRequired' => $this->isVerificationRequired,
                'registrationDate' => $this->user->created_at->format('F j, Y'),
                'contactPhone' => setting('contact_phone', '+44 20 7946 0958'),
                'companyAddress' => setting('company_address', 'London, UK'),
                'websiteUrl' => config('app.url'),
                'dashboardUrl' => route('user.dashboard'),
                'profileUrl' => route('user.profile.edit'),
                'productsUrl' => route('search.index'),
                'supportUrl' => config('app.url') . '/contact',
                'socialLinks' => $this->getSocialLinks(),
                'welcomeOffer' => $this->getWelcomeOffer(),
                'gettingStartedSteps' => $this->getGettingStartedSteps(),
                'popularProducts' => $this->getPopularProducts(),
                'companyInfo' => $this->getCompanyInfo(),
                'emailPreferences' => $this->getEmailPreferences(),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Get social media links
     */
    private function getSocialLinks(): array
    {
        return [
            'facebook' => setting('social_facebook', '#'),
            'twitter' => setting('social_twitter', '#'),
            'instagram' => setting('social_instagram', '#'),
            'linkedin' => setting('social_linkedin', '#'),
        ];
    }

    /**
     * Get welcome offer details
     */
    private function getWelcomeOffer(): ?array
    {
        $welcomeDiscount = setting('welcome_discount_percentage', 10);
        $welcomeCode = setting('welcome_discount_code', 'WELCOME10');
        
        if ($welcomeDiscount && $welcomeCode) {
            return [
                'discount_percentage' => $welcomeDiscount,
                'discount_code' => $welcomeCode,
                'valid_until' => now()->addDays(30)->format('F j, Y'),
                'minimum_order' => setting('welcome_minimum_order', 100),
                'terms' => 'Valid for first-time customers only. Cannot be combined with other offers.',
            ];
        }
        
        return null;
    }

    /**
     * Get getting started steps
     */
    private function getGettingStartedSteps(): array
    {
        return [
            [
                'step' => 1,
                'title' => 'Complete Your Profile',
                'description' => 'Add your contact details and preferences for a personalized experience.',
                'action' => 'Complete Profile',
                'url' => route('user.profile.edit'),
                'icon' => 'user-circle'
            ],
            [
                'step' => 2,
                'title' => 'Browse Our Products',
                'description' => 'Explore our wide range of kitchen rental solutions and equipment.',
                'action' => 'Browse Products',
                'url' => route('search.index'),
                'icon' => 'search'
            ],
            [
                'step' => 3,
                'title' => 'Place Your First Order',
                'description' => 'Find the perfect kitchen solution and place your first rental order.',
                'action' => 'Start Shopping',
                'url' => route('search.index'),
                'icon' => 'shopping-cart'
            ],
            [
                'step' => 4,
                'title' => 'Track Your Orders',
                'description' => 'Monitor your rental orders and manage your account from your dashboard.',
                'action' => 'View Dashboard',
                'url' => route('user.dashboard'),
                'icon' => 'dashboard'
            ]
        ];
    }

    /**
     * Get popular products for recommendations
     */
    private function getPopularProducts(): array
    {
        return [
            [
                'name' => 'Professional Commercial Kitchen',
                'description' => 'Fully equipped kitchen for large-scale catering',
                'price_per_day' => 150,
                'image' => '/images/products/commercial-kitchen.jpg',
                'url' => '#'
            ],
            [
                'name' => 'Mobile Food Truck Kitchen',
                'description' => 'Complete mobile kitchen solution',
                'price_per_day' => 120,
                'image' => '/images/products/food-truck.jpg',
                'url' => '#'
            ],
            [
                'name' => 'Event Catering Setup',
                'description' => 'Perfect for outdoor events and catering',
                'price_per_day' => 80,
                'image' => '/images/products/catering-setup.jpg',
                'url' => '#'
            ]
        ];
    }

    /**
     * Get company information
     */
    private function getCompanyInfo(): array
    {
        return [
            'established' => setting('company_established', '2020'),
            'locations' => setting('service_locations', 'London & Surrounding Areas'),
            'specialties' => [
                'Commercial Kitchen Rentals',
                'Event Catering Solutions',
                'Food Truck Kitchens',
                'Professional Equipment Rental'
            ],
            'certifications' => [
                'Food Safety Certified',
                'Health Department Approved',
                'Insurance Compliant',
                'Licensed & Bonded'
            ]
        ];
    }

    /**
     * Get email preferences options
     */
    private function getEmailPreferences(): array
    {
        return [
            'order_updates' => 'Receive updates about your orders',
            'promotions' => 'Get notified about special offers and promotions',
            'newsletters' => 'Monthly newsletter with tips and new products',
            'reminders' => 'Helpful reminders about your rentals and account'
        ];
    }
}
