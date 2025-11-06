<?php
namespace App\Enums;

enum ProductTypeEnum: string
{
    case KITCHEN_RENTAL = 'kitchen-rentals';
    case ITEM_RENTAL = 'item-rentals';


    public static function available(): array
    {
        return [
            self::KITCHEN_RENTAL,
            self::ITEM_RENTAL,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::KITCHEN_RENTAL => 'Kitchen Rentals',
            self::ITEM_RENTAL => 'Equipment Rentals',
        };
      
    }
}