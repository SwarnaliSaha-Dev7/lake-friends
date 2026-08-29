<?php

namespace Database\Seeders;

use App\Models\FoodItem;
use App\Models\Offer;
use App\Models\OfferItem;
use App\Models\OfferType;
use Illuminate\Database\Seeder;

class BuyTwoGetOneSixtyMlOfferSeeder extends Seeder
{
    // Applies to any 60ml peg (or 60ml cocktail) of these spirits — offers link
    // to a food_item_id (not a serving or a category), so "any 60ml liquor" has
    // to be approximated by attaching one offer_item per spirit, each scoped to
    // volume_ml=60 via the rules JSON so the item's 30ml/90ml pegs stay excluded.
    private const SPIRIT_NAMES = [
        'BACARDI WHITE RUM',
        'ABSOLUTE',
        'Smirnoff Triple Disttled Vodka',
        'OLD MONK MATURED XXX PREMIUM RUM',
        'MONKEY SHOULDER',
    ];

    public function run(): void
    {
        $clubId = 1;

        $offerType = OfferType::where('slug', 'b1g1')->first();
        if (!$offerType) {
            $this->command?->error('OfferType "b1g1" not found — run OfferTypeSeeder first.');
            return;
        }

        $offer = Offer::updateOrCreate(
            ['club_id' => $clubId, 'name' => 'Buy 2 Get 1 — 60ml Liquor'],
            [
                'offer_type_id'  => $offerType->id,
                'applies_to'     => 'liquor',
                'discount_value' => 0,
                'min_amount'     => 0,
                'buy_qty'        => '2',
                'get_qty'        => '1',
                'start_at'       => now()->toDateString(),
                'end_at'         => now()->addYear()->toDateString(),
                'status'         => 'active',
            ]
        );

        $spirits = FoodItem::where('club_id', $clubId)
            ->where('item_type', 'liquor')
            ->where('is_beer', 0)
            ->whereIn('name', self::SPIRIT_NAMES)
            ->get();

        foreach ($spirits as $spirit) {
            OfferItem::updateOrCreate(
                ['offer_id' => $offer->id, 'food_items_id' => $spirit->id],
                ['rules' => ['volume_ml' => 60]]
            );
        }

        $missing = collect(self::SPIRIT_NAMES)->diff($spirits->pluck('name'));
        if ($missing->isNotEmpty()) {
            $this->command?->warn('Spirits not found (skipped): ' . $missing->implode(', '));
        }

        $this->command?->info("Offer '{$offer->name}' seeded on {$spirits->count()} spirit(s), scoped to 60ml.");
    }
}
