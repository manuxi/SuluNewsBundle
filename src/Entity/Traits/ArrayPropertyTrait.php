<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity\Traits;

trait ArrayPropertyTrait
{
    /**
     * @param array $data
     * @param string $key
     * @param string|null $default
     * @return mixed|string|null
     */
    protected function getProperty(array $data, string $key, string $default = null)
    {
        if (\array_key_exists($key, $data)) {
            return $data[$key];
        }

        return $default;
    }

    /**
     * @param array $data
     * @param array $keys
     * @param string|null $default
     * @return mixed|string|null
     */
    protected function getPropertyMulti(array $data, array $keys, string $default = null)
    {
        $currentKey = array_shift($keys);
        if(0 === count($keys)){
            if (\array_key_exists($currentKey, $data)) {
                return $data[$currentKey];
            }
            return $default;
        } else {
            if(!\array_key_exists($currentKey, $data) || !\is_array($data[$currentKey])) {
                return null;
            } else {
                return $this->getPropertyMulti($data[$currentKey], $keys, $default);
            }
        }
    }

}
