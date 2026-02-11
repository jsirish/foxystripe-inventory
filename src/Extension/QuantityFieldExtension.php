<?php

namespace Dynamic\FoxyStripe\Extension;

use SilverStripe\Core\Extension;

/**
 * Class QuantityFieldExtension
 * @package Dynamic\FoxyStripe\Extension
 *
 * @property-read \Dynamic\FoxyStripe\Form\QuantityField $owner
 */
class QuantityFieldExtension extends Extension
{
    public function onBeforeRender()
    {
        if (!$this->owner->getProduct()->hasMethod('getHasInventory')) {
            return;
        }

        if (!$this->owner->getProduct()->getHasInventory()) {
            return;
        }

        $this->owner->setAttribute(
            'data-limit',
            $this->owner->getProduct()->getNumberAvailable()
        );
    }

    /**
     * Limit the quantity to the number available
     * @param $quantity
     */
    public function updateQuantity(&$quantity)
    {
        if (!$this->owner->getProduct()->hasMethod('getHasInventory')) {
            return;
        }

        if (!$this->owner->getProduct()->getHasInventory()) {
            return;
        }

        if ($quantity >= $this->owner->getProduct()->getNumberAvailable()) {
            $quantity = $this->owner->getProduct()->getNumberAvailable();
        }
    }

    /**
     * Adds limit
     * @param $data
     */
    public function updateData(&$data)
    {
        if (!$this->owner->getProduct()->hasMethod('getHasInventory')) {
            return;
        }

        if (!$this->owner->getProduct()->getHasInventory()) {
            return;
        }

        $data['limit'] = (int) $this->owner->getProduct()->getNumberAvailable();
    }
}
