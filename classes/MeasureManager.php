<?php namespace SunLab\Measures\Classes;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Winter\Storm\Database\Builder;
use Winter\Storm\Support\Facades\Str;
use SunLab\Measures\Behaviors\Measurable;
use SunLab\Measures\Models\Measure;

abstract class MeasureManager
{
    public static function incrementMeasure($model, $name = 1, $amount = 1)
    {
        self::incrementOrDecrementMeasure('increment', $model, $name, $amount);
    }

    public static function decrementMeasure($model, $name = 1, $amount = 1)
    {
        self::incrementOrDecrementMeasure('decrement', $model, $name, $amount);
    }

    public static function incrementOrphanMeasure($name, $amount = 1): int
    {
        $measure = Measure::firstOrCreate([
            'name' => $name,
            'measurable_type' => null,
            'measurable_id' => null,
        ]);

        Event::fire('sunlab.measures.incrementMeasure', [$measure, null]);

        return $measure->increment('amount', $amount);
    }

    public static function decrementOrphanMeasure($name, $amount = 1): int
    {
        $measure = Measure::firstOrCreate([
            'name' => $name,
            'measurable_type' => null,
            'measurable_id' => null,
        ]);

        Event::fire('sunlab.measures.decrementMeasure', [$measure, null]);

        return $measure->decrement('amount', $amount);
    }

    public static function resetOrphanMeasure($name, $amount = 0): int
    {
        $measure = Measure::firstOrCreate([
            'name' => $name,
            'measurable_type' => null,
            'measurable_id' => null,
        ]);

        $measure->amount = $amount;
        $measure->save();

        Event::fire('sunlab.measures.resetOrphanMeasure', [$measure, null]);

        return $measure->amount;
    }

    public static function resetMeasure($model, $name = 0, $amount = 0)
    {
        // Detect if we actually want to increment an orphan measure
        if (is_string($model) && is_int($name)) {
            return self::resetOrphanMeasure($model, $name);
        }

        // Detect if we want to increment a model related measure
        if (self::isUsingMeasurable($model)) {
            /* @var Measurable $model */
            return $model->resetMeasure($name, $amount);
        }

        // Detect if a Builder was passed for bulk reset
        if ($model instanceof Builder && self::isUsingMeasurable($model->getModel())) {
            $baseBuilder = clone $model;
            $baseBuilder2 = clone $model;
            if (!$baseBuilder->count()) {
                return;
            }

            // Find the models which doesn't have the measure yet
            $modelsWhichDoesntHaveMeasure =
                $baseBuilder->whereDoesntHave(
                    'measures',
                    static function ($q) use ($name) {
                        return $q->where('name', $name);
                    }
                )->get();

            $now = Carbon::now()->toDateTimeString();
            $newRelations = $modelsWhichDoesntHaveMeasure->map(
                static function ($relation) use ($amount, $name, $now) {
                    return [
                        'measurable_type' => $relation->getMorphClass(),
                        'measurable_id' => $relation->getKey(),
                        'name' => $name,
                        'amount' => $amount,
                        'created_at'=> $now,
                        'updated_at'=> $now
                    ];
                }
            );

            // TODO: Find a way to use Eloquent creation methods instead of insert to fire events
            Measure::insert($newRelations->toArray());

            $modelsIDs = $baseBuilder2->select('id')->get()->pluck(['id']);
            Measure::where([
                'name' => $name,
                'measurable_type' => $baseBuilder2->first()->getMorphClass()
            ])
                ->whereIn('measurable_id', $modelsIDs)
                ->update(['amount', $amount]);

            return true;
        }

        throw new \ErrorException("To use MeasureManager::resetMeasure, you should pass a Measurable model or a Builder querying Measurable model");
    }

    public static function getMeasure($name, $model = null)
    {
        // If no model was passed, return orphan measure
        if (is_null($model)) {
            return Measure::firstOrCreate([
                'name' => $name,
                'measurable_type' => null,
                'measurable_id' => null,
            ]);
        }

        // If a model was passed, make sure it's actually extendable my Measurable
        if (!self::isUsingMeasurable($model)) {
            throw new \ErrorException(get_class($model) . ' should implement SunLab.Measures.Behaviors.Measurable to use getMeasure on it.');
        }

        /* @var Measurable $model */
        return $model->getMeasure($name);
    }

    public static function amountOf($name, $model = null)
    {
        return self::getMeasure(...func_get_args())->amount;
    }

    public static function __callStatic($name, $parameters)
    {
        if ($name !== 'getMeasure' && Str::startsWith($name, 'get') && Str::endsWith($name, 'Measure')) {
            $measureName = str_replace(['get', 'Measure'], '', $name);
            $measureName = Str::kebab($measureName);

            return self::getMeasure($measureName);
        }

        if ($name !== 'get' && Str::startsWith($name, 'get')) {
            $measureName = str_replace('get', '', $name);
            $measureName = Str::kebab($measureName);

            return self::getMeasure($measureName, $parameters[0] ?: null)->amount;
        }

        if ($name !== 'increment' && Str::startsWith($name, 'increment')) {
            $measureName = str_replace('increment', '', $name);
            $measureName = Str::kebab($measureName);

            return self::incrementMeasure($measureName, $parameters[0] ?? 1, $parameters[1] ?? 1);
        }
    }

    protected static function isUsingMeasurable($model): bool
    {
        return method_exists($model, 'isClassExtendedWith')
                &&
               $model->isClassExtendedWith(Measurable::class);
    }

    protected static function incrementOrDecrementMeasure(string $incrementOrDecrement, $model, $name, $amount)
    {
        // Detect if we actually want to increment an orphan measure
        if (is_string($model) && is_int($name)) {
            return self::{$incrementOrDecrement.'OrphanMeasure'}($model, $name);
        }

        // Detect if we want to increment a model related measure
        if (self::isUsingMeasurable($model)) {
            /* @var Measurable $model */
            return $model->{$incrementOrDecrement.'Measure'}($name, $amount);
        }

        // Detect if a Builder was passed for bulk incrementation
        if ($model instanceof Builder && self::isUsingMeasurable($model->getModel())) {
            $baseBuilder = clone $model;
            $baseBuilder2 = clone $model;
            if (!$baseBuilder->count()) {
                return;
            }

            // Find the models which doesn't have the measure yet
            $modelsWhichDoesntHaveMeasure =
                $baseBuilder->whereDoesntHave(
                    'measures',
                    static function ($q) use ($name) {
                        return $q->where('name', $name);
                    }
                )->get();

            $now = Carbon::now()->toDateTimeString();
            $newRelations = $modelsWhichDoesntHaveMeasure->map(
                static function ($relation) use ($name, $now) {
                    return [
                        'measurable_type' => $relation->getMorphClass(),
                        'measurable_id' => $relation->getKey(),
                        'name' => $name,
                        'created_at'=> $now,
                        'updated_at'=> $now
                    ];
                }
            );

            // TODO: Find a way to use Eloquent creation methods instead of insert to fire events
            Measure::insert($newRelations->toArray());

            $modelsIDs = $baseBuilder2->select('id')->get()->pluck(['id']);
            Measure::where([
                'name' => $name,
                'measurable_type' => $baseBuilder2->first()->getMorphClass()
            ])
                ->whereIn('measurable_id', $modelsIDs)
                ->$incrementOrDecrement('amount', $amount);

            return true;
        }

        throw new \ErrorException("To use MeasureManager::${incrementOrDecrement}Measure, you should pass a Measurable model or a Builder querying Measurable model");
    }
}
