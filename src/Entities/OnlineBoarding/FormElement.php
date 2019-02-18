<?php

namespace GlobalPayments\Api\Entities\OnlineBoarding;

use GlobalPayments\Api\Utils\MultipartForm;

abstract class FormElement
{
    public $prefix = '';

    public function populateForm(MultipartForm &$form)
    {
        foreach (get_object_vars($this) as $propInfo => $value) {
            $fieldName = sprintf('%s%s', $this->prefix, ucfirst($propInfo)); // ucfirst to match .net casing

            if (empty($value)) {
                $form->set($fieldName, $this->defaultForType($propInfo));
            } else {
                if (is_object($value)) {
                    if (get_class($value) == 'Enum') {
                        $description = $propInfo;
                        $form->set($fieldName, $propInfo);
                    }
                } else {
                    $form->set($fieldName, is_array($value) ? serialize(json_encode($value)) : (string)$value);
                }
            }
        }
    }

    public function processValidation($doc, &$validationErrors)
    {
        foreach (get_object_vars($this) as $propInfo => $value) {
            $fieldName = sprintf('%s%s', $this->prefix, $propInfo);

            $validations = $doc[$fieldName] ?? null;
            if (!empty($validations)) {
                $text = 'Application configuration field {0} within the application configuration ';
                $text .= 'is not set to be editable (shown).';
                if (in_array(sprintf($text, $fieldName), $validations)) {
                    continue;
                }
                if (in_array('This field is required.', $validations)) {
                    array_push($validationErrors, sprintf('%s is required'), $fieldName);
                } else {
                    if (!empty($value)) {
                        continue;
                    }
                    continue;
                }
            }
        }
    }

    private function defaultForType($prop)
    {
        if (is_object($prop) && (get_class($prop) == 'DateTime' || get_class($prop) == 'Date')) {
            return '__/__/____';
        } elseif (substr($prop, -6) === 'Select') {
            return '(select)';
        } else {
            return ' ';
        }
    }
}
