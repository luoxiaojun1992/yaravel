<?php

namespace Tests;

use Mockery as M;
use Tests\Fixtures\Behaviors;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Mock class or object
     *
     * @param  $class
     * @param  Behaviors|null $behaviors
     * @param  string         $named_mock_class
     * @param  bool           $is_alias
     * @param  null           $mock_obj
     * @return M\MockInterface
     */
    protected function mock(
        $class,
        $behaviors = null,
        $named_mock_class = '',
        $is_alias = false,
        $mock_obj = null
    ) {
        if ($is_alias) {
            $class = 'alias:' . $class;
        }

        if (is_null($mock_obj)) {
            if ($named_mock_class) {
                $mock_obj = M::mock($class, $named_mock_class);
            } else {
                $mock_obj = M::mock($class);
            }
        }

        if (!is_null($behaviors)) {
            foreach ($behaviors as $behavior) {
                $mock = $mock_obj->shouldReceive($behavior['method']);
                if (isset($behavior['args'])) {
                    $mock = $mock->withArgs($behavior['args']);
                }
                if (isset($behavior['return'])) {
                    if (is_callable($behavior['return'])) {
                        $mock->andReturnUsing($behavior['return']);
                    } else {
                        $mock->andReturn($behavior['return']);
                    }
                }
                if (isset($behavior['times'])) {
                    $mock->times($behavior['times']);
                }
            }
        }

        if (!$is_alias) {
            \Registry::set($class, $mock_obj);
        }

        return $mock_obj;
    }
}
