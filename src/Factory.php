<?php

namespace Skollro\Factory;

class Factory
{
    protected static $resolver;

    protected $type;
    protected $resolvable;
    protected $hasResolved;

    protected function __construct($type)
    {
        $this->type = $type;
        $this->hasResolved = false;
    }

    public static function make($type)
    {
        return new self($type);
    }

    public static function resolveUsing($resolver)
    {
        self::$resolver = $resolver;
    }

    public function resolve($type, $resolvable = null)
    {
        if ($this->hasResolved) {
            return $this;
        }

        if (is_array($type) && isset($type[$this->type])) {
            $this->resolvable = $type[$this->type];
            $this->hasResolved = true;

            return $this;
        }

        if ($this->type == $type) {
            $this->resolvable = $resolvable;
            $this->hasResolved = true;
        }

        return $this;
    }

    public function otherwise($resolvable)
    {
        return $this->resolveInstance(function () use ($resolvable) {
            return is_callable($resolvable) ? $resolvable($this->type) : (self::$resolver)($resolvable);
        });
    }

    public function otherwiseThrow($resolvable)
    {
        return $this->resolveInstance(function () use ($resolvable) {
            throw is_callable($resolvable) ? $resolvable($this->type) : new $resolvable;
        });
    }

    protected function resolveInstance($otherwise)
    {
        if (!self::$resolver) {
            self::resolveUsing(function ($className) {
                return new $className;
            });
        }

        if ($this->hasResolved) {
            return is_callable($this->resolvable)
                ? ($this->resolvable)($this->type) : (self::$resolver)($this->resolvable);
        }

        return $otherwise();
    }
}
