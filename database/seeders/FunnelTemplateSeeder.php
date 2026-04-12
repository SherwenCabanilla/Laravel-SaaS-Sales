<?php

namespace Database\Seeders;

use App\Models\FunnelTemplate;
use App\Models\FunnelTemplateStep;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class FunnelTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $creatorId = $this->resolveSuperAdminCreatorId();

        foreach (array_merge($this->hardcodedTemplates(), $this->jsonTemplates()) as $templateDefinition) {
            $template = FunnelTemplate::query()->updateOrCreate(
                ['slug' => $templateDefinition['slug']],
                [
                    'created_by' => $creatorId,
                    'name' => $templateDefinition['name'],
                    'description' => $templateDefinition['description'],
                    'template_type' => $templateDefinition['template_type'],
                    'template_tags' => $templateDefinition['template_tags'],
                    'status' => 'published',
                    'published_at' => now(),
                ]
            );

            foreach ($templateDefinition['steps'] as $index => $stepDefinition) {
                FunnelTemplateStep::query()->updateOrCreate(
                    [
                        'funnel_template_id' => $template->id,
                        'slug' => $stepDefinition['slug'],
                    ],
                    [
                        'title' => $stepDefinition['title'],
                        'subtitle' => $stepDefinition['subtitle'] ?? null,
                        'type' => $stepDefinition['type'],
                        'content' => $stepDefinition['content'] ?? null,
                        'cta_label' => $stepDefinition['cta_label'] ?? null,
                        'price' => $stepDefinition['price'] ?? null,
                        'position' => $index + 1,
                        'is_active' => true,
                        'template' => 'simple',
                        'template_data' => [],
                        'step_tags' => $stepDefinition['step_tags'] ?? [],
                        'layout_json' => $stepDefinition['layout_json'],
                    ]
                );
            }
        }
    }

    private function resolveSuperAdminCreatorId(): ?int
    {
        $superAdminRoleId = Role::query()->where('slug', 'super-admin')->value('id');

        if ($superAdminRoleId) {
            $superAdminId = User::query()
                ->whereHas('roles', fn ($query) => $query->where('roles.id', $superAdminRoleId))
                ->value('id');

            if ($superAdminId) {
                return (int) $superAdminId;
            }
        }

        $fallbackId = User::query()->value('id');

        return $fallbackId ? (int) $fallbackId : null;
    }

    private function jsonTemplates(): array
    {
        $templatesDir = database_path('seeders/templates');
        if (!is_dir($templatesDir)) {
            return [];
        }

        $templateFiles = glob($templatesDir . '/*.json') ?: [];

        return collect($templateFiles)
            ->map(function (string $path) {
                try {
                    $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
                    return is_array($decoded) ? $decoded : null;
                } catch (\Throwable) {
                    return null;
                }
            })
            ->filter(fn ($template) => is_array($template) && isset($template['slug'], $template['name'], $template['steps']))
            ->values()
            ->all();
    }

    private function hardcodedTemplates(): array
    {
        return [
            [
                'name' => 'Service Lead + Offer Funnel',
                'slug' => 'service-lead-offer-funnel',
                'description' => 'Lead capture funnel with consultative offer, checkout, and thank-you flow.',
                'template_type' => 'service',
                'template_tags' => ['Lead Gen', 'Service', 'Consultation'],
                'steps' => [
                    [
                        'title' => 'Landing',
                        'slug' => 'landing',
                        'type' => 'landing',
                        'content' => 'Welcome to our proven service system.',
                        'cta_label' => 'See How It Works',
                        'layout_json' => $this->layoutWithElements([
                            $this->headingElement('Scale Your Client Acquisition'),
                            $this->textElement('Discover the exact process we use to turn visitors into booked calls and paying clients.'),
                            $this->buttonElement('See The Next Step', 'next_step'),
                        ]),
                    ],
                    [
                        'title' => 'Opt In',
                        'slug' => 'opt-in',
                        'type' => 'opt_in',
                        'content' => 'Capture high-intent leads.',
                        'layout_json' => $this->layoutWithElements([
                            $this->headingElement('Get The Funnel Blueprint'),
                            $this->textElement('Enter your details and we will send the full framework instantly.'),
                            $this->formElement([
                                ['type' => 'text', 'label' => 'First Name', 'required' => true],
                                ['type' => 'email', 'label' => 'Email', 'required' => true],
                            ], 'Get Instant Access'),
                        ]),
                    ],
                    [
                        'title' => 'Sales',
                        'slug' => 'sales',
                        'type' => 'sales',
                        'content' => 'Present offer details and social proof.',
                        'cta_label' => 'Go To Checkout',
                        'layout_json' => $this->layoutWithElements([
                            $this->headingElement('Done-For-You Funnel Buildout'),
                            $this->textElement('We set up your complete funnel stack, tracking, and conversion optimization in under 14 days.'),
                            $this->pricingElement('Growth Setup', 'PHP 9,900', 'one-time', 'Go To Checkout', 'next_step'),
                        ]),
                    ],
                    [
                        'title' => 'Checkout',
                        'slug' => 'checkout',
                        'type' => 'checkout',
                        'content' => 'Secure your setup slot.',
                        'cta_label' => 'Pay Now',
                        'price' => 9900,
                        'layout_json' => $this->layoutWithElements([
                            $this->headingElement('Secure Your Spot'),
                            $this->textElement('Complete your payment below to reserve onboarding this week.'),
                            $this->checkoutSummaryElement('Growth Setup', 'PHP 9,900', 'one-time', 'Pay Now'),
                        ]),
                    ],
                    [
                        'title' => 'Thank You',
                        'slug' => 'thank-you',
                        'type' => 'thank_you',
                        'content' => 'Payment received. We will email your onboarding steps.',
                        'layout_json' => $this->layoutWithElements([
                            $this->headingElement('You Are In'),
                            $this->textElement('Thank you for your order. Check your email for onboarding and next steps.'),
                        ]),
                    ],
                ],
            ],
            [
                'name' => 'Physical Product Quick Order',
                'slug' => 'physical-product-quick-order',
                'description' => 'Sales to checkout flow for COD or prepaid physical product campaigns.',
                'template_type' => 'physical_product',
                'template_tags' => ['Physical Product', 'Ecommerce', 'Quick Order'],
                'steps' => [
                    [
                        'title' => 'Sales',
                        'slug' => 'sales',
                        'type' => 'sales',
                        'content' => 'Show product value and urgency.',
                        'cta_label' => 'Order Now',
                        'layout_json' => $this->layoutWithElements([
                            $this->headingElement('Best Seller Product Offer'),
                            $this->textElement('Limited stock for this week. Reserve yours now with fast shipping.'),
                            $this->productOfferElement('Premium Bundle', 'PHP 1,499', 'Order Now', 'next_step'),
                        ]),
                    ],
                    [
                        'title' => 'Checkout',
                        'slug' => 'checkout',
                        'type' => 'checkout',
                        'content' => 'Collect delivery information and payment.',
                        'cta_label' => 'Place Order',
                        'price' => 1499,
                        'layout_json' => $this->layoutWithElements([
                            $this->headingElement('Complete Your Order'),
                            $this->shippingDetailsElement(),
                            $this->physicalCheckoutSummaryElement('Premium Bundle', 'PHP 1,499', 'Place Order'),
                        ]),
                    ],
                    [
                        'title' => 'Thank You',
                        'slug' => 'thank-you',
                        'type' => 'thank_you',
                        'content' => 'Order confirmed.',
                        'layout_json' => $this->layoutWithElements([
                            $this->headingElement('Order Confirmed'),
                            $this->textElement('Thank you. We will send shipping updates to your contact details soon.'),
                        ]),
                    ],
                ],
            ],
        ];
    }

    private function layoutWithElements(array $elements): array
    {
        return [
            'root' => [[
                'kind' => 'section',
                'id' => 'sec-' . uniqid(),
                'rows' => [[
                    'id' => 'row-' . uniqid(),
                    'columns' => [[
                        'id' => 'col-' . uniqid(),
                        'elements' => $elements,
                    ]],
                ]],
            ]],
            'sections' => [],
        ];
    }

    private function headingElement(string $content): array
    {
        return [
            'id' => 'el-' . uniqid(),
            'type' => 'heading',
            'content' => $content,
            'settings' => [],
        ];
    }

    private function textElement(string $content): array
    {
        return [
            'id' => 'el-' . uniqid(),
            'type' => 'text',
            'content' => $content,
            'settings' => [],
        ];
    }

    private function buttonElement(string $content, string $actionType): array
    {
        return [
            'id' => 'el-' . uniqid(),
            'type' => 'button',
            'content' => $content,
            'settings' => [
                'actionType' => $actionType,
            ],
        ];
    }

    private function formElement(array $fields, string $buttonLabel): array
    {
        return [
            'id' => 'el-' . uniqid(),
            'type' => 'form',
            'content' => $buttonLabel,
            'settings' => [
                'fields' => $fields,
            ],
        ];
    }

    private function pricingElement(string $plan, string $price, string $period, string $ctaLabel, string $actionType): array
    {
        return [
            'id' => 'el-' . uniqid(),
            'type' => 'pricing',
            'content' => '',
            'settings' => [
                'plan' => $plan,
                'price' => $price,
                'period' => $period,
                'ctaLabel' => $ctaLabel,
                'ctaActionType' => $actionType,
                'features' => [
                    'Ready-to-launch funnel pages',
                    'Checkout setup and automation',
                    'Tracking and analytics',
                ],
            ],
        ];
    }

    private function checkoutSummaryElement(string $plan, string $price, string $period, string $ctaLabel): array
    {
        return [
            'id' => 'el-' . uniqid(),
            'type' => 'checkout_summary',
            'content' => '',
            'settings' => [
                'plan' => $plan,
                'price' => $price,
                'period' => $period,
                'ctaLabel' => $ctaLabel,
            ],
        ];
    }

    private function productOfferElement(string $plan, string $price, string $ctaLabel, string $actionType): array
    {
        return [
            'id' => 'el-' . uniqid(),
            'type' => 'product_offer',
            'content' => '',
            'settings' => [
                'plan' => $plan,
                'price' => $price,
                'ctaLabel' => $ctaLabel,
                'ctaActionType' => $actionType,
                'features' => [
                    'Cash on delivery available',
                    'Free shipping promo',
                    '30-day guarantee',
                ],
            ],
        ];
    }

    private function shippingDetailsElement(): array
    {
        return [
            'id' => 'el-' . uniqid(),
            'type' => 'shipping_details',
            'content' => '',
            'settings' => [
                'title' => 'Delivery Information',
                'subtitle' => 'Provide your shipping details before placing the order.',
            ],
        ];
    }

    private function physicalCheckoutSummaryElement(string $plan, string $price, string $ctaLabel): array
    {
        return [
            'id' => 'el-' . uniqid(),
            'type' => 'physical_checkout_summary',
            'content' => '',
            'settings' => [
                'plan' => $plan,
                'price' => $price,
                'ctaLabel' => $ctaLabel,
            ],
        ];
    }
}
