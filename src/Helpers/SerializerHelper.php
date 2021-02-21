<?php

namespace App\Helpers;

use App\Serializer\Exclusion\DepthExclusionStrategy;
use JMS\Serializer\Exclusion\GroupsExclusionStrategy;

class SerializerHelper
{
    /**
     * Get association group map to display during the serialization process.
     *
     * @return array
     */
    public static function getGroupsMappingAssociations(): array {
        $request = AppHelper::getRequestStack()->getCurrentRequest();
        $groups = [GroupsExclusionStrategy::DEFAULT_GROUP];

        if(!$includes = $request->query->get('includes')){
            return $groups;
        }

        $includes = explode(",", $includes);

        foreach ($includes as $include){
            $groups[] = $include;
        }

        return $groups;
    }

    /**
     * Exclude list of fields that you do not want to show in the json response.
     *
     * @return array
     */
    public static function getExcludeFieldsList(): array {
        $request = AppHelper::getRequestStack()->getCurrentRequest();

        if(!$fields = $request->query->get('excludes')){
            return [];
        }

        return explode(",", $fields);
    }

    public static function getDepth(){
        $request = AppHelper::getRequestStack()->getCurrentRequest();

        return $request->query->getInt('depth', DepthExclusionStrategy::MAX_DEPTH);
    }
}