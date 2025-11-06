@extends('emails.layouts.base')

@section('content')
<div class="greeting">
    Welcome to {{ $companyName }}, {{ $userName }}! 🎉
</div>

<div class="content-section text-center">
    <h1 style="color: var(--brand-deep-ash); margin-bottom: 10px;">Welcome to the Family! 🏠</h1>
    <p style="font-size: 18px; color: var(--text-secondary);">We're thrilled to have you join our community of kitchen rental professionals.</p>
    <p>Your account was created on {{ $registrationDate }} and you're all set to start exploring our premium kitchen rental solutions.</p>
</div>

<!-- Welcome Offer -->
@if($welcomeOffer)
<div class="content-section">
    <div class="highlight-box text-center">
        <h3 style="margin-top: 0; color: var(--brand-deep-ash);">🎁 Welcome Gift Just for You!</h3>
        <div style="font-size: 48px; margin: 20px 0;">💝</div>
        <h2 style="color: var(--success-color); margin: 15px 0;">{{ $welcomeOffer['discount_percentage'] }}% OFF</h2>
        <p style="font-size: 18px; margin: 15px 0;">Your First Rental</p>
        
        <div style="background-color: white; padding: 15px; border-radius: 8px; margin: 20px 0; border: 2px dashed var(--brand-accent);">
            <p style="margin: 0; font-size: 14px; color: var(--text-muted);">Use promo code:</p>
            <p style="font-size: 24px; font-weight: 600; color: var(--brand-deep-ash); margin: 5px 0; letter-spacing: 2px;">{{ $welcomeOffer['discount_code'] }}</p>
        </div>
        
        <p style="font-size: 14px; color: var(--text-muted); margin: 10px 0;">
            Valid until {{ $welcomeOffer['valid_until'] }} • Minimum order £{{ $welcomeOffer['minimum_order'] }}<br>
            {{ $welcomeOffer['terms'] }}
        </p>
        
        <a href="{{ $productsUrl }}" class="btn btn-success" style="margin-top: 15px;">
            🛒 Start Shopping Now
        </a>
    </div>
</div>
@endif

<!-- Getting Started Steps -->
<div class="content-section">
    <h3>🚀 Getting Started is Easy!</h3>
    <p>Follow these simple steps to make the most of your {{ $companyName }} experience:</p>
    
    @foreach($gettingStartedSteps as $step)
    <div class="card" style="margin-bottom: 15px;">
        <div style="display: flex; align-items: center;">
            <div style="background-color: var(--brand-deep-ash); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; margin-right: 20px; flex-shrink: 0;">
                {{ $step['step'] }}
            </div>
            <div style="flex: 1;">
                <h4 style="margin: 0 0 5px 0;">{{ $step['title'] }}</h4>
                <p style="margin: 0 0 10px 0; color: var(--text-muted);">{{ $step['description'] }}</p>
                <a href="{{ $step['url'] }}" class="btn btn-secondary" style="font-size: 14px; padding: 8px 16px;">
                    @switch($step['icon'])
                        @case('user-circle')
                            👤
                            @break
                        @case('search')
                            🔍
                            @break
                        @case('shopping-cart')
                            🛒
                            @break
                        @case('dashboard')
                            📊
                            @break
                        @default
                            ➡️
                    @endswitch
                    {{ $step['action'] }}
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Popular Products -->
<div class="content-section">
    <h3>🔥 Popular Kitchen Solutions</h3>
    <p>Check out our most popular rental options to get started:</p>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0;">
        @foreach($popularProducts as $product)
        <div class="card text-center">
            <div style="background-color: var(--brand-light-blue); height: 120px; border-radius: 8px; margin-bottom: 15px; display: flex; align-items: center; justify-content: center; font-size: 48px;">
                🍳
            </div>
            <h4 style="margin: 0 0 8px 0;">{{ $product['name'] }}</h4>
            <p style="margin: 0 0 10px 0; font-size: 14px; color: var(--text-muted);">{{ $product['description'] }}</p>
            <p style="margin: 0 0 15px 0; font-size: 18px; font-weight: 600; color: var(--brand-deep-ash);">
                £{{ $product['price_per_day'] }}<span style="font-size: 14px; font-weight: normal;">/day</span>
            </p>
            <a href="{{ $product['url'] }}" class="btn btn-primary" style="font-size: 14px; padding: 8px 16px;">
                View Details
            </a>
        </div>
        @endforeach
    </div>
</div>

