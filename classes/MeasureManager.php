<?php namespace SunLab\Measures\Classes;

use Illuminate\Support\Carbon;
use October\Rain\Database\Builder;
use SunLab\Measures\Behaviors\Measurable;
use SunLab\Measures\Models\Measure;

abstract class MeasureManager
{
    public static function incrementMeasure($model, $name, $amount = 1)
    {
        if (method_exists($model, 'isClassExtendedWith')
            &&
            $model->isClassExtendedWith(Measurable::class)
        ) {
            /** Measurable $model */
            return $model->incrementMeasure($name, $amount);
        }

        $baseBuilder = clone $model;
        $baseBuilder2 = clone $model;
        if ($model instanceof Builder) {
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
                ->increment('amount', $amount);
        }
    }
}
