<?php

namespace App\Services;

class BasiqCategoryMapper
{
    /**
     * Map Basiq merchant subClass titles to application expense categories.
     */
    private const CATEGORY_MAPPINGS = [
        // Education
        'Adult, Community and Other Education' => 'education',
        'Education and Childcare' => 'education',
        'Primary and Secondary Education' => 'education',
        'Tertiary Education' => 'education',
        
        // Groceries/Food
        'Supermarket and Grocery Stores' => 'groceries',
        'Grocery, Liquor and Tobacco Product Wholesaling' => 'groceries',
        'Bakery Product Manufacturing' => 'groceries',
        
        // Recreation/Entertainment
        'Recreational Goods Retailing' => 'recreation',
        'Sports and Physical Recreation Activities' => 'recreation',
        'Pubs, Taverns and Bars' => 'recreation',
        'Cafes, Restaurants and Takeaway Food Services' => 'recreation',
        
        // Retail/Clothing
        'Clothing, Footwear and Personal Accessory Retailing' => 'clothing',
        'Department Stores' => 'clothing',
        'Furniture, Floor Coverings, Houseware and Textile Goods Retailing' => 'clothing',
        
        // Medical/Health
        'Pharmaceutical and Other Store-Based Retailing' => 'medical',
        'Hospitals' => 'medical',
        
        // Transport/Fuel
        'Fuel Retailing' => 'transport',
        'Taxi and Other Road Transport' => 'transport',
        'Air and Space Transport' => 'transport',
        
        // Hardware/Building
        'Hardware, Building and Garden Supplies Retailing' => 'housing',
        'Electrical and Electronic Goods Retailing' => 'housing',
        
        // Insurance
        'Insurance' => 'insurance',
        'Insurance and Superannuation Funds' => 'insurance',
        
        // Loans/Debt
        'Loans' => 'debt',
        'Non-Depository Financing' => 'debt',
    ];

    /**
     * Map a Basiq subClass title to an application expense category.
     */
    public static function mapToExpenseCategory(string $basiqTitle): ?string
    {
        return self::CATEGORY_MAPPINGS[$basiqTitle] ?? null;
    }

    /**
     * Get all unmapped Basiq categories for logging/debugging.
     */
    public static function getUnmappedCategories(array $transactions): array
    {
        $unmapped = [];
        foreach ($transactions as $txn) {
            $title = $txn['subClass']['title'] ?? 'Unknown';
            if (!isset(self::CATEGORY_MAPPINGS[$title])) {
                $unmapped[$title] = true;
            }
        }
        return array_keys($unmapped);
    }
}