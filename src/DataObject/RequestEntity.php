<?php declare(strict_types=1);


namespace JsonRpcServerBundle\DataObject;


use JsonRpcServerBundle\Exception\InvalidParamsException;

class RequestEntity
{
    /** @var string */
    protected $jsonrpc;
    /** @var string */
    protected $method;
    /** @var array  */
    protected $params;
    /** @var string */
    protected $id;

    /** @var bool */
    protected $valid = false;

    /**
     * RequestEntity constructor.
     * @param array|object $data
     */
    public function __construct($data)
    {
        $data = $this->convertDataToArray($data);

        if (array_key_exists('id', $data)) {
            $this->id = (string)$data['id'];
        }

        if (array_key_exists('method', $data)) {
            $this->method = $data['method'];
        }

        if (array_key_exists('params', $data)) {
            $this->params = $data['params'];
        }

        if (array_key_exists('jsonrpc', $data)) {
            $this->jsonrpc = $data['jsonrpc'];
        }
    }

    /**
     * @return string|null
     */
    public function getJsonrpc(): ?string
    {
        return $this->jsonrpc;
    }

    /**
     * @return string|null
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * @return array|null
     */
    public function getParams(): ?array
    {
        return $this->params;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * @throws InvalidParamsException
     */
    public function validate()
    {
        if (empty($this->getJsonrpc())) {
            throw new InvalidParamsException('jsonrpc index is mandatory');
        }

        if ($this->getJsonrpc() !== '2.0') {
            throw new InvalidParamsException('supports only 2.0 jsonrpc version');
        }

        if (empty($this->getMethod())) {
            throw new InvalidParamsException('method index is mandatory');
        }

        if ($this->params) {
            if (! is_array($this->params)) {
                throw new InvalidParamsException('params should be array');
            }
        }

        $this->valid = true;
    }

    /**
     * @param $objectedData
     * @return array
     */
    protected function convertDataToArray($objectedData): array
    {
        if (is_object($objectedData)) {
            $objectedData = (array)$objectedData;
        }

        $result = [];

        foreach ($objectedData as $key => $item) {
            if (is_array($item) || is_object($item)) {
                $result[$key] = $this->convertDataToArray($item);
                continue;
            }

            $result[$key] = $item;
        }

        return $result;
    }

}
