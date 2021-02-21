<?php

namespace App\Traits;

use App\Exceptions\UnprocessableEntityHttpException;
use App\Helpers\AppHelper;
use App\Helpers\ArrayHelper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

trait ApiRequestValidation
{
    /**
     * Validate request data.
     *
     * @param array $data
     * @param array $constraints
     * @param string|null $message
     * @param int|null $statusCode
     * @throws UnprocessableEntityHttpException
     */
    protected function validateRequestData(
        array $data,
        array $constraints,
        ?string $message = null,
        ?int $statusCode = Response::HTTP_BAD_REQUEST
    ): void {
        $container = AppHelper::getContainerInterface();
        $validator = $container->get('validator');

        $data = ArrayHelper::onlyItems($data, array_keys($constraints));

        $errors = $validator->validate($data, new Assert\Collection($constraints));

        if(count($errors) > 0){
            throw new UnprocessableEntityHttpException(
                $errors,
                isset($message) ? $message : 'Validation Failed'
            );
        }
    }
}