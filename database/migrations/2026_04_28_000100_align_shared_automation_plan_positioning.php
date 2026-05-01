<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $plans = [
            'free-trial' => [
                'summary' => 'Account Owner dashboard access during trial period with limited team, leads, and funnel usage.',
                'features' => [
                    'Account Owner dashboard access during trial period',
                    'Limited team, leads, and funnel usage',
                    'Upgrade to Starter, Growth, or Scale anytime',
                    'No advanced shared automation during the trial period',
                ],
                'max_workflows' => null,
                'automation_enabled' => false,
            ],
            'starter' => [
                'summary' => 'For teams launching their first lead capture and conversion funnels with simple built-in operations.',
                'features' => [
                    '1 workspace with Account Owner dashboard access',
                    'Lead capture funnels and conversion tracking',
                    'Basic funnel analytics and payment monitoring',
                    'Basic funnel operations with built-in tracking and status updates',
                ],
                'max_workflows' => null,
                'automation_enabled' => false,
            ],
            'growth' => [
                'summary' => 'For growing businesses ready to unlock shared automation across lead, funnel, and billing workflows.',
                'features' => [
                    'Unlimited active funnels for one brand workspace',
                    'Shared n8n automation included for lead, funnel, billing, and reminder flows',
                    'Role-based dashboards and pipeline visibility',
                    'PayMongo-ready checkout journeys for your offers',
                ],
                'max_workflows' => 10,
                'automation_enabled' => true,
            ],
            'scale' => [
                'summary' => 'For teams that want advanced automation coverage and higher-volume operations on the shared engine.',
                'features' => [
                    'Everything in Growth plus advanced shared automation coverage',
                    'Priority support for launch, billing, and operational workflows',
                    'Multi-team operational visibility for leaders',
                    'Built for aggressive campaign and revenue targets',
                ],
                'max_workflows' => null,
                'automation_enabled' => true,
            ],
        ];

        foreach ($plans as $code => $values) {
            DB::table('plans')
                ->where('code', $code)
                ->update([
                    'summary' => $values['summary'],
                    'features' => json_encode($values['features'], JSON_THROW_ON_ERROR),
                    'max_workflows' => $values['max_workflows'],
                    'automation_enabled' => $values['automation_enabled'],
                    'updated_at' => $now,
                ]);
        }
    }

    public function down(): void
    {
        // No-op: this migration aligns system plan messaging and automation entitlement.
    }
};
