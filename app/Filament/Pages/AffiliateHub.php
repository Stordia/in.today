<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\AffiliateConversionStatus;
use App\Enums\AffiliatePayoutStatus;
use App\Filament\Resources\AffiliateConversionResource;
use App\Filament\Resources\AffiliateLinkResource;
use App\Filament\Resources\AffiliatePayoutResource;
use App\Filament\Resources\AffiliateResource;
use App\Models\Affiliate;
use App\Models\AffiliateConversion;
use App\Models\AffiliateLink;
use App\Models\AffiliatePayout;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class AffiliateHub extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Partners';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'affiliate-hub';

    protected static string $view = 'filament.pages.affiliate-hub';

    public string $activeTab = 'overview';

    // KPI data
    public int $totalAffiliates = 0;

    public int $totalConversions = 0;

    public float $outstandingCommission = 0;

    public float $totalPaid = 0;

    // Tab data collections
    public Collection $recentConversions;

    public Collection $affiliates;

    public Collection $links;

    public Collection $conversions;

    public Collection $payouts;

    public static function getNavigationLabel(): string
    {
        return 'Affiliates';
    }

    public function getTitle(): string
    {
        return 'Affiliate Hub';
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && $user->isPlatformAdmin();
    }

    public function mount(): void
    {
        $this->loadKPIs();
        $this->loadTabData();
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    private function loadKPIs(): void
    {
        $this->totalAffiliates = Affiliate::count();
        $this->totalConversions = AffiliateConversion::count();

        // Outstanding = approved but not yet in a payout
        $this->outstandingCommission = (float) AffiliateConversion::query()
            ->where('status', AffiliateConversionStatus::Approved)
            ->whereNull('affiliate_payout_id')
            ->sum('commission_amount');

        // Total paid = conversions with status = paid
        $this->totalPaid = (float) AffiliateConversion::query()
            ->where('status', AffiliateConversionStatus::Paid)
            ->sum('commission_amount');
    }

    private function loadTabData(): void
    {
        // Recent conversions for Overview tab (last 10)
        $this->recentConversions = AffiliateConversion::query()
            ->with(['affiliate', 'affiliateLink', 'contactLead'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Affiliates tab data
        $this->affiliates = Affiliate::query()
            ->withCount(['links', 'conversions'])
            ->withSum(['conversions as paid_commission' => fn ($q) => $q->where('status', AffiliateConversionStatus::Paid)], 'commission_amount')
            ->orderBy('name')
            ->get();

        // Links tab data (latest 20)
        $this->links = AffiliateLink::query()
            ->with('affiliate')
            ->withCount('conversions')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Conversions tab data (latest 20)
        $this->conversions = AffiliateConversion::query()
            ->with(['affiliate', 'affiliateLink', 'contactLead', 'restaurant'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Payouts tab data (latest 20)
        $this->payouts = AffiliatePayout::query()
            ->with('affiliate')
            ->withCount('conversions')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
    }

    // Helper methods for generating URLs
    public function getAffiliateResourceUrl(string $action, ?Affiliate $record = null): string
    {
        if ($record) {
            return AffiliateResource::getUrl($action, ['record' => $record]);
        }

        return AffiliateResource::getUrl($action);
    }

    public function getLinkResourceUrl(string $action, ?AffiliateLink $record = null): string
    {
        if ($record) {
            return AffiliateLinkResource::getUrl($action, ['record' => $record]);
        }

        return AffiliateLinkResource::getUrl($action);
    }

    public function getConversionResourceUrl(string $action, ?AffiliateConversion $record = null): string
    {
        if ($record) {
            return AffiliateConversionResource::getUrl($action, ['record' => $record]);
        }

        return AffiliateConversionResource::getUrl($action);
    }

    public function getPayoutResourceUrl(string $action, ?AffiliatePayout $record = null): string
    {
        if ($record) {
            return AffiliatePayoutResource::getUrl($action, ['record' => $record]);
        }

        return AffiliatePayoutResource::getUrl($action);
    }

    // Status badge helpers
    public function getConversionStatusColor(AffiliateConversionStatus|string $status): string
    {
        if ($status instanceof AffiliateConversionStatus) {
            return $status->color();
        }

        return AffiliateConversionStatus::tryFrom($status)?->color() ?? 'gray';
    }

    public function getConversionStatusLabel(AffiliateConversionStatus|string $status): string
    {
        if ($status instanceof AffiliateConversionStatus) {
            return $status->label();
        }

        return AffiliateConversionStatus::tryFrom($status)?->label() ?? ucfirst($status);
    }

    public function getPayoutStatusColor(AffiliatePayoutStatus|string $status): string
    {
        if ($status instanceof AffiliatePayoutStatus) {
            return $status->color();
        }

        return AffiliatePayoutStatus::tryFrom($status)?->color() ?? 'gray';
    }

    public function getPayoutStatusLabel(AffiliatePayoutStatus|string $status): string
    {
        if ($status instanceof AffiliatePayoutStatus) {
            return $status->label();
        }

        return AffiliatePayoutStatus::tryFrom($status)?->label() ?? ucfirst($status);
    }

    public function getAffiliateTypeBadgeColor(string $type): string
    {
        return match ($type) {
            'agency' => 'info',
            'creator' => 'success',
            'consultant' => 'warning',
            default => 'gray',
        };
    }
}
