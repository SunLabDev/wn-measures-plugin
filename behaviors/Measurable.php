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
     * @param string $name
     * @param int $amount
     * @return int
     */
    public function incrementMeasure(string $name, int $amount = 1): int
    {
        return $this->incrementOrDecrementMeasure('increment', $name, $amount);
    }

    /** Create if not exists and decrement a measure by its name and an amount
     * @param string $name
     * @param int $amount
     * @return int
     */
    public function decrementMeasure(string $name, int $amount = 1): int
    {
        return $this->incrementOrDecrementMeasure('decrement', $name, $amount);
    }

    /**
     * Reset a measure at a precise amount, default to 0
     * @param string $name
     * @param int $amount
     * @return int
     */
    public function resetMeasure(string $name, int $amount = 0): int
    {
        $measure = $this->parent->measures()->firstOrCreate(['name' => $name]);

        $measure->amount = $amount;
        $measure->save();

        Event::fire("sunlab.measures.resetMeasure", [$this->parent, $measure, $amount]);

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

    protected function incrementOrDecrementMeasure(string $incrementOrDecrement, string $name, int $amount = 1)
    {
        $measure = $this->parent->measures()->firstOrCreate(['name' => $name]);

        $measure->$incrementOrDecrement('amount', $amount);

        Event::fire("sunlab.measures.{$incrementOrDecrement}Measure", [$this->parent, $measure, $amount]);

        return $measure->amount;
    }
}
