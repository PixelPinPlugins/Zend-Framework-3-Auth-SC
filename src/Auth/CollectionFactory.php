<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth;

use LogicException;
use SocialConnect\Provider\AbstractBaseProvider;
use SocialConnect\Provider\Consumer;
use SocialConnect\OAuth2;
use SocialConnect\OpenIDConnect;

/**
 * Class Factory
 * @package SocialConnect\Auth\Provider
 */
class CollectionFactory implements FactoryInterface
{
    /**
     * @var array
     */
    protected $providers = [
        'pixelpin'      => OpenIDConnect\Provider\PixelPin::class,
    ];

    /**
     * @param array $providers
     */
    public function __construct(array $providers = null)
    {
        if ($providers) {
            $this->providers = $providers;
        }
    }

    /**
     * @param $id
     * @return bool
     */
    public function has($id)
    {
        return isset($this->providers[$id]);
    }

    /**
     * @param string $id
     * @param array $parameters
     * @param Service $service
     * @return \SocialConnect\Provider\AbstractBaseProvider
     */
    public function factory($id, array $parameters, Service $service)
    {
        $consumer = new Consumer($parameters['applicationId'], $parameters['applicationSecret']);

        if (isset($parameters['applicationPublic'])) {
            $consumer->setPublic($parameters['applicationPublic']);
        }

        $id = strtolower($id);

        if (!isset($this->providers[$id])) {
            throw new LogicException('Provider with $id = ' . $id . ' doest not exist');
        }

        $providerClassName = $this->providers[$id];

        /**
         * @var $provider \SocialConnect\Provider\AbstractBaseProvider
         */
        $provider = new $providerClassName(
            $service->getHttpClient(),
            $service->getSession(),
            $consumer,
            array_merge(
                $parameters,
                $service->getConfig()
            )
        );

        return $provider;
    }

    /**
     * Register new provider to Provider's collection
     *
     * @param AbstractBaseProvider $provider
     */
    public function register(AbstractBaseProvider $provider)
    {
        $this->providers[$provider->getName()] = get_class($provider);
    }
}
