<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\AffiliateConversionStatus;
use App\Models\Affiliate;
use App\Models\AffiliateConversion;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AffiliateStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalAffiliates = Affiliate::count();
        $totalConversions = AffiliateConversion::count();

        $outstandingCommission = (float) AffiliateConversion::query()
            ->where('status', AffiliateConversionStatus::Approved)
            ->whereNull('affiliate_payout_id')
            ->sum('commission_amount');

        $totalPaid = (float) AffiliateConversion::query()
            ->where('status', AffiliateConversionStatus::Paid)
            ->sum('commission_amount');

        return [
            Stat::make('Total Affiliates', number_format($totalAffiliates))
                ->description('registered partners')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('primary'),

            Stat::make('Total Conversions', number_format($totalConversions))
                ->description('all time')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('info'),

            Stat::make('Outstanding Commission', '€' . number_format($outstandingCommission, 2, ',', '.'))
                ->description('approved, not paid')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Total Paid', '€' . number_format($totalPaid, 2, ',', '.'))
                ->description('all time')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),
        ];
    }
}
