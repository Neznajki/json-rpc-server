<?php


namespace JsonRpcServerBundle\Contract;


interface MethodHandlerInterface
{
    /**
     * NOTICE all arguments are optional with default value definition, use getRequiredParameters to specify them
     *
     * @return mixed json serializable data
     */
    public function handle();

    /**
     * @param string $paramName
     * @param mixed $value
     * @return void
     */
    public function setParameter(string $paramName, $value): void ;

    /**
     * @return string
     */
    public function getMethod(): string;

    /**
     * @return array
     */
    public function getRequiredParameters(): array;
}
