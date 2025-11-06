<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Site Settings
            [
                'key' => 'site_name', 
                'value' => 'Sejis Kitchen Rental', 
                'type' => 'string',
                'group' => 'site', 
                'is_public' => true,
                'description' => 'The name of the website'
            ],
            [
                'key' => 'site_description', 
                'value' => 'Professional Kitchen Rental Service', 
                'type' => 'string',
                'group' => 'site', 
                'is_public' => true,
                'description' => 'Site description for SEO'
            ],
            [
                'key' => 'site_logo', 
                'value' => '', 
                'type' => 'file',
                'group' => 'site', 
                'is_public' => true,
                'description' => 'Site logo image path'
            ],
            [
                'key' => 'contact_email', 
                'value' => 'info@sejis.com', 
                'type' => 'string',
                'group' => 'site', 
                'is_public' => true,
                'description' => 'Main contact email address'
            ],
            [
                'key' => 'contact_phone', 
                'value' => '+44 123 456 7890', 
                'type' => 'string',
                'group' => 'site', 
                'is_public' => true,
                'description' => 'Main contact phone number'
            ],
            [
                'key' => 'contact_address', 
                'value' => '123 Kitchen Street, London, UK', 
                'type' => 'string',
                'group' => 'site', 
                'is_public' => true,
                'description' => 'Business address'
            ],
            
            // Payment Settings
            [
                'key' => 'currency', 
                'value' => 'GBP', 
                'type' => 'string',
                'group' => 'payment', 
                'is_public' => true,
                'description' => 'Default currency code'
            ],
            [
                'key' => 'currency_symbol', 
                'value' => '£', 
                'type' => 'string',
                'group' => 'payment', 
                'is_public' => true,
                'description' => 'Currency symbol'
            ],
            [
                'key' => 'tax_rate', 
                'value' => '20', 
                'type' => 'integer',
                'group' => 'payment',
                'description' => 'Tax rate percentage'
            ],
            [
                'key' => 'tax_enabled', 
                'value' => '1', 
                'type' => 'boolean',
                'group' => 'payment',
                'description' => 'Enable tax calculation'
            ],
            [
                'key' => 'stripe_enabled', 
                'value' => '1', 
                'type' => 'boolean',
                'group' => 'payment',
                'description' => 'Enable Stripe payment gateway'
            ],
            [
                'key' => 'stripe_public_key', 
                'value' => '', 
                'type' => 'string',
                'group' => 'payment', 
                'is_public' => true,
                'description' => 'Stripe publishable key'
            ],
            [
                'key' => 'stripe_secret_key', 
                'value' => '', 
                'type' => 'string',
                'group' => 'payment', 
                'is_encrypted' => true,
                'description' => 'Stripe secret key'
            ],
            [
                'key' => 'paypal_enabled', 
                'value' => '1', 
                'type' => 'boolean',
                'group' => 'payment',
                'description' => 'Enable PayPal payment gateway'
            ],
            [
                'key' => 'bank_deposit_enabled', 
                'value' => '1', 
                'type' => 'boolean',
                'group' => 'payment',
                'description' => 'Enable bank deposit payment method'
            ],
            [
                'key' => 'takepayments_enabled', 
                'value' => '0', 
                'type' => 'boolean',
                'group' => 'payment',
                'description' => 'Enable TakePayments gateway'
            ],
            
            // Email Settings
            [
                'key' => 'mail_from_address', 
                'value' => 'noreply@sejis.com', 
                'type' => 'string',
                'group' => 'email',
                'description' => 'Default from email address'
            ],
            [
                'key' => 'mail_from_name', 
                'value' => 'Sejis Kitchen Rental', 
                'type' => 'string',
                'group' => 'email',
                'description' => 'Default from name'
            ],
            [
                'key' => 'order_confirmation_enabled', 
                'value' => '1', 
                'type' => 'boolean',
                'group' => 'email',
                'description' => 'Send order confirmation emails'
            ],
            [
                'key' => 'order_status_updates_enabled', 
                'value' => '1', 
                'type' => 'boolean',
                'group' => 'email',
                'description' => 'Send order status update emails'
            ],
            [
                'key' => 'welcome_email_enabled', 
                'value' => '1', 
                'type' => 'boolean',
                'group' => 'email',
                'description' => 'Send welcome emails to new customers'
            ],
            [
                'key' => 'admin_notifications_enabled', 
                'value' => '1', 
                'type' => 'boolean',
                'group' => 'email',
                'description' => 'Send admin notification emails'
            ],
            [
                'key' => 'admin_email', 
                'value' => 'admin@sejis.com', 
                'type' => 'string',
                'group' => 'email',
                'description' => 'Primary admin email address'
            ],
            [
                'key' => 'finance_admin_email', 
                'value' => 'finance@sejis.com', 
                'type' => 'string',
                'group' => 'email',
                'description' => 'Finance admin email for payment notifications'
            ],
            [
                'key' => 'operations_admin_email', 
                'value' => 'operations@sejis.com', 
                'type' => 'string',
                'group' => 'email',
                'description' => 'Operations admin email for delivery issues'
            ],
            [
                'key' => 'customer_service_email', 
                'value' => 'support@sejis.com', 
                'type' => 'string',
                'group' => 'email',
                'description' => 'Customer service email for complaints'
            ],
            [
                'key' => 'sales_manager_email', 
                'value' => 'sales@sejis.com', 
                'type' => 'string',
                'group' => 'email',
                'description' => 'Sales manager email for high-value orders'
            ],
            [
                'key' => 'high_value_threshold', 
                'value' => '1000', 
                'type' => 'integer',
                'group' => 'email',
                'description' => 'Order amount threshold for high-value notifications'
            ],
            [
                'key' => 'email_queue_enabled', 
                'value' => '1', 
                'type' => 'boolean',
                'group' => 'email',
                'description' => 'Use queue for email delivery'
            ],
            [
                'key' => 'welcome_discount_enabled', 
                'value' => '1', 
                'type' => 'boolean',
                'group' => 'email',
                'description' => 'Include welcome discount in welcome emails'
            ],
            [
                'key' => 'welcome_discount_percentage', 
                'value' => '10', 
                'type' => 'integer',
                'group' => 'email',
                'description' => 'Welcome discount percentage'
            ],
            [
                'key' => 'welcome_discount_code', 
                'value' => 'WELCOME10', 
                'type' => 'string',
                'group' => 'email',
                'description' => 'Welcome discount code'
            ],
            [
                'key' => 'welcome_minimum_order', 
                'value' => '100', 
                'type' => 'integer',
                'group' => 'email',
                'description' => 'Minimum order amount for welcome discount'
            ],
            
            // Business Settings
            [
                'key' => 'booking_advance_days', 
                'value' => '30', 
                'type' => 'integer',
                'group' => 'business',
                'description' => 'Maximum days in advance for booking'
            ],
            [
                'key' => 'min_booking_hours', 
                'value' => '2', 
                'type' => 'integer',
                'group' => 'business',
                'description' => 'Minimum booking duration in hours'
            ],
            [
                'key' => 'max_booking_hours', 
                'value' => '12', 
                'type' => 'integer',
                'group' => 'business',
                'description' => 'Maximum booking duration in hours'
            ],
            [
                'key' => 'booking_time_slots', 
                'value' => json_encode(['0800-1000', '1000-1200', '1200-1400', '1400-1600', '1600-1800', '1800-2000']), 
                'type' => 'json',
                'group' => 'business',
                'description' => 'Available booking time slots'
            ],
            [
                'key' => 'auto_approve_bookings', 
                'value' => '0', 
                'type' => 'boolean',
                'group' => 'business',
                'description' => 'Automatically approve bookings'
            ],
            [
                'key' => 'cancellation_hours', 
                'value' => '24', 
                'type' => 'integer',
                'group' => 'business',
                'description' => 'Hours before booking for free cancellation'
            ],
            
            // Feature Toggles
            [
                'key' => 'registration_enabled', 
                'value' => '1', 
                'type' => 'boolean',
                'group' => 'features',
                'description' => 'Allow new user registration'
            ],
            [
                'key' => 'reviews_enabled', 
                'value' => '1', 
                'type' => 'boolean',
                'group' => 'features', 
                'is_public' => true,
                'description' => 'Enable customer reviews'
            ],
            [
                'key' => 'newsletter_enabled', 
                'value' => '1', 
                'type' => 'boolean',
                'group' => 'features', 
                'is_public' => true,
                'description' => 'Enable newsletter subscription'
            ],
            [
                'key' => 'maintenance_mode', 
                'value' => '0', 
                'type' => 'boolean',
                'group' => 'features',
                'description' => 'Site maintenance mode'
            ],
            
            // SEO Settings
            [
                'key' => 'meta_title', 
                'value' => 'Sejis Kitchen Rental - Professional Kitchen Space', 
                'type' => 'string',
                'group' => 'seo', 
                'is_public' => true,
                'description' => 'Default meta title'
            ],
            [
                'key' => 'meta_description', 
                'value' => 'Rent professional kitchen space by the hour. Perfect for catering businesses, food startups, and culinary events.', 
                'type' => 'string',
                'group' => 'seo', 
                'is_public' => true,
                'description' => 'Default meta description'
            ],
            [
                'key' => 'meta_keywords', 
                'value' => 'kitchen rental, commercial kitchen, catering space, food business', 
                'type' => 'string',
                'group' => 'seo', 
                'is_public' => true,
                'description' => 'Default meta keywords'
            ]
        ];
        
        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}