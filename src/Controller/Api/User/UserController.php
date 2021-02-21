<?php

namespace App\Controller\Api\User;

use App\Controller\Api\ApiController;
use App\Exceptions\ValidationException;
use App\Traits\ApiRequestValidation;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;

class UserController extends ApiController
{
    use ApiRequestValidation;

    /**
     * @Route("/users", name="users_store", methods={"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function storeAction(Request $request): JsonResponse {
        $constraints = [
            'name' => [
                new Assert\NotBlank()
            ],
            'email' => [
                new Assert\NotBlank(),
                new Assert\Email(),
            ],
            'password' => [
                new Assert\NotBlank()
            ],
        ];

        $this->validateRequestData($request->request->all(), $constraints);

        return $this->showMessageResponse('User created', Response::HTTP_CREATED);
    }
}