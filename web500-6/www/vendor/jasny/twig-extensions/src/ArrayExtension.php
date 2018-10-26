<?php

namespace Jasny\Twig;

/**
 * Expose PHP's array functions to Twig
 */
class ArrayExtension extends \Twig_Extension
{
    /**
     * Return extension name
     * 
     * @return string
     */
    public function getName()
    {
        return 'jasny/array';
    }

    /**
     * Callback for Twig
     * @ignore
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('sum', [$this, 'sum']),
            new \Twig_SimpleFilter('product', [$this, 'product']),
            new \Twig_SimpleFilter('values', [$this, 'values']),
            new \Twig_SimpleFilter('as_array', [$this, 'asArray']),
            new \Twig_SimpleFilter('html_attr', [$this, 'htmlAttributes']),
        ];
    }
    

    /**
     * Calculate the sum of values in an array
     * 
     * @param array $array
     * @return int
     */
    public function sum($array)
    {
        return isset($array) ? array_sum((array)$array) : null;
    }
    
    /**
     * Calculate the product of values in an array
     * 
     * @param array $array
     * @return int
     */
    public function product($array)
    {
        return isset($array) ? array_product((array)$array) : null;
    }
    
    /**
     * Return all the values of an array or object
     * 
     * @param array|object $array
     * @return array
     */
    public function values($array)
    {
        return isset($array) ? array_values((array)$array) : null;
    }
    
    /**
     * Cast value to an array
     * 
     * @param object|mixed $value
     * @return array
     */
    public function asArray($value)
    {
        return is_object($value) ? get_object_vars($value) : (array)$value;
    }
    
    /**
     * Cast an array to an HTML attribute string
     * 
     * @param mixed $array
     * @return string
     */
    public function htmlAttributes($array)
    {
        if (!isset($array)) {
            return null;
        }
       
        $str = "";
        foreach ($array as $key => $value) {
            if (!isset($value) || $value === false) {
                continue;
            }
            
            if ($value === true) {
                $value = $key;
            }
            
            $str .= ' ' . $key . '="' . addcslashes($value, '"') . '"';
        }
        
        return trim($str);
    }
}
