<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AllowedFieldsToFilterValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof AllowedFieldsToFilter) {
            throw new UnexpectedTypeException($constraint, AllowedFieldsToFilter::class);
        }

        if (!is_array($constraint->fields)) {
            throw new ConstraintDefinitionException(
                sprintf(
                    'The options "%s" must be to array for constraint "%s".',
                    'fields',
                    get_class($constraint)
                )
            );
        }

        if (count($value) === 0) {
            return;
        }

        foreach ($value as $key => $item) {
            if (!in_array($key, $constraint->fields)) {

                return $this->context->buildViolation($constraint->message)
                    ->setParameter('%string%', implode(', ', $constraint->fields))
                    ->addViolation();
            }
        }

    }
}