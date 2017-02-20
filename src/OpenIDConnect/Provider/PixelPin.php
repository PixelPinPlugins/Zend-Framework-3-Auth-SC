<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 * @author Alexander Fedyashov <a@fedyashov.com>
 */

namespace SocialConnect\OpenIDConnect\Provider;

use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\OpenIDConnect\AbstractProvider;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;
use SocialConnect\OpenIDConnect\Exception\InvalidJWT;
use SocialConnect\Common\Http\Client\Client;
use Exception;

/**
 * Class Provider
 * @package SocialConnect\Google
 */
class PixelPin extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    public function getOpenIdUrl()
    {
        return 'https://login.pixelpin.io/.well-known/openid-configuration';
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://login.pixelpin.io/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://login.pixelpin.io/connect/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://login.pixelpin.io/connect/token';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pixelpin';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $response = $this->httpClient->request(
            $this->getBaseUri() . 'connect/userinfo',
            [
            'access_token' => $accessToken->getToken()
            ],
            Client::GET,
            [
                'Authorization' => 'Bearer ' . $accessToken->getToken()
            ]
        );

        if (!$response->isSuccess()) {
            throw new InvalidResponse(
                'API response with error code',
                $response
            );
        }

        $body = $response->getBody();

        //throw new InvalidJWT($body);

        $result = json_decode($body);

        $jsonAddress = $result->address;

        $decodeAddress = json_decode($jsonAddress);

        $sub2         = $result->sub;
        $given_name2  = $result->given_name;
        $family_name2 = $result->family_name;
        $email2       = $result->email;
        $displayName2 = $result->displayName;
        $gender2     = $result->gender;
        $phoneNumber2 = $result->phone_number;
        $birthdate2  = $result->birthdate;
        $streetAddress2 = $decodeAddress->street_address;
        $townCity2 = $decodeAddress->locality;
        $region2 = $decodeAddress->region;
        $postalCode2 = $decodeAddress->postal_code;
        $country2 = $decodeAddress->country;

        $sub         = (string)$sub2;
        $given_name  = (string)$given_name2;
        $family_name = (string)$family_name2;
        $email       = (string)$email2;
        $displayName = (string)$displayName2;
        $gender    = (string)$gender2;
        $phoneNumber = (string)$phoneNumber2;
        $birthdate  = (string)$birthdate2;
        $streetAddress = (string)$streetAddress2;
        $townCity = (string)$townCity2;
        $region = (string)$region2;
        $postalCode = (string)$postalCode2;
        $country = (string)$country2;

        $newResult = array(
            "sub" => $sub ,
            "given_name" => $given_name,
            "family_name" => $family_name,
            "email" => $email ,
            "display_name" => $displayName,
            "gender" => $gender ,
            "phone_number" => $phoneNumber,
            "birthdate" => $birthdate ,
            "street_address" => $streetAddress,
            "town_city" => $townCity,
            "region" => $region,
            "postal_code" => $postalCode,
            "country" => $country,
        );

        $encodeNewResult = json_encode($newResult);
        $decodeNewResult = json_decode($encodeNewResult);



        $hydrator = new ObjectMap(
            [
                'sub' => 'id',
                'given_name' => 'firstname',
                'family_name' => 'lastname',
                'email' => 'email',
                'display_name' => 'fullname',
                'gender' => 'gender',
                'phone_number' => 'phone',
                'birthdate' => 'birthdate',
                'street_address' => 'address',
                'town_city' => 'townCity',
                'region'   => 'region',
                'postal_code' => 'postalCode',
                'country' => 'country'
            ]
        );

        return $hydrator->hydrate(new User(), $decodeNewResult);
    }
}