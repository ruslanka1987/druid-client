<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Level23\Druid\TuningConfig\TuningConfig;

trait HasTuningConfig
{
    /**
     * @var TuningConfig|null
     */
    protected ?TuningConfig $tuningConfig = null;

    /**
     * Set the tuning config.
     *
     * @param array<string,string|int|bool|array<string,string|int>>|TuningConfig $tuningConfig
     *
     * @return $this
     */
    public function tuningConfig(array|TuningConfig $tuningConfig): self
    {
        if (!$tuningConfig instanceof TuningConfig) {
            $tuningConfig = new TuningConfig($tuningConfig);
        }

        $this->tuningConfig = $tuningConfig;

        return $this;
    }
}