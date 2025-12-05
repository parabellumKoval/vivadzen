<?php

namespace App\Support;

class ReviewRewardContext
{
    protected bool $skipRewards = false;

    public function skipRewards(bool $state = true): void
    {
        $this->skipRewards = $state;
    }

    public function shouldSkipRewards(): bool
    {
        return $this->skipRewards;
    }
}

