<?php

namespace App\Support;

use App\Http\Controllers\Api\BBPSController;
use App\Models\Operator;
use Illuminate\Database\Eloquent\Builder;

class DefaultOperatorCatalog
{
    public static function definitions(): array
    {
        return array_merge([
            ['code' => 'AIRTEL',      'name' => 'AIRTEL',             'category' => 'mobile'],
            ['code' => 'JIO',         'name' => 'JIO',                'category' => 'mobile'],
            ['code' => 'VI',          'name' => 'VI',                 'category' => 'mobile'],
            ['code' => 'BSNL_STV',    'name' => 'BSNL STV',           'category' => 'mobile'],
            ['code' => 'BSNL_TOPUP',  'name' => 'BSNL TOPUP',         'category' => 'mobile'],
            ['code' => 'IDEA',        'name' => 'IDEA',               'category' => 'mobile'],
            ['code' => 'AIRTEL_DTH',  'name' => 'Airtel DTH',         'category' => 'dth'],
            ['code' => 'TATASKY',     'name' => 'TATASKY',            'category' => 'dth'],
            ['code' => 'SUNDIRECT',   'name' => 'SUNDIRECT',          'category' => 'dth'],
            ['code' => 'VIDEOCON',    'name' => 'VIDEOCON',           'category' => 'dth'],
        ], self::bbpsDefinitions());
    }

    public static function categories(): array
    {
        return [
            'mobile', 'dth', 'broadband',
            'electricity', 'gas', 'water', 'insurance',
            'landline', 'loan', 'fastag', 'credit_card',
            'municipal_tax', 'education', 'subscription',
        ];
    }

    private static function bbpsDefinitions(): array
    {
        $rows = [];
        foreach (BBPSController::BILLERS as $category => $billers) {
            foreach ($billers as $biller) {
                $rows[] = [
                    'code'     => $biller['id'],
                    'name'     => $biller['name'],
                    'category' => $category,
                ];
            }
        }

        return $rows;
    }

    public static function ensure(): void
    {
        $legacyTata = Operator::withTrashed()->where('code', 'TATA_SKY')->first();
        $targetTata = Operator::withTrashed()->where('code', 'TATASKY')->first();
        if ($legacyTata && ! $targetTata) {
            $legacyTata->update(['code' => 'TATASKY']);
        }

        foreach (self::definitions() as $row) {
            $operator = Operator::withTrashed()->updateOrCreate(
                ['code' => $row['code']],
                [
                    'name'             => $row['name'],
                    'category'         => $row['category'],
                    'is_active'        => true,
                    'prepaid_enabled'  => $row['category'] === 'mobile',
                    'postpaid_enabled' => false,
                    'min_amount'       => 10,
                    'max_amount'       => 100000,
                ]
            );

            if (method_exists($operator, 'trashed') && $operator->trashed()) {
                $operator->restore();
            }
        }
    }

    public static function ordered(Builder $query): Builder
    {
        $codes = array_values(array_unique(array_column(self::definitions(), 'code')));
        $case = 'CASE';
        foreach ($codes as $index => $code) {
            $case .= " WHEN code = '" . str_replace("'", "''", $code) . "' THEN " . ($index + 1);
        }
        $case .= ' ELSE 999 END';

        return $query->orderByRaw($case)->orderBy('name');
    }
}
