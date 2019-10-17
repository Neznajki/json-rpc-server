<?php


namespace JsonRpcServerBundle\Service;


use JsonRpcServerBundle\DataObject\RequestEntity;
use JsonRpcServerBundle\ValueObject\SuccessResponseEntity;
use JsonRpcServerBundle\Exception\InternalErrorException;
use JsonRpcServerBundle\Exception\InvalidParamsException;
use JsonRpcServerBundle\Exception\MethodNotFoundException;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use RuntimeException;

/**
 * Class MethodExecutorService
 * @package App\src\Service
 */
class MethodExecutorService
{
    /** @var MethodCollectionService */
    protected $methodCollectionService;

    /**
     * MethodExecutorService constructor.
     * @param MethodCollectionService $methodCollectionService
     */
    public function __construct(MethodCollectionService $methodCollectionService)
    {
        $this->methodCollectionService = $methodCollectionService;
    }

    /**
     * @param RequestEntity $requestEntity
     * @return SuccessResponseEntity
     * @throws InvalidParamsException
     * @throws MethodNotFoundException
     * @throws ReflectionException
     * @throws InternalErrorException
     */
    public function executeMethod(RequestEntity $requestEntity): SuccessResponseEntity
    {
        $method = $this->getMethodCollectionService()->getMethod($requestEntity->getMethod());

        $classReflection  = new ReflectionClass($method);
        $reflectionMethod = $classReflection->getMethod('handle');

        $parameters = $reflectionMethod->getParameters();

        $arguments             = [];
        $requiredArguments     = $method->getRequiredParameters();
        $leftRequiredArguments = array_flip($requiredArguments);
        foreach ($parameters as $parameter) {
            $arg = $this->getMethodParameter($requiredArguments, $parameter, $requestEntity);

            $arguments[] = $arg;
            $paramName   = $parameter->getName();
            if (array_key_exists($paramName, $leftRequiredArguments)) {
                unset($leftRequiredArguments[$paramName]);
            }
        }

        if (! empty($leftRequiredArguments)) {
            throw new RuntimeException(
                sprintf('required parameters are not described as injections (%s)', implode(', ', array_keys($leftRequiredArguments)))
            );
        }

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $response = $method->handle(... $arguments);

        $successResponseEntity = new SuccessResponseEntity($requestEntity, $response);

        return $successResponseEntity;
    }

    /**
     * @return MethodCollectionService
     */
    public function getMethodCollectionService(): MethodCollectionService
    {
        return $this->methodCollectionService;
    }

    /**
     * @param array $requiredArguments
     * @param ReflectionParameter $parameter
     * @param RequestEntity $requestEntity
     * @return mixed
     * @throws InvalidParamsException
     * @throws ReflectionException
     */
    public function getMethodParameter(array $requiredArguments, ReflectionParameter $parameter, RequestEntity $requestEntity)
    {
        $requestParams = $requestEntity->getParams() ?? [];
        $paramName     = $parameter->getName();
        if (array_key_exists($paramName, $requestParams)) {
            $incomingParamValue = $requestParams[$paramName];

            $incomingParamValue = $this->convertAndValidateKnownValue($parameter, $incomingParamValue, $requestEntity);

            $result = $incomingParamValue;
        } elseif ($parameter->isDefaultValueAvailable() && ! in_array($paramName, $requiredArguments)) {
            $result = $parameter->getDefaultValue();
        } else {
            throw new InvalidParamsException("parameter ({$paramName}) is required for method {$requestEntity->getMethod()}");
        }

        return $result;
    }

    /**
     * @param ReflectionParameter $parameter
     * @param $value
     * @param RequestEntity $requestEntity
     * @return bool
     * @throws InvalidParamsException
     */
    protected function convertAndValidateKnownValue(ReflectionParameter $parameter, $value, RequestEntity $requestEntity)
    {
        switch ($parameter->getType()) {
            case 'string':
                if (! is_string($value)) {
                    throw new InvalidParamsException(
                        "Parameter \"{$parameter->getName()}\" must be string for method {$requestEntity->getMethod()}"
                    );
                }
                $result = $value;
                break;
            case 'bool':
                // cast to bool, if needed
                $result = $value;
                if ($value === 1 || $value === 0 || $value === "1" || $value === "0") {
                    $result = (bool)($value);
                }

                if (! is_bool($result)) {
                    throw new InvalidParamsException(
                        "Parameter \"{$parameter->getName()}\" must be bool for method {$requestEntity->getMethod()}"
                    );
                }
                break;
            case 'int':
                if (! preg_match('/^-?[0-9]+$/', $value)) {
                    throw new InvalidParamsException(
                        "Parameter \"{$parameter->getName()}\" must be integer for method {$requestEntity->getMethod()}"
                    );
                }
                $result = (int)$value;
                break;
            case 'float':
                if (! is_numeric($value)) {
                    throw new InvalidParamsException(
                        "Parameter \"{$parameter->getName()}\" must be numeric for method {$requestEntity->getMethod()}"
                    );
                }
                $result = (float)$value;
                break;
            case 'array':
                if (! is_array($value)) {
                    throw new InvalidParamsException(
                        "Parameter \"{$parameter->getName()}\" must be array for method {$requestEntity->getMethod()}"
                    );
                }
                $result = $value;
                break;

            default:
                throw new InvalidParamsException("unsupported type {$parameter->getType()}");
        }

        return $result;
    }
}
