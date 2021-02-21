<?php

namespace App\Helpers;

use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationHelper
{
    /**
     * Aplicar formato a los errores de validación
     *
     * @param ConstraintViolationListInterface $errors
     * @return array
     */
    public static function formatErrors(ConstraintViolationListInterface $errors): array
    {
        $output = [];

        foreach ($errors as $error) {
            /* @var ConstraintViolation $error */

            $key = substr(str_replace(['[', ']'], ['', '.'], $error->getPropertyPath()), 0, -1);

            if(in_array($key, array_column($output, 'field'))){
               continue;
            }

            $output[] = array(
                'field' => $key,
                'message' => $error->getMessage()
            );
        }

        return $output;
    }
}