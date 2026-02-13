<?php

namespace Dynamic\FoxyStripe\ORM;

use Dynamic\FoxyStripe\Model\ProductCartReservation;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordViewer;
use SilverStripe\Forms\NumericField;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\ValidationResult;

/**
 * Class ProductPageDataExtension
 * @package Dynamic\Sheeps\ProductCartExpiry\ORM
 */
class ProductPageDataExtension extends Extension
{
    /**
     * @var array
     */
    private static $db = [
        'CartExpiration' => 'Boolean',
        'ExpirationMinutes' => 'Int',
    ];

    /**
     * @var array
     */
    private static $has_many = [
        'CartReservations' => ProductCartReservation::class,
    ];

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $expirationFields = [
            CheckboxField::create('CartExpiration')
            ->setTitle('Cart Product Expiration'),
            $duration = NumericField::create('ExpirationMinutes')
            ->setTitle('Expiration In Minutes')
            ->setDescription("After the time listed above in minutes, this product will be removed from the user's cart"),
        ];

        $duration->displayIf('CartExpiration')->isChecked()->end();

        if ($this->owner->CartReservations()->exists()) {
            $expirationGrid = GridField::create(
                'CartReservations',
                'Cart Reservations',
                $this->owner->CartReservations()
                ->filter('Expires:GreaterThan', date('Y-m-d H:i:s', strtotime('now')))
                ->sort('Created'),
                $cartResConfig = GridFieldConfig_RecordViewer::create()
            );
            $expirationGrid->displayIf('CartExpiration')->isChecked()->end();

            $expirationFields[] = $expirationGrid;
        }

        $fields->addFieldsToTab(
            'Root.Inventory',
            $expirationFields
        );
    }

    /**
     * @param ValidationResult $validationResult
     */
    public function validate(ValidationResult $validationResult)
    {
        if ($this->owner->CartExpiration && $this->owner->ExpirationMinutes < 1) {
            $validationResult
                ->addFieldError(
                'ExpirationMinutes',
                'You must set the "Expiration In Minutes" or disable "Cart Product Expiration"'
            );
        }
    }
}