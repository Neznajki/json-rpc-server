<?php


namespace JsonRpcServerBundle\Contract;


interface MethodHandlerInterface
{
    /** @noinspection PhpDocSignatureInspection */
    /**
     * NOTICE all arguments are optional with default value definition, use getRequiredParameters to specify them
     *
     * @param string[]|int[] ...$arguments
     * @return mixed json serializable data
     */
    public function handle();

    /**
     * @return string
     */
    public function getMethod(): string;

    /**
     * @return array
     */
    public function getRequiredParameters(): array;
}
