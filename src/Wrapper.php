<?php namespace CupOfTea\Support;

use RuntimeException;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;

trait Wrapper
{
    /**
     * Instance of the wrapped class.
     *
     * @var object
     */
    private $_wrapped;
    
    /**
     * Get the instance of the wrapped class.
     *
     * @return object
     */
    public function getWrapped()
    {
        $wrapped_property = $this->wrapped_property ? $this->{$this->wrapped_property} : '_wrapped';
        
        if (isset($this->$wrapped_property)) {
            return $this->$wrapped_property;
        }
        
        $class = static::$wraps;
        
        if (! $class) {
            throw new RuntimeException('The wrapped class must be defined using ' . __CLASS__ . '::$wraps.');
        }
        
        $container = isset($this->container) && $this->container instanceof Container::class ? $this->container : ($this->container = new Container());
        $reflector = new ReflectionClass($class);
        $dependencies = $reflector->getConstructor()->getParameters();
        $parameters = [];
        
        foreach ($dependencies as $dependency) {
            if (isset($this->{$dependency->name})) {
                $parameters[] = $this->{$dependency->name};
            } elseif ($c = $dependency->getClass()) {
                try {
                    $parameters[] = $container->make($c);
                } catch (BindingResolutionException $e) {
                    if ($dependency->isOptional()) {
                        $parameters[] = $dependency->getDefaultValue();
                        
                        continue;
                    }
                    
                    throw $e;
                }
            }
        }
        
        if (count($parameters)) {
            return new $class(...$parameters);
        }
        
        return new $class;
    }
    
    /**
     * @see \CupOfTea\Support\Wrapper::getWrapped()
     */
    public function getWrappedObject()
    {
        return $this->getWrapped();
    }
    
    /**
     * Forward method calls to instance of the wrapped class.
     *
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        $instance = $this->getWrapped();
        
        switch (count($args)) {
            case 0:
                return $instance->$method();
            case 1:
                return $instance->$method($args[0]);
            case 2:
                return $instance->$method($args[0], $args[1]);
            case 3:
                return $instance->$method($args[0], $args[1], $args[2]);
            case 4:
                return $instance->$method($args[0], $args[1], $args[2], $args[3]);
            default:
                return call_user_func_array([$instance, $method], $args);
        }
    }
    
    /**
     * Statically forward method calls to instance of the wrapped class.
     *
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        $class = static::$wraps;
        
        if (! $class) {
            throw new RuntimeException('The wrapped class must be defined using ' . __CLASS__ . '::$wraps.');
        }
        
        switch (count($args)) {
            case 0:
                return $class::$method();
            case 1:
                return $class::$method($args[0]);
            case 2:
                return $class::$method($args[0], $args[1]);
            case 3:
                return $class::$method($args[0], $args[1], $args[2]);
            case 4:
                return $class::$method($args[0], $args[1], $args[2], $args[3]);
            default:
                return call_user_func_array([$class, $method], $args);
        }
    }
}
