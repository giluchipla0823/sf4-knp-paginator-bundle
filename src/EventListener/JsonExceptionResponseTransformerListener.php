<?php

namespace App\EventListener;

use App\Exceptions\BaseHttpException;
use Exception;
use App\Traits\ApiResponse;
use App\Exceptions\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class JsonExceptionResponseTransformerListener
{
    use ApiResponse;

    /* @var ExceptionEvent $event */
    protected $exceptionEvent;

    public function onKernelException(ExceptionEvent $event){
        $this->exceptionEvent = $event;

        $exception = $event->getThrowable();

        if ($exception instanceof BaseHttpException) {
            return $this->getBaseHttpExceptionToResponse($exception);
        }

        if($exception instanceof NotFoundHttpException){
            return $this->getNotFoundHttpExceptionResponse($exception);
        }

        if($exception instanceof HttpExceptionInterface){
            return $this->getEventExceptionResponse($exception->getMessage(), $exception->getStatusCode());
        }

        try {
            return $this->getEventExceptionResponse($exception->getMessage(), $exception->getCode());
        }catch (Exception $exc){
            return $this->getUnknownException($exception);
        }
    }

    /**
     * Gets information on the unknown exception. These exceptions can be PHP's own warnings or notices.
     *
     * @param Exception $exception
     */
    private function getUnknownException(Exception $exception) {
        $extras = [];

        if (getenv('APP_ENV') === 'dev') {
            $extras = [
                'exception' => [
                    'class' => get_class($exception),
                    'trace' => $exception->getTrace()
                ]
            ];
        }

        return $this->getEventExceptionResponse(
            $exception->getMessage(),
            Response::HTTP_INTERNAL_SERVER_ERROR,
            $extras
        );
    }

    /**
     * Get json response for "HttpNotFoundException" type exceptions. In addition, it handles responses
     * for when there is no information from the requested entity.
     *
     * @param NotFoundHttpException $exception
     * @return void
     */
    private function getNotFoundHttpExceptionResponse(NotFoundHttpException $exception){
        $message = $exception->getMessage();

        if(strpos($message, 'object not found by the @ParamConverter annotation') !== FALSE){
            list($entity) = explode(" ", $message);

            $entity = substr(strrchr($entity, "\\"), 1);

            $message = "No se encontró una instancia de \"{$entity}\" con los parámetros especificados.";
        }

        return $this->getEventExceptionResponse($message, $exception->getStatusCode());
    }

    /**
     * Resolve json response for exceptions.
     *
     * @param string $message
     * @param int $statusCode
     * @param array $extras
     */
    private function getEventExceptionResponse(string $message, int $statusCode, array $extras = []): void{
        $json = $this->errorResponse($message, $statusCode, $extras);

        $this->exceptionEvent->setResponse($json);
    }

    private function getBaseHttpExceptionToResponse(BaseHttpException $exception) {
        $extras = [];

        $data = $exception->getData();
        $errors = $exception->getErrors();

        if (count($data) > 0) {
            $extras['data'] = $data;
        }

        if (count($errors) > 0) {
            $extras['errors'] = $errors;
        }

        return $this->getEventExceptionResponse(
            $exception->getMessage(),
            $exception->getStatusCode(),
            $extras
        );
    }



}