<?php namespace SunLab\Measures\Behaviors;

use October\Rain\Support\Collection;
use SunLab\Measures\Models\Measure;

/**
 * Measurable Extension
 * @package SunLab\Measures\Behaviors
 *
 * @method measures(): October\Rain\Database\Relations\MorphMany
 */
class Measurable extends \October\Rain\Extension\ExtensionBase
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
     * @return mixed
     */
    public function incrementMeasure($name, $amount = 1)
    {
        $measure = $this->parent->measures()->firstOrCreate(['name' => $name]);
        $measure->increment('amount', $amount);

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

    /** Return all Measure models related to this model
     * @return Measure[]|Collection
     */
    public function getMeasures(): Collection
    {
        return $this->parent->measures()->all();
    }
}
