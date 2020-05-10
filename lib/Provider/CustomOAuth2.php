<?php

namespace OCA\SocialLogin\Provider;

use Hybridauth\Adapter\OAuth2;
use Hybridauth\Data;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\User;

class CustomOAuth2 extends OAuth2
{
    /**
     * @return User\Profile
     * @throws UnexpectedApiResponseException
     * @throws \Hybridauth\Exception\HttpClientFailureException
     * @throws \Hybridauth\Exception\HttpRequestFailedException
     * @throws \Hybridauth\Exception\InvalidAccessTokenException
     */
    public function getUserProfile()
    {
        $profileFields = $this->strToArray($this->config->get('profile_fields'));
        $profileUrl = $this->config->get('endpoints')['profile_url'];

        if (count($profileFields) > 0) {
            $profileUrl .= (strpos($profileUrl, '?') !== false ? '&' : '?') . 'fields=' . implode(',', $profileFields);
        }

        $response = $this->apiRequest($profileUrl);
        $data = new Data\Collection($response);

        if($this->config->get("attribute_path") && !empty(trim($this->config->get("attribute_path")))){
          $data = $this->stripPath($data);
        }

        if (!$data->exists("identifier") && $data->exists("id")) {
            $data->set("identifier", $data->get("id"));
        }
        if (!$data->exists("identifier") && $data->exists("data") && isset($data->get("data")->id)) {
            $data->set("identifier", $data->get("data")->id);
        }
        if (!$data->exists("identifier") && $data->exists("user_id")) {
            $data->set("identifier", $data->get("user_id"));
        }

        if (!$data->exists('identifier')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();
        foreach ($data->toArray() as $key => $value) {
            if ($key !== 'data' && property_exists($userProfile, $key)) {
                $userProfile->$key = $value;
            }
        }

        if (null !== $groups = $this->getGroups($data)) {
            $userProfile->data['groups'] = $groups;
        }
        if ($groupMapping = $this->config->get('group_mapping')) {
            $userProfile->data['group_mapping'] = $groupMapping;
        }

        return $userProfile;
    }

    protected function getGroups(Data\Collection $data)
    {
        if ($groupsClaim = $this->config->get('groups_claim')) {
            $nestedClaims = explode('.', $groupsClaim);
            $claim = array_shift($nestedClaims);
            $groups = $data->get($claim);
            while (count($nestedClaims) > 0) {
                $claim = array_shift($nestedClaims);
                if (!isset($groups->{$claim})) {
                    $groups = [];
                    break;
                }
                $groups = $groups->{$claim};
            }
            if (is_array($groups)) {
                return $groups;
            } elseif (is_string($groups)) {
                return $this->strToArray($groups);
            }
            return [];
        }
        return null;
    }

    private function strToArray($str)
    {
        return array_filter(
            array_map('trim', explode(',', $str)),
            function ($val) { return $val !== ''; }
        );
    }

    private function stripPath(Data\Collection $data){
      $path = explode(",", $this->config->get("attribute_path"));
      foreach($path as $node){
        if($data->exists($node)){
          $data = new Data\Collection($data->get($node));
        }
      }
      return $data;
    }
}
