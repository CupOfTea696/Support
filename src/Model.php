<?php namespace CupOfTea\Support;

use ArrayAccess;
use Carbon\Carbon;
use InvalidArgumentException;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

class Model implements ArrayAccess, Arrayable, Jsonable
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];
    
    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [];
    
    /**
     * The default date format.
     *
     * @var string
     */
    protected $dateFormat = 'd-m-Y';
    
    /**
     * Create a new model instance
     * @param  array|object  $data
     * @return void
     * @throws \InvalidArgumentException
     */
    public function __construct($data = [])
    {
        if (! is_array($data) && ! is_object($data)) {
            throw new InvalidArgumentException('The $data parameter must be an array or object.');
        }
        
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }
    
    /**
     * Get a property's value.
     *
     * @param  string  $attribute
     * @return mixed
     */
    public function __get($key)
    {
        $key = Str::studly($key);
        
        return array_get($this->attributes, $key);
    }
    
    /**
     * Set a property's value.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        $key = Str::studly($key);
        $value = value($value);
        
        if ($this->hasMutator($key)) {
            $value = $this->{$this->getMutator($key)}($value);
        }
        
        $this->attributes[$key] = $value;
    }
    
    /**
     * Check if a property is set.
     *
     * @param  string  $attribute
     * @return bool
     */
    public function __isset($key)
    {
        $key = Str::studly($key);
        
        return isset($this->attributes[$key]);
    }
    
    /**
     * Unset a property.
     *
     * @param  string  $attribute
     * @return void
     */
    public function __unset($key)
    {
        $key = Str::studly($key);
        
        unset($this->attributes[$key]);
    }
    
    /**
     * Get a property's value.
     *
     * @param  string  $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        $offset = Str::studly($offset);
        
        return array_get($this->attributes, $offset);
    }
    
    /**
     * Set a property's value.
     *
     * @param  string  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value) {
        $offset = Str::studly($offset);
        
        $this->$offset = $value;
    }
    
    /**
     * Check if a property is set.
     *
     * @param  string  $offset
     * @return bool
     */
    public function offsetExists($offset) {
        $offset = Str::studly($offset);
        
        return isset($this->attributes[$offset]);
    }
    
    /**
     * Unset a property.
     *
     * @param  string  $offset
     * @return void
     */
    public function offsetUnset($offset) {
        $offset = Str::studly($offset);
        
        unset($this->attributes[$offset]);
    }
    
    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $data = [];
        
        foreach ($this->attributes as $key => $value) {
            $data[Str::snake($key)] = $value;
        }
        
        return $data;
    }
    
    /**
     * Convert the model instance to JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
    
    /**
     * Get the property's mutator method name.
     *
     * @param  string  $attribute
     * @return string
     */
    private function getMutator($key)
    {
        return 'set' . Str::studly($key) . 'Attribute';
    }
    
    /**
     * Check if the model has a mutator for the given property.
     * @param  string  $attribute
     * @return bool
     */
    private function hasMutator($key)
    {
        return method_exists($this, $this->getMutator($key));
    }
    
    /**
     * Get the type of cast for a model attribute.
     *
     * @param  string  $key
     * @return string
     */
    protected function getCastType($key)
    {
        return trim(strtolower($this->casts[$key]));
    }
    
    /**
     * Cast an attribute to a native PHP type.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    private function castAttribute($key, $value)
    {
        if (is_null($value)) {
            return $value;
        }
        
        switch ($this->getCastType($key)) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'real':
            case 'float':
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'object':
                return (object) $value;
            case 'array':
                return (array) $value;
            case 'collection':
                return new Collection((array) $value);
            case 'date':
            case 'datetime':
                return $this->asDateTime($value);
            case 'timestamp':
                return $this->asTimeStamp($value);
            default:
                return $value;
        }
    }
    
    /**
     * Return a timestamp as DateTime object.
     *
     * @param  mixed  $value
     * @return \Carbon\Carbon
     */
    private function asDateTime($value)
    {
        // If this value is already a Carbon instance, we shall just return it as is.
        // This prevents us having to reinstantiate a Carbon instance when we know
        // it already is one, which wouldn't be fulfilled by the DateTime check.
        if ($value instanceof Carbon) {
            return $value;
        }
        
        // If the value is already a DateTime instance, we will just skip the rest of
        // these checks since they will be a waste of time, and hinder performance
        // when checking the field. We will just return the DateTime right away.
        if ($value instanceof DateTime) {
            return Carbon::instance($value);
        }
        
        // If this value is an integer, we will assume it is a UNIX timestamp's value
        // and format a Carbon object from this timestamp. This allows flexibility
        // when defining your date fields as they might be UNIX timestamps here.
        if (is_numeric($value)) {
            return Carbon::createFromTimestamp($value);
        }
        
        // If the value is in simply year, month, day format, we will instantiate the
        // Carbon instances from that format. Again, this provides for simple date
        // fields on the database, while still supporting Carbonized conversion.
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value)) {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        }
        
        // Finally, we will just assume this date is in the format used by default on
        // the database connection and use that format to create the Carbon object
        // that is returned back out to the developers after we convert it here.
        return Carbon::createFromFormat($this->dateFormat, $value);
    }
    
    /**
     * Return a timestamp as unix timestamp.
     *
     * @param  mixed  $value
     * @return int
     */
    protected function asTimeStamp($value)
    {
        return (int) $this->asDateTime($value)->timestamp;
    }
}
