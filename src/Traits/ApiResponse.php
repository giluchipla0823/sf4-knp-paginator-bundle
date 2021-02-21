<?php

namespace App\Traits;

use App\Helpers\AppHelper;
use App\Helpers\SerializerHelper;
use App\Serializer\Exclusion\DepthExclusionStrategy;
use App\Serializer\Exclusion\RemoveFieldsListExclusionStrategy;
use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

trait ApiResponse
{
    /**
     * Construir respuesta de éxito.
     *
     * @param null $data
     * @param string $message
     * @param int $code
     * @param array $extras
     * @param SerializationContext|null $context
     * @return JsonResponse
     */
    protected function successResponse(
        $data = NULL,
        string $message = 'OK',
        int $code = Response::HTTP_OK,
        array $extras = [],
        ?SerializationContext $context = null
    ): JsonResponse {

        if($data instanceof PaginationInterface){
            return $this->knpPaginatorResponse($data, $context);
        }

        return $this->makeResponse($data, $message, $code, $extras, $context);
    }

    /**
     * Construir respuesta para mostrar sólo mensajes.
     *
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    protected function showMessageResponse(string $message, int $code = Response::HTTP_OK): JsonResponse{
        return $this->makeResponse(NULL, $message, $code);
    }

    /**
     * Construir respuesta de error.
     *
     * @param string $message
     * @param int $code
     * @param array $extras
     * @return JsonResponse
     */
    protected function errorResponse(string $message, int $code, array $extras = []): JsonResponse {
        return $this->makeResponse(NULL, $message, $code, $extras);
    }

    /**
     * Estructura de respuesta json.
     *
     * @param null $data
     * @param string $message
     * @param int $code
     * @param array $extras
     * @param SerializationContext|null $context
     * @return JsonResponse
     */
    private function makeResponse(
        $data = NULL,
        string $message,
        int $code,
        array $extras = [],
        ?SerializationContext $context = null
    ): JsonResponse {
        $response = [
            'code' => $code,
            'message' => $message,
        ];

        if(!is_null($data)){
            $response['data'] = $this->serializerData($data, $context);
        }

        $response = array_merge($response, $extras);

        return new JsonResponse($response, $code);
    }

    /**
     * Serializar data usando JMS Serializer.
     *
     * @param $data
     * @param SerializationContext|null $context
     * @return mixed
     */
    private function serializerData($data, ?SerializationContext $context){
        $container = AppHelper::getContainerInterface();

        if ($container->has('jms_serializer')) {
            $context = $this->createSerializationContext($context);

            $json = $container->get('jms_serializer')->serialize(
                $data,
                'json',
                $context
            );

            $data = json_decode($json, TRUE);
        }

        return $data;
    }

    /**
     * Create serialization context.
     *
     * @param SerializationContext|null $context
     * @return SerializationContext
     */
    private function createSerializationContext(?SerializationContext $context){
        if(!$context){
            return SerializationContext::create()
                        ->setSerializeNull(true)
                        ->addExclusionStrategy(
                            new RemoveFieldsListExclusionStrategy(SerializerHelper::getExcludeFieldsList())
                        )
                        ->addExclusionStrategy(
                            new DepthExclusionStrategy(SerializerHelper::getDepth())
                        );
                        // ->setGroups(SerializerHelper::getGroupsMappingAssociations());
        }

        return $context;
    }

    /**
     * Construir respuesta para data usando KnpPaginatorBundle.
     *
     * @param array $data
     * @param SerializationContext|null $context
     * @return JsonResponse
     */
    private function knpPaginatorResponse(array $data, ?SerializationContext $context): JsonResponse{
        return $this->makeResponse(
            NULL,
            'OK',
            Response::HTTP_OK,
            $this->serializerData($data, $context)
        );
    }

    /**
     * Returns a BinaryFileResponse object with original or customized file name and disposition header.
     *
     * @param \SplFileInfo|string $file File object or path to file to be sent as response
     * @param string|null $fileName
     * @param string $disposition
     * @return BinaryFileResponse
     * @final
     */
    protected function file($file, string $fileName = null, string $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT): BinaryFileResponse
    {
        $response = new BinaryFileResponse($file);
        $response->setContentDisposition($disposition, null === $fileName ? $response->getFile()->getFilename() : $fileName);

        return $response;
    }
}