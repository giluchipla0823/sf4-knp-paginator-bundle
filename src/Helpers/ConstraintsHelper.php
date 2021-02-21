<?php


namespace App\Helpers;

use Symfony\Component\Validator\Constraints as Assert;


class ConstraintsHelper
{
    /**
     *
     *
     * @param array $data
     * @param array $constrains
     * @return Assert\Optional
     */
    public static function createOptionalConstraintToCollection(
        array $data,
        array $constrains = []
    ): Assert\Optional {
        $fields = [];

        if(count($constrains) === 0){
            $constrains[] = new Assert\NotBlank();
        }

        foreach ($data as $item){
            $fields[$item] = new Assert\Optional($constrains);
        }

        return new Assert\Optional(
            new Assert\Collection([
                'fields' => $fields
            ])
        );
    }
}