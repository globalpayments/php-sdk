<?php

namespace GlobalPayments\Api\Builders\RequestBuilder;

use GlobalPayments\Api\Builders\BaseBuilder;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\Builders\BaseBuilder\Validations;

class RequestBuilderValidations
{
    private Validations $validations;

    public function __construct(Validations $validations)
    {
        $this->validations = $validations;
    }

    public function validate($builder, $actionType)
    {
        array_map(
            [$this, 'maybeRunValidationKeyRules'],
            array_keys($this->validations->rules), [$actionType], [$builder]
        );
    }

    /**
     * Runs validations for `$key`
     *
     * @param mixed $key Validation rules key
     * @param string $actionType
     * @param BaseBuilder $builder
     *
     * @throws BuilderException
     * @return void
     */
    protected function maybeRunValidationKeyRules($key, string $actionType, BaseBuilder $builder)
    {
        if (($key & $actionType) !== $actionType) {
            return;
        }

        foreach ($this->validations->rules[$key] as $validation) {
            if (null === $validation->clause) {
                continue;
            }
            if (!call_user_func($validation->clause->callback, $builder)) {
                throw new BuilderException($validation->clause->message);
            }
        }
    }
}