<?php namespace SunLab\Measures\Behaviors;

use Illuminate\Support\Facades\Event;
use SunLab\Measures\Models\Measure;

/**
 * Measurable Extension
 * @package SunLab\Measures\Behaviors
 *
 * @method measures(): Winter\Storm\Database\Relations\MorphMany
 */
class Measurable extends \Winter\Storm\Extension\ExtensionBase
{
    protected $parent;

    public function __construct($parent)
    {
        $this->parent = $parent;
        $this->parent->morphMany['measures'] = [Measure::class, 'name' => 'measurable'];
    }

    /** Create if not exists and increment a measure by its name and an amount
     * @param $name
     * @param int $amount
     * @return int
     */
    public function incrementMeasure($name, int $amount = 1): int
    {
        $measure = $this->parent->measures()->firstOrCreate(['name' => $name]);

        $measure->increment('amount', $amount);

        Event::fire('sunlab.measures.incrementMeasure', [$this->parent, $measure]);

        return $measure->amount;
    }

    /** Create if not exists and return a Measure model from its name
     * @param $name
     * @return Measure
     */
    public function getMeasure($name): Measure
    {
        return $this->parent->measures()->firstOrCreate(['name' => $name]);
    }

    /** Create if not exists and return a Measure model from its name
     * @param $name
     * @return int
     */
    public function getAmountOf($name): ?int
    {
        return $this->getMeasure(...func_get_args())->amount;
    }
}
