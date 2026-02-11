<?php

namespace Dynamic\FoxyStripe\Extension;

use Dynamic\FoxyStripe\Model\ProductCartReservation;
use Dynamic\FoxyStripe\Page\ProductPage;
use SilverStripe\Core\Extension;
use SilverStripe\Dev\Debug;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\HiddenField;

/**
 * Class ProductFormExtension
 * @package Dynamic\Sheeps\ProductCartExpiry\Extension
 */
class PurchaseFormExtension extends Extension
{
    /**
     * @param \SilverStripe\Forms\FieldList $fields
     */
    public function updatePurchaseFormFields(FieldList &$fields)
    {
        if ($this->owner->getProduct()->CartExpiration) {
            $fields->insertBefore(
                'quantity',
                HiddenField::create('expires')
                    ->setValue(
                        ProductPage::getGeneratedValue(
                            $this->owner->getProduct()->Code,
                            'expires',
                            $this->owner->getProduct()->ExpirationMinutes,
                            'value'
                        )
                    )
            );
        }

        if ($this  ->isOutOfStock()) {
            $fields = FieldList::create(
                HeaderField::create('OutOfStock', 'Out of stock')
                    ->setHeadingLevel(3)
            );
        }
    }

    /**
     * @param \SilverStripe\Forms\FieldList $actions
     */
    public function updateFoxyStripePurchaseFormActions(FieldList &$actions)
    {
        if ($this->isOutOfStock()) {
            $actions = FieldList::create();
        }
    }

    /**
     * @return bool
     */
    public function isOutOfStock()
    {
        if (!$this->owner->getProduct()->ControlInventory) {
            return false;
        }
        $reserved = ProductCartReservation::get()
            ->filter([
                'Code' => $this->owner->getProduct()->Code,
                'Expires:GreaterThan' => date('Y-m-d H:i:s', strtotime('now')),
            ])->count();
        $sold = $this->owner->getProduct()->getNumberPurchased();

        if ($reserved + $sold >= $this->owner->getProduct()->PurchaseLimit) {
            return true;
        }

        return false;
    }
}
