<?php

namespace App\Validator\Constraints;

use App\Helpers\AppHelper;
use Symfony\Component\Validator\Constraint;
use Symfony\Contracts\Translation\TranslatorInterface;

class AllowedFieldsToFilter extends Constraint
{
    /**
     * @var string
     */
    public $message;

    /**
     * @var array
     */
    public $fields;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct($options = null)
    {
        parent::__construct($options);

        $this->translator = AppHelper::getTranslatorInterface();

        $this->message = $this->translator->trans('validators.allowed_fields_to_filter');
    }

    public function getRequiredOptions(): array
    {
        return ['fields'];
    }

    public function validatedBy()
    {
        return get_class($this) . 'Validator';
    }
}