<!-- Company Information -->
<div class="content-section">
    <h3>🏢 About {{ $companyName }}</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
        <div class="card">
            <h4>📅 Established {{ $companyInfo['established'] }}</h4>
            <p>Serving {{ $companyInfo['locations'] }} with premium kitchen rental solutions.</p>
        </div>
        
        <div class="card">
            <h4>🌟 Our Specialties</h4>
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($companyInfo['specialties'] as $specialty)
                <li>{{ $specialty }}</li>
                @endforeach
            </ul>
        </div>
        
        <div class="card">
            <h4>✅ Certifications</h4>
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($companyInfo['certifications'] as $certification)
                <li>{{ $certification }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

<!-- Email Verification -->
@if($isVerificationRequired)
<div class="content-section">
    <div class="alert alert-warning text-center">
        <h3 style="margin: 0 0 10px 0;">📧 Please Verify Your Email</h3>
        <p style="margin: 0 0 15px 0;">To ensure account security and receive important updates, please verify your email address.</p>
        <a href="#" class="btn btn-primary">Verify Email Address</a>
    </div>
</div>
@endif

<!-- Email Preferences -->
<div class="content-section">
    <h3>📬 Stay Informed</h3>
    <p>We'll keep you updated with relevant information. You can manage your email preferences anytime:</p>
    
    <div class="card">
        @foreach($emailPreferences as $key => $description)
        <div style="display: flex; align-items: center; padding: 8px 0; border-bottom: 1px solid var(--border-color);">
            <div style="margin-right: 15px;">✅</div>
            <div>
                <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}</strong><br>
                <small style="color: var(--text-muted);">{{ $description }}</small>
            </div>
        </div>
        @endforeach
        
        <div style="text-align: center; margin-top: 15px;">
            <a href="{{ $profileUrl }}" class="btn btn-secondary">Manage Email Preferences</a>
        </div>
    </div>
</div>

<!-- Support Information -->
<div class="content-section">
    <h3>🤝 We're Here to Help!</h3>
    <p>Our dedicated support team is ready to assist you with any questions or needs:</p>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
        <div class="card text-center">
            <div style="font-size: 32px; margin-bottom: 10px;">📞</div>
            <h4>Phone Support</h4>
            <p><a href="tel:{{ $contactPhone }}">{{ $contactPhone }}</a></p>
            <p style="font-size: 14px; color: var(--text-muted);">Mon-Fri: 8AM-8PM</p>
        </div>
        
        <div class="card text-center">
            <div style="font-size: 32px; margin-bottom: 10px;">📧</div>
            <h4>Email Support</h4>
            <p><a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a></p>
            <p style="font-size: 14px; color: var(--text-muted);">24/7 Response</p>
        </div>
        
        <div class="card text-center">
            <div style="font-size: 32px; margin-bottom: 10px;">💬</div>
            <h4>Live Chat</h4>
            <p><a href="{{ $websiteUrl }}">Website Chat</a></p>
            <p style="font-size: 14px; color: var(--text-muted);">Mon-Fri: 9AM-6PM</p>
        </div>
    </div>
</div>

<!-- Call to Action -->
<div class="content-section text-center">
    <h3 style="color: var(--brand-deep-ash);">Ready to Get Started? 🎯</h3>
    <p>Explore our kitchen rental solutions and find the perfect fit for your needs!</p>
    
    <div style="margin: 30px 0;">
        <a href="{{ $productsUrl }}" class="btn btn-primary" style="margin: 10px;">
            🔍 Browse Kitchens
        </a>
        
        <a href="{{ $dashboardUrl }}" class="btn btn-secondary" style="margin: 10px;">
            📊 View Dashboard
        </a>
        
        <a href="{{ $profileUrl }}" class="btn btn-secondary" style="margin: 10px;">
            👤 Complete Profile
        </a>
    </div>
</div>

<!-- Social Follow -->
<div class="content-section text-center">
    <h3>🌐 Follow Us for Updates & Tips</h3>
    <p>Stay connected and get the latest kitchen rental tips, success stories, and special offers:</p>
    
    <div style="margin: 20px 0;">
        @if(isset($socialLinks))
            @foreach($socialLinks as $platform => $url)
                @if($url && $url !== '#')
                <a href="{{ $url }}" class="btn btn-secondary" style="margin: 5px; font-size: 14px; padding: 8px 16px;">
                    @switch($platform)
                        @case('facebook')
                            📘 Facebook
                            @break
                        @case('twitter')
                            🐦 Twitter
                            @break
                        @case('instagram')
                            📷 Instagram
                            @break
                        @case('linkedin')
                            💼 LinkedIn
                            @break
                        @default
                            🔗 {{ ucfirst($platform) }}
                    @endswitch
                </a>
                @endif
            @endforeach
        @endif
    </div>
</div>

<!-- Thank You -->
<div class="content-section text-center">
    <h2 style="color: var(--brand-deep-ash); margin-bottom: 15px;">Thank You for Choosing {{ $companyName }}! 🙏</h2>
    <p style="font-size: 18px;">We're excited to be part of your culinary journey and look forward to serving you with excellence.</p>
    
    <div style="background-color: var(--brand-light-blue); padding: 20px; border-radius: 8px; margin: 20px 0;">
        <p style="margin: 0; font-style: italic; color: var(--brand-deep-ash);">
            "Your success is our success. We're here to provide you with the kitchen solutions that help your business thrive."
        </p>
        <p style="margin: 10px 0 0 0; font-weight: 600; color: var(--brand-deep-ash);">
            - The {{ $companyName }} Team
        </p>
    </div>
</div>
@endsection