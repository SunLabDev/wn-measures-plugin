<?php namespace SunLab\Measures\Models;

use Model;

/**
 * Statistic Model
 */
class Measure extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'sunlab_measures_measures';

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['name', 'amount', 'measurable_type', 'measurable_id'];

    protected $guarded = [];

    /**
     * @var array Attributes to be cast to Argon (Carbon) instances
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * @var array Relations
     */
    public $morphTo = [
        'measurable' => []
    ];
}
