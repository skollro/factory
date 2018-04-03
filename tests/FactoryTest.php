<?php

namespace Skollro\Factory\Test;

use Exception;
use Skollro\Factory\Factory;
use PHPUnit\Framework\TestCase;
use function Skollro\Factory\make;

class FactoryTest extends TestCase
{
    protected function setUp()
    {
        Factory::resolveUsing(null);
    }

    /** @test */
    public function make_helper_returns_an_instance()
    {
        $factory = make('A');

        $this->assertInstanceOf(Factory::class, $factory);
    }

    /** @test */
    public function it_uses_the_given_resolver_for_resolving()
    {
        Factory::resolveUsing(function ($className) {
            return $className;
        });

        $instance = make('A')
            ->resolve('A', A::class)
            ->otherwiseThrow(Exception::class);

        $this->assertEquals(A::class, $instance);
    }

    /** @test */
    public function it_resolves_an_instance()
    {
        $instance = make('A')
            ->resolve('A', A::class)
            ->otherwiseThrow(Exception::class);

        $this->assertInstanceOf(A::class, $instance);
    }

    /** @test */
    public function it_resolves_the_instance_for_the_first_match()
    {
        $instance = make('A')
            ->resolve('A', A::class)
            ->resolve('A', B::class)
            ->resolve([
                'A' => B::class,
            ])
            ->otherwiseThrow(Exception::class);

        $this->assertInstanceOf(A::class, $instance);
    }

    /** @test */
    public function it_throws_an_exception_if_no_instance_has_been_resolved()
    {
        try {
            $instance = make('A')
                ->resolve('B', B::class)
                ->otherwiseThrow(Exception::class);
        } catch (Exception $e) {
            $this->assertInstanceOf(Exception::class, $e);

            return;
        }

        $this->fail();
    }

    /** @test */
    public function it_throws_an_exception_which_is_supplied_by_a_callback()
    {
        try {
            $instance = make('A')
                ->resolve('B', B::class)
                ->otherwiseThrow(function ($type) {
                    return new Exception("Type {$type} not found");
                });
        } catch (Exception $e) {
            $this->assertInstanceOf(Exception::class, $e);
            $this->assertEquals('Type A not found', $e->getMessage());

            return;
        }

        $this->fail();
    }

    /** @test */
    public function it_resolves_an_instance_which_is_supplied_by_a_callback()
    {
        $instance = make('A')
            ->resolve('A', function ($type) {
                return $type == 'A' ? new A : new B;
            })
            ->otherwiseThrow(Exception::class);

        $this->assertInstanceOf(A::class, $instance);
    }

    /** @test */
    public function it_resolves_an_instance_from_an_array()
    {
        $instance = make('A')
            ->resolve([
                'A' => A::class,
                'B' => B::class,
            ])
            ->otherwiseThrow(Exception::class);

        $this->assertInstanceOf(A::class, $instance);
    }

    /** @test */
    public function it_resolves_otherwise_if_no_instance_has_been_resolved_before()
    {
        $instance = make('A')
            ->resolve('B', B::class)
            ->otherwise(C::class);

        $this->assertInstanceOf(C::class, $instance);
    }

    /** @test */
    public function it_resolves_an_otherwise_callback_if_no_instance_has_been_resolved_before()
    {
        $instance = make('A')
            ->resolve('B', B::class)
            ->otherwise(function ($type) {
                return $type == 'A' ? new C : new B;
            });

        $this->assertInstanceOf(C::class, $instance);
    }

    /** @test */
    public function it_does_not_resolve_otherwise_if_an_instance_has_resolved_before()
    {
        $instance = make('A')
            ->resolve('A', A::class)
            ->otherwise(B::class);

        $this->assertInstanceOf(A::class, $instance);
    }

    /** @test */
    public function it_does_not_resolve_otherwise_if_an_instance_is_supplied_by_a_callback()
    {
        $instance = make('A')
            ->resolve('A', function ($type) {
                return $type == 'A' ? new A : new B;
            })
            ->otherwise(C::class);

        $this->assertInstanceOf(A::class, $instance);
    }
}

class A
{
}

class B
{
}

class C
{
}
