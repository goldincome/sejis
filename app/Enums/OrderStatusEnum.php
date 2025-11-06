<?php
namespace App\Enums;

enum OrderStatusEnum: string
{

    case PENDING = 'pending';
    case CANCELED = 'canceled';
    case PAID = 'paid';
    case COMPLETED = 'completed';

    

    public function customerLabel(): string
    {
        return match ($this) {
            self::PENDING => 'pending',
            self::CANCELED => 'Order Canceled',
            self::PAID => 'Paid',
            self::COMPLETED => 'Completed',
        };
    }

    public function adminLabel(): string
    {
        return match ($this) {
            self::PENDING=> 'pending',
            self::CANCELED => 'Order Canceled',
            self::PAID => 'Payment Made',
            self::COMPLETED => 'Completed',
        };
    }
   
    public static function toArray(): array
    {
      return array_column(self::cases(), 'value');
    }

}
