<?php

namespace Tests\Unit;

use Backpack\Profile\app\Console\Commands\GenerateBotUsers;
use ReflectionClass;
use Tests\TestCase;

class BotAvatarPromptDistributionTest extends TestCase
{
    public function test_side_face_angle_limit_is_enforced_as_a_batch_cap(): void
    {
        config()->set('backpack.profile.bot_generation.avatar_prompt.distribution.face_side_45_max_percent', 4);

        $inputs = [];
        for ($slot = 0; $slot < 100; $slot++) {
            $inputs[$slot] = [
                'slot' => $slot,
                'avatar_type' => 'face',
                'face_camera_angle' => $slot < 10 ? 'around 45-degree side angle' : 'frontal selfie, eye-level',
            ];
        }

        $command = new GenerateBotUsers();
        $method = (new ReflectionClass($command))->getMethod('enforceAvatarPlanDistribution');
        $method->setAccessible(true);

        $result = $method->invoke($command, $inputs);

        $sideAngles = collect($result)
            ->filter(fn (array $input): bool => str_contains(strtolower((string) $input['face_camera_angle']), '45'))
            ->count();

        $this->assertSame(4, $sideAngles);
    }
}
