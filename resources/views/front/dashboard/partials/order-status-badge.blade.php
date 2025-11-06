@php
    $statusConfig = [
        'pending' => [
            'class' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'icon' => '⏳',
            'label' => 'Pending'
        ],
        'paid' => [
            'class' => 'bg-blue-100 text-blue-800 border-blue-200',
            'icon' => '💳',
            'label' => 'Paid'
        ],
        'confirmed' => [
            'class' => 'bg-green-100 text-green-800 border-green-200',
            'icon' => '✅',
            'label' => 'Confirmed'
        ],
        'completed' => [
            'class' => 'bg-green-100 text-green-800 border-green-200',
            'icon' => '🎉',
            'label' => 'Completed'
        ],
        'cancelled' => [
            'class' => 'bg-red-100 text-red-800 border-red-200',
            'icon' => '❌',
            'label' => 'Cancelled'
        ],
        'failed' => [
            'class' => 'bg-red-100 text-red-800 border-red-200',
            'icon' => '⚠️',
            'label' => 'Failed'
        ],
        'refunded' => [
            'class' => 'bg-purple-100 text-purple-800 border-purple-200',
            'icon' => '💰',
            'label' => 'Refunded'
        ],
        'refund_requested' => [
            'class' => 'bg-orange-100 text-orange-800 border-orange-200',
            'icon' => '🔄',
            'label' => 'Refund Requested'
        ]
    ];
    
    $config = $statusConfig[$status] ?? $statusConfig['pending'];
@endphp

<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $config['class'] }}">
    <span class="mr-1">{{ $config['icon'] }}</span>
    {{ $config['label'] }}
</span>