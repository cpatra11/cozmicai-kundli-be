<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Psr\Log\LoggerInterface;

class ExceptionHandler
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {        
        $this->logger = $logger;
    }

    public function onKernelException(ExceptionEvent $event): void
    {        
        $exception = $event->getThrowable();
        $request = $event->getRequest();
        
        if ($exception instanceof ApiException) {
            $this->logger->warning('API exception: ' . $exception->getMessage(), [
                'status_code' => $exception->getStatusCode(),
                'details' => $exception->getDetails(),
                'path' => $request->getPathInfo(),
                'method' => $request->getMethod()
            ]);
            
            $response = new JsonResponse([
                'error' => $exception->getMessage(),
                'details' => $exception->getDetails(),
            ], $exception->getStatusCode());
        } elseif ($exception instanceof HttpExceptionInterface) {
            $this->logger->warning('HTTP exception: ' . $exception->getMessage(), [
                'status_code' => $exception->getStatusCode(),
                'path' => $request->getPathInfo(),
                'method' => $request->getMethod()
            ]);
            
            $response = new JsonResponse([
                'error' => $exception->getMessage()
            ], $exception->getStatusCode());
        } else {
            $this->logger->error('An error occurred: ' . $exception->getMessage(), [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'path' => $request->getPathInfo(),
                'method' => $request->getMethod()
            ]);
            
            $response = new JsonResponse([
                'error' => 'An internal error occurred'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
        $event->setResponse($response);
    }
}