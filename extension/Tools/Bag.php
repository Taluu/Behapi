<?php
namespace Behapi\Extension\Tools;

use ArrayAccess;

use OutOfBoundsException;
use BadMethodCallException;

class Bag implements ArrayAccess
{
    const IGNORE_ON_INVALID = 0;
    const EXCEPTION_ON_INVALID = 1;

    /** @var mixed[] */
    private $defaults;

    /** @var mixed[] */
    private $parameters;

    public function __construct(array $defaults = [])
    {
        $this->defaults = $defaults;
        $this->parameters = $defaults;
    }

    /**
     * Set a value within this Bag
     *
     * @param string $key Key to use
     * @param mixed $value value to set
     */
    public function set($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Get a key from this Bag
     *
     * @param string $key key to fetch
     * @param integer $invalid behaviour to adopt on key not found
     * @param mixed $default Default value to use if not found
     *
     * @return mixed
     * @throws OutOfBoundsException Key not found and invalid behaviour set to EXCEPTION
     */
    public function get($key, $invalid = self::IGNORE_ON_INVALID, $default = null)
    {
        if ($this->has($key)) {
            return $this->parameters[$key];
        }

        if (self::IGNORE_ON_INVALID === $invalid) {
            return $default;
        }

        throw new OutOfBoundsException(sprintf('The key "%s" is not within this Bag.', $key));
    }

    /**
     * Checks if a key is within this Bag
     *
     * @param string $key Key to search
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->parameters);
    }

    /** Reset this Bag */
    public function reset()
    {
        $this->parameters = $this->defaults;
    }

    /** {@inheritDoc} */
    public function offsetGet($offset)
    {
        return $this->get($offset, self::IGNORE_ON_INVALID, null);
    }

    /** {@inheritDoc} */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /** {@inheritDoc} */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /** {@inheritDoc} */
    public function offsetUnset($offset)
    {
        throw new BadMethodCallException('The unset method is not supported on a Bag');
    }
}

