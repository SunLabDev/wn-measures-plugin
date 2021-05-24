<?php namespace SunLab\Measures\Tests\Unit;

use SunLab\Measures\Classes\MeasureManager;
use SunLab\Measures\Tests\MeasuresPluginTestCase;

class OrphanMeasuresTest extends MeasuresPluginTestCase
{
    public function testIncrementingOrphanMeasure()
    {
        MeasureManager::incrementMeasure('orphan_measure');

        $this->assertEquals(1, MeasureManager::amountOf('orphan_measure'));
    }

    public function testDecrementingOrphanMeasure()
    {
        MeasureManager::incrementMeasure('orphan_measure');
        MeasureManager::incrementMeasure('orphan_measure');
        MeasureManager::incrementMeasure('orphan_measure');

        MeasureManager::decrementMeasure('orphan_measure');

        $this->assertEquals(2, MeasureManager::amountOf('orphan_measure'));
    }

    public function testResettingOrphanMeasure()
    {
        MeasureManager::incrementMeasure('orphan_measure');
        MeasureManager::incrementMeasure('orphan_measure');
        MeasureManager::incrementMeasure('orphan_measure');

        MeasureManager::resetMeasure('orphan_measure');

        $this->assertEquals(0, MeasureManager::amountOf('orphan_measure'));
    }

    public function testResettingOrphanMeasuresAtPreciseAmount()
    {
        MeasureManager::incrementMeasure('orphan_measure');
        MeasureManager::incrementMeasure('orphan_measure');
        MeasureManager::incrementMeasure('orphan_measure');
        MeasureManager::incrementMeasure('orphan_measure');
        MeasureManager::incrementMeasure('orphan_measure');

        MeasureManager::resetMeasure('orphan_measure', 2);

        $this->assertEquals(2, MeasureManager::amountOf('orphan_measure'));
    }
}
