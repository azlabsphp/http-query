<?php

declare(strict_types=1);

namespace Drewlabs\Query\Http\Validation;

use Drewlabs\Query\Http\Query;
use Illuminate\Contracts\Validation\ValidationRule;
use InvalidArgumentException;

final class Exists implements ValidationRule
{
    use HasBuilder;
    use HandlesRequestError;

    /** @var Query */
    private $builder;

    /** @var string */
    private $property = 'id';

    /**
     * Exist class constructor
     * 
     * @param Query $builder 
     * @param string $property 
     * @return void 
     */
    public function __construct(Query $builder, string $property = 'id')
    {
        $this->builder = $builder;
        $this->property = $property;
    }

    /**
     * Exist rule factory constructor
     * 
     * @param string $url 
     * @param string $property
     * @return static 
     * @throws InvalidArgumentException 
     */
    public static function new(string $url, string $property = 'id')
    {
        return new static(Query::new()->from($url), $property);
    }

    public function validate(string $attribute, $value, \Closure $fail): void
    {
        try {
            if ($this->builder->eq($this->property, $value)->count() === 0) {
                $fail(sprintf("%s attribute value is invalid", $attribute));
            }
        } catch (\Throwable $e) {
            if ($this->failsOnError) {
                $fail(sprintf("%s attribute value is invalid", $attribute));
            }
        }
    }

    public function __invoke(string $attribute, $value, \Closure $fail): void
    {
        $this->validate($attribute, $value, $fail);
    }
}
