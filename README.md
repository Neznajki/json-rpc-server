# json-rpc-authorization
makes authorization for json rpc

# bundle installation
* composer install 

# installation
* composer install neznajki/json-rpc-server
* services.yaml
```yaml
imports:
    - { resource: '@JsonRpcServerBundle/Resources/config/services.yaml' }

```

* routes.yaml
```yaml

jsonRpc:
    resource: '@JsonRpcServerBundle/Resources/config/routing.yaml'

```

# method creation
```yaml
    App\Api\CoolMethod:
        tags: [ 'json.rpc.server.method' ]
```
```php
class CoolMethod implements MethodHandlerInterface
{
    /**
     * @param string $test
     * @param int|null $test2
     * @return mixed
     */
    public function handle(string $test = null, ?int $test2 = null)
    {
        return $test . 'tt';
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return 'coolMethodIncomingName';
    }

    /**
     * @return array
     */
    public function getRequiredParameters(): array
    {
        return ['test'];
    }
}
```
