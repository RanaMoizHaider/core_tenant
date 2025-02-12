@php
    use App\Data\Stripe\StripeDataLoader;
@endphp

<div class="mt-2" x-data="{
    state: $wire.entangle('mountedActionsData.0.billing_period'),
    formatPrice: price => `R$ ${(price / 100).toFixed(2)}`,
}">

    <div class="pb-[76px]">
        <h2 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">
            Your Benefits
        </h2>

        <div class="space-y-4">
            @foreach (StripeDataLoader::getProductsData()->first()['features'] as $feature)
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <x-heroicon-m-check-circle class="size-5 text-primary-500" />
                    </div>

                    <span class="text-sm text-left text-gray-900 dark:text-white">
                        {{$feature['description']}}
                    </span>
                </div>
            @endforeach
        </div>
        <br>
    </div>

    <div class="relative bg-white border-t border-gray-200 rounded-b-xl dark:bg-gray-900 dark:border-gray-800">
        <!-- Price and information -->
        <div class="px-10 py-4">
            <div class="flex flex-wrap items-baseline gap-1">
                @foreach (StripeDataLoader::getProductsData()->first()['prices'] as $price)
                    <div class="flex items-baseline w-full gap-1" x-show="state === '{{ $price['interval'] }}'">
                        <span class="text-3xl font-semibold text-gray-900 dark:text-white">
                            {{ 'R$ ' . number_format($price['interval'] === 'yearly' ? $price['unit_amount'] / 12 : $price['unit_amount'], 2, ',', '.') }}
                        </span>
                        <span class="text-sm text-gray-500 dark:text-gray-400"> / </span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            per {{ $price['interval_description'] }}

                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Centered button below price, occupying full modal width -->
        <div class="justify-center">
            {{ $action->getModalAction('checkout') }}
        </div>
    </div>
</div>
