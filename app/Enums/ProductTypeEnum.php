<?php
namespace App\Enums;

enum ProductTypeEnum: string
{
    case KITCHEN_RENTAL = 'kitchen-rental';
    case ITEM_RENTAL = 'item-rental';


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
            self::KITCHEN_RENTAL => 'Kitchen Rental',
            self::ITEM_RENTAL => 'Item Rental',
        };
      
    }
}